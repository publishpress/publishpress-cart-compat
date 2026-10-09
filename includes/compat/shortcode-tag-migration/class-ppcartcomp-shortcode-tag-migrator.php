<?php

if (! defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared -- LIKE/IN fragments are built from escaped patterns; backup table name is plugin-owned.

/**
 * Merchant-run leftover → canonical shortcode rewrite in stored content.
 */
class PPCartComp_Shortcode_Tag_Migrator
{
    public const CHUNK_SIZE   = 100;
    public const TABLE_SUFFIX = 'ppcart_shortcode_tag_migration_backup';
    public const STEP1_LOCK   = 'ppcart_shortcode_tag_migration_step1_lock';

    /**
     * @return string
     */
    public static function table_name()
    {
        global $wpdb;

        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    /**
     * @return void
     */
    public static function maybe_create_table()
    {
        global $wpdb;

        $table           = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();
        $sql             = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			object_kind varchar(32) NOT NULL,
			object_id varchar(191) NOT NULL,
			old_value longtext NOT NULL,
			new_value varchar(191) NOT NULL,
			step tinyint(3) unsigned NOT NULL DEFAULT 1,
			migrated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY object_kind (object_kind),
			KEY step (step)
		) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * @return array<string, mixed>
     */
    public static function run_chunk()
    {
        if (! current_user_can('manage_options')) {
            return [ 'ok' => false, 'error' => 'forbidden' ];
        }

        $state = ppcart_shortcode_tag_migration_get_state();
        if ('complete' === $state['step1']) {
            return [ 'ok' => true, 'done' => true, 'step' => 1 ];
        }

        if (! empty($state['stop_requested']) && 'running' === $state['step1']) {
            ppcart_shortcode_tag_migration_save_state(
                [
                    'step1'          => 'paused',
                    'stop_requested' => false,
                ]
            );

            return [ 'ok' => true, 'paused' => true, 'step' => 1 ];
        }

        if (function_exists('get_transient') && get_transient(self::STEP1_LOCK)) {
            return [ 'ok' => true, 'busy' => true, 'step' => 1 ];
        }

        if (function_exists('set_transient')) {
            set_transient(self::STEP1_LOCK, 1, 60);
        }

        self::maybe_create_table();
        ppcart_shortcode_tag_migration_save_state(
            [
                'step1' => 'running',
                'mode'  => 'legacy',
            ]
        );

        try {
            $moved = self::migrate_chunk();
        } finally {
            if (function_exists('delete_transient')) {
                delete_transient(self::STEP1_LOCK);
            }
        }

        if ($moved > 0) {
            return [ 'ok' => true, 'done' => false, 'moved' => $moved, 'step' => 1 ];
        }

        ppcart_shortcode_tag_migration_save_state(
            [
                'step1' => 'complete',
                'mode'  => 'canonical',
            ]
        );

        return [ 'ok' => true, 'done' => true, 'step' => 1 ];
    }

    /**
     * @return array{posts: int, options: int, postmeta: int, elementor: int}
     */
    public static function count_remaining()
    {
        return [
            'posts'     => self::count_leftover_posts(),
            'options'   => self::count_leftover_options(),
            'postmeta'  => self::count_leftover_notification_meta(),
            'elementor' => PPCartComp_Shortcode_Tag_Elementor::count_remaining(),
        ];
    }

    /**
     * @return int
     */
    private static function migrate_chunk()
    {
        $moved = self::migrate_post_chunk();
        if ($moved > 0) {
            return $moved;
        }

        $moved = self::migrate_email_options();
        if ($moved > 0) {
            return $moved;
        }

        $moved = self::migrate_notification_meta_chunk();
        if ($moved > 0) {
            return $moved;
        }

        return PPCartComp_Shortcode_Tag_Elementor::migrate_chunk();
    }

    /**
     * @return int
     */
    private static function migrate_post_chunk()
    {
        global $wpdb;

        $ids = self::query_leftover_post_ids(self::CHUNK_SIZE);
        if ([] === $ids) {
            return 0;
        }

        $moved = 0;
        foreach ($ids as $id) {
            $post = get_post($id);
            if (! $post) {
                continue;
            }

            $content = (string) $post->post_content;
            $excerpt = (string) $post->post_excerpt;
            $next_content = ppcart_rewrite_leftover_shortcode_tags($content);
            $next_excerpt = ppcart_rewrite_leftover_shortcode_tags($excerpt);

            if ($next_content === $content && $next_excerpt === $excerpt) {
                continue;
            }

            if ($next_content !== $content) {
                self::insert_backup('post_content', (string) $id, $content, 'rewritten');
            }
            if ($next_excerpt !== $excerpt) {
                self::insert_backup('post_excerpt', (string) $id, $excerpt, 'rewritten');
            }

            $wpdb->update(
                $wpdb->posts,
                [
                    'post_content' => $next_content,
                    'post_excerpt' => $next_excerpt,
                ],
                [ 'ID' => $id ],
                [ '%s', '%s' ],
                [ '%d' ]
            );
            if (function_exists('clean_post_cache')) {
                clean_post_cache($id);
            }
            ++$moved;
        }

        return $moved;
    }

    /**
     * @return int
     */
    private static function migrate_email_options()
    {
        $moved = 0;
        foreach (ppcart_shortcode_tag_migration_email_option_keys() as $option) {
            $value = get_option($option, false);
            if (! is_string($value) || ! ppcart_value_has_leftover_email_tags($value)) {
                continue;
            }

            $rewritten = ppcart_rewrite_leftover_email_tags($value);
            if ($rewritten === $value) {
                continue;
            }

            self::insert_backup('option', $option, $value, 'rewritten');
            update_option($option, $rewritten, false);
            ++$moved;
        }

        return $moved;
    }

    /**
     * @return int
     */
    private static function migrate_notification_meta_chunk()
    {
        global $wpdb;

        $ids = self::query_leftover_notification_meta_ids(self::CHUNK_SIZE);
        if ([] === $ids) {
            return 0;
        }

        $moved = 0;
        foreach ($ids as $row) {
            $post_id  = (int) $row['post_id'];
            $meta_key = (string) $row['meta_key'];
            $value    = get_post_meta($post_id, $meta_key, true);
            if (! ppcart_value_has_leftover_email_tags($value)) {
                continue;
            }

            $rewritten = ppcart_rewrite_leftover_email_tags_in_value($value);
            self::insert_backup(
                'postmeta',
                $post_id . ':' . $meta_key,
                maybe_serialize($value),
                'rewritten'
            );
            update_post_meta($post_id, $meta_key, $rewritten);
            ++$moved;
        }

        return $moved;
    }

    /**
     * @param int $limit Limit.
     * @return array<int, int>
     */
    private static function query_leftover_post_ids($limit)
    {
        global $wpdb;

        $sql = self::leftover_like_sql("{$wpdb->posts}.post_content")
            . ' OR ' . self::leftover_like_sql("{$wpdb->posts}.post_excerpt");
        if ('' === $sql) {
            return [];
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Leftover-tag scan uses escaped LIKE fragments.
        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE ({$sql}) ORDER BY ID ASC LIMIT %d",
                $limit
            )
        );

        return array_map('intval', is_array($ids) ? $ids : []);
    }

    /**
     * @param int $limit Limit.
     * @return array<int, array{post_id: int, meta_key: string}>
     */
    private static function query_leftover_notification_meta_ids($limit)
    {
        global $wpdb;

        $keys = ppcart_shortcode_tag_migration_notification_meta_keys();
        $like = self::leftover_like_sql("{$wpdb->postmeta}.meta_value", ppcart_email_tag_migration_like_patterns());
        if ('' === $like || [] === $keys) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($keys), '%s'));
        $args         = array_merge($keys, [ $limit ]);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.ReplacementsWrongNumber -- Key list plus leftover LIKE scan.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT post_id, meta_key FROM {$wpdb->postmeta} WHERE meta_key IN ({$placeholders}) AND ({$like}) ORDER BY meta_id ASC LIMIT %d",
                $args
            ),
            ARRAY_A
        );

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return int
     */
    private static function count_leftover_posts()
    {
        global $wpdb;

        $sql = self::leftover_like_sql("{$wpdb->posts}.post_content")
            . ' OR ' . self::leftover_like_sql("{$wpdb->posts}.post_excerpt");
        if ('' === $sql || ! isset($wpdb->posts)) {
            return 0;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- Leftover-tag count.
        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->posts} WHERE ({$sql})");
    }

    /**
     * @return int
     */
    private static function count_leftover_options()
    {
        $count = 0;
        foreach (ppcart_shortcode_tag_migration_email_option_keys() as $option) {
            $value = get_option($option, false);
            if (is_string($value) && ppcart_value_has_leftover_email_tags($value)) {
                ++$count;
            }
        }

        return $count;
    }

    /**
     * @return int
     */
    private static function count_leftover_notification_meta()
    {
        global $wpdb;

        $keys = ppcart_shortcode_tag_migration_notification_meta_keys();
        $like = self::leftover_like_sql("{$wpdb->postmeta}.meta_value", ppcart_email_tag_migration_like_patterns());
        if ('' === $like || [] === $keys || empty($wpdb->postmeta)) {
            return 0;
        }

        $placeholders = implode(',', array_fill(0, count($keys), '%s'));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- Notification-meta leftover count.
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key IN ({$placeholders}) AND ({$like})",
                $keys
            )
        );
    }

    /**
     * @param string $column Qualified column name.
     * @return string
     */
    private static function leftover_like_sql($column, $patterns = null)
    {
        global $wpdb;

        $parts = [];
        foreach (null === $patterns ? ppcart_shortcode_tag_migration_like_patterns() : $patterns as $pattern) {
            $parts[] = $wpdb->prepare("{$column} LIKE %s", $pattern);
        }

        return implode(' OR ', $parts);
    }

    /**
     * @param string $kind      Backup kind.
     * @param string $object_id Object id.
     * @param string $old_value Original value.
     * @param string $new_value Marker.
     * @return void
     */
    public static function insert_backup($kind, $object_id, $old_value, $new_value)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Reverse-map backup.
        $wpdb->insert(
            self::table_name(),
            [
                'object_kind' => $kind,
                'object_id'   => $object_id,
                'old_value'   => $old_value,
                'new_value'   => $new_value,
                'step'        => 1,
                'migrated_at' => current_time('mysql'),
            ],
            [ '%s', '%s', '%s', '%s', '%d', '%s' ]
        );
    }

    /**
     * Restore rewritten posts, email options, and notification meta.
     *
     * @return array<string, mixed>
     */
    public static function restore()
    {
        if (! current_user_can('manage_options')) {
            return [ 'ok' => false, 'error' => 'forbidden' ];
        }

        self::maybe_create_table();
        self::restore_rows();

        ppcart_shortcode_tag_migration_save_state(
            [
                'step1'            => 'idle',
                'mode'             => 'legacy',
                'stop_requested'   => false,
                'cursor'           => null,
                'elementor_cursor' => 0,
            ]
        );

        return [ 'ok' => true ];
    }

    /**
     * @return void
     */
    private static function restore_rows()
    {
        global $wpdb;

        $table = self::table_name();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Restore scan.
        $rows = $wpdb->get_results("SELECT * FROM {$table} WHERE step = 1 ORDER BY id DESC", ARRAY_A);
        if (! is_array($rows)) {
            return;
        }

        foreach ($rows as $row) {
            $kind = (string) $row['object_kind'];
            $id   = (string) $row['object_id'];
            $old  = (string) $row['old_value'];

            if ('post_content' === $kind) {
                $wpdb->update($wpdb->posts, [ 'post_content' => $old ], [ 'ID' => (int) $id ], [ '%s' ], [ '%d' ]);
                clean_post_cache((int) $id);
                continue;
            }

            if ('post_excerpt' === $kind) {
                $wpdb->update($wpdb->posts, [ 'post_excerpt' => $old ], [ 'ID' => (int) $id ], [ '%s' ], [ '%d' ]);
                clean_post_cache((int) $id);
                continue;
            }

            if ('option' === $kind) {
                update_option($id, $old, false);
                continue;
            }

            if (PPCartComp_Shortcode_Tag_Elementor::BACKUP_KIND === $kind) {
                PPCartComp_Shortcode_Tag_Elementor::restore_row($id, $old);
                continue;
            }

            if ('postmeta' === $kind && false !== strpos($id, ':')) {
                [ $post_id, $meta_key ] = explode(':', $id, 2);
                update_post_meta((int) $post_id, $meta_key, maybe_unserialize($old));
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function delete_backup()
    {
        global $wpdb;

        if (! current_user_can('manage_options')) {
            return [ 'ok' => false, 'error' => 'forbidden' ];
        }

        $table = self::table_name();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Merchant-requested backup drop.
        $wpdb->query("DROP TABLE IF EXISTS {$table}");

        return [ 'ok' => true ];
    }
}
