<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart constant-alias helpers.
 *
 * Compatibility Mode copies a predefined legacy wp-config/Pro value into the
 * canonical PPCART_* name when the canonical name is missing, then exposes
 * missing legacy aliases from canonical values. Canonical values win when both
 * names are already defined. Aliases are never redefined.
 *
 * NCS_CART_LEGACY_COMPAT suppresses outbound legacy aliases only, matching the
 * class_alias developer/CI kill switch.
 */

if (! function_exists('ncs_cart_get_legacy_constant_map')) {
    /**
     * Canonical => legacy PHP constant names.
     *
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_constant_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map = [];
        $map_file = __DIR__ . '/maps/constants.php';

        if (! is_readable($map_file)) {
            return $map;
        }

        $entries = include $map_file;

        if (! is_array($entries)) {
            return $map;
        }

        foreach ($entries as $canonical => $legacy_names) {
            if (! is_string($canonical) || '' === $canonical || ! is_array($legacy_names)) {
                continue;
            }

            foreach ($legacy_names as $legacy) {
                if (! is_string($legacy) || '' === $legacy) {
                    continue;
                }

                if (! isset($map[ $canonical ])) {
                    $map[ $canonical ] = [];
                }

                if (! in_array($legacy, $map[ $canonical ], true)) {
                    $map[ $canonical ][] = $legacy;
                }
            }
        }

        return $map;
    }
}

if (! function_exists('ncs_cart_resolve_canonical_constants')) {
    /**
     * Copy a defined legacy constant into a missing canonical name.
     *
     * Does nothing when the canonical name is already defined.
     *
     * @return void
     */
    function ncs_cart_resolve_canonical_constants()
    {
        foreach (ncs_cart_get_legacy_constant_map() as $canonical => $legacy_names) {
            if (defined($canonical)) {
                continue;
            }

            foreach ($legacy_names as $legacy) {
                if (! defined($legacy)) {
                    continue;
                }

                define($canonical, constant($legacy));
                break;
            }
        }
    }
}

if (! function_exists('ncs_cart_register_legacy_constants')) {
    /**
     * Define missing legacy aliases from canonical values.
     *
     * Honors ncs_cart_legacy_compat_enabled(). Never redefines an existing name.
     *
     * @return void
     */
    function ncs_cart_register_legacy_constants()
    {
        if (! ncs_cart_legacy_compat_enabled()) {
            return;
        }

        foreach (ncs_cart_get_legacy_constant_map() as $canonical => $legacy_names) {
            if (! defined($canonical)) {
                continue;
            }

            foreach ($legacy_names as $legacy) {
                if (defined($legacy)) {
                    continue;
                }

                define($legacy, constant($canonical));
            }
        }
    }
}
