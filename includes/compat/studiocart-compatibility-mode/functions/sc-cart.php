<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_cart_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

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
