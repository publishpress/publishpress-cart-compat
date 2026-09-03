<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart submenu under Cart (Compatibility Mode + Data migration).
 */
class PPCartComp_Studiocart_Admin
{
    public const PAGE_SLUG     = 'ppcart-studiocart';
    public const OPTION_GROUP  = 'ppcartcomp_studiocart';
    public const HOOK_SUFFIX   = 'ppcart_page_ppcart-studiocart';

    /**
     * @return void
     */
    public static function init()
    {
        add_action('admin_menu', [ self::class, 'register_submenu' ], 20);
        add_action('admin_init', [ self::class, 'register_setting' ]);
        add_action('admin_enqueue_scripts', [ self::class, 'enqueue_assets' ]);
    }

    /**
     * @param string $hook_suffix Current admin hook suffix.
     * @return bool
     */
    public static function is_screen($hook_suffix = '')
    {
        if (is_string($hook_suffix) && '' !== $hook_suffix) {
            if (self::HOOK_SUFFIX === $hook_suffix) {
                return true;
            }

            $parent = class_exists('PPCart_Admin_Screens', false)
                ? PPCart_Admin_Screens::PAGE_DASHBOARD
                : 'ppcart';
            if (function_exists('get_plugin_page_hookname') && get_plugin_page_hookname(self::PAGE_SLUG, $parent) === $hook_suffix) {
                return true;
            }
        }

        if (! isset($_GET['page']) || ! is_string($_GET['page'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page check.
            return false;
        }

        return self::PAGE_SLUG === sanitize_key(wp_unslash($_GET['page'])); // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page check.
    }

    /**
     * @return string
     */
    public static function screen_url()
    {
        return admin_url('admin.php?page=' . self::PAGE_SLUG);
    }

    /**
     * @return void
     */
    public static function register_submenu()
    {
        $parent = class_exists('PPCart_Admin_Screens', false)
            ? PPCart_Admin_Screens::PAGE_DASHBOARD
            : 'ppcart';

        add_submenu_page(
            $parent,
            esc_html__('StudioCart', 'publishpress-cart-compat'),
            esc_html__('StudioCart', 'publishpress-cart-compat'),
            'manage_options',
            self::PAGE_SLUG,
            [ self::class, 'render_page' ]
        );
    }

    /**
     * @return void
     */
    public static function register_setting()
    {
        if (! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)) {
            return;
        }

        register_setting(
            self::OPTION_GROUP,
            PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME,
            [
                'type'              => 'string',
                'sanitize_callback' => [ self::class, 'sanitize_compatibility_mode' ],
                'default'           => '0',
            ]
        );
    }

    /**
     * @param mixed $value Submitted value.
     * @return string
     */
    public static function sanitize_compatibility_mode($value)
    {
        if (
            class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            && PPCartComp_Studiocart_Compatibility_Mode::is_wp_config_forced()
        ) {
            $current = get_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '0');

            return '1' === (string) $current ? '1' : '0';
        }

        return '1' === (string) $value ? '1' : '0';
    }

    /**
     * @param string $hook_suffix Hook.
     * @return void
     */
    public static function enqueue_assets($hook_suffix)
    {
        if (! self::is_screen(is_string($hook_suffix) ? $hook_suffix : '')) {
            return;
        }

        $version = defined('PPCARTCOMP_VERSION') ? PPCARTCOMP_VERSION : '1.0.0';

        wp_enqueue_script(
            'ppcartcomp-custom-table-migration',
            plugins_url('includes/compat/custom-table-migration/admin.js', PPCARTCOMP_BASE_FILE),
            [ 'jquery' ],
            $version,
            true
        );
        wp_localize_script(
            'ppcartcomp-custom-table-migration',
            'ppcartcompCustomTableMigration',
            [
                'confirm' => __(
                    'Rename leftover custom tables to ppcart_* names? Take a backup first and run this during a maintenance window.',
                    'publishpress-cart-compat'
                ),
            ]
        );
    }

    /**
     * @return void
     */
    public static function render_page()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        include __DIR__ . '/templates/studiocart-page.php';
    }
}
