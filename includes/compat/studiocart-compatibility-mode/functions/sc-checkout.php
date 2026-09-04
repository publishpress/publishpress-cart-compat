<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_checkout_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

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
