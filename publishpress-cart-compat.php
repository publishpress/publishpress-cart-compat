<?php

/**
 * The plugin bootstrap file
 *
 * @link https://publishpress.com/
 * @since 1.0.0
 * @package PPCartComp
 *
 * @wordpress-plugin
 * Plugin Name:       PublishPress Cart Compatibility
 * Plugin URI:        https://publishpress.com/publishpress-cart/
 * Description:       StudioCart shims and data migration for PublishPress Cart.
 * Version:           1.0.0
 * Author:            PublishPress
 * Author URI:        https://publishpress.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       publishpress-cart-compat
 * Domain Path:       /languages
 * Requires at least: 6.7
 * Requires PHP:      7.4
 */

if (! defined('WPINC')) {
    exit('Direct access is not allowed.');
}

if (defined('PPCARTCOMP_LOADED')) {
    return;
}

const PPCARTCOMP_MINIMUM_PHP_VERSION = '7.4';
const PPCARTCOMP_MINIMUM_WP_VERSION = '6.7';

global $wp_version;

if (version_compare(PHP_VERSION, PPCARTCOMP_MINIMUM_PHP_VERSION, '<')) {
    return;
}

if (version_compare($wp_version, PPCARTCOMP_MINIMUM_WP_VERSION, '<')) {
    return;
}

if (! defined('PPCARTCOMP_BASE_FILE')) {
    define('PPCARTCOMP_BASE_FILE', __FILE__);
}

if (! defined('PPCARTCOMP_BASE_DIR')) {
    define('PPCARTCOMP_BASE_DIR', plugin_dir_path(__FILE__));
}

require_once PPCARTCOMP_BASE_DIR . 'includes/class-ppcartcomp-studiocart-conflict.php';

if (! function_exists('ppcartcomp_refuse_studiocart_activation')) {
    /**
     * @return void
     */
    function ppcartcomp_refuse_studiocart_activation()
    {
        PPCartComp_Studiocart_Conflict::on_activate(PPCARTCOMP_BASE_FILE);
    }
}

register_activation_hook(PPCARTCOMP_BASE_FILE, 'ppcartcomp_refuse_studiocart_activation');

if (PPCartComp_Studiocart_Conflict::is_active()) {
    PPCartComp_Studiocart_Conflict::register_notice();

    return;
}

define('PPCARTCOMP_LOADED', true);

if (! defined('PPCARTCOMP_VERSION')) {
    define('PPCARTCOMP_VERSION', '1.0.0');
}

if (! defined('PPCARTCOMP_BASE_URL')) {
    define('PPCARTCOMP_BASE_URL', plugin_dir_url(__FILE__));
}

if (! function_exists('ppcartcomp_cart_is_loaded')) {
    /**
     * Whether PublishPress Cart Free (or Pro-vendored Free) is present.
     *
     * Do not use Requires Plugins: publishpress-cart — Pro vendors Free and
     * that slug is not active on Pro-only sites.
     *
     * @return bool
     */
    function ppcartcomp_cart_is_loaded()
    {
        return defined('PPCART_FREE_LOADED') || defined('PPCART_VERSION');
    }
}

if (! function_exists('ppcartcomp_missing_cart_notice')) {
    /**
     * @return void
     */
    function ppcartcomp_missing_cart_notice()
    {
        if (! current_user_can('activate_plugins')) {
            return;
        }

        echo '<div class="notice notice-error"><p>';
        echo esc_html__(
            'PublishPress Cart Compatibility requires PublishPress Cart. Activate Cart Free or Cart Pro, then activate this plugin.',
            'publishpress-cart-compat'
        );
        echo '</p></div>';
    }
}

if (! function_exists('ppcartcomp_load_textdomain')) {
    /**
     * @return void
     */
    function ppcartcomp_load_textdomain()
    {
        load_plugin_textdomain(
            'publishpress-cart-compat',
            false,
            dirname(plugin_basename(PPCARTCOMP_BASE_FILE)) . '/languages'
        );
    }
}

if (! function_exists('ppcartcomp_register_cli')) {
    /**
     * Register WP-CLI commands when WP-CLI is present.
     *
     * @return void
     */
    function ppcartcomp_register_cli()
    {
        if (! defined('WP_CLI') || ! WP_CLI) {
            return;
        }

        require_once PPCARTCOMP_BASE_DIR . 'includes/cli/class-ppcartcomp-cli-seed-migration.php';
    }
}

if (! function_exists('ppcartcomp_run')) {
    /**
     * Companion admin only. Packages are loaded by Cart from this plugin directory.
     *
     * @return void
     */
    function ppcartcomp_run()
    {
        if (! ppcartcomp_cart_is_loaded()) {
            add_action('admin_notices', 'ppcartcomp_missing_cart_notice');

            return;
        }

        require_once PPCARTCOMP_BASE_DIR . 'includes/admin/class-ppcartcomp-studiocart-admin.php';
        require_once PPCARTCOMP_BASE_DIR . 'includes/admin/class-ppcartcomp-data-migration-wizard.php';

        if (class_exists('PPCartComp_Studiocart_Admin', false)) {
            PPCartComp_Studiocart_Admin::init();
        }
        if (class_exists('PPCartComp_Data_Migration_Wizard', false)) {
            PPCartComp_Data_Migration_Wizard::init();
        }
    }
}

add_action('init', 'ppcartcomp_load_textdomain', 0);
add_action('plugins_loaded', 'ppcartcomp_run', 5);
add_action('cli_init', 'ppcartcomp_register_cli');

