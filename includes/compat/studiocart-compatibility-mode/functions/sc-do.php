<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_do_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

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
