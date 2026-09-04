<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_get_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

if (! function_exists('sc_get_admin_notification_recipients')) {
    function sc_get_admin_notification_recipients()
    {
        return ppcart_get_admin_notification_recipients();
    }
}
if (! function_exists('sc_get_currencies')) {
    function sc_get_currencies()
    {
        return ppcart_get_currencies();
    }
}
if (! function_exists('sc_get_currency_symbol')) {
    function sc_get_currency_symbol()
    {
        return ppcart_get_currency_symbol();
    }
}
if (! function_exists('sc_get_currency_symbols')) {
    function sc_get_currency_symbols()
    {
        return ppcart_get_currency_symbols();
    }
}
if (! function_exists('sc_get_customers')) {
    function sc_get_customers($customer_id = 0)
    {
        return ppcart_get_customers($customer_id);
    }
}
if (! function_exists('sc_get_editable_order_details_email_body')) {
    function sc_get_editable_order_details_email_body($intro = '', $extra = '')
    {
        return ppcart_get_editable_order_details_email_body($intro, $extra);
    }
}
if (! function_exists('sc_get_email_allowed_html')) {
    function sc_get_email_allowed_html()
    {
        return ppcart_get_email_allowed_html();
    }
}
if (! function_exists('sc_get_email_from_email')) {
    function sc_get_email_from_email()
    {
        return ppcart_get_email_from_email();
    }
}
if (! function_exists('sc_get_email_from_name')) {
    function sc_get_email_from_name()
    {
        return ppcart_get_email_from_name();
    }
}
if (! function_exists('sc_get_email_headers')) {
    function sc_get_email_headers($reply_to = '')
    {
        return ppcart_get_email_headers($reply_to);
    }
}
if (! function_exists('sc_get_email_html')) {
    function sc_get_email_html($atts)
    {
        return ppcart_get_email_html($atts);
    }
}
if (! function_exists('sc_get_email_preview_order_data')) {
    function sc_get_email_preview_order_data()
    {
        return ppcart_get_email_preview_order_data();
    }
}
if (! function_exists('sc_get_email_reply_to')) {
    function sc_get_email_reply_to()
    {
        return ppcart_get_email_reply_to();
    }
}
if (! function_exists('sc_get_email_template_default')) {
    function sc_get_email_template_default($template_key, $field)
    {
        return ppcart_get_email_template_default($template_key, $field);
    }
}
if (! function_exists('sc_get_email_template_option_id')) {
    function sc_get_email_template_option_id($template_key, $field)
    {
        return ppcart_get_email_template_option_id($template_key, $field);
    }
}
if (! function_exists('sc_get_email_template_value')) {
    function sc_get_email_template_value($template_key, $field)
    {
        return ppcart_get_email_template_value($template_key, $field);
    }
}
if (! function_exists('sc_get_email_template_values')) {
    function sc_get_email_template_values($template_key)
    {
        return ppcart_get_email_template_values($template_key);
    }
}
if (! function_exists('sc_get_font_awesome_icon_html')) {
    function sc_get_font_awesome_icon_html($icon)
    {
        return ppcart_get_font_awesome_icon_html($icon);
    }
}
if (! function_exists('sc_get_item_list')) {
    function sc_get_item_list($order_id, $full = true, $qty_col = false)
    {
        return ppcart_get_item_list($order_id, $full, $qty_col);
    }
}
if (! function_exists('sc_get_items_from_legacy_order')) {
    function sc_get_items_from_legacy_order($order, $qty_col = true)
    {
        return ppcart_get_items_from_legacy_order($order, $qty_col);
    }
}
if (! function_exists('sc_get_latest_email_preview_order_id')) {
    function sc_get_latest_email_preview_order_id()
    {
        return ppcart_get_latest_email_preview_order_id();
    }
}
if (! function_exists('sc_get_mailchimp_data')) {
    function sc_get_mailchimp_data()
    {
        return ppcart_get_mailchimp_data();
    }
}
if (! function_exists('sc_get_mock_email_preview_order_data')) {
    function sc_get_mock_email_preview_order_data()
    {
        return ppcart_get_mock_email_preview_order_data();
    }
}
if (! function_exists('sc_get_next_bill_date_paypal')) {
    function sc_get_next_bill_date_paypal($sub)
    {
        return ppcart_get_next_bill_date_paypal($sub);
    }
}
if (! function_exists('sc_get_order')) {
    function sc_get_order($id)
    {
        return ppcart_get_order($id);
    }
}
if (! function_exists('sc_get_order_details_email_markup')) {
    function sc_get_order_details_email_markup()
    {
        return ppcart_get_order_details_email_markup();
    }
}
if (! function_exists('sc_get_order_details_html')) {
    function sc_get_order_details_html($order, $type = 'confirmation')
    {
        return ppcart_get_order_details_html($order, $type);
    }
}
if (! function_exists('sc_get_order_items')) {
    function sc_get_order_items($order, $qty_col = true, $show_hidden = false)
    {
        return ppcart_get_order_items($order, $qty_col, $show_hidden);
    }
}
if (! function_exists('sc_get_order_user_id')) {
    function sc_get_order_user_id($order, $create = false)
    {
        return ppcart_get_order_user_id($order, $create);
    }
}
if (! function_exists('sc_get_orders')) {
    function sc_get_orders($args)
    {
        return ppcart_get_orders($args);
    }
}
if (! function_exists('sc_get_products')) {
    function sc_get_products()
    {
        return ppcart_get_products();
    }
}
if (! function_exists('sc_get_public_product_name')) {
    function sc_get_public_product_name($id = false)
    {
        return ppcart_get_public_product_name($id);
    }
}
if (! function_exists('sc_get_sensitive_option')) {
    function sc_get_sensitive_option($option_name, $default = false)
    {
        return ppcart_get_sensitive_option($option_name, $default);
    }
}
if (! function_exists('sc_get_stripe_platform_credential_options')) {
    function sc_get_stripe_platform_credential_options($mode = '')
    {
        return ppcart_get_stripe_platform_credential_options($mode);
    }
}
if (! function_exists('sc_get_stripe_platform_credentials')) {
    function sc_get_stripe_platform_credentials($mode = '')
    {
        return ppcart_get_stripe_platform_credentials($mode);
    }
}
if (! function_exists('sc_get_stripe_platform_credentials_status')) {
    function sc_get_stripe_platform_credentials_status($mode = '')
    {
        return ppcart_get_stripe_platform_credentials_status($mode);
    }
}
if (! function_exists('sc_get_stripe_platform_raw_credentials')) {
    function sc_get_stripe_platform_raw_credentials($mode = '')
    {
        return ppcart_get_stripe_platform_raw_credentials($mode);
    }
}
if (! function_exists('sc_get_stripe_resource_id')) {
    function sc_get_stripe_resource_id($resource)
    {
        return ppcart_get_stripe_resource_id($resource);
    }
}
if (! function_exists('sc_get_stripe_resource_value')) {
    function sc_get_stripe_resource_value($resource, $key, $default = null)
    {
        return ppcart_get_stripe_resource_value($resource, $key, $default);
    }
}
if (! function_exists('sc_get_subscription_cancel_success_response')) {
    function sc_get_subscription_cancel_success_response($refund_error = '')
    {
        return ppcart_get_subscription_cancel_success_response($refund_error);
    }
}
if (! function_exists('sc_get_subscription_txn_id')) {
    function sc_get_subscription_txn_id($order_id, $subsription = false)
    {
        return ppcart_get_subscription_txn_id($order_id, $subsription);
    }
}
if (! function_exists('sc_get_tax_data')) {
    function sc_get_tax_data($item_tax_rate)
    {
        return ppcart_get_tax_data($item_tax_rate);
    }
}
if (! function_exists('sc_get_transaction_id')) {
    function sc_get_transaction_id($order_id)
    {
        return ppcart_get_transaction_id($order_id);
    }
}
if (! function_exists('sc_get_user_address')) {
    function sc_get_user_address($user_id)
    {
        return ppcart_get_user_address($user_id);
    }
}
if (! function_exists('sc_get_user_orders')) {
    function sc_get_user_orders($user_id, $status = ['paid', 'completed', 'refunded'], $order_id = 0, $renewals = false, $hide_free = false)
    {
        return ppcart_get_user_orders($user_id, $status, $order_id, $renewals, $hide_free);
    }
}
if (! function_exists('sc_get_user_phone')) {
    function sc_get_user_phone($user_id)
    {
        return ppcart_get_user_phone($user_id);
    }
}
if (! function_exists('sc_get_user_subscriptions')) {
    function sc_get_user_subscriptions($user_id, $status = 'any', $type = null, $plan_id = 0)
    {
        return ppcart_get_user_subscriptions($user_id, $status, $type, $plan_id);
    }
}
if (! function_exists('sc_get_webhook_url')) {
    function sc_get_webhook_url($payment_slug)
    {
        return ppcart_get_webhook_url($payment_slug);
    }
}
if (! function_exists('sc_get_zero_decimal_currencies')) {
    function sc_get_zero_decimal_currencies()
    {
        return ppcart_get_zero_decimal_currencies();
    }
}
