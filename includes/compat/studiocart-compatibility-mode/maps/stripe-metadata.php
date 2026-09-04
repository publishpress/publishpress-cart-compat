<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical Stripe object metadata keys mapped to leftover StudioCart names.
 *
 * Compatibility Mode reads leftover keys when the canonical key is empty.
 * First-party writes stay canonical. This is not a class-alias map.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_order_id' => [ 'sc_order_id' ],
    'ppcart_option_id' => [ 'sc_option_id' ],
    'ppcart_product_id' => [ 'sc_product_id' ],
    'ppcart_stripe_subscription_id' => [ 'sc_stripe_subscription_id' ],
    'ppcart_subscription_id' => [ 'sc_subscription_id' ],
];
