<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Chunked leftover `_sc_*` post/user-meta key migrator. Never runs unless asked.
 */
class PPCartComp_Meta_Key_Migrator
{
    public const CHUNK_SIZE = 500;
    public const TABLE_SUFFIX = 'ppcart_meta_key_migration_backup';
    public const STEP1_LOCK = 'ppcart_meta_key_migration_step1_lock';

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
     * Run one chunk. Returns progress payload.
     *
     * @return array<string, mixed>
     */
    public static function run_chunk()
    {
        if (! current_user_can('manage_options')) {
            return [ 'ok' => false, 'error' => 'forbidden' ];
        }

        $state = ppcart_meta_key_migration_get_state();
        if ('complete' === $state['step1']) {
            return [ 'ok' => true, 'done' => true, 'step' => 1 ];
        }

        if (! empty($state['stop_requested']) && 'running' === $state['step1']) {
            ppcart_meta_key_migration_save_state(
                [
                    'step1'          => 'paused',
                    'stop_requested' => false,
                ]
            );

            return [ 'ok' => true, 'paused' => true, 'step' => 1 ];
        }

        if (get_transient(self::STEP1_LOCK)) {
            return [ 'ok' => true, 'busy' => true, 'step' => 1 ];
        }

        set_transient(self::STEP1_LOCK, 1, 60);
        self::maybe_create_table();

        ppcart_meta_key_migration_save_state(
            [
                'step1' => 'running',
                'mode'  => 'legacy',
            ]
        );

        $moved = self::migrate_chunk();
        delete_transient(self::STEP1_LOCK);

        $remaining = self::count_remaining();
        $done      = 0 === $remaining['postmeta'] && 0 === $remaining['usermeta'];

        if ($done) {
            ppcart_meta_key_migration_save_state(
                [
                    'step1'          => 'complete',
                    'mode'           => 'canonical',
                    'stop_requested' => false,
                ]
            );

            return [ 'ok' => true, 'done' => true, 'step' => 1, 'moved' => $moved, 'remaining' => $remaining ];
        }

        return [ 'ok' => true, 'done' => false, 'step' => 1, 'moved' => $moved, 'remaining' => $remaining ];
    }

    /**
     * @return array{postmeta: int, usermeta: int}
     */
    public static function count_remaining()
    {
        global $wpdb;

        $like = $wpdb->esc_like('_sc_') . '%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Remaining leftover-key count.
        $postmeta = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key LIKE %s",
                $like
            )
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Remaining leftover-key count.
        $usermeta = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key LIKE %s",
                $like
            )
        );

        return [
            'postmeta' => $postmeta,
            'usermeta' => $usermeta,
        ];
    }

    /**
     * @return int Rows processed.
     */
    private static function migrate_chunk()
    {
        $moved = self::migrate_table_chunk('post');
        if ($moved > 0) {
            return $moved;
        }

        return self::migrate_table_chunk('user');
    }

    /**
     * @param string $kind post|user.
     * @return int
     */
    private static function migrate_table_chunk($kind)
    {
        global $wpdb;

        $is_user = 'user' === $kind;
        $table   = $is_user ? $wpdb->usermeta : $wpdb->postmeta;
        $id_col  = $is_user ? 'umeta_id' : 'meta_id';
        $like    = $wpdb->esc_like('_sc_') . '%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Chunked leftover-key scan.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT {$id_col} AS meta_id, meta_key FROM {$table} WHERE meta_key LIKE %s ORDER BY {$id_col} ASC LIMIT %d",
                $like,
                self::CHUNK_SIZE
            ),
            ARRAY_A
        );

        if (! is_array($rows) || [] === $rows) {
            return 0;
        }

        $moved = 0;
        foreach ($rows as $row) {
            self::migrate_one_row($kind, (int) $row['meta_id'], (string) $row['meta_key']);
            ++$moved;
        }

        return $moved;
    }

    /**
     * Canonical wins when both keys exist. Otherwise rename leftover → canonical.
     *
     * @param string $kind     post|user.
     * @param int    $meta_id  Row ID.
     * @param string $legacy   Leftover key.
     * @return void
     */
    private static function migrate_one_row($kind, $meta_id, $legacy)
    {
        global $wpdb;

        if (! function_exists('ppcart_meta_key') || ! ppcart_is_legacy_meta_key($legacy)) {
            return;
        }

        $canonical = ppcart_meta_key($legacy);
        if ($canonical === $legacy) {
            return;
        }

        $is_user = 'user' === $kind;
        $table   = $is_user ? $wpdb->usermeta : $wpdb->postmeta;
        $id_col  = $is_user ? 'umeta_id' : 'meta_id';

        self::insert_backup($kind, (string) $meta_id, $legacy, $canonical);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Twin-key lookup.
        $object_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT " . ( $is_user ? 'user_id' : 'post_id' ) . " FROM {$table} WHERE {$id_col} = %d",
                $meta_id
            )
        );

        if (! $object_id) {
            return;
        }

        $owner_col = $is_user ? 'user_id' : 'post_id';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Twin-key lookup.
        $canonical_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT {$id_col} FROM {$table} WHERE {$owner_col} = %d AND meta_key = %s LIMIT 1",
                $object_id,
                $canonical
            )
        );

        if ($canonical_id) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Canonical wins.
            $wpdb->delete($table, [ $id_col => $meta_id ], [ '%d' ]);
        } else {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Rename leftover key.
            $wpdb->update($table, [ 'meta_key' => $canonical ], [ $id_col => $meta_id ], [ '%s' ], [ '%d' ]);
        }

        if (function_exists('wp_cache_delete')) {
            wp_cache_delete($object_id, $is_user ? 'user_meta' : 'post_meta');
        }
    }

    /**
     * @param string $kind       post|user.
     * @param string $object_id  Meta row ID.
     * @param string $old_value  Leftover key.
     * @param string $new_value  Canonical key.
     * @return void
     */
    private static function insert_backup($kind, $object_id, $old_value, $new_value)
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
     * Restore completed chunks. Canonical rows that replaced leftover are renamed back
     * only when the leftover key is gone.
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

        ppcart_meta_key_migration_save_state(
            [
                'step1'          => 'idle',
                'mode'           => 'legacy',
                'stop_requested' => false,
                'cursor'         => null,
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
            $is_user   = 'user' === $row['object_kind'];
            $meta_table = $is_user ? $wpdb->usermeta : $wpdb->postmeta;
            $id_col    = $is_user ? 'umeta_id' : 'meta_id';
            $meta_id   = (int) $row['object_id'];
            $legacy    = (string) $row['old_value'];
            $canonical = (string) $row['new_value'];

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Restore lookup.
            $current = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT meta_key FROM {$meta_table} WHERE {$id_col} = %d",
                    $meta_id
                ),
                ARRAY_A
            );

            if (is_array($current) && $canonical === $current['meta_key']) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Restore leftover key.
                $wpdb->update($meta_table, [ 'meta_key' => $legacy ], [ $id_col => $meta_id ], [ '%s' ], [ '%d' ]);
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
