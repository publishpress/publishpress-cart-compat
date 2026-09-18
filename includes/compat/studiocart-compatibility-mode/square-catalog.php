<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_square_catalog_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_square_catalog_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/square-catalog.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_get_legacy_square_catalog_names')) {
    /**
     * Leftover Square catalog prefixes for one canonical prefix.
     *
     * @param string $canonical_key Canonical prefix.
     * @return array<int, string>
     */
    function ncs_cart_get_legacy_square_catalog_names($canonical_key)
    {
        if (! is_string($canonical_key) || '' === $canonical_key) {
            return [];
        }

        $map = ncs_cart_get_legacy_square_catalog_map();

        if (! isset($map[ $canonical_key ]) || ! is_array($map[ $canonical_key ])) {
            return [];
        }

        return array_values(array_filter($map[ $canonical_key ], 'is_string'));
    }
}

if (! function_exists('ppcart_legacy_square_catalog_lookup')) {
    /**
     * Compatibility lookup for ppcart_pro_square_plan_object_id_candidates().
     *
     * @param string $canonical_key Canonical prefix.
     * @return array<int, string>
     */
    function ppcart_legacy_square_catalog_lookup($canonical_key)
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return [];
        }

        return ncs_cart_get_legacy_square_catalog_names($canonical_key);
    }
}

if (! function_exists('ppcart_register_legacy_square_catalog_bridges')) {
    /**
     * Register leftover Square catalog-prefix lookup when Compatibility Mode is on.
     *
     * @return void
     */
    function ppcart_register_legacy_square_catalog_bridges()
    {
        if (function_exists('ppcart_pro_gateway_metadata_compatibility_lookup')) {
            ppcart_pro_gateway_metadata_compatibility_lookup('square', 'ppcart_legacy_square_catalog_lookup', true);
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_square_catalog_bridges')) {
    /**
     * Clear leftover Square catalog-prefix lookup. Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_square_catalog_bridges()
    {
        if (function_exists('ppcart_pro_gateway_metadata_compatibility_lookup')) {
            ppcart_pro_gateway_metadata_compatibility_lookup('square', null, true);
        }
    }
}
