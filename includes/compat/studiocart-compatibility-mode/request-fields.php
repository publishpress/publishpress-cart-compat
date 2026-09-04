<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_request_field_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_request_field_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/request-fields.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_get_legacy_request_field_names')) {
    /**
     * Leftover request keys for one canonical key.
     *
     * @param string $canonical_key Canonical request field name.
     * @return array<int, string>
     */
    function ncs_cart_get_legacy_request_field_names($canonical_key)
    {
        if (! is_string($canonical_key) || '' === $canonical_key) {
            return [];
        }

        $map = ncs_cart_get_legacy_request_field_map();

        if (! isset($map[ $canonical_key ]) || ! is_array($map[ $canonical_key ])) {
            return [];
        }

        return array_values(array_filter($map[ $canonical_key ], 'is_string'));
    }
}

if (! function_exists('ppcart_legacy_request_field_lookup')) {
    /**
     * Compatibility lookup for ppcart_filter_input().
     *
     * @param string $canonical_key Canonical request field name.
     * @return array<int, string>
     */
    function ppcart_legacy_request_field_lookup($canonical_key)
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return [];
        }

        return ncs_cart_get_legacy_request_field_names($canonical_key);
    }
}

if (! function_exists('ncs_cart_legacy_request_fields_copied')) {
    /**
     * @return bool
     */
    function &ncs_cart_legacy_request_fields_copied()
    {
        static $copied = false;

        return $copied;
    }
}

if (! function_exists('ncs_cart_legacy_admin_action_callbacks')) {
    /**
     * @return array<string, callable>
     */
    function &ncs_cart_legacy_admin_action_callbacks()
    {
        static $callbacks = [];

        return $callbacks;
    }
}

if (! function_exists('ppcart_copy_legacy_request_fields')) {
    /**
     * Copy leftover POST/GET/REQUEST keys onto canonical keys.
     *
     * Canonical wins when both are set. Leftover keys stay in place.
     * Does not map WordPress `action`.
     *
     * @return void
     */
    function ppcart_copy_legacy_request_fields()
    {
        $copied = &ncs_cart_legacy_request_fields_copied();

        if ($copied) {
            return;
        }

        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return;
        }

        $sources = [ '_POST', '_GET', '_REQUEST' ];

        foreach (ncs_cart_get_legacy_request_field_map() as $canonical => $leftovers) {
            if (! is_string($canonical) || '' === $canonical || 'action' === $canonical) {
                continue;
            }

            if (! is_array($leftovers)) {
                continue;
            }

            foreach ($sources as $source) {
                if (! isset($GLOBALS[ $source ]) || ! is_array($GLOBALS[ $source ])) {
                    continue;
                }

                if (array_key_exists($canonical, $GLOBALS[ $source ])) {
                    continue;
                }

                foreach ($leftovers as $leftover) {
                    if (! is_string($leftover) || '' === $leftover || 'action' === $leftover) {
                        continue;
                    }

                    if (! array_key_exists($leftover, $GLOBALS[ $source ])) {
                        continue;
                    }

                    $GLOBALS[ $source ][ $canonical ] = $GLOBALS[ $source ][ $leftover ];
                    break;
                }
            }
        }

        $copied = true;
    }
}

if (! function_exists('ppcart_register_legacy_admin_action_bridges')) {
    /**
     * Dual-register leftover admin_action_sc_duplicate_product when Compat is on.
     *
     * @return void
     */
    function ppcart_register_legacy_admin_action_bridges()
    {
        $registered = &ncs_cart_legacy_admin_action_callbacks();

        if (isset($registered['admin_action_sc_duplicate_product'])) {
            return;
        }

        $callback = static function () {
            if (
                ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
                || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
            ) {
                return;
            }

            do_action('admin_action_ppcart_duplicate_product');
        };

        add_action('admin_action_sc_duplicate_product', $callback, 0);
        $registered['admin_action_sc_duplicate_product'] = $callback;
    }
}

if (! function_exists('ppcart_unregister_legacy_admin_action_bridges')) {
    /**
     * Remove leftover admin_action bridges. Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_admin_action_bridges()
    {
        $registered = &ncs_cart_legacy_admin_action_callbacks();

        foreach ($registered as $hook => $callback) {
            remove_action($hook, $callback, 0);
        }

        $registered = [];
    }
}

if (! function_exists('ppcart_register_legacy_request_field_bridges')) {
    /**
     * Register leftover request-field copy, filter_input lookup, and admin_action bridge.
     *
     * @return void
     */
    function ppcart_register_legacy_request_field_bridges()
    {
        if (function_exists('ppcart_request_field_compatibility_lookup')) {
            ppcart_request_field_compatibility_lookup('ppcart_legacy_request_field_lookup', true);
        }

        ppcart_copy_legacy_request_fields();
        ppcart_register_legacy_admin_action_bridges();
    }
}

if (! function_exists('ppcart_unregister_legacy_request_field_bridges')) {
    /**
     * Clear leftover request-field bridges. Intended for isolated tests.
     *
     * Does not reverse copies already applied to superglobals.
     *
     * @return void
     */
    function ppcart_unregister_legacy_request_field_bridges()
    {
        if (function_exists('ppcart_request_field_compatibility_lookup')) {
            ppcart_request_field_compatibility_lookup(null, true);
        }

        $copied = &ncs_cart_legacy_request_fields_copied();
        $copied = false;

        ppcart_unregister_legacy_admin_action_bridges();
    }
}
