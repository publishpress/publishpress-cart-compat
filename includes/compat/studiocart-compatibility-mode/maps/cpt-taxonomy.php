<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical CPT / taxonomy / FSE / rewrite names mapped to leftover StudioCart names.
 *
 * Compatibility Mode registers leftover FSE template slugs and leftover taxonomy
 * rewrite aliases. First-party source strings stay canonical. This is not a class-alias map.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_collection' => [ 'sc_collection' ],
    'ppcart_membership' => [ 'sc_membership' ],
    'ppcart_upgrade_path' => [ 'sc_upgrade_path' ],
    'ppcart_us_path' => [ 'sc_us_path' ],
    'ppcart_product_cat' => [ 'sc_product_cat' ],
    'ppcart_product_tag' => [ 'sc_product_tag' ],
    'single-ppcart_product' => [ 'single-sc_product' ],
];
