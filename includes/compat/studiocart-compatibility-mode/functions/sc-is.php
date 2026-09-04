<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_is_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

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
