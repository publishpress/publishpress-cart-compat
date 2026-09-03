<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Live Free post type slug for a family (product|order|subscription).
 *
 * During a mixed step 1, new writes use the canonical slug; queries should
 * call ppcart_query_post_types() instead.
 *
 * @param string $family Family key.
 * @return string
 */
function ppcart_live_post_type($family)
{
    $maps = ppcart_cpt_slug_migration_maps();
    if (! isset($maps['post_types'][ $family ])) {
        return '';
    }

    $pair = $maps['post_types'][ $family ];

    if (ppcart_cpt_slug_migration_step1_complete() || ppcart_cpt_slug_migration_step1_mixed()) {
        $slug = $pair['canonical'];
    } else {
        $slug = $pair['legacy'];
    }

    // Writes must target a registered CPT. Missing migration state defaults to
    // leftover slugs even after canonical CPTs are already registered.
    if (function_exists('post_type_exists') && $slug && ! post_type_exists($slug)) {
        $fallback = $slug === $pair['legacy'] ? $pair['canonical'] : $pair['legacy'];
        if (post_type_exists($fallback)) {
            return $fallback;
        }
    }

    return $slug;
}

/**
 * Post type slugs to use in WP_Query / SQL IN lists.
 *
 * @param string $family Family key.
 * @return array<int, string>
 */
function ppcart_query_post_types($family)
{
    $maps = ppcart_cpt_slug_migration_maps();
    if (! isset($maps['post_types'][ $family ])) {
        return [];
    }

    $pair = $maps['post_types'][ $family ];

    if (ppcart_cpt_slug_migration_step1_mixed()) {
        return [ $pair['canonical'], $pair['legacy'] ];
    }

    return [ ppcart_live_post_type($family) ];
}

/**
 * @param string $family Family key.
 * @return string
 */
function ppcart_live_taxonomy($family)
{
    $maps = ppcart_cpt_slug_migration_maps();
    if (! isset($maps['taxonomies'][ $family ])) {
        return '';
    }

    $pair = $maps['taxonomies'][ $family ];

    if (ppcart_cpt_slug_migration_step1_complete() || ppcart_cpt_slug_migration_step1_mixed()) {
        return $pair['canonical'];
    }

    return $pair['legacy'];
}

/**
 * @param string $family Family key.
 * @return array<int, string>
 */
function ppcart_query_taxonomies($family)
{
    $maps = ppcart_cpt_slug_migration_maps();
    if (! isset($maps['taxonomies'][ $family ])) {
        return [];
    }

    $pair = $maps['taxonomies'][ $family ];

    if (ppcart_cpt_slug_migration_step1_mixed()) {
        return [ $pair['canonical'], $pair['legacy'] ];
    }

    return [ ppcart_live_taxonomy($family) ];
}

/**
 * Public rewrite slug for a taxonomy family.
 *
 * Leftover until Data migration step 1 completes so existing category/tag
 * URLs keep working. Canonical after that. Compatibility Mode may register
 * leftover rewrite aliases when the live slug is already canonical.
 *
 * @param string $family Family key.
 * @return string
 */
function ppcart_taxonomy_rewrite_slug($family)
{
    $maps = ppcart_cpt_slug_migration_maps();
    if (! isset($maps['taxonomies'][ $family ])) {
        return '';
    }

    $pair    = $maps['taxonomies'][ $family ];
    $legacy  = isset($pair['rewrite_legacy']) ? $pair['rewrite_legacy'] : $pair['legacy'];
    $rewrite = isset($pair['rewrite']) ? $pair['rewrite'] : $pair['canonical'];

    if (ppcart_cpt_slug_migration_step1_complete() && ! ppcart_cpt_slug_migration_step1_mixed()) {
        return $rewrite;
    }

    return $legacy;
}

/**
 * @param string $slug Post type slug.
 * @return bool
 */
function ppcart_is_product_post_type($slug)
{
    return in_array((string) $slug, ppcart_query_post_types('product'), true);
}

/**
 * @param string $slug Post type slug.
 * @return bool
 */
function ppcart_is_order_post_type($slug)
{
    return in_array((string) $slug, ppcart_query_post_types('order'), true);
}

/**
 * @param string $slug Post type slug.
 * @return bool
 */
function ppcart_is_subscription_post_type($slug)
{
    return in_array((string) $slug, ppcart_query_post_types('subscription'), true);
}

/**
 * Live capability string.
 *
 * @param string $legacy_or_key Legacy cap or short key manager_option|manage_orders.
 * @return string
 */
function ppcart_live_cap($legacy_or_key)
{
    $maps   = ppcart_cpt_slug_migration_maps();
    $legacy = $legacy_or_key;

    if ('manager_option' === $legacy_or_key) {
        $legacy = 'sc_manager_option';
    } elseif ('manage_orders' === $legacy_or_key) {
        $legacy = 'sc_manage_orders';
    }

    if (! isset($maps['capabilities'][ $legacy ])) {
        return $legacy;
    }

    if (ppcart_cpt_slug_migration_step1_complete()) {
        return $maps['capabilities'][ $legacy ];
    }

    return $legacy;
}

/**
 * @param string $legacy_or_key Legacy cap or short key.
 * @return bool
 */
function ppcart_user_can($legacy_or_key)
{
    if (! function_exists('current_user_can')) {
        return false;
    }

    $live = ppcart_live_cap($legacy_or_key);
    if (current_user_can($live)) {
        return true;
    }

    $maps   = ppcart_cpt_slug_migration_maps();
    $legacy = $legacy_or_key;
    if ('manager_option' === $legacy_or_key) {
        $legacy = 'sc_manager_option';
    } elseif ('manage_orders' === $legacy_or_key) {
        $legacy = 'sc_manage_orders';
    }

    if (! isset($maps['capabilities'][ $legacy ])) {
        return false;
    }

    $canonical = $maps['capabilities'][ $legacy ];
    $other     = $live === $legacy ? $canonical : $legacy;

    return $other !== $live && current_user_can($other);
}

/**
 * Live role slug.
 *
 * @param string $legacy_role Legacy role.
 * @return string
 */
function ppcart_live_role($legacy_role)
{
    $maps = ppcart_cpt_slug_migration_maps();
    if (! isset($maps['roles'][ $legacy_role ])) {
        return $legacy_role;
    }

    if (ppcart_cpt_slug_migration_step2_complete()) {
        return $maps['roles'][ $legacy_role ];
    }

    return $legacy_role;
}

/**
 * Families to register this request (legacy, canonical, or both).
 *
 * @return array<int, array{family: string, cpt_name: string, cap_type: string, show_ui: bool}>
 */
function ppcart_cpt_slug_migration_registration_post_types()
{
    $rows = [];
    $maps = ppcart_cpt_slug_migration_maps();

    foreach ($maps['post_types'] as $family => $pair) {
        $cap_type = ppcart_cpt_slug_migration_step1_complete() ? $pair['canonical'] : $pair['legacy'];

        if (ppcart_cpt_slug_migration_step1_mixed()) {
            $rows[] = [
                'family'   => $family,
                'cpt_name' => $pair['legacy'],
                'cap_type' => $cap_type,
                'show_ui'  => false,
            ];
            $rows[] = [
                'family'   => $family,
                'cpt_name' => $pair['canonical'],
                'cap_type' => $cap_type,
                'show_ui'  => true,
            ];
            continue;
        }

        $rows[] = [
            'family'   => $family,
            'cpt_name' => ppcart_live_post_type($family),
            'cap_type' => $cap_type,
            'show_ui'  => true,
        ];
    }

    return $rows;
}

/**
 * Product post types for queries and Gutenberg, including Pro filter additions.
 *
 * @return array<int, string>
 */
function ppcart_filtered_product_post_types()
{
    $default    = ppcart_live_post_type('product');
    $post_types = (array) apply_filters('ppcart_product_post_type', $default);
    $post_types = array_merge($post_types, ppcart_query_post_types('product'));
    $post_types = array_filter(array_map('sanitize_key', $post_types));

    return $post_types ? array_values(array_unique($post_types)) : [ $default ? $default : ppcart_canonical_post_type('product') ];
}

/**
 * SQL fragment of quoted live/query post type slugs for IN (...).
 *
 * @param string $family Family key.
 * @return string
 */
function ppcart_sql_in_post_types($family)
{
    global $wpdb;

    $types = ppcart_query_post_types($family);
    if (! $types) {
        return "''";
    }

    $placeholders = implode(',', array_fill(0, count($types), '%s'));

    return $wpdb->prepare($placeholders, $types);
}

require_once __DIR__ . '/known-slugs.php';
