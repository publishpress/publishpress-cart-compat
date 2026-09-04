<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrappers — sc_format_*.
 * Loaded only when Compatibility Mode is on (see functions/index.php).
 */

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
