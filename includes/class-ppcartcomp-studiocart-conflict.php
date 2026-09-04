<?php

if (! defined('ABSPATH') && ! defined('WPINC')) {
    exit;
}

/**
 * Refuse to load while leftover Studiocart is still active.
 *
 * Detection uses plugin basenames, not NCS_Cart_* symbols — Compatibility Mode
 * shims those names when Studiocart is already gone.
 */
class PPCartComp_Studiocart_Conflict
{
    /**
     * @param string $basename Plugin basename (folder/file.php).
     * @return bool
     */
    public static function is_studiocart_basename($basename)
    {
        $basename = str_replace('\\', '/', (string) $basename);
        $basename = ltrim($basename, '/');
        if ('' === $basename) {
            return false;
        }

        $lower = strtolower($basename);
        if (false !== strpos($lower, 'publishpress-cart')) {
            return false;
        }

        $file = strtolower(basename($basename));
        $dir  = strtolower(dirname($basename));

        if ('studiocart.php' === $file || 'ncs-cart.php' === $file) {
            return true;
        }

        if ('.' !== $dir && false !== strpos($dir, 'studiocart')) {
            return true;
        }

        return false;
    }

    /**
     * @param array<int, string>       $active  active_plugins list.
     * @param array<string, mixed>     $network active_sitewide_plugins map.
     * @return string Basename or empty.
     */
    public static function find_active_basename(array $active, array $network = [])
    {
        foreach ($active as $basename) {
            if (is_string($basename) && self::is_studiocart_basename($basename)) {
                return $basename;
            }
        }

        foreach (array_keys($network) as $basename) {
            if (is_string($basename) && self::is_studiocart_basename($basename)) {
                return $basename;
            }
        }

        return '';
    }

    /**
     * @return string
     */
    public static function active_basename()
    {
        $active = [];
        if (function_exists('get_option')) {
            $option = get_option('active_plugins', []);
            if (is_array($option)) {
                $active = $option;
            }
        }

        $network = [];
        if (function_exists('is_multisite') && is_multisite() && function_exists('get_site_option')) {
            $option = get_site_option('active_sitewide_plugins', []);
            if (is_array($option)) {
                $network = $option;
            }
        }

        return self::find_active_basename($active, $network);
    }

    /**
     * @return bool
     */
    public static function is_active()
    {
        return '' !== self::active_basename();
    }

    /**
     * @return void
     */
    public static function register_notice()
    {
        if (defined('PPCART_STUDIOCART_CONFLICT_NOTICED')) {
            return;
        }

        define('PPCART_STUDIOCART_CONFLICT_NOTICED', true);
        add_action('admin_notices', [ self::class, 'render_notice' ]);
    }

    /**
     * @return void
     */
    public static function render_notice()
    {
        if (function_exists('current_user_can') && ! current_user_can('activate_plugins')) {
            return;
        }

        echo '<div class="notice notice-error"><p>';
        echo esc_html__(
            'PublishPress Cart and PublishPress Cart Compatibility cannot run while Studiocart is active. Deactivate Studiocart first, then activate those plugins.',
            'publishpress-cart-compat'
        );
        echo '</p></div>';
    }

    /**
     * @param string $plugin_file Absolute path to this plugin's bootstrap file.
     * @return void
     */
    public static function on_activate($plugin_file)
    {
        if (! self::is_active()) {
            return;
        }

        if (function_exists('deactivate_plugins') && function_exists('plugin_basename')) {
            deactivate_plugins(plugin_basename($plugin_file));
        }

        $message = __(
            'PublishPress Cart Compatibility cannot run while Studiocart is active. Deactivate Studiocart first, then activate PublishPress Cart and this plugin.',
            'publishpress-cart-compat'
        );

        if (function_exists('wp_die')) {
            wp_die(
                esc_html($message),
                esc_html__('Studiocart must be deactivated', 'publishpress-cart-compat'),
                [ 'back_link' => true ]
            );
        }
    }
}
