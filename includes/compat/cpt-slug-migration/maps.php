<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical maps for Free CPT, taxonomy, capability, and role-slug migration.
 *
 * @return array{
 *     post_types: array<string, array{legacy: string, canonical: string}>,
 *     pro_post_types: array<string, array{legacy: string, canonical: string}>,
 *     taxonomies: array<string, array{legacy: string, canonical: string, rewrite: string, rewrite_legacy: string}>,
 *     capabilities: array<string, string>,
 *     roles: array<string, string>
 * }
 */
function ppcart_cpt_slug_migration_maps()
{
    static $maps = null;

    if (null !== $maps) {
        return $maps;
    }

    $post_types = [
        'product'      => [ 'legacy' => 'sc_product', 'canonical' => 'ppcart_product' ],
        'order'        => [ 'legacy' => 'sc_order', 'canonical' => 'ppcart_order' ],
        'subscription' => [ 'legacy' => 'sc_subscription', 'canonical' => 'ppcart_subscription' ],
    ];

    $pro_post_types = [
        'collection'   => [ 'legacy' => 'sc_collection', 'canonical' => 'ppcart_collection' ],
        'us_path'      => [ 'legacy' => 'sc_us_path', 'canonical' => 'ppcart_us_path' ],
        'membership'   => [ 'legacy' => 'sc_membership', 'canonical' => 'ppcart_membership' ],
        'upgrade_path' => [ 'legacy' => 'sc_upgrade_path', 'canonical' => 'ppcart_upgrade_path' ],
    ];

    $capabilities = [
        'sc_manager_option' => 'ppcart_manager_option',
        'sc_manage_orders'  => 'ppcart_manage_orders',
    ];

    foreach ($post_types as $pair) {
        $legacy    = $pair['legacy'];
        $canonical = $pair['canonical'];
        $legacy_pl = $legacy . 's';
        $canon_pl  = $canonical . 's';
        $capabilities[ 'edit_' . $legacy ]                 = 'edit_' . $canonical;
        $capabilities[ 'read_' . $legacy ]                 = 'read_' . $canonical;
        $capabilities[ 'delete_' . $legacy ]               = 'delete_' . $canonical;
        $capabilities[ 'edit_' . $legacy_pl ]              = 'edit_' . $canon_pl;
        $capabilities[ 'edit_others_' . $legacy_pl ]       = 'edit_others_' . $canon_pl;
        $capabilities[ 'publish_' . $legacy_pl ]           = 'publish_' . $canon_pl;
        $capabilities[ 'read_private_' . $legacy_pl ]      = 'read_private_' . $canon_pl;
        $capabilities[ 'delete_' . $legacy_pl ]            = 'delete_' . $canon_pl;
        $capabilities[ 'delete_private_' . $legacy_pl ]    = 'delete_private_' . $canon_pl;
        $capabilities[ 'delete_published_' . $legacy_pl ]  = 'delete_published_' . $canon_pl;
        $capabilities[ 'delete_others_' . $legacy_pl ]     = 'delete_others_' . $canon_pl;
        $capabilities[ 'edit_private_' . $legacy_pl ]      = 'edit_private_' . $canon_pl;
        $capabilities[ 'edit_published_' . $legacy_pl ]    = 'edit_published_' . $canon_pl;
    }

    $maps = [
        'post_types'     => $post_types,
        'pro_post_types' => $pro_post_types,
        'taxonomies'     => [
            'product_cat' => [
                'legacy'         => 'sc_product_cat',
                'canonical'      => 'ppcart_product_cat',
                'rewrite'        => 'ppcart_product_cat',
                'rewrite_legacy' => 'sc_product_cat',
            ],
            'product_tag' => [
                'legacy'         => 'sc_product_tag',
                'canonical'      => 'ppcart_product_tag',
                'rewrite'        => 'ppcart_product_tag',
                'rewrite_legacy' => 'sc_product_tag',
            ],
        ],
        'capabilities' => $capabilities,
        'roles'        => [
            'sc_cart_manager'       => 'ppcart_manager',
            'sc_cart_administrator' => 'ppcart_administrator',
        ],
    ];

    return $maps;
}
