<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_n–sc_z misc and other.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

if (! function_exists('pp_cart_testid')) {
    function pp_cart_testid($value)
    {
        return ppcart_testid($value);
    }
}
if (! function_exists('sc_new_user_notification')) {
    function sc_new_user_notification($user, $order_id, $test = false)
    {
        return ppcart_new_user_notification($user, $order_id, $test);
    }
}
if (! function_exists('sc_next_bill_time')) {
    function sc_next_bill_time($sub, $date = null)
    {
        return ppcart_next_bill_time($sub, $date);
    }
}
if (! function_exists('sc_normalize_product_name')) {
    function sc_normalize_product_name($name)
    {
        return ppcart_normalize_product_name($name);
    }
}
if (! function_exists('sc_normalize_stripe_mode')) {
    function sc_normalize_stripe_mode($mode = '')
    {
        return ppcart_normalize_stripe_mode($mode);
    }
}
if (! function_exists('sc_pause_restart_subscription')) {
    function sc_pause_restart_subscription()
    {
        return ppcart_pause_restart_subscription();
    }
}
if (! function_exists('sc_payment_method_label_allowed_html')) {
    function sc_payment_method_label_allowed_html()
    {
        return ppcart_payment_method_label_allowed_html();
    }
}
if (! function_exists('sc_payment_method_label_html')) {
    function sc_payment_method_label_html($label)
    {
        return ppcart_payment_method_label_html($label);
    }
}
if (! function_exists('sc_payment_methods')) {
    function sc_payment_methods()
    {
        return ppcart_payment_methods();
    }
}
if (! function_exists('scrt_payment_methods')) {
    function scrt_payment_methods()
    {
        return ppcart_payment_methods();
    }
}
if (! function_exists('sc_payment_plan_options')) {
    function sc_payment_plan_options($post_id, $hide_labels, $plan = false, $include_coupon = true)
    {
        return ppcart_payment_plan_options($post_id, $hide_labels, $plan, $include_coupon);
    }
}
if (! function_exists('sc_paypal_ipn_get_dedupe_key')) {
    function sc_paypal_ipn_get_dedupe_key($request_data)
    {
        return ppcart_paypal_ipn_get_dedupe_key($request_data);
    }
}
if (! function_exists('sc_paypal_ipn_mark_processed')) {
    function sc_paypal_ipn_mark_processed($dedupe_key)
    {
        return ppcart_paypal_ipn_mark_processed($dedupe_key);
    }
}
if (! function_exists('sc_personalize')) {
    function sc_personalize($str, $order_info, $filter = false, $include_order_details = true)
    {
        return ppcart_personalize($str, $order_info, $filter, $include_order_details);
    }
}
if (! function_exists('sc_personalize_product_info')) {
    function sc_personalize_product_info($scp, $order_info)
    {
        return ppcart_personalize_product_info($scp, $order_info);
    }
}
if (! function_exists('sc_plan_detail')) {
    function sc_plan_detail($atts)
    {
        return ppcart_plan_detail($atts);
    }
}
if (! function_exists('sc_pluralize_interval')) {
    function sc_pluralize_interval($int)
    {
        return ppcart_pluralize_interval($int);
    }
}
if (! function_exists('sc_prepare_email_template_options')) {
    function sc_prepare_email_template_options($options)
    {
        return ppcart_prepare_email_template_options($options);
    }
}
if (! function_exists('sc_preserve_stripe_owned_fields')) {
    function sc_preserve_stripe_owned_fields($record)
    {
        return ppcart_preserve_stripe_owned_fields($record);
    }
}
if (! function_exists('sc_price_in_cents')) {
    function sc_price_in_cents($amount, $currency = false)
    {
        return ppcart_price_in_cents($amount, $currency);
    }
}
if (! function_exists('sc_product_detail')) {
    function sc_product_detail($atts)
    {
        return ppcart_product_detail($atts);
    }
}
if (! function_exists('sc_redact_secrets_from_text')) {
    function sc_redact_secrets_from_text($text)
    {
        return ppcart_redact_secrets_from_text($text);
    }
}
if (! function_exists('sc_redirect')) {
    function sc_redirect($url)
    {
        return ppcart_redirect($url);
    }
}
if (! function_exists('sc_register_email_template_option_filters')) {
    function sc_register_email_template_option_filters()
    {
        return ppcart_register_email_template_option_filters();
    }
}
if (! function_exists('sc_remove_repeater_blank')) {
    function sc_remove_repeater_blank($value)
    {
        return ppcart_remove_repeater_blank($value);
    }
}
if (! function_exists('sc_render_order_summary_items')) {
    function sc_render_order_summary_items($post_id, $plan = false)
    {
        return ppcart_render_order_summary_items($post_id, $plan);
    }
}
if (! function_exists('sc_render_site_info')) {
    function sc_render_site_info()
    {
        return ppcart_render_site_info();
    }
}
if (! function_exists('sc_resend_purchase_confirmation_email_ajax')) {
    function sc_resend_purchase_confirmation_email_ajax()
    {
        return ppcart_resend_purchase_confirmation_email_ajax();
    }
}
if (! function_exists('sc_reset_email_template')) {
    function sc_reset_email_template($template_key)
    {
        return ppcart_reset_email_template($template_key);
    }
}
if (! function_exists('sc_run_price_formatting')) {
    function sc_run_price_formatting()
    {
        return ppcart_run_price_formatting();
    }
}
if (! function_exists('sc_run_scheduled_cancellation')) {
    function sc_run_scheduled_cancellation($sub)
    {
        return ppcart_run_scheduled_cancellation($sub);
    }
}
if (! function_exists('sc_safe_meta_unserialize')) {
    function sc_safe_meta_unserialize($value)
    {
        return ppcart_safe_meta_unserialize($value);
    }
}
if (! function_exists('sc_safe_remote_get')) {
    function sc_safe_remote_get($url, $args = [])
    {
        return ppcart_safe_remote_get($url, $args);
    }
}
if (! function_exists('sc_safe_remote_post')) {
    function sc_safe_remote_post($url, $args = [])
    {
        return ppcart_safe_remote_post($url, $args);
    }
}
if (! function_exists('sc_send_product_notification')) {
    function sc_send_product_notification($n, $order_info, $to_override = null)
    {
        return ppcart_send_product_notification($n, $order_info, $to_override);
    }
}
if (! function_exists('sc_sendfox_api_request')) {
    function sc_sendfox_api_request($endpoint = 'me', $data = [], $method = 'GET')
    {
        return ppcart_sendfox_api_request($endpoint, $data, $method);
    }
}
if (! function_exists('sc_set_sensitive_option')) {
    function sc_set_sensitive_option($option_name, $value)
    {
        return ppcart_set_sensitive_option($option_name, $value);
    }
}
if (! function_exists('sc_set_time_limit')) {
    function sc_set_time_limit($limit = 0)
    {
        return ppcart_set_time_limit($limit);
    }
}
if (! function_exists('sc_setup_order')) {
    function sc_setup_order($id, $array = false)
    {
        return ppcart_setup_order($id, $array);
    }
}
if (! function_exists('sc_setup_product')) {
    function sc_setup_product($id)
    {
        return ppcart_setup_product($id);
    }
}
if (! function_exists('sc_setup_stripe')) {
    function sc_setup_stripe()
    {
        return ppcart_setup_stripe();
    }
}
if (! function_exists('sc_singularize_interval')) {
    function sc_singularize_interval($int)
    {
        return ppcart_singularize_interval($int);
    }
}
if (! function_exists('sc_states_autocomplte_format_list')) {
    function sc_states_autocomplte_format_list()
    {
        return ppcart_states_autocomplte_format_list();
    }
}
if (! function_exists('sc_states_list')) {
    function sc_states_list($cc = null)
    {
        return ppcart_states_list($cc);
    }
}
if (! function_exists('sc_step_wrappers_1')) {
    function sc_step_wrappers_1()
    {
        return ppcart_step_wrappers_1();
    }
}
if (! function_exists('sc_step_wrappers_2')) {
    function sc_step_wrappers_2()
    {
        return ppcart_step_wrappers_2();
    }
}
if (! function_exists('sc_step_wrappers_3')) {
    function sc_step_wrappers_3()
    {
        return ppcart_step_wrappers_3();
    }
}
if (! function_exists('sc_stripe_sync_context_run')) {
    function sc_stripe_sync_context_run($callback)
    {
        return ppcart_stripe_sync_context_run($callback);
    }
}
if (! function_exists('sc_test_order_data')) {
    function sc_test_order_data()
    {
        return ppcart_test_order_data();
    }
}
if (! function_exists('sc_translate_js')) {
    function sc_translate_js($js_script = '')
    {
        return ppcart_translate_js($js_script);
    }
}
if (! function_exists('sc_trigger_integrations')) {
    function sc_trigger_integrations($status, $order_info)
    {
        return ppcart_trigger_integrations($status, $order_info);
    }
}
if (! function_exists('sc_unsubscribe_customer')) {
    function sc_unsubscribe_customer()
    {
        return ppcart_unsubscribe_customer();
    }
}
if (! function_exists('sc_update_user_profile')) {
    function sc_update_user_profile()
    {
        return ppcart_update_user_profile();
    }
}
if (! function_exists('sc_validate_payment_key')) {
    function sc_validate_payment_key()
    {
        return ppcart_validate_payment_key();
    }
}
if (! function_exists('sc_vat_countries_list')) {
    function sc_vat_countries_list()
    {
        return ppcart_vat_countries_list();
    }
}
if (! function_exists('sc_verifyTransaction')) {
    function sc_verifyTransaction($data)
    {
        return ppcart_verifyTransaction($data);
    }
}
if (! function_exists('sc_webhook_order_body')) {
    function sc_webhook_order_body($order, $type = '', $price_in_cents = false)
    {
        return ppcart_webhook_order_body($order, $type, $price_in_cents);
    }
}
if (! function_exists('studiocart_default_fields_filter')) {
    function studiocart_default_fields_filter($fields, $scp)
    {
        return ppcart_default_fields_filter($fields, $scp);
    }
}
if (! function_exists('studiocart_notification_send')) {
    function studiocart_notification_send($status, $order_info, $test = false)
    {
        return ppcart_notification_send($status, $order_info, $test);
    }
}
if (! function_exists('studiocart_plan')) {
    function studiocart_plan($option_id, $sale = '', $product_id = '', $array = false)
    {
        return ppcart_plan($option_id, $sale, $product_id, $array);
    }
}
if (! function_exists('activate_ncs_cart')) {
    function activate_ncs_cart()
    {
        return ppcart_activate();
    }
}
if (! function_exists('deactivate_ncs_cart')) {
    function deactivate_ncs_cart()
    {
        return ppcart_deactivate();
    }
}
if (! function_exists('upgrade_ncs_cart')) {
    function upgrade_ncs_cart($upgrader_object, $options)
    {
        return ppcart_upgrade($upgrader_object, $options);
    }
}
if (! function_exists('run_ncs_cart')) {
    function run_ncs_cart()
    {
        return ppcart_run();
    }
}
