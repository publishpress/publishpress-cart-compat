<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical post type slug for a Free family (maps, not live row state).
 *
 * @param string $family Family key.
 * @return string
 */
function ppcart_canonical_post_type($family)
{
    $maps = ppcart_cpt_slug_migration_maps();

    return isset($maps['post_types'][ $family ]['canonical']) ? $maps['post_types'][ $family ]['canonical'] : '';
}

/**
 * Canonical taxonomy slug (maps, not live row state).
 *
 * @param string $family Family key.
 * @return string
 */
function ppcart_canonical_taxonomy($family)
{
    $maps = ppcart_cpt_slug_migration_maps();

    return isset($maps['taxonomies'][ $family ]['canonical']) ? $maps['taxonomies'][ $family ]['canonical'] : '';
}

/**
 * Both leftover and canonical slugs for a Free CPT family (screen matching).
 *
 * @param string $family Family key.
 * @return array<int, string>
 */
function ppcart_known_post_types($family)
{
    $maps = ppcart_cpt_slug_migration_maps();
    if (! isset($maps['post_types'][ $family ])) {
        return [];
    }

    $pair = $maps['post_types'][ $family ];

    return array_values(array_unique([ $pair['canonical'], $pair['legacy'] ]));
}

/**
 * Both leftover and canonical slugs for a Pro-owned CPT family.
 *
 * Pro has no Free Data migration; queries and screen matching keep both names.
 *
 * @param string $family Family key.
 * @return array<int, string>
 */
function ppcart_known_pro_post_types($family)
{
    $maps = ppcart_cpt_slug_migration_maps();
    if (! isset($maps['pro_post_types'][ $family ])) {
        return [];
    }

    $pair = $maps['pro_post_types'][ $family ];

    return array_values(array_unique([ $pair['canonical'], $pair['legacy'] ]));
}

/**
 * WP_Query slugs for a Pro-owned CPT. Always leftover + canonical.
 *
 * @param string $family Family key.
 * @return array<int, string>
 */
function ppcart_query_pro_post_types($family)
{
    return ppcart_known_pro_post_types($family);
}

/**
 * Canonical Pro-owned CPT slug for first-party writes.
 *
 * @param string $family Family key.
 * @return string
 */
function ppcart_live_pro_post_type($family)
{
    $maps = ppcart_cpt_slug_migration_maps();

    return isset($maps['pro_post_types'][ $family ]['canonical']) ? $maps['pro_post_types'][ $family ]['canonical'] : '';
}

/**
 * @param string $family Family key.
 * @param string $slug   Post type slug.
 * @return bool
 */
function ppcart_is_pro_post_type($family, $slug)
{
    return in_array((string) $slug, ppcart_known_pro_post_types($family), true);
}

/**
 * Product + collection slugs for storefront/admin product pickers.
 *
 * @return array<int, string>
 */
function ppcart_query_product_and_collection_post_types()
{
    return array_values(
        array_unique(
            array_merge(
                ppcart_query_post_types('product'),
                ppcart_query_pro_post_types('collection')
            )
        )
    );
}

/**
 * Every leftover and canonical Free + Pro CPT slug.
 *
 * @return array<int, string>
 */
function ppcart_all_known_post_types()
{
    $maps = ppcart_cpt_slug_migration_maps();
    $out  = [];

    foreach (array_keys($maps['post_types']) as $family) {
        $out = array_merge($out, ppcart_known_post_types($family));
    }
    foreach (array_keys($maps['pro_post_types']) as $family) {
        $out = array_merge($out, ppcart_known_pro_post_types($family));
    }

    return array_values(array_unique($out));
}

/**
 * Both leftover and canonical slugs for a taxonomy family.
 *
 * @param string $family Family key.
 * @return array<int, string>
 */
function ppcart_known_taxonomies($family)
{
    $maps = ppcart_cpt_slug_migration_maps();
    if (! isset($maps['taxonomies'][ $family ])) {
        return [];
    }

    $pair = $maps['taxonomies'][ $family ];

    return array_values(array_unique([ $pair['canonical'], $pair['legacy'] ]));
}

/**
 * Every leftover and canonical product taxonomy slug.
 *
 * @return array<int, string>
 */
function ppcart_all_known_taxonomies()
{
    $maps = ppcart_cpt_slug_migration_maps();
    $out  = [];

    foreach (array_keys($maps['taxonomies']) as $family) {
        $out = array_merge($out, ppcart_known_taxonomies($family));
    }

    return array_values(array_unique($out));
}

/**
 * Body class WordPress emits for the live product singular template.
 *
 * @return string
 */
function ppcart_product_singular_body_class()
{
    $slug = ppcart_live_post_type('product');
    if ('' === $slug) {
        $slug = ppcart_canonical_post_type('product');
    }

    return $slug ? 'single-' . $slug : 'single-ppcart_product';
}

/**
 * CSS selector for the live product singular body class.
 *
 * @return string
 */
function ppcart_product_singular_body_selector()
{
    return '.' . ppcart_product_singular_body_class();
}

/**
 * Compatibility Mode leftover FSE template slugs (`single-sc_product`).
 *
 * @param array<int, string>|null $slugs Slugs to store, or null to read.
 * @param bool                    $set   Whether to replace the current list.
 * @return array<int, string>
 */
function ppcart_cpt_leftover_fse_template_slugs($slugs = null, $set = false)
{
    static $registered = [];

    if ($set) {
        $registered = [];
        if (is_array($slugs)) {
            foreach ($slugs as $slug) {
                if (is_string($slug) && '' !== $slug) {
                    $registered[] = function_exists('sanitize_key') ? sanitize_key($slug) : strtolower(preg_replace('/[^a-z0-9_\-]/i', '', $slug));
                }
            }
        }
    }

    return $registered;
}
