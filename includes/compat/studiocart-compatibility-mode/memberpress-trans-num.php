<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_memberpress_trans_num_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_memberpress_trans_num_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/memberpress-trans-num.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_get_legacy_memberpress_trans_num_names')) {
    /**
     * Leftover MemberPress trans_num prefixes for one canonical prefix.
     *
     * @param string $canonical_key Canonical prefix.
     * @return array<int, string>
     */
    function ncs_cart_get_legacy_memberpress_trans_num_names($canonical_key)
    {
        if (! is_string($canonical_key) || '' === $canonical_key) {
            return [];
        }

        $map = ncs_cart_get_legacy_memberpress_trans_num_map();

        if (! isset($map[ $canonical_key ]) || ! is_array($map[ $canonical_key ])) {
            return [];
        }

        return array_values(array_filter($map[ $canonical_key ], 'is_string'));
    }
}

if (! function_exists('ppcart_legacy_memberpress_trans_num_lookup')) {
    /**
     * Compatibility lookup for ppcart_pro_memberpress_trans_num_candidates().
     *
     * @param string $canonical_key Canonical prefix.
     * @return array<int, string>
     */
    function ppcart_legacy_memberpress_trans_num_lookup($canonical_key)
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return [];
        }

        return ncs_cart_get_legacy_memberpress_trans_num_names($canonical_key);
    }
}

if (! function_exists('ppcart_register_legacy_memberpress_trans_num_bridges')) {
    /**
     * Register leftover MemberPress trans_num lookup when Compatibility Mode is on.
     *
     * @return void
     */
    function ppcart_register_legacy_memberpress_trans_num_bridges()
    {
        if (function_exists('ppcart_pro_third_party_key_compatibility_lookup')) {
            ppcart_pro_third_party_key_compatibility_lookup(
                'memberpress_trans_num',
                'ppcart_legacy_memberpress_trans_num_lookup',
                true
            );
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_memberpress_trans_num_bridges')) {
    /**
     * Clear leftover MemberPress trans_num lookup. Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_memberpress_trans_num_bridges()
    {
        if (function_exists('ppcart_pro_third_party_key_compatibility_lookup')) {
            ppcart_pro_third_party_key_compatibility_lookup('memberpress_trans_num', null, true);
        }
    }
}
