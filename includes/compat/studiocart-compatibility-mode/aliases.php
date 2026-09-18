<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart class-alias helpers.
 *
 * The developer/CI kill switch stays here. The user-facing Compatibility Mode
 * gate lives on PPCartComp_Studiocart_Compatibility_Mode.
 */

if (! function_exists('ncs_cart_legacy_compat_enabled')) {
    /**
     * Returns whether internal legacy shims are allowed to run.
     *
     * @return bool
     */
    function ncs_cart_legacy_compat_enabled()
    {
        if (defined('NCS_CART_LEGACY_COMPAT')) {
            return (bool) NCS_CART_LEGACY_COMPAT;
        }

        return (bool) apply_filters('ncs_cart_legacy_compat_enabled', true);
    }
}

if (! function_exists('ncs_cart_get_legacy_class_alias_map')) {
    /**
     * Merged canonical => legacy class names from maps/*.php.
     *
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_class_alias_map()
    {
        $map      = [];
        $maps_dir = __DIR__ . '/maps';

        if (! is_dir($maps_dir)) {
            return $map;
        }

        $map_files = glob($maps_dir . '/*.php');

        if (! is_array($map_files)) {
            return $map;
        }

        sort($map_files);

        foreach ($map_files as $map_file) {
            if (in_array(basename($map_file), [ 'index.php', 'options.php', 'hooks.php', 'constants.php', 'shortcodes.php', 'ajax.php', 'nonces.php', 'request-fields.php', 'cookies.php', 'custom-tables.php', 'pro-meta-keys.php', 'stripe-metadata.php', 'mollie-metadata.php', 'razorpay-notes.php', 'square-catalog.php', 'memberpress-trans-num.php', 'affiliate-meta.php', 'cpt-taxonomy.php', 'integration-repeater-fields.php', 'integration-service-ids.php', 'invoice-format-values.php', 'tax-rate-inner-keys.php', 'theme-template-dirs.php', 'gutenberg-blocks.php', 'rest-namespaces.php', 'elementor-widgets.php', 'divi-modules.php' ], true)) {
                continue;
            }

            $entries = include $map_file;

            if (! is_array($entries)) {
                continue;
            }

            foreach ($entries as $canonical => $legacy_names) {
                if (! is_string($canonical) || '' === $canonical) {
                    continue;
                }

                if (! is_array($legacy_names)) {
                    continue;
                }

                if (! isset($map[ $canonical ])) {
                    $map[ $canonical ] = [];
                }

                foreach ($legacy_names as $legacy) {
                    if (! is_string($legacy) || '' === $legacy) {
                        continue;
                    }

                    if (! in_array($legacy, $map[ $canonical ], true)) {
                        $map[ $canonical ][] = $legacy;
                    }
                }
            }
        }

        return $map;
    }
}

if (! function_exists('ncs_cart_register_class_alias')) {
    /**
     * Registers a legacy class alias when the developer/CI switch allows it.
     *
     * @param string $canonical Canonical class name.
     * @param string $legacy    Legacy class name.
     * @return void
     */
    function ncs_cart_register_class_alias($canonical, $legacy)
    {
        if (
            ! ncs_cart_legacy_compat_enabled()
            || class_exists($legacy, false)
            || ! class_exists($canonical, false)
        ) {
            return;
        }

        class_alias($canonical, $legacy);
    }
}

if (! function_exists('ncs_cart_register_legacy_class_aliases')) {
    /**
     * Registers legacy class aliases for canonical classes already loaded.
     *
     * Safe to call multiple times during bootstrap; skips aliases whose
     * canonical class has not been required yet.
     *
     * @return void
     */
    function ncs_cart_register_legacy_class_aliases()
    {
        foreach (ncs_cart_get_legacy_class_alias_map() as $canonical => $legacy_names) {
            foreach ($legacy_names as $legacy) {
                ncs_cart_register_class_alias($canonical, $legacy);
            }
        }
    }
}
