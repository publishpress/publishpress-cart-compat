<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_integration_repeater_field_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_integration_repeater_field_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/integration-repeater-fields.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_get_legacy_integration_service_id_map')) {
    /**
     * Canonical integration `services` values mapped to leftover ids.
     *
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_integration_service_id_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/integration-service-ids.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ppcart_copy_legacy_integration_repeater_rows')) {
    /**
     * Copy leftover inner keys onto canonical keys. Canonical wins.
     *
     * Remaps leftover `services` values onto canonical ids. Canonical
     * `services` wins. Leftover keys stay in place. Does not write stored rows.
     *
     * @param mixed $rows Repeater rows.
     * @return mixed
     */
    function ppcart_copy_legacy_integration_repeater_rows($rows)
    {
        if (! is_array($rows)) {
            return $rows;
        }

        $map = ncs_cart_get_legacy_integration_repeater_field_map();
        $service_map = ncs_cart_get_legacy_integration_service_id_map();

        foreach ($rows as $index => $row) {
            if (! is_array($row)) {
                continue;
            }

            foreach ($map as $canonical => $leftovers) {
                if (! is_string($canonical) || '' === $canonical || ! is_array($leftovers)) {
                    continue;
                }

                if (array_key_exists($canonical, $row)) {
                    continue;
                }

                foreach ($leftovers as $leftover) {
                    if (! is_string($leftover) || '' === $leftover) {
                        continue;
                    }

                    if (! array_key_exists($leftover, $row)) {
                        continue;
                    }

                    $row[ $canonical ] = $row[ $leftover ];
                    break;
                }
            }

            $row = ppcart_copy_legacy_integration_service_id($row, $service_map);

            $rows[ $index ] = $row;
        }

        return $rows;
    }
}

if (! function_exists('ppcart_copy_legacy_integration_service_id')) {
    /**
     * Remap leftover `services` onto the canonical id. Canonical wins.
     *
     * @param array<string, mixed>              $row          Repeater row.
     * @param array<string, array<int, string>> $service_map Canonical => leftover ids.
     * @return array<string, mixed>
     */
    function ppcart_copy_legacy_integration_service_id($row, $service_map)
    {
        if (! isset($row['services']) || ! is_string($row['services']) || '' === $row['services']) {
            return $row;
        }

        foreach ($service_map as $canonical => $leftovers) {
            if (! is_string($canonical) || '' === $canonical || ! is_array($leftovers)) {
                continue;
            }

            if ($row['services'] === $canonical) {
                return $row;
            }

            if (in_array($row['services'], $leftovers, true)) {
                $row['services'] = $canonical;
                return $row;
            }
        }

        return $row;
    }
}

if (! function_exists('ppcart_maybe_copy_legacy_integration_repeater_rows')) {
    /**
     * Copy leftover inner keys only while Compatibility Mode is on.
     *
     * @param mixed $rows Repeater rows.
     * @return mixed
     */
    function ppcart_maybe_copy_legacy_integration_repeater_rows($rows)
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return $rows;
        }

        return ppcart_copy_legacy_integration_repeater_rows($rows);
    }
}

if (! function_exists('ppcart_filter_legacy_integration_repeater_saved_rows')) {
    /**
     * @param mixed  $rows     Repeater rows.
     * @param string $field_id Repeater field id.
     * @return mixed
     */
    function ppcart_filter_legacy_integration_repeater_saved_rows($rows, $field_id)
    {
        if ('_ppcart_integrations' !== $field_id && '_sc_integrations' !== $field_id) {
            return $rows;
        }

        return ppcart_maybe_copy_legacy_integration_repeater_rows($rows);
    }
}

add_filter('ppcart_repeater_saved_rows', 'ppcart_filter_legacy_integration_repeater_saved_rows', 10, 2);

if (! function_exists('ppcart_migrate_integration_repeater_inner_keys')) {
    /**
     * Merchant Data migration: copy leftover inner keys onto canonical keys
     * inside stored integration rows. Canonical wins. Leftover inner keys stay.
     *
     * Call only from Data migration. Never from boot or read-through.
     *
     * @return int Number of stored rows rewritten.
     */
    function ppcart_migrate_integration_repeater_inner_keys()
    {
        global $wpdb;

        if (! isset($wpdb->postmeta) || ! function_exists('ppcart_copy_legacy_integration_repeater_rows')) {
            return 0;
        }

        if (! is_object($wpdb) || ! method_exists($wpdb, 'get_results') || ! method_exists($wpdb, 'update')) {
            return 0;
        }

        $keys         = [ '_ppcart_integrations', '_sc_integrations' ];
        $placeholders = implode(',', array_fill(0, count($keys), '%s'));

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Merchant Data migration scan of stored integration rows.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT meta_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key IN ({$placeholders})",
                $keys
            ),
            defined('ARRAY_A') ? ARRAY_A : 'ARRAY_A'
        );

        if (! is_array($rows) || [] === $rows) {
            return 0;
        }

        $updated = 0;

        foreach ($rows as $row) {
            if (! is_array($row) || ! isset($row['meta_id'])) {
                continue;
            }

            $value  = maybe_unserialize(isset($row['meta_value']) ? $row['meta_value'] : '');
            $copied = ppcart_copy_legacy_integration_repeater_rows($value);

            if ($copied === $value) {
                continue;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Writes canonical inner keys onto the stored blob.
            $wpdb->update(
                $wpdb->postmeta,
                [ 'meta_value' => maybe_serialize($copied) ],
                [ 'meta_id' => (int) $row['meta_id'] ],
                [ '%s' ],
                [ '%d' ]
            );

            ++$updated;
        }

        return $updated;
    }
}
