<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Whether a string contains a leftover shortcode opening tag.
 *
 * @param string $text Content.
 * @return bool
 */
function ppcart_content_has_leftover_shortcode_tags($text)
{
    if (! is_string($text) || '' === $text) {
        return false;
    }

    foreach (array_keys(ppcart_shortcode_tag_migration_pairs()) as $legacy) {
        if (false !== strpos($text, '[' . $legacy)) {
            return true;
        }
    }

    return false;
}

/**
 * Rewrite leftover shortcode opening tags to canonical tags.
 *
 * @param string $text Content.
 * @return string
 */
function ppcart_rewrite_leftover_shortcode_tags($text)
{
    if (! is_string($text) || '' === $text) {
        return $text;
    }

    foreach (ppcart_shortcode_tag_migration_pairs() as $legacy => $canonical) {
        $text = str_replace('[' . $legacy, '[' . $canonical, $text);
    }

    return $text;
}

/**
 * Recursively rewrite leftover shortcode tags in strings.
 *
 * @param mixed $value Content or nested notification payload.
 * @return mixed
 */
function ppcart_rewrite_leftover_shortcode_tags_in_value($value)
{
    if (is_string($value)) {
        return ppcart_rewrite_leftover_shortcode_tags($value);
    }

    if (is_array($value)) {
        foreach ($value as $key => $item) {
            $value[ $key ] = ppcart_rewrite_leftover_shortcode_tags_in_value($item);
        }
    }

    return $value;
}

/**
 * Whether a nested value still contains leftover shortcode tags.
 *
 * @param mixed $value Content or nested notification payload.
 * @return bool
 */
function ppcart_value_has_leftover_shortcode_tags($value)
{
    if (is_string($value)) {
        return ppcart_content_has_leftover_shortcode_tags($value);
    }

    if (! is_array($value)) {
        return false;
    }

    foreach ($value as $item) {
        if (ppcart_value_has_leftover_shortcode_tags($item)) {
            return true;
        }
    }

    return false;
}

/**
 * SQL LIKE fragments for leftover opening tags.
 *
 * @return array<int, string>
 */
function ppcart_shortcode_tag_migration_like_patterns()
{
    global $wpdb;

    $patterns = [];
    foreach (array_keys(ppcart_shortcode_tag_migration_pairs()) as $legacy) {
        $escaped    = method_exists($wpdb, 'esc_like') ? $wpdb->esc_like('[' . $legacy) : '[' . $legacy;
        $patterns[] = '%' . $escaped . '%';
    }

    return $patterns;
}
