<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart hook bridge helpers — state.
 * Loaded from hooks/index.php.
 */

if (! function_exists('ncs_cart_hook_bridge_state')) {
    /**
     * @return array{depth: int}
     */
    function &ncs_cart_hook_bridge_state()
    {
        static $state = [ 'depth' => 0 ];

        return $state;
    }
}
if (! function_exists('ncs_cart_hook_bridge_depth')) {
    /**
     * @return int
     */
    function ncs_cart_hook_bridge_depth()
    {
        return ncs_cart_hook_bridge_state()['depth'];
    }
}
if (! function_exists('ncs_cart_hook_bridge_enter')) {
    /**
     * @return void
     */
    function ncs_cart_hook_bridge_enter()
    {
        ++ncs_cart_hook_bridge_state()['depth'];
    }
}
if (! function_exists('ncs_cart_hook_bridge_leave')) {
    /**
     * @return void
     */
    function ncs_cart_hook_bridge_leave()
    {
        $state = &ncs_cart_hook_bridge_state();

        if ($state['depth'] > 0) {
            --$state['depth'];
        }
    }
}
if (! function_exists('ncs_cart_hook_bridge_is_active')) {
    /**
     * @return bool
     */
    function ncs_cart_hook_bridge_is_active()
    {
        return ncs_cart_hook_bridge_depth() > 0;
    }
}
if (! function_exists('ncs_cart_hook_is_canonical_name')) {
    /**
     * @param string $hook Hook name.
     * @return bool
     */
    function ncs_cart_hook_is_canonical_name($hook)
    {
        return is_string($hook)
            && (
                0 === strpos($hook, 'ppcart_')
                || 0 === strpos($hook, '_ppcart_')
                || 0 === strpos($hook, 'ppcart-')
            );
    }
}
if (! function_exists('ncs_cart_hook_is_legacy_name')) {
    /**
     * @param string $hook Hook name.
     * @return bool
     */
    function ncs_cart_hook_is_legacy_name($hook)
    {
        if (! is_string($hook) || '' === $hook || ncs_cart_hook_name_is_excluded($hook)) {
            return false;
        }

        return null !== ncs_cart_legacy_to_canonical_hook_name($hook);
    }
}
if (! function_exists('ncs_cart_hook_bridge_compat_enabled')) {
    /**
     * @return bool
     */
    function ncs_cart_hook_bridge_compat_enabled()
    {
        return class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            && PPCartComp_Studiocart_Compatibility_Mode::is_enabled();
    }
}
