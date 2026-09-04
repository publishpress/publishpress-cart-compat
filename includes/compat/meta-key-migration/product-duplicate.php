<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Leftover product-duplicate handling for PublishPress Cart Free.
 *
 * Free skips `_sc_*` write keys. When cart-compat is loaded, rewrite leftover
 * source rows to canonical `_ppcart_*` (and exclude leftover Stripe identity).
 * Persistent leftover rows are renamed by merchant Data migration
 * (`PPCartComp_Meta_Key_Migrator`, LIKE `_sc_%` on postmeta/usermeta) — not by Free.
 */

add_filter('ppcart_product_duplicate_meta_key', 'ppcartcomp_product_duplicate_meta_key', 10, 2);
add_filter('ppcart_product_duplicate_excluded_meta_keys', 'ppcartcomp_product_duplicate_excluded_meta_keys');
add_filter('ppcart_default_fields_ids', 'ppcartcomp_default_fields_ids');

/**
 * Rewrite leftover `_sc_*` product meta to canonical on duplicate.
 *
 * @param string               $meta_key Source meta key (or prior filter result).
 * @param array<string, mixed> $all_meta Source custom meta.
 * @return string|null
 */
function ppcartcomp_product_duplicate_meta_key($meta_key, $all_meta)
{
    if (! is_string($meta_key) || ! ppcart_is_legacy_meta_key($meta_key)) {
        return $meta_key;
    }

    $canonical = ppcart_meta_key($meta_key);
    if (isset($all_meta[ $canonical ])) {
        return null;
    }

    return $canonical;
}

/**
 * @param array<int, string> $excluded_meta Excluded meta keys.
 * @return array<int, string>
 */
function ppcartcomp_product_duplicate_excluded_meta_keys($excluded_meta)
{
    $excluded_meta   = is_array($excluded_meta) ? $excluded_meta : [];
    $excluded_meta[] = ppcart_legacy_meta_key('stripe_prod_id');

    return array_values(array_unique(array_map('strval', $excluded_meta)));
}

/**
 * Accept leftover default_fields POST/metabox ids when Compatibility Mode is on.
 *
 * @param array<int, string> $ids Field ids.
 * @return array<int, string>
 */
function ppcartcomp_default_fields_ids($ids)
{
    $ids = is_array($ids) ? $ids : [];

    if (
        ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
        || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
    ) {
        return $ids;
    }

    $ids[] = ppcart_legacy_meta_key('default_fields');

    return array_values(array_unique(array_map('strval', $ids)));
}
