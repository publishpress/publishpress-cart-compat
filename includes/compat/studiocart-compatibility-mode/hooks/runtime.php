<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart hook bridge helpers — runtime.
 * Loaded from hooks/index.php.
 */

if (! function_exists('ppcart_hook_bridge_forward_to_canonical')) {
    /**
     * Cron drain: legacy scheduled hook name forwards to canonical listeners.
     *
     * @param string $legacy Legacy hook name.
     * @return void
     */
    function ppcart_hook_bridge_forward_to_canonical($legacy)
    {
        if (ncs_cart_hook_bridge_is_active()) {
            return;
        }

        $canonical = ncs_cart_legacy_to_canonical_hook_name($legacy);

        if (null === $canonical || $canonical === $legacy) {
            return;
        }

        ncs_cart_hook_bridge_enter();
        do_action_ref_array($canonical, array_slice(func_get_args(), 1));
        ncs_cart_hook_bridge_leave();
    }
}
if (! function_exists('ncs_cart_hook_bridge_is_doing_action')) {
    /**
     * Whether WordPress is executing a hook via do_action (not apply_filters).
     *
     * @param string $hook Hook name.
     * @return bool
     */
    function ncs_cart_hook_bridge_is_doing_action($hook)
    {
        global $wp_filter;

        if (! is_string($hook) || '' === $hook || ! isset($wp_filter[ $hook ])) {
            return false;
        }

        $hook_object = $wp_filter[ $hook ];

        if (! $hook_object instanceof WP_Hook) {
            return false;
        }

        static $reflection = null;

        if (null === $reflection) {
            $reflection = new \ReflectionProperty(WP_Hook::class, 'doing_action');
            $reflection->setAccessible(true);
        }

        return (bool) $reflection->getValue($hook_object);
    }
}
if (! function_exists('ppcart_hook_bridge_fire_legacy_from_canonical')) {
    /**
     * @param mixed ...$args Hook arguments.
     * @return mixed First filter value when invoked during a filter.
     */
    function ppcart_hook_bridge_fire_legacy_from_canonical(...$args)
    {
        $canonical = array_shift($args);
        $value = $args[0] ?? null;

        if (
            ncs_cart_hook_bridge_is_active()
            || ! ncs_cart_hook_bridge_compat_enabled()
            || ! is_string($canonical)
            || '' === $canonical
            || ! ncs_cart_hook_bridge_is_doing_action($canonical)
        ) {
            return $value;
        }

        $legacy_names = ncs_cart_canonical_to_legacy_hook_names($canonical);

        if ([] === $legacy_names) {
            return;
        }

        ncs_cart_hook_bridge_enter();

        foreach ($legacy_names as $legacy) {
            if ($legacy === $canonical) {
                continue;
            }

            do_action_ref_array($legacy, $args);
        }

        ncs_cart_hook_bridge_leave();

        return $value;
    }
}
if (! function_exists('ppcart_hook_bridge_filter_legacy_from_canonical')) {
    /**
     * @param mixed $value Filter value.
     * @return mixed
     */
    function ppcart_hook_bridge_filter_legacy_from_canonical($value)
    {
        $args = func_get_args();
        array_shift($args);
        $canonical = array_shift($args);

        if (
            ncs_cart_hook_bridge_is_active()
            || ! ncs_cart_hook_bridge_compat_enabled()
            || ! is_string($canonical)
            || '' === $canonical
            || ncs_cart_hook_bridge_is_doing_action($canonical)
        ) {
            return $value;
        }

        $legacy_names = ncs_cart_canonical_to_legacy_hook_names($canonical);

        if ([] === $legacy_names) {
            return $value;
        }

        ncs_cart_hook_bridge_enter();

        foreach ($legacy_names as $legacy) {
            if ($legacy === $canonical) {
                continue;
            }

            $value = apply_filters_ref_array($legacy, array_merge([ $value ], $args));
        }

        ncs_cart_hook_bridge_leave();

        return $value;
    }
}
if (! function_exists('ppcart_hook_bridge_fire_canonical_from_legacy')) {
    /**
     * @param mixed ...$args Hook arguments.
     * @return mixed First filter value when invoked during a filter.
     */
    function ppcart_hook_bridge_fire_canonical_from_legacy(...$args)
    {
        $legacy = array_shift($args);
        $value = $args[0] ?? null;

        if (
            ncs_cart_hook_bridge_is_active()
            || ! ncs_cart_hook_bridge_compat_enabled()
            || ! is_string($legacy)
            || '' === $legacy
            || ! ncs_cart_hook_bridge_is_doing_action($legacy)
        ) {
            return $value;
        }

        $canonical = ncs_cart_legacy_to_canonical_hook_name($legacy);

        if (null === $canonical || $canonical === $legacy) {
            return;
        }

        ncs_cart_hook_bridge_enter();
        do_action_ref_array($canonical, $args);
        ncs_cart_hook_bridge_leave();

        return $value;
    }
}
if (! function_exists('ppcart_hook_bridge_filter_canonical_from_legacy')) {
    /**
     * @param mixed $value Filter value.
     * @return mixed
     */
    function ppcart_hook_bridge_filter_canonical_from_legacy($value)
    {
        $args = func_get_args();
        array_shift($args);
        $legacy = array_shift($args);

        if (
            ncs_cart_hook_bridge_is_active()
            || ! ncs_cart_hook_bridge_compat_enabled()
            || ! is_string($legacy)
            || '' === $legacy
            || ncs_cart_hook_bridge_is_doing_action($legacy)
        ) {
            return $value;
        }

        $canonical = ncs_cart_legacy_to_canonical_hook_name($legacy);

        if (null === $canonical || $canonical === $legacy) {
            return $value;
        }

        ncs_cart_hook_bridge_enter();
        $value = apply_filters_ref_array($canonical, array_merge([ $value ], $args));
        ncs_cart_hook_bridge_leave();

        return $value;
    }
}
