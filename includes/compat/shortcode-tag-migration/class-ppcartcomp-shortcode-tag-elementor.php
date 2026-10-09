<?php

if (! defined('ABSPATH')) {
    exit;
}

// phpcs:disable WordPress.DB.PreparedSQL.InterpolatedNotPrepared,WordPress.DB.PreparedSQL.NotPrepared -- LIKE fragments are built from escaped patterns.

/**
 * Leftover shortcode tags inside Elementor `_elementor_data` post meta.
 *
 * Elementor renders from this JSON, not from post_content. Rows are read and
 * written raw by meta_id: update_post_meta() would unslash the JSON.
 */
class PPCartComp_Shortcode_Tag_Elementor
{
    public const META_KEY    = '_elementor_data';
    public const BACKUP_KIND = 'elementor_data';

    /**
     * Rewrites one chunk and writes a reverse-map backup per row.
     *
     * @param int $limit Rows per chunk.
     * @return int Rows rewritten.
     */
    public static function migrate_chunk($limit)
    {
        $moved = 0;
        foreach (self::query_rows($limit) as $row) {
            $meta_id = (int) $row['meta_id'];
            $post_id = (int) $row['post_id'];
            $value   = (string) $row['meta_value'];
            $next    = ppcart_rewrite_leftover_shortcode_tags($value);
            if ($next === $value) {
                continue;
            }

            PPCartComp_Shortcode_Tag_Migrator::insert_backup(self::BACKUP_KIND, (string) $meta_id, $value, 'rewritten');
            self::write($meta_id, $post_id, $next);
            ++$moved;
        }

        return $moved;
    }

    /**
     * @return int
     */
    public static function count_remaining()
    {
        global $wpdb;

        $like = self::like_sql();
        if ('' === $like || empty($wpdb->postmeta)) {
            return 0;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Leftover-tag count.
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND ({$like})",
                self::META_KEY
            )
        );
    }

    /**
     * Puts a backed-up `_elementor_data` value back.
     *
     * @param string $meta_id   Backup object id.
     * @param string $old_value Original raw meta value.
     * @return void
     */
    public static function restore_row($meta_id, $old_value)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Restore lookup.
        $post_id = (int) $wpdb->get_var(
            $wpdb->prepare("SELECT post_id FROM {$wpdb->postmeta} WHERE meta_id = %d", (int) $meta_id)
        );
        if ($post_id > 0) {
            self::write((int) $meta_id, $post_id, $old_value);
        }
    }

    /**
     * @param int    $meta_id Meta row id.
     * @param int    $post_id Post id.
     * @param string $value   Raw meta value.
     * @return void
     */
    private static function write($meta_id, $post_id, $value)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Raw JSON write keeps Elementor's slashes.
        $wpdb->update($wpdb->postmeta, [ 'meta_value' => $value ], [ 'meta_id' => $meta_id ], [ '%s' ], [ '%d' ]);
        // Elementor caches rendered widget output, including shortcodes.
        delete_post_meta($post_id, '_elementor_element_cache');
        wp_cache_delete($post_id, 'post_meta');
    }

    /**
     * @param int $limit Limit.
     * @return array<int, array{meta_id: string, post_id: string, meta_value: string}>
     */
    private static function query_rows($limit)
    {
        global $wpdb;

        $like = self::like_sql();
        if ('' === $like) {
            return [];
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Leftover-tag scan uses escaped LIKE fragments.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_id, post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND ({$like}) ORDER BY meta_id ASC LIMIT %d",
                self::META_KEY,
                $limit
            ),
            ARRAY_A
        );

        return is_array($rows) ? $rows : [];
    }

    /**
     * @return string
     */
    private static function like_sql()
    {
        global $wpdb;

        $parts = [];
        foreach (ppcart_shortcode_tag_migration_like_patterns() as $pattern) {
            $parts[] = $wpdb->prepare('meta_value LIKE %s', $pattern);
        }

        return implode(' OR ', $parts);
    }
}
