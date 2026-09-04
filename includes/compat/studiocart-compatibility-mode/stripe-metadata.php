<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_stripe_metadata_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_stripe_metadata_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/stripe-metadata.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_get_legacy_stripe_metadata_names')) {
    /**
     * Leftover Stripe metadata keys for one canonical key.
     *
     * @param string $canonical_key Canonical metadata key.
     * @return array<int, string>
     */
    function ncs_cart_get_legacy_stripe_metadata_names($canonical_key)
    {
        if (! is_string($canonical_key) || '' === $canonical_key) {
            return [];
        }

        $map = ncs_cart_get_legacy_stripe_metadata_map();

        if (! isset($map[ $canonical_key ]) || ! is_array($map[ $canonical_key ])) {
            return [];
        }

        return array_values(array_filter($map[ $canonical_key ], 'is_string'));
    }
}

if (! function_exists('ppcart_legacy_stripe_metadata_lookup')) {
    /**
     * Compatibility lookup for ppcart_stripe_metadata().
     *
     * @param string $canonical_key Canonical metadata key.
     * @return array<int, string>
     */
    function ppcart_legacy_stripe_metadata_lookup($canonical_key)
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return [];
        }

        return ncs_cart_get_legacy_stripe_metadata_names($canonical_key);
    }
}

if (! function_exists('ppcart_register_legacy_stripe_metadata_bridges')) {
    /**
     * Register leftover Stripe-metadata lookup when Compatibility Mode is on.
     *
     * @return void
     */
    function ppcart_register_legacy_stripe_metadata_bridges()
    {
        if (function_exists('ppcart_stripe_metadata_compatibility_lookup')) {
            ppcart_stripe_metadata_compatibility_lookup('ppcart_legacy_stripe_metadata_lookup', true);
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_stripe_metadata_bridges')) {
    /**
     * Clear leftover Stripe-metadata lookup. Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_stripe_metadata_bridges()
    {
        if (function_exists('ppcart_stripe_metadata_compatibility_lookup')) {
            ppcart_stripe_metadata_compatibility_lookup(null, true);
        }
    }
}
