<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Legacy wp-admin page slug redirects for Compatibility Mode.
 */
final class PPCartComp_Studiocart_Admin_Page_Compatibility
{
    public const REDIRECT_STATUS = 302;

    /**
     * @var bool
     */
    private static $registered = false;

    /**
     * @return void
     */
    public static function init()
    {
        if (self::$registered) {
            return;
        }

        add_action('admin_init', [ self::class, 'maybe_redirect_legacy_admin_page' ], 0);

        self::$registered = true;
    }

    /**
     * @return array<string, string>
     */
    public static function legacy_admin_page_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/admin-page-slugs.php';
        if (! is_readable($map_file)) {
            $map = [];

            return $map;
        }

        $loaded = include $map_file;
        $map    = is_array($loaded) ? $loaded : [];

        return $map;
    }

    /**
     * Build a redirect URL when a legacy admin page slug is requested.
     *
     * @param array<string, mixed> $get Query vars, usually $_GET.
     * @return string|null
     */
    public static function get_legacy_redirect_url(array $get)
    {
        if (! isset($get['page']) || ! is_string($get['page'])) {
            return null;
        }

        $page = sanitize_key(wp_unslash($get['page']));
        $map  = self::legacy_admin_page_map();

        if (! isset($map[ $page ])) {
            return null;
        }

        $canonical = sanitize_key((string) $map[ $page ]);
        if ('' === $canonical || $canonical === $page) {
            return null;
        }

        $args         = $get;
        $args['page'] = $canonical;

        return add_query_arg($args, admin_url('admin.php'));
    }

    /**
     * @return void
     */
    public static function maybe_redirect_legacy_admin_page()
    {
        if (! self::is_enabled() || ! is_admin()) {
            return;
        }

        if (self::is_ajax_or_rest_request()) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page canonicalization.
        $redirect_url = self::get_legacy_redirect_url($_GET);
        if (! is_string($redirect_url) || '' === $redirect_url) {
            return;
        }

        if (wp_safe_redirect($redirect_url, self::REDIRECT_STATUS, 'PublishPress Cart')) {
            exit;
        }
    }

    /**
     * @return bool
     */
    private static function is_ajax_or_rest_request()
    {
        $doing_ajax = function_exists('wp_doing_ajax') && wp_doing_ajax();
        $doing_rest = defined('REST_REQUEST') && REST_REQUEST;
        if (function_exists('wp_doing_rest')) {
            $doing_rest = (bool) wp_doing_rest();
        }

        return $doing_ajax || $doing_rest;
    }

    /**
     * @return bool
     */
    private static function is_enabled()
    {
        return class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            && PPCartComp_Studiocart_Compatibility_Mode::is_enabled();
    }
}
