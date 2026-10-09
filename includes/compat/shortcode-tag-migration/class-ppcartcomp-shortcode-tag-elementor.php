<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Leftover shortcode tags inside Elementor `_elementor_data` post meta.
 *
 * Elementor renders from this JSON, not from post_content. Rows are scanned
 * by meta_id cursor and matched in PHP: a LIKE scan over large Elementor
 * stores is too slow for one AJAX chunk. Rows are written raw by meta_id
 * because update_post_meta() would unslash the JSON.
 */
class PPCartComp_Shortcode_Tag_Elementor
{
    public const META_KEY    = '_elementor_data';
    public const BACKUP_KIND = 'elementor_data';
    public const BATCH_SIZE  = 200;

    /**
     * Scans one batch after the cursor and rewrites rows with leftover tags.
     *
     * @return int Rows scanned. 0 when the scan is finished.
     */
    public static function migrate_chunk()
    {
        $state  = ppcart_shortcode_tag_migration_get_state();
        $cursor = (int) $state['elementor_cursor'];
        $rows   = self::query_rows($cursor, self::BATCH_SIZE);
        if ([] === $rows) {
            return 0;
        }

        foreach ($rows as $row) {
            $meta_id = (int) $row['meta_id'];
            $value   = (string) $row['meta_value'];
            $next    = ppcart_rewrite_leftover_shortcode_tags($value);
            $cursor  = $meta_id;
            if ($next === $value) {
                continue;
            }

            PPCartComp_Shortcode_Tag_Migrator::insert_backup(self::BACKUP_KIND, (string) $meta_id, $value, 'rewritten');
            self::write($meta_id, (int) $row['post_id'], $next);
        }

        ppcart_shortcode_tag_migration_save_state([ 'elementor_cursor' => $cursor ]);

        return count($rows);
    }

    /**
     * Elementor rows not scanned yet.
     *
     * @return int
     */
    public static function count_remaining()
    {
        global $wpdb;

        if (empty($wpdb->postmeta)) {
            return 0;
        }

        $state = ppcart_shortcode_tag_migration_get_state();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Indexed count of rows after the cursor.
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_id > %d",
                self::META_KEY,
                (int) $state['elementor_cursor']
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
     * @param int $cursor Last scanned meta_id.
     * @param int $limit  Limit.
     * @return array<int, array{meta_id: string, post_id: string, meta_value: string}>
     */
    private static function query_rows($cursor, $limit)
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Cursor batch over Elementor rows.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_id, post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_id > %d ORDER BY meta_id ASC LIMIT %d",
                self::META_KEY,
                $cursor,
                $limit
            ),
            ARRAY_A
        );

        return is_array($rows) ? $rows : [];
    }
}
