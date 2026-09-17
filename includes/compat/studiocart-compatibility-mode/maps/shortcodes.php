<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical shortcode tag => leftover StudioCart tag.
 *
 * Values are leftover tag strings (1:1). The late Pro checkout helper may
 * add a callback so leftover registers before checkout1.php.
 *
 * @return array<string, string|array{legacy: string, callback?: string}>
 */
return [
    'ppcart_form'                          => 'studiocart-form',
    'ppcart_receipt'                       => 'studiocart-receipt',
    'ppcart_store'                         => 'studiocart-store',
    'ppcart_order_downloads'               => 'studiocart-order-downloads',
    'ppcart_account'                       => 'studiocart_account',
    'ppcart_account_link'                  => 'studiocart_account_link',
    'ppcart_account_order_detail'          => 'studiocart_order_detail',
    'ppcart_account_subscription_detail'   => 'studiocart_subscription_detail',
    'ppcart_customer_bought_product'       => 'sc_customer_bought_product',
    'ppcart_customer_has_subscription'     => 'sc_customer_has_subscription',
    'ppcart_order_detail'                  => 'sc_order_detail',
    'ppcart_plan'                          => 'sc_plan',
    'ppcart_product'                       => 'sc_product',
    'ppcart_affiliate_dashboard'           => 'sc_affiliate_dashboard',
    'ppcart_affiliate_signup'              => 'sc_affiliate_signup',
    'ppcart_wpdomainchecker'               => [
        'legacy'   => 'sc_wpdomainchecker',
        'callback' => 'ppcartcomp_legacy_wpdomainchecker_shortcode',
    ],
    'ppcart_order_summary_items_view'      => [
        'legacy'   => 'sc_order_summary_items_view',
        'callback' => 'ppcart_order_summary_shortcode',
    ],
];
