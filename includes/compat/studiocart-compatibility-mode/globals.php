<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart runtime-global aliases.
 *
 * Compatibility Mode makes the shipped StudioCart global names references to
 * the canonical $ppcart_* globals so third-party reads and writes stay in sync.
 * A leftover legacy value is copied inbound when the canonical name is missing.
 *
 * NCS_CART_LEGACY_COMPAT suppresses the aliases, matching the class_alias
 * developer/CI kill switch.
 */

if (! function_exists('ncs_cart_get_legacy_global_map')) {
    /**
     * Canonical => legacy PHP global variable names (without $).
     *
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_global_map()
    {
        return [
            'ppcart_stripe'                        => [ 'sc_stripe' ],
            'ppcart_currency'                      => [ 'sc_currency' ],
            'ppcart_currency_symbol'               => [ 'sc_currency_symbol' ],
            'ppcart_debug_logger'                  => [ 'sc_debug_logger' ],
            'ppcart_product'                       => [ 'scp' ],
            'ppcart_checkout_request_rendered'     => [ 'sc_checkout_request_rendered' ],
            'ppcart_checkout_block_arrangement'    => [ 'sc_checkout_block_arrangement' ],
            'ppcart_public'                        => [ 'studiocart' ],
            'ppcart_files'                         => [ 'scFiles' ],
            'ppcart_product_fields'                => [ 'sc_product_fields' ],
            'ppcart_is_admin_screen'               => [ 'sc_is_studiocart_admin_screen' ],
        ];
    }
}

if (! function_exists('ncs_cart_register_legacy_globals')) {
    /**
     * Alias legacy global names onto canonical globals by reference.
     *
     * Honors ncs_cart_legacy_compat_enabled().
     *
     * @return void
     */
    function ncs_cart_register_legacy_globals()
    {
        if (! ncs_cart_legacy_compat_enabled()) {
            return;
        }

        foreach (ncs_cart_get_legacy_global_map() as $canonical => $legacy_names) {
            foreach ($legacy_names as $legacy) {
                ncs_cart_alias_runtime_global($canonical, $legacy);
            }
        }
    }
}

if (! function_exists('ncs_cart_alias_runtime_global')) {
    /**
     * Make $legacy a reference to $canonical, copying inbound when needed.
     *
     * @param string $canonical Canonical global name without $.
     * @param string $legacy    Legacy global name without $.
     * @return void
     */
    function ncs_cart_alias_runtime_global($canonical, $legacy)
    {
        if (! array_key_exists($canonical, $GLOBALS) && array_key_exists($legacy, $GLOBALS)) {
            $GLOBALS[ $canonical ] = $GLOBALS[ $legacy ];
        } elseif (! array_key_exists($canonical, $GLOBALS)) {
            $GLOBALS[ $canonical ] = null;
        }

        $GLOBALS[ $legacy ] =& $GLOBALS[ $canonical ];
    }
}
