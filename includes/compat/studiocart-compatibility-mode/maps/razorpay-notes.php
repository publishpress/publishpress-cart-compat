<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical Razorpay notes keys mapped to leftover StudioCart names.
 *
 * Compatibility Mode reads leftover keys when the canonical key is empty.
 * First-party writes stay canonical. This is not a class-alias map.
 *
 * `order_id` is the unprefixed notes key the Razorpay webhook previously read.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_subscription_id' => [ 'sc_subscription_id' ],
    'ppcart_order_id' => [ 'sc_order_id', 'order_id' ],
];
