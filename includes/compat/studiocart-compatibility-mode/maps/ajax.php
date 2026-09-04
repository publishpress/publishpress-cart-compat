<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical AJAX action contract.
 *
 * Each action declares its shipped legacy value and exact authenticated /
 * unauthenticated exposure. Compatibility Mode must not add an exposure that
 * is false here.
 */
return [
    'ppcart_ajax_action' => [
        'legacy' => [ 'ncs_ajax_action' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_capture_lead' => [
        'legacy' => [ 'sc_capture_lead' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_check_username' => [
        'legacy' => [ 'sc_check_username' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_create_checkout_session' => [
        'legacy' => [ 'sc_create_checkout_session' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_create_payment_intent' => [
        'legacy' => [ 'sc_create_payment_intent' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_create_setup_intent' => [
        'legacy' => [ 'sc_create_setup_intent' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_create_subscription' => [
        'legacy' => [ 'sc_create_subscription' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_dismissed_notice_handler' => [
        'legacy' => [ 'sc_dismissed_notice_handler' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_fresh_product' => [
        'legacy' => [ 'sc_fresh_product' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_get_payment_options' => [
        'legacy' => [ 'sc_get_payment_options' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_json_search_user' => [
        'legacy' => [ 'sc_json_search_user' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_mailchimp_groups_tags' => [
        'legacy' => [ 'sc_mailchimp_groups_tags' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_migrate_secrets' => [
        'legacy' => [ 'sc_migrate_secrets' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_order_refund' => [
        'legacy' => [ 'sc_order_refund' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_pause_restart_subscription' => [
        'legacy' => [ 'sc_pause_restart_subscription' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_paypal_request' => [
        'legacy' => [ 'sc_paypal_request' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_preview_email_template' => [
        'legacy' => [ 'sc_preview_email_template' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_preview_product_notification_email' => [
        'legacy' => [ 'sc_preview_product_notification_email' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_product_plans' => [
        'legacy' => [ 'sc_product_plans' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_renew_integrations_lists' => [
        'legacy' => [ 'sc_renew_integrations_lists' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_resend_purchase_confirmation_email' => [
        'legacy' => [ 'sc_resend_purchase_confirmation_email' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_reset_email_template' => [
        'legacy' => [ 'sc_reset_email_template' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_save_order_to_db' => [
        'legacy' => [ 'sc_save_order_to_db' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_search_report_customers' => [
        'legacy' => [ 'ncs_cart_search_report_customers' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_search_report_products' => [
        'legacy' => [ 'ncs_cart_search_report_products' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_send_email_test' => [
        'legacy' => [ 'sc_send_email_test' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_send_product_notification_test' => [
        'legacy' => [ 'sc_send_product_notification_test' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_set_encrypt_secrets' => [
        'legacy' => [ 'sc_set_encrypt_secrets' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_sync_order' => [
        'legacy' => [ 'sc_sync_order' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_sync_subscription' => [
        'legacy' => [ 'sc_sync_subscription' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_unsubscribe_customer' => [
        'legacy' => [ 'sc_unsubscribe_customer' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_update_cart_amount' => [
        'legacy' => [ 'sc_update_cart_amount' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_update_payment_intent_amt' => [
        'legacy' => [ 'sc_update_payment_intent_amt' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_update_stripe_order_status' => [
        'legacy' => [ 'sc_update_stripe_order_status' ],
        'auth'   => true,
        'nopriv' => true,
    ],
    'ppcart_update_stripe_payment_method' => [
        'legacy' => [ 'sc_update_stripe_payment_method' ],
        'auth'   => true,
        'nopriv' => false,
    ],
    'ppcart_update_user_profile' => [
        'legacy' => [ 'sc_update_user_profile' ],
        'auth'   => true,
        'nopriv' => false,
    ],
];
