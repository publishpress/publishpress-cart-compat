<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_email_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

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
