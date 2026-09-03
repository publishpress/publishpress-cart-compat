<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Leftover → canonical maps for custom tables.
 *
 * CPT slug maps live in the CPT Data migration package. Do not add CPT
 * slugs, option keys, shortcodes, or HTML class names here.
 *
 * @return array<string, array<string, mixed>>
 */
function ppcart_custom_table_family_map()
{
    return [
        'tax_rate'       => [
            'leftover'   => 'ncs_tax_rate',
            'canonical'  => 'ppcart_tax_rate',
        ],
        'order_items'    => [
            'leftover'   => 'ncs_order_items',
            'canonical'  => 'ppcart_order_items',
        ],
        'order_itemmeta' => [
            'leftover'   => 'ncs_order_itemmeta',
            'canonical'  => 'ppcart_order_itemmeta',
            'object_id_column' => [
                'leftover'  => 'ncs_order_item_id',
                'canonical' => 'ppcart_order_item_id',
            ],
        ],
        'downloads'      => [
            'leftover'   => 'ncs_downloads',
            'canonical'  => 'ppcart_downloads',
        ],
    ];
}

/**
 * Leftover → canonical metadata type for order item meta.
 *
 * Must stay paired with the order_itemmeta object-id column.
 *
 * @return array{leftover: string, canonical: string}
 */
function ppcart_custom_table_metadata_type_map()
{
    return [
        'leftover'  => 'ncs_order_item',
        'canonical' => 'ppcart_order_item',
    ];
}

/**
 * @param string $family Family key from ppcart_custom_table_family_map().
 * @return array<string, mixed>|null
 */
function ppcart_custom_table_family($family)
{
    $map = ppcart_custom_table_family_map();

    return isset($map[ $family ]) ? $map[ $family ] : null;
}
