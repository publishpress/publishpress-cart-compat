<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — ncs_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
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
