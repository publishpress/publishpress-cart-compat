<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart hook bridge helpers — state store.
 * Loaded from hooks/index.php.
 */

if (! function_exists('ncs_cart_hook_bridge_attached_hooks')) {
    /**
     * @return array<string, bool>
     */
    function &ncs_cart_hook_bridge_attached_hooks()
    {
        static $attached = [];

        return $attached;
    }
}
if (! function_exists('ncs_cart_hook_bridge_cron_registered_hooks')) {
    /**
     * @return array<string, bool>
     */
    function &ncs_cart_hook_bridge_cron_registered_hooks()
    {
        static $registered = [];

        return $registered;
    }
}
if (! function_exists('ncs_cart_hook_bridge_legacy_all_registered')) {
    /**
     * @return bool
     */
    function &ncs_cart_hook_bridge_legacy_all_registered()
    {
        static $registered = false;

        return $registered;
    }
}
if (! function_exists('ncs_cart_reset_hook_bridge_lazy_attach_state')) {
    /**
     * Clear lazy-attach bookkeeping (integration tests).
     *
     * @return void
     */
    function ncs_cart_reset_hook_bridge_lazy_attach_state()
    {
        $attached = &ncs_cart_hook_bridge_attached_hooks();
        $attached = [];

        $registered = &ncs_cart_hook_bridge_cron_registered_hooks();
        $registered = [];

        $legacy_all_registered = &ncs_cart_hook_bridge_legacy_all_registered();
        $legacy_all_registered = false;

        if (function_exists('remove_action')) {
            remove_action('all', 'ppcart_hook_bridge_on_all', 0);
        }
    }
}
