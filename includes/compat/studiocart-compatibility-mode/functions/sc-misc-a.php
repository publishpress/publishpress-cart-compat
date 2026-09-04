<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_a–sc_m misc.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

if (! function_exists('sc_address_fields')) {
    function sc_address_fields($post_id, $hide_labels = false)
    {
        return ppcart_address_fields($post_id, $hide_labels);
    }
}
if (! function_exists('sc_admin_menu_slug')) {
    function sc_admin_menu_slug()
    {
        return ppcart_admin_menu_slug();
    }
}
if (! function_exists('sc_ajax_notice_handler')) {
    function sc_ajax_notice_handler()
    {
        return ppcart_ajax_notice_handler();
    }
}
if (! function_exists('sc_alert_print_scripts')) {
    function sc_alert_print_scripts()
    {
        return ppcart_alert_print_scripts();
    }
}
if (! function_exists('sc_build_product_notification_email')) {
    function sc_build_product_notification_email($n, $order_info)
    {
        return ppcart_build_product_notification_email($n, $order_info);
    }
}
if (! function_exists('sc_check_and_send_stock_notifications')) {
    function sc_check_and_send_stock_notifications($product_id)
    {
        return ppcart_check_and_send_stock_notifications($product_id);
    }
}
if (! function_exists('sc_check_currency_setting')) {
    function sc_check_currency_setting()
    {
        return ppcart_check_currency_setting();
    }
}
if (! function_exists('sc_clear_reminders')) {
    function sc_clear_reminders($type)
    {
        return ppcart_clear_reminders($type);
    }
}
if (! function_exists('sc_consent_services')) {
    function sc_consent_services()
    {
        return ppcart_consent_services();
    }
}
if (! function_exists('sc_countries_autocomplte_format_list')) {
    function sc_countries_autocomplte_format_list()
    {
        return ppcart_countries_autocomplte_format_list();
    }
}
if (! function_exists('sc_countries_list')) {
    function sc_countries_list()
    {
        return ppcart_countries_list();
    }
}
if (! function_exists('sc_create_user')) {
    function sc_create_user($order_id, $customerEmail, $first_name, $last_name, $user_role = '', $send_email_override = null, $change_roles = false)
    {
        return ppcart_create_user($order_id, $customerEmail, $first_name, $last_name, $user_role, $send_email_override, $change_roles);
    }
}
if (! function_exists('sc_csv_escape_cell')) {
    function sc_csv_escape_cell($value)
    {
        return ppcart_csv_escape_cell($value);
    }
}
if (! function_exists('sc_csv_row')) {
    function sc_csv_row($values)
    {
        return ppcart_csv_row($values);
    }
}
if (! function_exists('sc_currency')) {
    function sc_currency()
    {
        return ppcart_currency();
    }
}
if (! function_exists('sc_currency_countries_code_list')) {
    function sc_currency_countries_code_list()
    {
        return ppcart_currency_countries_code_list();
    }
}
if (! function_exists('sc_currency_settings')) {
    function sc_currency_settings()
    {
        return ppcart_currency_settings();
    }
}
if (! function_exists('sc_daily_event_reminder_activation')) {
    function sc_daily_event_reminder_activation($old_value, $value, $option)
    {
        return ppcart_daily_event_reminder_activation($old_value, $value, $option);
    }
}
if (! function_exists('sc_daily_event_trial_reminder_activation')) {
    function sc_daily_event_trial_reminder_activation($old_value, $value, $option)
    {
        return ppcart_daily_event_trial_reminder_activation($old_value, $value, $option);
    }
}
if (! function_exists('sc_data_testid_attribute')) {
    function sc_data_testid_attribute($value)
    {
        return ppcart_data_testid_attribute($value);
    }
}
if (! function_exists('sc_db_update_complete_notice')) {
    function sc_db_update_complete_notice()
    {
        return ppcart_db_update_complete_notice();
    }
}
if (! function_exists('sc_db_update_manually_notice')) {
    function sc_db_update_manually_notice()
    {
        return ppcart_db_update_manually_notice();
    }
}
if (! function_exists('sc_db_update_notice')) {
    function sc_db_update_notice()
    {
        return ppcart_db_update_notice();
    }
}
if (! function_exists('sc_enabled_processors')) {
    function sc_enabled_processors()
    {
        return ppcart_enabled_processors();
    }
}
if (! function_exists('sc_enqueue_checkout_inline_style')) {
    function sc_enqueue_checkout_inline_style($css)
    {
        return ppcart_enqueue_checkout_inline_style($css);
    }
}
if (! function_exists('sc_enqueue_or_print_inline_style')) {
    function sc_enqueue_or_print_inline_style($handle, $css, $fallback_handle = 'ppcart-inline-style')
    {
        return ppcart_enqueue_or_print_inline_style($handle, $css, $fallback_handle);
    }
}
if (! function_exists('sc_express_payment_method')) {
    function sc_express_payment_method($post_id)
    {
        return ppcart_express_payment_method($post_id);
    }
}
if (! function_exists('sc_fill_email_preview_order_data')) {
    function sc_fill_email_preview_order_data($order_info)
    {
        return ppcart_fill_email_preview_order_data($order_info);
    }
}
if (! function_exists('sc_filter_format_subcription_terms_text')) {
    function sc_filter_format_subcription_terms_text($text, $terms, $plan, $trial_days = false, $sign_up_fee = false, $discount = false, $discount_duration = false)
    {
        return ppcart_filter_format_subcription_terms_text($text, $terms, $plan, $trial_days, $sign_up_fee, $discount, $discount_duration);
    }
}
if (! function_exists('sc_filter_input_request')) {
    function sc_filter_input_request($key, $filter = FILTER_SANITIZE_FULL_SPECIAL_CHARS, $options = null)
    {
        return ppcart_filter_input_request($key, $filter, $options);
    }
}
if (! function_exists('sc_find_upcoming_renewals')) {
    function sc_find_upcoming_renewals($type)
    {
        return ppcart_find_upcoming_renewals($type);
    }
}
if (! function_exists('sc_formatted_price')) {
    function sc_formatted_price($price)
    {
        return ppcart_formatted_price($price);
    }
}
if (! function_exists('sc_generate_login_creds')) {
    function sc_generate_login_creds($customerEmail, $password = false)
    {
        return ppcart_generate_login_creds($customerEmail, $password);
    }
}
if (! function_exists('sc_hosted_checkout_confirmation_url')) {
    function sc_hosted_checkout_confirmation_url($order_id, $product_id)
    {
        return ppcart_hosted_checkout_confirmation_url($order_id, $product_id);
    }
}
if (! function_exists('sc_hosted_checkout_find_order_id_by_session')) {
    function sc_hosted_checkout_find_order_id_by_session($session_id)
    {
        return ppcart_hosted_checkout_find_order_id_by_session($session_id);
    }
}
if (! function_exists('sc_hosted_checkout_modes_to_try')) {
    function sc_hosted_checkout_modes_to_try($preferred = '')
    {
        return ppcart_hosted_checkout_modes_to_try($preferred);
    }
}
if (! function_exists('sc_hosted_checkout_resolve_return_context')) {
    function sc_hosted_checkout_resolve_return_context($session_id)
    {
        return ppcart_hosted_checkout_resolve_return_context($session_id);
    }
}
if (! function_exists('sc_issue_stripe_subscription_cancellation_refund')) {
    function sc_issue_stripe_subscription_cancellation_refund($sub, $stripe_sub, $stripe)
    {
        return ppcart_issue_stripe_subscription_cancellation_refund($sub, $stripe_sub, $stripe);
    }
}
if (! function_exists('sc_json_search_user')) {
    function sc_json_search_user()
    {
        return ppcart_json_search_user();
    }
}
if (! function_exists('sc_kses_email_html')) {
    function sc_kses_email_html($html)
    {
        return ppcart_kses_email_html($html);
    }
}
if (! function_exists('sc_localize_dt')) {
    function sc_localize_dt($date = 'now')
    {
        return ppcart_localize_dt($date);
    }
}
if (! function_exists('sc_log_entry')) {
    function sc_log_entry($order_id, $entry)
    {
        return ppcart_log_entry($order_id, $entry);
    }
}
if (! function_exists('sc_match_plan')) {
    function sc_match_plan($plan_ids, $order_price_ids)
    {
        return ppcart_match_plan($plan_ids, $order_price_ids);
    }
}
if (! function_exists('sc_matched_plan')) {
    function sc_matched_plan($order_data, $plan_ids, $order_ids = [])
    {
        return ppcart_matched_plan($order_data, $plan_ids, $order_ids);
    }
}
if (! function_exists('sc_maybe_auto_login_user')) {
    function sc_maybe_auto_login_user($user_id, $order_id)
    {
        return ppcart_maybe_auto_login_user($user_id, $order_id);
    }
}
if (! function_exists('sc_maybe_do_subscription_complete')) {
    function sc_maybe_do_subscription_complete($subscription_id)
    {
        return ppcart_maybe_do_subscription_complete($subscription_id);
    }
}
if (! function_exists('sc_maybe_format_date')) {
    function sc_maybe_format_date($str, $format = false)
    {
        return ppcart_maybe_format_date($str, $format);
    }
}
if (! function_exists('sc_maybe_schedule_reminders')) {
    function sc_maybe_schedule_reminders($type)
    {
        return ppcart_maybe_schedule_reminders($type);
    }
}
if (! function_exists('sc_maybe_update_stock')) {
    function sc_maybe_update_stock($product_id, $action = 'decrease', $qty = 1)
    {
        return ppcart_maybe_update_stock($product_id, $action, $qty);
    }
}
if (! function_exists('sc_merge_tag_list')) {
    function sc_merge_tag_list()
    {
        return ppcart_merge_tag_list();
    }
}
if (! function_exists('sc_merge_tag_select')) {
    function sc_merge_tag_select()
    {
        return ppcart_merge_tag_select();
    }
}
if (! function_exists('sc_meta_value_has_incomplete_object')) {
    function sc_meta_value_has_incomplete_object($value, $depth = 0)
    {
        return ppcart_meta_value_has_incomplete_object($value, $depth);
    }
}
