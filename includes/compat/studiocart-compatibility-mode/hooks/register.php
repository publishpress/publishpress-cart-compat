<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart hook bridge helpers — register.
 * Loaded from hooks/index.php.
 */

if (! function_exists('ppcart_register_cron_hook_bridges')) {
    /**
     * Always-on cron drain: legacy scheduled hook names forward to canonical.
     *
     * @return void
     */
    function ppcart_register_cron_hook_bridges()
    {
        $registered = &ncs_cart_hook_bridge_cron_registered_hooks();
        $config = ncs_cart_get_legacy_hook_map_config();

        foreach ($config['cron'] as $legacy => $canonical) {
            if (! is_string($legacy) || '' === $legacy || ! is_string($canonical) || '' === $canonical) {
                continue;
            }

            if (isset($registered[ $legacy ])) {
                continue;
            }

            add_action($legacy, static function () use ($legacy) {
                ppcart_hook_bridge_forward_to_canonical($legacy, ...func_get_args());
            }, 10, 99);

            $registered[ $legacy ] = true;
        }
    }
}
if (! function_exists('ppcart_register_legacy_hook_bridges')) {
    /**
     * Compat-gated legacy <=> canonical hook bridges.
     *
     * @return void
     */
    function ppcart_register_legacy_hook_bridges()
    {
        $registered = &ncs_cart_hook_bridge_legacy_all_registered();

        if (! $registered) {
            add_action('all', 'ppcart_hook_bridge_on_all', 0, 1);
            $registered = true;
        }

        ppcart_prepare_registered_hook_bridges();
    }
}
