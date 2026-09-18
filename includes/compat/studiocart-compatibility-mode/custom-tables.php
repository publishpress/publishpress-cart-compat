<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_custom_table_map')) {
    /**
     * Canonical suffix => leftover suffixes for Compatibility Mode SQL rewrite.
     *
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_custom_table_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/custom-tables.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ppcart_legacy_custom_table_rewrite_pairs')) {
    /**
     * Leftover prefixed table (and object-id column) => live name, longest first.
     *
     * Only includes pairs where leftover differs from live (migrated stores).
     *
     * @return array<int, array{0: string, 1: string}>
     */
    function ppcart_legacy_custom_table_rewrite_pairs()
    {
        if (isset($GLOBALS['ppcart_legacy_custom_table_rewrite_pairs_cache']) && is_array($GLOBALS['ppcart_legacy_custom_table_rewrite_pairs_cache'])) {
            return $GLOBALS['ppcart_legacy_custom_table_rewrite_pairs_cache'];
        }

        if (! empty($GLOBALS['ppcart_legacy_custom_table_rewrite_building'])) {
            return [];
        }

        if (! function_exists('ppcart_custom_table_family_map')) {
            return [];
        }

        $GLOBALS['ppcart_legacy_custom_table_rewrite_building'] = true;
        $pairs = [];

        try {
            foreach (array_keys(ppcart_custom_table_family_map()) as $family) {
                $names = function_exists('ppcart_custom_table_prefixed_names')
                    ? ppcart_custom_table_prefixed_names($family)
                    : null;
                if (! $names || '' === $names['leftover']) {
                    continue;
                }

                $live = function_exists('ppcart_live_table') ? ppcart_live_table($family) : $names['canonical'];
                if (is_string($live) && '' !== $live && $names['leftover'] !== $live) {
                    $pairs[] = [ $names['leftover'], $live ];
                }

                $spec = ppcart_custom_table_family($family);
                if (! $spec || empty($spec['object_id_column'])) {
                    continue;
                }

                $leftover_column = (string) $spec['object_id_column']['leftover'];
                $live_column     = (string) $spec['object_id_column']['canonical'];
                if ('' === $leftover_column) {
                    continue;
                }
                if ('affiliatemeta' === $family && function_exists('ppcart_live_affiliate_id_column')) {
                    $live_column = ppcart_live_affiliate_id_column();
                } elseif ('order_itemmeta' === $family && function_exists('ppcart_live_order_item_id_column')) {
                    $live_column = ppcart_live_order_item_id_column();
                }

                if ('' !== $live_column && $leftover_column !== $live_column) {
                    $pairs[] = [ $leftover_column, $live_column ];
                }
            }

            usort(
                $pairs,
                static function ($left, $right) {
                    return strlen((string) $right[0]) <=> strlen((string) $left[0]);
                }
            );

            $GLOBALS['ppcart_legacy_custom_table_rewrite_pairs_cache'] = $pairs;
        } finally {
            $GLOBALS['ppcart_legacy_custom_table_rewrite_building'] = false;
        }

        return $pairs;
    }
}

if (! function_exists('ppcart_rewrite_legacy_custom_table_sql')) {
    /**
     * Rewrite leftover table names in SQL onto the live name.
     *
     * Canonical SQL is left alone. Does not rename stored tables.
     *
     * @param string $query SQL.
     * @return string
     */
    function ppcart_rewrite_legacy_custom_table_sql($query)
    {
        static $depth = 0;

        if (! is_string($query) || '' === $query) {
            return $query;
        }

        // Must run before is_enabled()/live_table(): those call get_option / SHOW TABLES.
        if ($depth > 0 || ! empty($GLOBALS['ppcart_legacy_custom_table_rewrite_building'])) {
            return $query;
        }

        ++$depth;

        try {
            if (
                ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
                || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
            ) {
                return $query;
            }

            // Schema/DDL must stay literal so SHOW COLUMNS / ALTER / RENAME see leftover names.
            if (preg_match('/^\s*(SHOW|DESCRIBE|DESC|EXPLAIN|ALTER|RENAME|CREATE|DROP|TRUNCATE)\b/i', $query)) {
                return $query;
            }

            foreach (ppcart_legacy_custom_table_rewrite_pairs() as $pair) {
                if ('' === (string) $pair[0]) {
                    continue;
                }
                $query = str_replace($pair[0], $pair[1], $query);
            }
        } finally {
            --$depth;
        }

        return $query;
    }
}

if (! function_exists('ppcart_register_legacy_custom_table_bridges')) {
    /**
     * @return void
     */
    function ppcart_register_legacy_custom_table_bridges()
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return;
        }

        ppcart_legacy_custom_table_rewrite_pairs();
        ppcart_register_legacy_affiliate_meta_alias();

        if (function_exists('add_filter')) {
            add_filter('query', 'ppcart_rewrite_legacy_custom_table_sql');
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_custom_table_bridges')) {
    /**
     * Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_custom_table_bridges()
    {
        if (function_exists('remove_filter')) {
            remove_filter('query', 'ppcart_rewrite_legacy_custom_table_sql');
        }
    }
}

if (! function_exists('ppcart_register_legacy_affiliate_meta_alias')) {
    /**
     * Point leftover $wpdb->ncs_affiliatemeta at the live table while Compatibility Mode is on.
     *
     * @return void
     */
    function ppcart_register_legacy_affiliate_meta_alias()
    {
        global $wpdb;

        if (! isset($wpdb) || ! is_object($wpdb)) {
            return;
        }

        if (! function_exists('ppcart_live_table')) {
            return;
        }

        $table = ppcart_live_table('affiliatemeta');
        if (is_string($table) && '' !== $table) {
            $wpdb->ncs_affiliatemeta = $table;
        }
    }
}
