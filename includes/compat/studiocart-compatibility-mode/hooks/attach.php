<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart hook bridge helpers — attach.
 * Loaded from hooks/index.php.
 */

if (! function_exists('ppcart_hook_bridge_maybe_attach')) {
    /**
     * Lazily attach canonical <=> legacy bridge callbacks for one hook tag.
     *
     * @param string $hook Hook name currently firing.
     * @return void
     */
    function ppcart_hook_bridge_maybe_attach($hook)
    {
        $attached = &ncs_cart_hook_bridge_attached_hooks();

        if (! is_string($hook) || '' === $hook || isset($attached[ $hook ])) {
            return;
        }

        $attached[ $hook ] = true;

        if (ncs_cart_hook_is_canonical_name($hook)) {
            $priority = PHP_INT_MAX - 1;

            add_filter(
                $hook,
                static function ($value, ...$args) use ($hook) {
                    return ppcart_hook_bridge_filter_legacy_from_canonical($value, $hook, ...$args);
                },
                $priority,
                99
            );

            add_action(
                $hook,
                static function (...$args) use ($hook) {
                    return ppcart_hook_bridge_fire_legacy_from_canonical($hook, ...$args);
                },
                $priority,
                99
            );

            return;
        }

        if (! ncs_cart_hook_is_legacy_name($hook)) {
            return;
        }

        add_filter(
            $hook,
            static function ($value, ...$args) use ($hook) {
                return ppcart_hook_bridge_filter_canonical_from_legacy($value, $hook, ...$args);
            },
            0,
            99
        );

        add_action(
            $hook,
            static function (...$args) use ($hook) {
                return ppcart_hook_bridge_fire_canonical_from_legacy($hook, ...$args);
            },
            0,
            99
        );
    }
}
if (! function_exists('ncs_cart_hook_bridge_is_candidate')) {
    /**
     * Cheap prefix gate before compat checks on the `all` hook.
     *
     * @param string $hook Hook name.
     * @return bool
     */
    function ncs_cart_hook_bridge_is_candidate($hook)
    {
        if (! is_string($hook) || '' === $hook) {
            return false;
        }

        $prefixes = [
            'ppcart_',
            '_ppcart_',
            'ppcart-',
            'sc_',
            '_sc_',
            'ncs_',
            'nsc_',
            'studiocart_',
            'ncs-cart-',
        ];

        foreach ($prefixes as $prefix) {
            if (0 === strpos($hook, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
if (! function_exists('ppcart_hook_bridge_on_all')) {
    /**
     * @param string $hook Hook name.
     * @return void
     */
    function ppcart_hook_bridge_on_all($hook)
    {
        if (ncs_cart_hook_bridge_is_active() || ! ncs_cart_hook_bridge_is_candidate($hook)) {
            return;
        }

        if (! ncs_cart_hook_bridge_compat_enabled()) {
            return;
        }

        ppcart_hook_bridge_maybe_attach($hook);
    }
}
if (! function_exists('ppcart_prepare_registered_hook_bridges')) {
    /**
     * Eagerly attach bridge pairs for hooks that already have listeners.
     *
     * The `all` hook cannot affect the first filter call when the source hook
     * had no callbacks before apply_filters() began. Preparing both names here
     * keeps first-call filter values bridgeable in either direction.
     *
     * @return void
     */
    function ppcart_prepare_registered_hook_bridges()
    {
        global $wp_filter;

        if (! is_array($wp_filter)) {
            return;
        }

        foreach (array_keys($wp_filter) as $hook) {
            if (! ncs_cart_hook_bridge_is_candidate($hook)) {
                continue;
            }

            ppcart_hook_bridge_maybe_attach($hook);

            if (ncs_cart_hook_is_canonical_name($hook)) {
                foreach (ncs_cart_canonical_to_legacy_hook_names($hook) as $legacy) {
                    ppcart_hook_bridge_maybe_attach($legacy);
                }

                continue;
            }

            $canonical = ncs_cart_legacy_to_canonical_hook_name($hook);

            if (null !== $canonical) {
                ppcart_hook_bridge_maybe_attach($canonical);
            }
        }
    }
}
