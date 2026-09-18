<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical cancel-subscription repeater field ids mapped to leftover keys.
 *
 * Compatibility Mode copies leftover inner keys onto canonical keys when the
 * canonical key is missing. Stored rows are not rewritten.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_sub_prod_id' => [ 'sc_sub_prod_id' ],
    'ppcart_sub_plan_id' => [ 'sc_sub_plan_id' ],
    'ppcart_sub_cancel' => [ 'sc_sub_cancel' ],
    'ppcart_refund_prod_id' => [ 'sc_refund_prod_id' ],
];
