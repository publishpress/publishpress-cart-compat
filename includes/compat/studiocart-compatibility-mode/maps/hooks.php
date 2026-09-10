<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Hook bridge exclusions, overrides, extras, and cron drain mappings.
 *
 * Most hook names are derived by ncs_cart_legacy_to_canonical_hook_name(). Entries
 * here are frozen (excluded), non-rule mappings, 1:N extras, or cron drain only.
 */
return [
    'exclude' => [
        // WordPress AJAX hook suffixes match frozen POST action values.
        'wp_ajax_sc_',
        'wp_ajax_nopriv_sc_',
        'wp_ajax_ncs_',
        'wp_ajax_nopriv_ncs_',
        // CPT-derived WordPress hooks are rewritten by ncs_cart_rewrite_derived_cpt_hook().
        'admin_action_sc_',
        'admin_post_sc_',
        // Option-key WordPress hooks (handled by the options bridge).
        'pre_option_',
        'pre_update_option_',
        'add_option_',
        'update_option_',
        'delete_option_',
        'option_',
        'default_option_',
    ],
    'override' => [
        'studiocart_checkout_complete'     => 'ppcart_after_order_paid',
        'studiocart_page_metabox'          => 'ppcart_page_metabox',
        'ncs-cart-currencies'               => 'ppcart_currencies',
        'ncs-cart-currency-symbols'         => 'ppcart_currency_symbols',
        'ncs-cart-zero-decimal-currency'    => 'ppcart_zero_decimal_currency',
        'before_sc_order_refund'           => 'ppcart_before_order_refund',
        'ppcart_sc_subscription_integrations' => 'ppcart_ppcart_subscription_integrations',
        'ppcart_sc_refund_order_integrations' => 'ppcart_ppcart_refund_order_integrations',
        'ppcart_sc_wpdomainchecker_integrations' => 'ppcart_ppcart_wpdomainchecker_integrations',
        'sc-cart-cpt-options'                  => 'ppcart_cpt_options',
        'sc-cart-taxonomy-options'            => 'ppcart_taxonomy_options',
        'sc_cart_loaded'                       => 'ppcart_loaded',
        'ncs_cart_loaded'                      => 'ppcart_loaded',
        'sc_cart_supports_feature'             => 'ppcart_supports_feature',
    ],
    // Slice 34 integration triggers (`sc_order_*` / `sc_subscription_*` /
    // `sc_renewal_*`) use the mechanical sc_ → ppcart_ rule. Do not add 1:1
    // extras unless a leftover name is not a mechanical prefix.
    // Slice 49 leftover service-id hooks already start with ppcart_, so they
    // are not a mechanical sc_ prefix.
    'extra' => [
        'ppcart_after_order_paid' => [
            'studiocart_checkout_complete',
        ],
        'ppcart_before_order_refund' => [
            'before_sc_order_refund',
        ],
        'ppcart_ppcart_subscription_integrations' => [
            'ppcart_sc_subscription_integrations',
        ],
        'ppcart_ppcart_refund_order_integrations' => [
            'ppcart_sc_refund_order_integrations',
        ],
        'ppcart_ppcart_wpdomainchecker_integrations' => [
            'ppcart_sc_wpdomainchecker_integrations',
        ],
        'ppcart_cpt_options' => [
            'sc-cart-cpt-options',
        ],
        'ppcart_taxonomy_options' => [
            'sc-cart-taxonomy-options',
        ],
        'ppcart_loaded' => [
            'sc_cart_loaded',
            'ncs_cart_loaded',
        ],
        'ppcart_supports_feature' => [
            'sc_cart_supports_feature',
        ],
    ],
    'cron' => [
        'sc_run_price_formatting'              => 'ppcart_run_price_formatting',
        'nsc_run_price_formatting'           => 'ppcart_run_price_formatting',
        'ncs_email_schedule_hook'            => 'ppcart_email_schedule_hook',
        'studiocart_daily_events'            => 'ppcart_daily_events',
        'studiocart_subscription_reminder_event' => 'ppcart_subscription_reminder_event',
        'studiocart_cleanup_preloaded_intents' => 'ppcart_cleanup_preloaded_intents',
        'sc_cancel_subscription_event'       => 'ppcart_cancel_subscription_event',
        'sc_stripe_reconcile_stale_subscriptions' => 'ppcart_stripe_reconcile_stale_subscriptions',
    ],
];
