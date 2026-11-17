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
            $next    = self::rewrite_leftover_widgets(ppcart_rewrite_leftover_shortcode_tags($value));
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
     * Rewrite leftover StudioCart widgets (sc-orderform, bump-*, ...) to the
     * PublishPress Cart widgets, so pages no longer need Compatibility Mode.
     *
     * @param string $value Raw `_elementor_data` JSON.
     * @return string Raw JSON, unchanged when it has no leftover widgets.
     */
    public static function rewrite_leftover_widgets($value)
    {
        if (! self::has_leftover_widget($value)) {
            return $value;
        }

        self::load_widget_helpers();
        if (! function_exists('ppcartcomp_rewrite_elementor_elements')) {
            return $value;
        }

        $elements = json_decode($value, true);
        if (! is_array($elements)) {
            return $value;
        }

        $rewritten = ppcartcomp_rewrite_elementor_elements($elements);
        if ($rewritten === $elements) {
            return $value;
        }

        // Elementor saves this meta with wp_json_encode(); keep the same encoding.
        $json = function_exists('wp_json_encode') ? wp_json_encode($rewritten) : json_encode($rewritten);

        return is_string($json) ? $json : $value;
    }

    /**
     * Whether raw `_elementor_data` JSON holds a leftover StudioCart widget.
     *
     * @param string $value Raw JSON.
     * @return bool
     */
    public static function has_leftover_widget($value)
    {
        foreach (self::leftover_widget_markers() as $marker) {
            if (false !== strpos((string) $value, $marker)) {
                return true;
            }
        }

        return false;
    }

    /**
     * `"widgetType":"<leftover>"` needles for each leftover widget name.
     *
     * @return string[]
     */
    public static function leftover_widget_markers()
    {
        self::load_widget_helpers();
        if (! function_exists('ncs_cart_get_legacy_elementor_widget_map')) {
            return [];
        }

        $markers = [];
        foreach (ncs_cart_get_legacy_elementor_widget_map() as $legacy) {
            $markers[] = '"widgetType":"' . $legacy . '"';
        }

        return $markers;
    }

    /**
     * Whether any `_elementor_data` row still holds a leftover widget.
     *
     * @return bool
     */
    public static function site_has_leftover_widgets()
    {
        global $wpdb;

        if (empty($wpdb->postmeta)) {
            return false;
        }

        $markers = self::leftover_widget_markers();
        if ([] === $markers) {
            return false;
        }

        $likes = [];
        $args  = [ self::META_KEY ];
        foreach ($markers as $marker) {
            $likes[] = 'meta_value LIKE %s';
            $args[]  = '%' . $wpdb->esc_like($marker) . '%';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- One-time check; placeholders are built above.
        $found = $wpdb->get_var($wpdb->prepare("SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND (" . implode(' OR ', $likes) . ') LIMIT 1', $args));

        return null !== $found;
    }

    /**
     * The widget maps and rewrite helpers live with Compatibility Mode, which
     * may be off while the Data migration runs.
     *
     * @return void
     */
    private static function load_widget_helpers()
    {
        if (function_exists('ppcartcomp_rewrite_elementor_elements')) {
            return;
        }

        $file = dirname(__DIR__) . '/studiocart-compatibility-mode/elementor-widgets.php';
        if (is_readable($file)) {
            require_once $file;
        }
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
        // Elementor caches rendered widget output, including shortcodes, and
        // per-widget CSS, so both are rebuilt for the new widget names.
        delete_post_meta($post_id, '_elementor_element_cache');
        delete_post_meta($post_id, '_elementor_css');
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
