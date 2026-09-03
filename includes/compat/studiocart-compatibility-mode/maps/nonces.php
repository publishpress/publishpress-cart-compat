<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Legacy nonce action strings accepted only by Compatibility Mode.
 *
 * Leftover nonce *action strings* (not request field names) accepted only
 * while Compatibility Mode is on. Request field names are slice 26.
 */
return [
    'exact' => [
        'ppcart_admin_search_user_nonce'              => [ 'sc-admin-search-user-nonce' ],
        'ppcart_ajax_nonce'                           => [ 'sc-ajax-nonce' ],
        'ppcart_cart'                                 => [ 'sc-cart' ],
        'ppcart_csv_export'                           => [ 'sc_csv_export' ],
        'ppcart_dismiss_secrets_notice'               => [ 'sc_dismiss_secrets_notice' ],
        'ppcart_download_debug_log'                   => [ 'sc_download_debug_log' ],
        'ppcart_download_stripe_webhook_log'          => [ 'sc_download_stripe_webhook_log' ],
        'ppcart_migrate_secrets'                      => [ 'sc_migrate_secrets' ],
        'ppcart_price_format'                         => [ 'sc-price-format' ],
        'ppcart_purchase_nonce'                       => [ 'sc_purchase_nonce' ],
        'ppcart_related_product'                      => [ 'sc_related_product' ],
        'ppcart_resend_purchase_confirmation_email'  => [ 'sc-resend-purchase-confirmation-email' ],
        'ppcart_reset_debug_log'                      => [ 'sc_reset_debug_log' ],
        'ppcart_search_report_customers'              => [ 'ncs_cart_search_report_customers' ],
        'ppcart_search_report_products'               => [ 'ncs_cart_search_report_products' ],
        'ppcart_set_encrypt_secrets'                  => [ 'sc_set_encrypt_secrets' ],
        'ppcart_stripe_connect_disconnect'            => [ 'sc-stripe-connect-disconnect' ],
        'ppcart_stripe_connect_setup_webhook'         => [ 'sc-stripe-connect-setup-webhook' ],
        'ppcart_stripe_connect_start'                 => [ 'sc-stripe-connect-start' ],
        'ppcart_stripe_webhook_manual_setup'          => [ 'sc-stripe-webhook-manual-setup' ],
        'ppcart_view_debug_log'                       => [ 'sc_view_debug_log' ],
        'ppcart_view_stripe_webhook_log'              => [ 'sc_view_stripe_webhook_log' ],
        'ppcart_clear_stripe_webhook_log'             => [ 'sc_clear_stripe_webhook_log' ],
    ],
    'prefix' => [
        'ppcart_downsell-'          => [ 'studiocart_downsell-' ],
        'ppcart_duplicate_product_' => [ 'sc_duplicate_product_' ],
        'ppcart_upsell-'            => [ 'studiocart_upsell-' ],
    ],
];
