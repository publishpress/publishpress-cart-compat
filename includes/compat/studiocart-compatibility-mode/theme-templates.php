<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_theme_template_dir_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_theme_template_dir_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/theme-template-dirs.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ppcart_legacy_theme_template_path')) {
    /**
     * Leftover theme override path for a plugin template relative.
     *
     * @param string $relative Path under public/templates/, including .php.
     * @return string Absolute leftover theme path, or empty when none mapped.
     */
    function ppcart_legacy_theme_template_path($relative)
    {
        if (! is_string($relative) || '' === $relative) {
            return '';
        }

        $map = ncs_cart_get_legacy_theme_template_dir_map();
        if (! isset($map['publishpress-cart']) || ! is_array($map['publishpress-cart'])) {
            return '';
        }

        foreach ($map['publishpress-cart'] as $leftover_dir) {
            if (! is_string($leftover_dir) || '' === $leftover_dir) {
                continue;
            }

            $leftover = get_stylesheet_directory() . '/' . $leftover_dir . '/' . $relative;
            if (file_exists($leftover)) {
                return $leftover;
            }
        }

        return '';
    }
}

if (! function_exists('ppcart_maybe_use_legacy_theme_template_path')) {
    /**
     * Substitute leftover theme copies only while Compatibility Mode is on.
     *
     * Canonical theme files win. Does not copy or rename theme files.
     *
     * @param mixed  $path     Canonical theme path from Cart.
     * @param string $relative Path under public/templates/, including .php.
     * @return mixed
     */
    function ppcart_maybe_use_legacy_theme_template_path($path, $relative = '')
    {
        if (is_string($path) && '' !== $path && file_exists($path)) {
            return $path;
        }

        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return $path;
        }

        $leftover = ppcart_legacy_theme_template_path($relative);

        return '' !== $leftover ? $leftover : $path;
    }
}

add_filter('ppcart_theme_template_path', 'ppcart_maybe_use_legacy_theme_template_path', 10, 2);
