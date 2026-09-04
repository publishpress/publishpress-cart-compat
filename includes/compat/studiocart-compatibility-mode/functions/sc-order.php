<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_order_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

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
