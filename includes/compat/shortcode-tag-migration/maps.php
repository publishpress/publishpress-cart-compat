<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Leftover shortcode tag => canonical tag, longest leftover first.
 *
 * @return array<string, string>
 */
function ppcart_shortcode_tag_migration_pairs()
{
    static $pairs = null;

    if (null !== $pairs) {
        return $pairs;
    }

    $pairs = [];

    if (function_exists('ncs_cart_get_legacy_shortcode_map')) {
        foreach (ncs_cart_get_legacy_shortcode_map() as $canonical => $entry) {
            $legacy = function_exists('ncs_cart_legacy_shortcode_tag')
                ? ncs_cart_legacy_shortcode_tag($entry)
                : '';
            if ('' === $legacy || ! is_string($canonical) || '' === $canonical) {
                continue;
            }
            $pairs[ $legacy ] = $canonical;
        }
    }

    uksort(
        $pairs,
        static function ($left, $right) {
            return strlen((string) $right) <=> strlen((string) $left);
        }
    );

    return $pairs;
}

/**
 * Leftover email merge tag => canonical merge tag.
 *
 * @return array<string, string>
 */
function ppcart_email_merge_tag_migration_pairs()
{
    return [ 'studiocart' => 'publishpress_cart' ];
}

/**
 * Site option keys that store email HTML (canonical and leftover names).
 *
 * @return array<int, string>
 */
function ppcart_shortcode_tag_migration_email_option_keys()
{
    $canonical = [
        '_ppcart_email_pending_body',
        '_ppcart_email_confirmation_body',
        '_ppcart_email_refunded_body',
        '_ppcart_email_renewal_body',
        '_ppcart_email_failed_body',
        '_ppcart_email_canceled_body',
        '_ppcart_email_footer_text',
    ];

    $keys = $canonical;
    foreach ($canonical as $key) {
        $keys[] = preg_replace('/^_ppcart_/', '_sc_', $key);
    }

    return array_values(array_unique($keys));
}

/**
 * Product notification meta keys that store email HTML.
 *
 * @return array<int, string>
 */
function ppcart_shortcode_tag_migration_notification_meta_keys()
{
    return [ '_ppcart_notifications', '_sc_notifications' ];
}
