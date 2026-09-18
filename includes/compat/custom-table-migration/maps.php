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
        'affiliates'     => [
            'leftover'   => 'ncs_affiliates',
            'canonical'  => 'ppcart_affiliates',
        ],
        'affiliatemeta'  => [
            'leftover'   => 'ncs_affiliatemeta',
            'canonical'  => 'ppcart_affiliatemeta',
            'object_id_column' => [
                'leftover'  => 'ncs_affiliate_id',
                'canonical' => 'ppcart_affiliate_id',
            ],
        ],
        'affiliate_commissions' => [
            'leftover'   => 'ncs_affiliate_commissions',
            'canonical'  => 'ppcart_affiliate_commissions',
        ],
        'affiliate_payouts' => [
            'leftover'   => 'ncs_affiliate_payouts',
            'canonical'  => 'ppcart_affiliate_payouts',
        ],
        'affiliate_visits' => [
            'leftover'   => 'ncs_affiliate_visits',
            'canonical'  => 'ppcart_affiliate_visits',
        ],
        'affiliate_product_rel' => [
            'leftover'   => 'ncs_affiliate_product_rel',
            'canonical'  => 'ppcart_affiliate_product_rel',
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
 * Leftover → canonical metadata type for affiliate meta.
 *
 * Must stay paired with the affiliatemeta object-id column.
 *
 * @return array{leftover: string, canonical: string}
 */
function ppcart_custom_table_affiliate_metadata_type_map()
{
    return [
        'leftover'  => 'ncs_affiliate',
        'canonical' => 'ppcart_affiliate',
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
