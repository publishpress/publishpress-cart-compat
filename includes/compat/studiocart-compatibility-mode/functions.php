<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Legacy StudioCart function wrappers.
 *
 * Loaded only when StudioCart Compatibility Mode is enabled.
 */

if (! function_exists('ncs_account_tabs')) {
    function ncs_account_tabs()
    {
        return ppcart_account_tabs();
    }
}

if (! function_exists('ncs_cart_ajax_search_report_customers')) {
    function ncs_cart_ajax_search_report_customers()
    {
        return ppcart_ajax_search_report_customers();
    }
}

if (! function_exists('ncs_cart_ajax_search_report_products')) {
    function ncs_cart_ajax_search_report_products()
    {
        return ppcart_ajax_search_report_products();
    }
}

if (! function_exists('ncs_cart_apply_report_filters_to_query_args')) {
    function ncs_cart_apply_report_filters_to_query_args($args, $filters)
    {
        return ppcart_apply_report_filters_to_query_args($args, $filters);
    }
}

if (! function_exists('ncs_cart_format_report_customer_label')) {
    function ncs_cart_format_report_customer_label($email, $first_name = '', $last_name = '')
    {
        return ppcart_format_report_customer_label($email, $first_name, $last_name);
    }
}

if (! function_exists('ncs_cart_get_report_customer_label')) {
    function ncs_cart_get_report_customer_label($email)
    {
        return ppcart_get_report_customer_label($email);
    }
}

if (! function_exists('ncs_cart_get_report_identity_query_args')) {
    function ncs_cart_get_report_identity_query_args($filters)
    {
        return ppcart_get_report_identity_query_args($filters);
    }
}

if (! function_exists('ncs_cart_get_report_meta_query')) {
    function ncs_cart_get_report_meta_query($filters)
    {
        return ppcart_get_report_meta_query($filters);
    }
}

if (! function_exists('ncs_cart_get_report_product_label')) {
    function ncs_cart_get_report_product_label($product_id)
    {
        return ppcart_get_report_product_label($product_id);
    }
}

if (! function_exists('ncs_cart_parse_report_filters')) {
    function ncs_cart_parse_report_filters($source = null)
    {
        return ppcart_parse_report_filters($source);
    }
}

if (! function_exists('ncs_cart_repeater_entry_is_enabled')) {
    function ncs_cart_repeater_entry_is_enabled($entry, $key = 'enabled')
    {
        return ppcart_repeater_entry_is_enabled($entry, $key);
    }
}

if (! function_exists('ncs_cart_report_filter_scalar')) {
    function ncs_cart_report_filter_scalar($value)
    {
        return ppcart_report_filter_scalar($value);
    }
}

if (! function_exists('ncs_cart_report_identity_filters_are_active')) {
    function ncs_cart_report_identity_filters_are_active($filters)
    {
        return ppcart_report_identity_filters_are_active($filters);
    }
}

if (! function_exists('ncs_cart_search_report_customers')) {
    function ncs_cart_search_report_customers($term, $limit = 10)
    {
        return ppcart_search_report_customers($term, $limit);
    }
}

if (! function_exists('ncs_cart_search_report_products')) {
    function ncs_cart_search_report_products($term, $limit = 10)
    {
        return ppcart_search_report_products($term, $limit);
    }
}

if (! function_exists('ncs_cart_value_is_enabled')) {
    function ncs_cart_value_is_enabled($value)
    {
        return ppcart_value_is_enabled($value);
    }
}

if (! function_exists('ncs_email_schedule_hook')) {
    function ncs_email_schedule_hook($schedules)
    {
        return ppcart_email_schedule_hook($schedules);
    }
}

if (! function_exists('ncs_get_template')) {
    function ncs_get_template($slug, $name = '', $attr = [])
    {
        return ppcart_get_template($slug, $name, $attr);
    }
}

if (! function_exists('ncs_get_template_path')) {
    function ncs_get_template_path($slug, $name = '')
    {
        return ppcart_get_template_path($slug, $name);
    }
}

if (! function_exists('ncs_helper')) {
    function ncs_helper()
    {
        return ppcart_helper();
    }
}

if (! function_exists('ncs_price_format_error')) {
    function ncs_price_format_error()
    {
        return ppcart_price_format_error();
    }
}

if (! function_exists('ncs_register_report_schedule_event')) {
    function ncs_register_report_schedule_event()
    {
        return ppcart_register_report_schedule_event();
    }
}

if (! function_exists('ncs_schedule_email_function')) {
    function ncs_schedule_email_function()
    {
        return ppcart_schedule_email_function();
    }
}

if (! function_exists('ncs_schedule_email_temp')) {
    function ncs_schedule_email_temp()
    {
        return ppcart_schedule_email_temp();
    }
}

if (! function_exists('ncs_schedule_function')) {
    function ncs_schedule_function()
    {
        return ppcart_schedule_function();
    }
}

if (! function_exists('ncs_schedule_reminder')) {
    function ncs_schedule_reminder($sub, $days)
    {
        return ppcart_schedule_reminder($sub, $days);
    }
}

if (! function_exists('ncs_schedule_report_collect_cancellation')) {
    function ncs_schedule_report_collect_cancellation(&$summary, $subscription, $start, $end)
    {
        return ppcart_schedule_report_collect_cancellation($subscription, $start, $end);
    }
}

if (! function_exists('ncs_schedule_report_collect_renewal')) {
    function ncs_schedule_report_collect_renewal(&$summary, $subscription, $start, $end)
    {
        return ppcart_schedule_report_collect_renewal($subscription, $start, $end);
    }
}

if (! function_exists('ncs_schedule_report_data')) {
    function ncs_schedule_report_data()
    {
        return ppcart_schedule_report_data();
    }
}

if (! function_exists('ncs_schedule_report_date_query')) {
    function ncs_schedule_report_date_query($date1, $date2)
    {
        return ppcart_schedule_report_date_query($date1, $date2);
    }
}

if (! function_exists('ncs_schedule_report_email_html')) {
    function ncs_schedule_report_email_html($report)
    {
        return ppcart_schedule_report_email_html($report);
    }
}

if (! function_exists('ncs_schedule_report_order_args')) {
    function ncs_schedule_report_order_args($date1, $date2)
    {
        return ppcart_schedule_report_order_args($date1, $date2);
    }
}

if (! function_exists('ncs_schedule_report_order_summary')) {
    function ncs_schedule_report_order_summary($order_args, $subscription_args)
    {
        return ppcart_schedule_report_order_summary($order_args, $subscription_args);
    }
}

if (! function_exists('ncs_schedule_report_period')) {
    function ncs_schedule_report_period($schedule)
    {
        return ppcart_schedule_report_period($schedule);
    }
}

if (! function_exists('ncs_schedule_report_refund_args')) {
    function ncs_schedule_report_refund_args($date1, $date2)
    {
        return ppcart_schedule_report_refund_args($date1, $date2);
    }
}

if (! function_exists('ncs_schedule_report_refund_summary')) {
    function ncs_schedule_report_refund_summary($args)
    {
        return ppcart_schedule_report_refund_summary($args);
    }
}

if (! function_exists('ncs_schedule_report_render_intro')) {
    function ncs_schedule_report_render_intro($report)
    {
        return ppcart_schedule_report_render_intro($report);
    }
}

if (! function_exists('ncs_schedule_report_render_revenue_list')) {
    function ncs_schedule_report_render_revenue_list($report)
    {
        return ppcart_schedule_report_render_revenue_list($report);
    }
}

if (! function_exists('ncs_schedule_report_render_subscription_table')) {
    function ncs_schedule_report_render_subscription_table($heading, $rows, $date_heading, $amount_heading, $heading_class, $table_class)
    {
        return ppcart_schedule_report_render_subscription_table($heading, $rows, $date_heading, $amount_heading, $heading_class, $table_class);
    }
}

if (! function_exists('ncs_schedule_report_render_upcoming_period')) {
    function ncs_schedule_report_render_upcoming_period($report)
    {
        return ppcart_schedule_report_render_upcoming_period($report);
    }
}

if (! function_exists('ncs_schedule_report_subscription_args')) {
    function ncs_schedule_report_subscription_args()
    {
        return ppcart_schedule_report_subscription_args();
    }
}

if (! function_exists('ncs_schedule_report_subscription_summary')) {
    function ncs_schedule_report_subscription_summary($start_date, $end_date)
    {
        return ppcart_schedule_report_subscription_summary($start_date, $end_date);
    }
}

if (! function_exists('ncs_template')) {
    function ncs_template($slug, $name = '', $attr = [])
    {
        return ppcart_template($slug, $name, $attr);
    }
}

if (! function_exists('pp_cart_testid')) {
    function pp_cart_testid($value)
    {
        return ppcart_testid($value);
    }
}

if (! function_exists('sc_add_remove_activecampaign_subscriber')) {
    function sc_add_remove_activecampaign_subscriber($order_id, $service_id, $action_name, $list_id, $_sc_mail_tags, $_sc_mail_groups, $email, $phone, $fname, $lname, $fieldmap)
    {
        return ppcart_add_remove_activecampaign_subscriber($order_id, $service_id, $action_name, $list_id, $_sc_mail_tags, $_sc_mail_groups, $email, $phone, $fname, $lname, $fieldmap);
    }
}

if (! function_exists('sc_add_remove_mailchimp_subscriber')) {
    function sc_add_remove_mailchimp_subscriber($order_id, $service_id, $action_name, $list_id, $_sc_mail_tags, $_sc_mail_groups, $email, $phone, $fname, $lname, $intg, $order)
    {
        return ppcart_add_remove_mailchimp_subscriber($order_id, $service_id, $action_name, $list_id, $_sc_mail_tags, $_sc_mail_groups, $email, $phone, $fname, $lname, $intg, $order);
    }
}

if (! function_exists('sc_add_remove_mailpoet_subscriber')) {
    function sc_add_remove_mailpoet_subscriber($order_id, $_sc_services, $_sc_service_action, $mailpoet_list, $customerEmail, $first_name, $last_name)
    {
        return ppcart_add_remove_mailpoet_subscriber($order_id, $_sc_services, $_sc_service_action, $mailpoet_list, $customerEmail, $first_name, $last_name);
    }
}

if (! function_exists('sc_add_remove_membervault_subscriber')) {
    function sc_add_remove_membervault_subscriber($order_id, $service_id, $action_name, $member_vault_course_id, $email, $phone, $fname, $lname)
    {
        return ppcart_add_remove_membervault_subscriber($order_id, $service_id, $action_name, $member_vault_course_id, $email, $phone, $fname, $lname);
    }
}

if (! function_exists('sc_add_remove_sendfox_subscriber')) {
    function sc_add_remove_sendfox_subscriber($order_id, $_sc_services, $_sc_service_action, $sendfox_list, $customerEmail, $first_name, $last_name)
    {
        return ppcart_add_remove_sendfox_subscriber($order_id, $_sc_services, $_sc_service_action, $sendfox_list, $customerEmail, $first_name, $last_name);
    }
}

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

if (! function_exists('sc_cart_is_pro')) {
    function sc_cart_is_pro()
    {
        return ppcart_cart_is_pro();
    }
}

if (! function_exists('sc_cart_pro_feature_lock')) {
    function sc_cart_pro_feature_lock($args = [])
    {
        return ppcart_cart_pro_feature_lock($args);
    }
}

if (! function_exists('sc_cart_pro_is_locked')) {
    function sc_cart_pro_is_locked()
    {
        return ppcart_cart_pro_is_locked();
    }
}

if (! function_exists('sc_cart_pro_locked_control_html')) {
    function sc_cart_pro_locked_control_html($field)
    {
        return ppcart_cart_pro_locked_control_html($field);
    }
}

if (! function_exists('sc_cart_pro_locked_email_rows_html')) {
    function sc_cart_pro_locked_email_rows_html($exclude_titles = [])
    {
        return ppcart_cart_pro_locked_email_rows_html($exclude_titles);
    }
}

if (! function_exists('sc_cart_pro_locked_emails')) {
    function sc_cart_pro_locked_emails()
    {
        return ppcart_cart_pro_locked_emails();
    }
}

if (! function_exists('sc_cart_pro_locked_field_rows_html')) {
    function sc_cart_pro_locked_field_rows_html($context)
    {
        return ppcart_cart_pro_locked_field_rows_html($context);
    }
}

if (! function_exists('sc_cart_pro_locked_fields')) {
    function sc_cart_pro_locked_fields($context)
    {
        return ppcart_cart_pro_locked_fields($context);
    }
}

if (! function_exists('sc_cart_pro_locked_integration_keys')) {
    function sc_cart_pro_locked_integration_keys()
    {
        return ppcart_cart_pro_locked_integration_keys();
    }
}

if (! function_exists('sc_cart_pro_locked_payment_methods')) {
    function sc_cart_pro_locked_payment_methods()
    {
        return ppcart_cart_pro_locked_payment_methods();
    }
}

if (! function_exists('sc_cart_pro_locked_product_field_rows_html')) {
    function sc_cart_pro_locked_product_field_rows_html($tab_id, $with_lock = true)
    {
        return ppcart_cart_pro_locked_product_field_rows_html($tab_id, $with_lock);
    }
}

if (! function_exists('sc_cart_pro_locked_product_fields')) {
    function sc_cart_pro_locked_product_fields($tab_id)
    {
        return ppcart_cart_pro_locked_product_fields($tab_id);
    }
}

if (! function_exists('sc_cart_pro_locked_product_tabs')) {
    function sc_cart_pro_locked_product_tabs()
    {
        return ppcart_cart_pro_locked_product_tabs();
    }
}

if (! function_exists('sc_cart_pro_locked_settings_field_sections')) {
    function sc_cart_pro_locked_settings_field_sections()
    {
        return ppcart_cart_pro_locked_settings_field_sections();
    }
}

if (! function_exists('sc_cart_pro_locked_tabs')) {
    function sc_cart_pro_locked_tabs()
    {
        return ppcart_cart_pro_locked_tabs();
    }
}

if (! function_exists('sc_cart_pro_nav_badge')) {
    function sc_cart_pro_nav_badge()
    {
        return ppcart_cart_pro_nav_badge();
    }
}

if (! function_exists('sc_cart_pro_panel_lock_overlay')) {
    function sc_cart_pro_panel_lock_overlay($args = [])
    {
        return ppcart_cart_pro_panel_lock_overlay($args);
    }
}

if (! function_exists('sc_cart_pro_render_locked_product_tab')) {
    function sc_cart_pro_render_locked_product_tab($tab_id)
    {
        return ppcart_cart_pro_render_locked_product_tab($tab_id);
    }
}

if (! function_exists('sc_cart_pro_upgrade_url')) {
    function sc_cart_pro_upgrade_url($context = '')
    {
        return ppcart_cart_pro_upgrade_url($context);
    }
}

if (! function_exists('sc_cart_render_admin_sidebar')) {
    function sc_cart_render_admin_sidebar()
    {
        return ppcart_cart_render_admin_sidebar();
    }
}

if (! function_exists('sc_cart_supports')) {
    function sc_cart_supports($feature)
    {
        return ppcart_cart_supports($feature);
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

if (! function_exists('sc_checkout_arranged_core_card_details_callbacks')) {
    function sc_checkout_arranged_core_card_details_callbacks()
    {
        return ppcart_checkout_arranged_core_card_details_callbacks();
    }
}

if (! function_exists('sc_checkout_card_details_callback')) {
    function sc_checkout_card_details_callback($callback, $priority)
    {
        return ppcart_checkout_card_details_callback($callback, $priority);
    }
}

if (! function_exists('sc_checkout_claim_request_render')) {
    function sc_checkout_claim_request_render($context = [])
    {
        return ppcart_checkout_claim_request_render($context);
    }
}

if (! function_exists('sc_checkout_content_sections')) {
    function sc_checkout_content_sections()
    {
        return ppcart_checkout_content_sections();
    }
}

if (! function_exists('sc_checkout_default_content_order')) {
    function sc_checkout_default_content_order()
    {
        return ppcart_checkout_default_content_order();
    }
}

if (! function_exists('sc_checkout_get_arrangement_context')) {
    function sc_checkout_get_arrangement_context()
    {
        return ppcart_checkout_get_arrangement_context();
    }
}

if (! function_exists('sc_checkout_get_request_render_key')) {
    function sc_checkout_get_request_render_key($context = [])
    {
        return ppcart_checkout_get_request_render_key($context);
    }
}

if (! function_exists('sc_checkout_get_text_settings')) {
    function sc_checkout_get_text_settings()
    {
        return ppcart_checkout_get_text_settings();
    }
}

if (! function_exists('sc_checkout_has_arranged_content')) {
    function sc_checkout_has_arranged_content()
    {
        return ppcart_checkout_has_arranged_content();
    }
}

if (! function_exists('sc_checkout_has_card_details_callback')) {
    function sc_checkout_has_card_details_callback($callback)
    {
        return ppcart_checkout_has_card_details_callback($callback);
    }
}

if (! function_exists('sc_checkout_has_text_setting')) {
    function sc_checkout_has_text_setting($key)
    {
        return ppcart_checkout_has_text_setting($key);
    }
}

if (! function_exists('sc_checkout_normalize_content_order')) {
    function sc_checkout_normalize_content_order($content_order)
    {
        return ppcart_checkout_normalize_content_order($content_order);
    }
}

if (! function_exists('sc_checkout_product_context')) {
    function sc_checkout_product_context($post_id = 0)
    {
        return ppcart_checkout_product_context($post_id);
    }
}

if (! function_exists('sc_checkout_render_section')) {
    function sc_checkout_render_section($section_id, $post_id, $hide_labels, $plan = false, $template = '')
    {
        return ppcart_checkout_render_section($section_id, $post_id, $hide_labels, $plan, $template);
    }
}

if (! function_exists('sc_checkout_reset_request_render_guard')) {
    function sc_checkout_reset_request_render_guard()
    {
        return ppcart_checkout_reset_request_render_guard();
    }
}

if (! function_exists('sc_checkout_selected_plan')) {
    function sc_checkout_selected_plan($post_id, $plan = false)
    {
        return ppcart_checkout_selected_plan($post_id, $plan);
    }
}

if (! function_exists('sc_checkout_should_guard_duplicate_render')) {
    function sc_checkout_should_guard_duplicate_render($context = [])
    {
        return ppcart_checkout_should_guard_duplicate_render($context);
    }
}

if (! function_exists('sc_checkout_testid_suffix')) {
    function sc_checkout_testid_suffix($value)
    {
        return ppcart_checkout_testid_suffix($value);
    }
}

if (! function_exists('sc_checkout_text_setting')) {
    function sc_checkout_text_setting($key, $default)
    {
        return ppcart_checkout_text_setting($key, $default);
    }
}

if (! function_exists('sc_checkout_total_label')) {
    function sc_checkout_total_label($post_id, $plan = false)
    {
        return ppcart_checkout_total_label($post_id, $plan);
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

if (! function_exists('sc_do_2step_checkout_form_open')) {
    function sc_do_2step_checkout_form_open($post_id)
    {
        return ppcart_do_2step_checkout_form_open($post_id);
    }
}

if (! function_exists('sc_do_2step_checkoutform_fields')) {
    function sc_do_2step_checkoutform_fields($post_id, $hide_labels)
    {
        return ppcart_do_2step_checkoutform_fields($post_id, $hide_labels);
    }
}

if (! function_exists('sc_do_arranged_checkout_form')) {
    function sc_do_arranged_checkout_form($post_id, $hide_labels, $plan = false)
    {
        return ppcart_do_arranged_checkout_form($post_id, $hide_labels, $plan);
    }
}

if (! function_exists('sc_do_cancel_subscription')) {
    function sc_do_cancel_subscription($sub, $sub_id = false, $now = true, $echo = true, $options = [])
    {
        return ppcart_do_cancel_subscription($sub, $sub_id, $now, $echo, $options);
    }
}

if (! function_exists('sc_do_card_details_fields')) {
    function sc_do_card_details_fields($post_id, $hide_labels)
    {
        return ppcart_do_card_details_fields($post_id, $hide_labels);
    }
}

if (! function_exists('sc_do_cart_closed_message')) {
    function sc_do_cart_closed_message()
    {
        return ppcart_do_cart_closed_message();
    }
}

if (! function_exists('sc_do_checkout_form')) {
    function sc_do_checkout_form($post_id, $hide_labels, $plan = false)
    {
        return ppcart_do_checkout_form($post_id, $hide_labels, $plan);
    }
}

if (! function_exists('sc_do_checkout_form_close')) {
    function sc_do_checkout_form_close()
    {
        return ppcart_do_checkout_form_close();
    }
}

if (! function_exists('sc_do_checkout_form_open')) {
    function sc_do_checkout_form_open($post_id)
    {
        return ppcart_do_checkout_form_open($post_id);
    }
}

if (! function_exists('sc_do_checkout_form_scripts')) {
    function sc_do_checkout_form_scripts($prod_id, $coupon = false)
    {
        return ppcart_do_checkout_form_scripts($prod_id, $coupon);
    }
}

if (! function_exists('sc_do_checkout_hidden_fields')) {
    function sc_do_checkout_hidden_fields($post_id)
    {
        return ppcart_do_checkout_hidden_fields($post_id);
    }
}

if (! function_exists('sc_do_checkoutform_fields')) {
    function sc_do_checkoutform_fields($post_id, $hide_labels, $twostep = false)
    {
        return ppcart_do_checkoutform_fields($post_id, $hide_labels, $twostep);
    }
}

if (! function_exists('sc_do_confirmation_message')) {
    function sc_do_confirmation_message()
    {
        return ppcart_do_confirmation_message();
    }
}

if (! function_exists('sc_do_coupon_section')) {
    function sc_do_coupon_section($post_id)
    {
        return ppcart_do_coupon_section($post_id);
    }
}

if (! function_exists('sc_do_error_messages')) {
    function sc_do_error_messages()
    {
        return ppcart_do_error_messages();
    }
}

if (! function_exists('sc_do_express_payment_section')) {
    function sc_do_express_payment_section($post_id)
    {
        return ppcart_do_express_payment_section($post_id);
    }
}

if (! function_exists('sc_do_field')) {
    function sc_do_field($args)
    {
        return ppcart_do_field($args);
    }
}

if (! function_exists('sc_do_integrations')) {
    function sc_do_integrations($sc_product_id, $order, $trigger = 'purchased')
    {
        return ppcart_do_integrations($sc_product_id, $order, $trigger);
    }
}

if (! function_exists('sc_do_notifications')) {
    function sc_do_notifications($order_info)
    {
        return ppcart_do_notifications($order_info);
    }
}

if (! function_exists('sc_do_order_summary')) {
    function sc_do_order_summary($post_id, $plan = false)
    {
        return ppcart_do_order_summary($post_id, $plan);
    }
}

if (! function_exists('sc_do_order_summary_content')) {
    function sc_do_order_summary_content($post_id, $plan = false)
    {
        return ppcart_do_order_summary_content($post_id, $plan);
    }
}

if (! function_exists('sc_do_order_summary_section')) {
    function sc_do_order_summary_section($post_id, $plan = false)
    {
        return ppcart_do_order_summary_section($post_id, $plan);
    }
}

if (! function_exists('sc_do_order_table')) {
    function sc_do_order_table($type, $order)
    {
        return ppcart_do_order_table($type, $order);
    }
}

if (! function_exists('sc_do_payment_confirmation')) {
    function sc_do_payment_confirmation($prod_id)
    {
        return ppcart_do_payment_confirmation($prod_id);
    }
}

if (! function_exists('sc_do_payment_details_content')) {
    function sc_do_payment_details_content($post_id, $hide_labels)
    {
        return ppcart_do_payment_details_content($post_id, $hide_labels);
    }
}

if (! function_exists('sc_do_payment_details_section')) {
    function sc_do_payment_details_section($post_id, $hide_labels)
    {
        return ppcart_do_payment_details_section($post_id, $hide_labels);
    }
}

if (! function_exists('sc_do_payment_method_section')) {
    function sc_do_payment_method_section($post_id)
    {
        return ppcart_do_payment_method_section($post_id);
    }
}

if (! function_exists('sc_do_payment_methods')) {
    function sc_do_payment_methods($post_id)
    {
        return ppcart_do_payment_methods($post_id);
    }
}

if (! function_exists('sc_do_remaining_card_details_fields')) {
    function sc_do_remaining_card_details_fields($post_id, $hide_labels, $plan = false)
    {
        return ppcart_do_remaining_card_details_fields($post_id, $hide_labels, $plan);
    }
}

if (! function_exists('sc_do_submit_button_content')) {
    function sc_do_submit_button_content()
    {
        return ppcart_do_submit_button_content();
    }
}

if (! function_exists('sc_do_submit_button_section')) {
    function sc_do_submit_button_section()
    {
        return ppcart_do_submit_button_section();
    }
}

if (! function_exists('sc_do_terms_consent_content')) {
    function sc_do_terms_consent_content($post_id)
    {
        return ppcart_do_terms_consent_content($post_id);
    }
}

if (! function_exists('sc_do_terms_consent_section')) {
    function sc_do_terms_consent_section($post_id)
    {
        return ppcart_do_terms_consent_section($post_id);
    }
}

if (! function_exists('sc_do_test_mode_message')) {
    function sc_do_test_mode_message()
    {
        return ppcart_do_test_mode_message();
    }
}

if (! function_exists('sc_do_vat_info')) {
    function sc_do_vat_info()
    {
        return ppcart_do_vat_info();
    }
}

if (! function_exists('sc_email_personalize_replacements')) {
    function sc_email_personalize_replacements($replacements, $order_info)
    {
        return ppcart_email_personalize_replacements($replacements, $order_info);
    }
}

if (! function_exists('sc_email_preview_value_is_empty')) {
    function sc_email_preview_value_is_empty($value)
    {
        return ppcart_email_preview_value_is_empty($value);
    }
}

if (! function_exists('sc_email_reply_to_header')) {
    function sc_email_reply_to_header($args)
    {
        return ppcart_email_reply_to_header($args);
    }
}

if (! function_exists('sc_email_template_alias_options')) {
    function sc_email_template_alias_options()
    {
        return ppcart_email_template_alias_options();
    }
}

if (! function_exists('sc_email_template_default_option')) {
    function sc_email_template_default_option($default, $option = '')
    {
        return ppcart_email_template_default_option($default, $option);
    }
}

if (! function_exists('sc_email_template_field_from_option')) {
    function sc_email_template_field_from_option($option_id)
    {
        return ppcart_email_template_field_from_option($option_id);
    }
}

if (! function_exists('sc_email_template_key_from_section')) {
    function sc_email_template_key_from_section($section)
    {
        return ppcart_email_template_key_from_section($section);
    }
}

if (! function_exists('sc_email_template_option_defaults')) {
    function sc_email_template_option_defaults()
    {
        return ppcart_email_template_option_defaults();
    }
}

if (! function_exists('sc_email_template_option_names')) {
    function sc_email_template_option_names()
    {
        return ppcart_email_template_option_names();
    }
}

if (! function_exists('sc_email_template_option_value')) {
    function sc_email_template_option_value($value, $option = '')
    {
        return ppcart_email_template_option_value($value, $option);
    }
}

if (! function_exists('sc_email_template_text_field_from_option')) {
    function sc_email_template_text_field_from_option($option_id)
    {
        return ppcart_email_template_text_field_from_option($option_id);
    }
}

if (! function_exists('sc_email_templates')) {
    function sc_email_templates()
    {
        return ppcart_email_templates();
    }
}

if (! function_exists('sc_email_type_uses_editable_order_details')) {
    function sc_email_type_uses_editable_order_details($type)
    {
        return ppcart_email_type_uses_editable_order_details($type);
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

if (! function_exists('sc_format_address')) {
    function sc_format_address($address)
    {
        return ppcart_format_address($address);
    }
}

if (! function_exists('sc_format_number')) {
    function sc_format_number($amt)
    {
        return ppcart_format_number($amt);
    }
}

if (! function_exists('sc_format_order_address')) {
    function sc_format_order_address($order)
    {
        return ppcart_format_order_address($order);
    }
}

if (! function_exists('sc_format_price')) {
    function sc_format_price($amt, $html = true)
    {
        return ppcart_format_price($amt, $html);
    }
}

if (! function_exists('sc_format_stripe_number')) {
    function sc_format_stripe_number($amount, $sc_currency = 'USD')
    {
        return ppcart_format_stripe_number($amount, $sc_currency);
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

if (! function_exists('sc_is_cart_closed')) {
    function sc_is_cart_closed($prod_id = false)
    {
        return ppcart_is_cart_closed($prod_id);
    }
}

if (! function_exists('sc_is_email_template_customized')) {
    function sc_is_email_template_customized($template_key, $field = '')
    {
        return ppcart_is_email_template_customized($template_key, $field);
    }
}

if (! function_exists('sc_is_file_valid_csv')) {
    function sc_is_file_valid_csv($file, $check_path = true)
    {
        return ppcart_is_file_valid_csv($file, $check_path);
    }
}

if (! function_exists('sc_is_number_formatted')) {
    function sc_is_number_formatted($amt, $decisep = '.', $thousep = ',', $decinum = 2)
    {
        return ppcart_is_number_formatted($amt, $decisep, $thousep, $decinum);
    }
}

if (! function_exists('sc_is_prod_on_sale')) {
    function sc_is_prod_on_sale($prod_id = false)
    {
        return ppcart_is_prod_on_sale($prod_id);
    }
}

if (! function_exists('sc_is_sensitive_option')) {
    function sc_is_sensitive_option($option_name)
    {
        return ppcart_is_sensitive_option($option_name);
    }
}

if (! function_exists('sc_is_stripe_backed_post')) {
    function sc_is_stripe_backed_post($post_id)
    {
        return ppcart_is_stripe_backed_post($post_id);
    }
}

if (! function_exists('sc_is_stripe_owned_field')) {
    function sc_is_stripe_owned_field($field, $record = null)
    {
        return ppcart_is_stripe_owned_field($field, $record);
    }
}

if (! function_exists('sc_is_stripe_publishable_key')) {
    function sc_is_stripe_publishable_key($key, $mode = '')
    {
        return ppcart_is_stripe_publishable_key($key, $mode);
    }
}

if (! function_exists('sc_is_stripe_secret_key')) {
    function sc_is_stripe_secret_key($key, $mode = '')
    {
        return ppcart_is_stripe_secret_key($key, $mode);
    }
}

if (! function_exists('sc_is_studiocart')) {
    function sc_is_studiocart()
    {
        return ppcart_is_studiocart();
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

if (! function_exists('sc_order_address')) {
    function sc_order_address($id)
    {
        return ppcart_order_address($id);
    }
}

if (! function_exists('sc_order_detail')) {
    function sc_order_detail($atts)
    {
        return ppcart_order_detail($atts);
    }
}

if (! function_exists('sc_order_details')) {
    function sc_order_details($order_id)
    {
        return ppcart_order_details($order_id);
    }
}

if (! function_exists('sc_order_log')) {
    function sc_order_log($order_id)
    {
        return ppcart_order_log($order_id);
    }
}

if (! function_exists('sc_order_refund')) {
    function sc_order_refund($data)
    {
        return ppcart_order_refund($data);
    }
}

if (! function_exists('sc_order_summary_info')) {
    function sc_order_summary_info($post_id, $plan = false)
    {
        return ppcart_order_summary_info($post_id, $plan);
    }
}

if (! function_exists('sc_order_summary_shortcode')) {
    function sc_order_summary_shortcode()
    {
        return ppcart_order_summary_shortcode();
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
