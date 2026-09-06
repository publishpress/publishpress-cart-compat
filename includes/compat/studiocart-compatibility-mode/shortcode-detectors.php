<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ppcartcomp_leftover_shortcode_detectors_enabled')) {
    /**
     * @return bool
     */
    function ppcartcomp_leftover_shortcode_detectors_enabled()
    {
        return class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            && PPCartComp_Studiocart_Compatibility_Mode::is_enabled();
    }
}

if (! function_exists('ppcartcomp_content_has_leftover_shortcode_opening')) {
    /**
     * Leftover openings that match Cart's frontend-asset shortcode list.
     *
     * @param string $text Content.
     * @return bool
     */
    function ppcartcomp_content_has_leftover_shortcode_opening($text)
    {
        if (! is_string($text) || '' === $text) {
            return false;
        }

        foreach (ppcartcomp_frontend_asset_leftover_shortcode_tags() as $legacy) {
            $quoted = preg_quote($legacy, '/');
            if (preg_match('/\[' . $quoted . '(?:\s|\]|\/|$)/', $text)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('ppcartcomp_frontend_asset_leftover_shortcode_tags')) {
    /**
     * Leftover tags whose canonical siblings enqueue Cart frontend assets.
     *
     * @return array<int, string>
     */
    function ppcartcomp_frontend_asset_leftover_shortcode_tags()
    {
        $canonical = [
            'ppcart_product',
            'ppcart_plan',
            'ppcart_order_detail',
            'ppcart_order_summary_items_view',
            'ppcart_account',
            'ppcart_account_link',
            'ppcart_account_order_detail',
            'ppcart_account_subscription_detail',
            'ppcart_form',
            'ppcart_receipt',
            'ppcart_store',
            'ppcart_order_downloads',
        ];

        $tags = [];
        $map  = function_exists('ncs_cart_get_legacy_shortcode_map')
            ? ncs_cart_get_legacy_shortcode_map()
            : [];

        foreach ($canonical as $tag) {
            if (! isset($map[ $tag ])) {
                continue;
            }

            $legacy = function_exists('ncs_cart_legacy_shortcode_tag')
                ? ncs_cart_legacy_shortcode_tag($map[ $tag ])
                : '';
            if ('' !== $legacy) {
                $tags[] = $legacy;
            }
        }

        return $tags;
    }
}

if (! function_exists('ppcartcomp_content_has_leftover_account_shortcode')) {
    /**
     * Leftover account page tag, not studiocart_account_link.
     *
     * @param string $text Content.
     * @return bool
     */
    function ppcartcomp_content_has_leftover_account_shortcode($text)
    {
        if (! is_string($text) || '' === $text) {
            return false;
        }

        return (bool) preg_match('/\[studiocart_account(?:\s|\]|\/|$)/', $text);
    }
}

if (! function_exists('ppcartcomp_content_has_leftover_order_summary_shortcode')) {
    /**
     * @param string $text Content.
     * @return bool
     */
    function ppcartcomp_content_has_leftover_order_summary_shortcode($text)
    {
        if (! is_string($text) || '' === $text) {
            return false;
        }

        return false !== strpos($text, '[sc_order_summary_items_view');
    }
}

if (! function_exists('ppcartcomp_maybe_need_frontend_assets_for_leftover_shortcodes')) {
    /**
     * @param mixed $needed Whether Cart already decided to load assets.
     * @return bool
     */
    function ppcartcomp_maybe_need_frontend_assets_for_leftover_shortcodes($needed)
    {
        if ($needed || ! ppcartcomp_leftover_shortcode_detectors_enabled()) {
            return (bool) $needed;
        }

        global $post;
        if (! is_a($post, 'WP_Post')) {
            return false;
        }

        return ppcartcomp_content_has_leftover_shortcode_opening($post->post_content);
    }
}

if (! function_exists('ppcartcomp_maybe_add_leftover_account_body_class')) {
    /**
     * @param mixed $classes Body classes.
     * @return mixed
     */
    function ppcartcomp_maybe_add_leftover_account_body_class($classes)
    {
        if (! ppcartcomp_leftover_shortcode_detectors_enabled()) {
            return $classes;
        }

        global $post;
        if (! is_a($post, 'WP_Post') || ! ppcartcomp_content_has_leftover_account_shortcode($post->post_content)) {
            return $classes;
        }

        if (! is_array($classes)) {
            return $classes;
        }

        if (! in_array('account-page', $classes, true)) {
            $classes[] = 'account-page';
        }

        return $classes;
    }
}

if (! function_exists('ppcartcomp_maybe_skip_default_order_summary_for_leftover')) {
    /**
     * @param mixed  $skip    Whether Cart already skips the default summary.
     * @param string $content Product post content.
     * @return bool
     */
    function ppcartcomp_maybe_skip_default_order_summary_for_leftover($skip, $content = '')
    {
        if ($skip || ! ppcartcomp_leftover_shortcode_detectors_enabled()) {
            return (bool) $skip;
        }

        return ppcartcomp_content_has_leftover_order_summary_shortcode((string) $content);
    }
}

if (! function_exists('ppcartcomp_maybe_add_leftover_order_download_shortcode_tag')) {
    /**
     * @param mixed $tags Download shortcode openings Cart will replace.
     * @return array<int, string>
     */
    function ppcartcomp_maybe_add_leftover_order_download_shortcode_tag($tags)
    {
        if (! is_array($tags)) {
            return $tags;
        }

        if (! ppcartcomp_leftover_shortcode_detectors_enabled()) {
            return $tags;
        }

        if (! in_array('[studiocart-order-downloads]', $tags, true)) {
            $tags[] = '[studiocart-order-downloads]';
        }

        return $tags;
    }
}

add_filter('ppcart_frontend_assets_needed', 'ppcartcomp_maybe_need_frontend_assets_for_leftover_shortcodes');
add_filter('body_class', 'ppcartcomp_maybe_add_leftover_account_body_class');
add_filter('ppcart_skip_default_order_summary', 'ppcartcomp_maybe_skip_default_order_summary_for_leftover', 10, 2);
add_filter('ppcart_order_download_shortcode_tags', 'ppcartcomp_maybe_add_leftover_order_download_shortcode_tag');
