<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical Divi module slug => leftover slug, stored field names, and field types.
 *
 * Compatibility Mode dual-registers leftover `stof_studiocart_order_form`.
 *
 * @return array<string, array{legacy: string, fields: array<string, string>, field_types: array<string, string>}>
 */
return [
    'ppcart_order_form' => [
        'legacy'      => 'stof_studiocart_order_form',
        'fields'      => [
            'ppcart_product'     => 'sc_product',
            'ppcart_hide_labels' => 'sc_hide_labels',
            'ppcart_template'    => 'sc_template',
            'ppcart_coupon'      => 'sc_coupon',
            'ppcart_label_color' => 'sc_lable_color',
        ],
        'field_types' => [
            'ppcart_input' => 'stof_input',
        ],
    ],
];
