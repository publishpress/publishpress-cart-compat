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

define('PPCARTCOMP_LOADED', true);

if (! defined('PPCARTCOMP_VERSION')) {
    define('PPCARTCOMP_VERSION', '1.0.0');
}

if (! defined('PPCARTCOMP_BASE_DIR')) {
    define('PPCARTCOMP_BASE_DIR', plugin_dir_path(__FILE__));
}

if (! defined('PPCARTCOMP_BASE_URL')) {
    define('PPCARTCOMP_BASE_URL', plugin_dir_url(__FILE__));
}

if (! defined('PPCARTCOMP_BASE_FILE')) {
    define('PPCARTCOMP_BASE_FILE', __FILE__);
}
