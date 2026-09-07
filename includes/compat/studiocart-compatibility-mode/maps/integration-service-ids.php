<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical integration `services` ids mapped to leftover values.
 *
 * Compatibility Mode remaps leftover `services` onto canonical ids when the
 * current value is leftover. Stored rows are not rewritten.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_subscription' => [ 'sc_subscription' ],
    'ppcart_refund_order' => [ 'sc_refund_order' ],
    'ppcart_wpdomainchecker' => [ 'sc_wpdomainchecker' ],
];
