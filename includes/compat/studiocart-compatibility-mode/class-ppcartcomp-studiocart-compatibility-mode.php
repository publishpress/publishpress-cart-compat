<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart Compatibility Mode — user-toggleable compatibility bridges for
 * third-party plugins, apps, and links that still use StudioCart PHP APIs or
 * legacy public query variables.
 */
class PPCartComp_Studiocart_Compatibility_Mode
{
    public const FEATURE_ID                = 'ppcart-compatibility-mode';
    public const LEGACY_FEATURE_ID         = 'studiocart-compatibility-mode';
    public const OPTION_NAME               = '_ppcart_compatibility_mode';
    public const LEGACY_OPTION_NAME        = '_ppcart_studiocart_compatibility_mode';
    public const OLDEST_LEGACY_OPTION_NAME = '_sc_studiocart_compatibility_mode';
    public const WP_CONFIG_CONSTANT        = 'PPCART_STUDIOCART_COMPATIBILITY_MODE';

    /**
     * Whether legacy option API bridges were registered this request.
     *
     * @var bool
     */
    private static $legacy_options_registered = false;

    /**
     * Whether leftover option read-through was registered this request.
     *
     * @var bool
     */
    private static $option_storage_bridges_registered = false;


    /**
     * Whether leftover meta-key API bridges were registered this request.
     *
     * @var bool
     */
    private static $legacy_metadata_registered = false;

    /**
     * Whether legacy function wrappers were loaded this request.
     *
     * @var bool
     */
    private static $legacy_functions_registered = false;

    /**
     * Whether legacy hook API bridges were registered this request.
     *
     * @var bool
     */
    private static $legacy_hooks_registered = false;

    /**
     * Whether always-on cron hook bridges were registered this request.
     *
     * @var bool
     */
    private static $cron_hook_bridges_registered = false;

    /**
     * Whether Compatibility Mode ran the constant bridge this request.
     *
     * @var bool
     */
    private static $legacy_constants_registered = false;

    /**
     * Whether legacy runtime-global aliases were registered this request.
     *
     * @var bool
     */
    private static $legacy_globals_registered = false;

    /**
     * Wire settings, the feature gate, and shim hooks.
     *
     * @return void
     */
    public static function init()
    {
        add_filter('ppcart_supports_feature', [ self::class, 'supports_feature' ], 20, 2);
        add_action('plugins_loaded', [ self::class, 'maybe_register_ajax' ], 0);
        add_action('plugins_loaded', [ self::class, 'maybe_register_request_fields' ], 0);
        add_action('plugins_loaded', [ self::class, 'maybe_register_stripe_metadata' ], 0);
        add_action('plugins_loaded', [ self::class, 'maybe_register_cpt_taxonomy' ], 0);
        add_action('plugins_loaded', [ self::class, 'maybe_register_download_upload_directory' ], 0);
        add_action('plugins_loaded', [ self::class, 'maybe_register_shortcodes' ], 0);
        add_action('plugins_loaded', [ self::class, 'maybe_register_hooks' ], PHP_INT_MAX);

        add_action('init', [ self::class, 'maybe_register_gutenberg_blocks' ], 11);
        add_action('enqueue_block_editor_assets', [ self::class, 'enqueue_legacy_gutenberg_editor_assets' ], 20);

        add_action('ppcart_before_load', [ self::class, 'maybe_register_aliases' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_functions' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_ajax' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_request_fields' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_stripe_metadata' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_cpt_taxonomy' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_download_upload_directory' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_shortcodes' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_option_storage_bridges' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_options' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_metadata_bridges' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_hooks' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_constants' ], PHP_INT_MAX);
        add_action('ppcart_before_load', [ self::class, 'maybe_register_globals' ], PHP_INT_MAX);
        add_action('elementor/widgets/register', [ self::class, 'maybe_register_aliases' ], 11);
        add_action('elementor/widgets/register', [ self::class, 'maybe_register_functions' ], 11);
        add_action('elementor/widgets/register', [ self::class, 'maybe_register_shortcodes' ], 11);
        add_action('elementor/widgets/register', [ self::class, 'maybe_register_option_storage_bridges' ], 11);
        add_action('elementor/widgets/register', [ self::class, 'maybe_register_options' ], 11);
        add_action('elementor/widgets/register', [ self::class, 'maybe_register_metadata_bridges' ], 11);
        add_action('elementor/widgets/register', [ self::class, 'maybe_register_hooks' ], 11);
        add_action('elementor/widgets/register', [ self::class, 'maybe_register_constants' ], 11);
        add_action('elementor/widgets/register', [ self::class, 'maybe_register_globals' ], 11);
        add_action('ppcart_run_price_formatting', [ self::class, 'maybe_register_aliases' ], 0);
        add_action('ppcart_run_price_formatting', [ self::class, 'maybe_register_functions' ], 0);
        add_action('ppcart_run_price_formatting', [ self::class, 'maybe_register_shortcodes' ], 0);
        add_action('ppcart_run_price_formatting', [ self::class, 'maybe_register_option_storage_bridges' ], 0);
        add_action('ppcart_run_price_formatting', [ self::class, 'maybe_register_options' ], 0);
        add_action('ppcart_run_price_formatting', [ self::class, 'maybe_register_metadata_bridges' ], 0);
        add_action('ppcart_run_price_formatting', [ self::class, 'maybe_register_hooks' ], 0);
        add_action('ppcart_run_price_formatting', [ self::class, 'maybe_register_constants' ], 0);
        add_action('ppcart_run_price_formatting', [ self::class, 'maybe_register_globals' ], 0);

        add_action('admin_enqueue_scripts', [ self::class, 'enqueue_legacy_admin_styles' ], 20);
        add_action('wp_enqueue_scripts', [ self::class, 'enqueue_legacy_public_styles' ], 20);
        add_action('ppcart_enqueue_frontend_assets', [ self::class, 'enqueue_legacy_public_styles' ], 11);

        self::maybe_register_option_storage_bridges();
        self::maybe_register_metadata_bridges();
        self::maybe_register_cron_hook_bridges();
        self::maybe_register_ajax();
        self::maybe_register_request_fields();
        self::maybe_register_stripe_metadata();
        self::maybe_register_cpt_taxonomy();
        self::maybe_register_download_upload_directory();
        self::maybe_register_shortcodes();

        if (class_exists('PPCartComp_Studiocart_Public_Query_Compatibility', false)) {
            PPCartComp_Studiocart_Public_Query_Compatibility::init();
            PPCartComp_Studiocart_Admin_Page_Compatibility::init();
        }
    }

    /**
     * Whether Compatibility Mode is forced via wp-config.php.
     *
     * @return bool
     */
    public static function is_wp_config_forced()
    {
        return defined(self::WP_CONFIG_CONSTANT);
    }

    /**
     * Whether StudioCart Compatibility Mode is on for this site.
     *
     * Default is off. When PPCART_STUDIOCART_COMPATIBILITY_MODE is defined in
     * wp-config.php it overrides the Settings checkbox.
     *
     * @return bool
     */
    public static function is_user_enabled()
    {
        if (self::is_wp_config_forced()) {
            return (bool) constant(self::WP_CONFIG_CONSTANT);
        }

        return '1' === (string) self::read_option_value();
    }

    /**
     * Read the merchant toggle without mutating historical option rows.
     *
     * @return mixed Stored value, or false when unset.
     */
    private static function read_option_value()
    {
        foreach ([ self::OPTION_NAME, self::LEGACY_OPTION_NAME, self::OLDEST_LEGACY_OPTION_NAME ] as $option_name) {
            $value = get_option($option_name, false);
            if (false !== $value) {
                return $value;
            }
        }

        return false;
    }

    /**
     * Whether Compatibility Mode is enabled (merchant setting or wp-config).
     *
     * @return bool
     */
    public static function is_enabled()
    {
        return self::is_user_enabled();
    }

    /**
     * Gate the canonical feature id and the Compat-only legacy alias.
     *
     * Runs after Pro's blanket supports filter so this feature stays
     * merchant-controlled in both Free and Pro.
     *
     * @param bool   $supports Current support value.
     * @param string $feature  Feature key.
     * @return bool
     */
    public static function supports_feature($supports, $feature)
    {
        if (self::FEATURE_ID !== $feature && self::LEGACY_FEATURE_ID !== $feature) {
            return $supports;
        }

        return self::is_enabled();
    }

    /**
     * Register class aliases when Compatibility Mode is on.
     *
     * @return void
     */
    public static function maybe_register_aliases()
    {
        if (! self::is_enabled() || ! function_exists('ncs_cart_register_legacy_class_aliases')) {
            return;
        }

        ncs_cart_register_legacy_class_aliases();
    }

    /**
     * Register leftover option read-through (no row copy or delete).
     *
     * Option key renames run only via merchant Data migration.
     *
     * @return void
     */
    public static function maybe_register_option_storage_bridges()
    {
        if (self::$option_storage_bridges_registered || ! function_exists('ppcart_register_option_storage_bridges')) {
            return;
        }

        ppcart_register_option_storage_bridges();

        self::$option_storage_bridges_registered = true;
    }

    /**
     * Register legacy option API bridges when Compatibility Mode is on.
     *
     * @return void
     */
    public static function maybe_register_options()
    {
        if (! self::is_enabled() || self::$legacy_options_registered) {
            return;
        }

        if (! function_exists('ppcart_register_legacy_option_api_bridges')) {
            return;
        }

        self::maybe_register_option_storage_bridges();
        ppcart_register_legacy_option_api_bridges();
        self::$legacy_options_registered = true;
    }

    /**
     * Register leftover meta-key API bridges when Compatibility Mode is on.
     *
     * @return void
     */
    public static function maybe_register_metadata_bridges()
    {
        if (! self::is_enabled() || self::$legacy_metadata_registered) {
            return;
        }

        if (! function_exists('ppcart_register_legacy_metadata_api_bridges')) {
            return;
        }

        ppcart_register_legacy_metadata_api_bridges();
        self::$legacy_metadata_registered = true;
    }

    /**
     * Whether legacy option API bridges were registered for this request.
     *
     * @return bool
     */
    public static function legacy_options_registered()
    {
        return self::$legacy_options_registered;
    }

    /**
     * Whether leftover option read-through was registered for this request.
     *
     * @return bool
     */
    public static function option_storage_bridges_registered()
    {
        return self::$option_storage_bridges_registered;
    }

    /**
     * Whether leftover meta-key API bridges were registered for this request.
     *
     * @return bool
     */
    public static function legacy_metadata_registered()
    {
        return self::$legacy_metadata_registered;
    }

    /**
     * Load legacy function wrappers when Compatibility Mode is on.
     *
     * @return void
     */
    public static function maybe_register_functions()
    {
        if (! self::is_enabled() || self::$legacy_functions_registered) {
            return;
        }

        $functions_file = __DIR__ . '/functions/index.php';

        if (! is_readable($functions_file)) {
            return;
        }

        require_once $functions_file;
        self::$legacy_functions_registered = true;
    }

    /**
     * Whether legacy function wrappers were loaded for this request.
     *
     * @return bool
     */
    public static function legacy_functions_registered()
    {
        return self::$legacy_functions_registered;
    }

    /**
     * Register legacy AJAX actions and nonce verification when the mode is on.
     *
     * Called immediately so admin-ajax.php can resolve a legacy action before
     * WordPress dispatches its dynamic hook. Repeated calls are idempotent.
     *
     * @return void
     */
    public static function maybe_register_ajax()
    {
        if (! self::is_enabled() || ! function_exists('ppcart_register_legacy_ajax_bridges')) {
            return;
        }

        ppcart_register_legacy_ajax_bridges();
    }

    /**
     * Copy leftover POST/GET keys onto canonical keys when the mode is on.
     *
     * Called immediately so checkout and admin handlers that read $_POST see
     * canonical keys. Repeated calls are idempotent.
     *
     * @return void
     */
    public static function maybe_register_request_fields()
    {
        if (! self::is_enabled() || ! function_exists('ppcart_register_legacy_request_field_bridges')) {
            return;
        }

        ppcart_register_legacy_request_field_bridges();
    }

    /**
     * Register leftover Stripe-metadata lookup when the mode is on.
     *
     * @return void
     */
    public static function maybe_register_stripe_metadata()
    {
        if (! function_exists('ppcart_register_legacy_stripe_metadata_bridges')) {
            return;
        }

        if (! self::is_enabled()) {
            if (function_exists('ppcart_unregister_legacy_stripe_metadata_bridges')) {
                ppcart_unregister_legacy_stripe_metadata_bridges();
            }

            return;
        }

        ppcart_register_legacy_stripe_metadata_bridges();
    }

    /**
     * Register leftover CPT/taxonomy/FSE names when the mode is on.
     *
     * @return void
     */
    public static function maybe_register_cpt_taxonomy()
    {
        if (! function_exists('ppcart_register_legacy_cpt_taxonomy_bridges')) {
            return;
        }

        if (! self::is_enabled()) {
            if (function_exists('ppcart_unregister_legacy_cpt_taxonomy_bridges')) {
                ppcart_unregister_legacy_cpt_taxonomy_bridges();
            }

            return;
        }

        ppcart_register_legacy_cpt_taxonomy_bridges();
    }

    /**
     * Allow leftover sc-uploads as an extra download root when the mode is on.
     *
     * In-memory extra root only. Does not create, copy, or move files.
     *
     * @return void
     */
    public static function maybe_register_download_upload_directory()
    {
        if (! function_exists('ppcart_register_legacy_download_upload_roots')) {
            return;
        }

        if (! self::is_enabled()) {
            if (function_exists('ppcart_unregister_legacy_download_upload_roots')) {
                ppcart_unregister_legacy_download_upload_roots();
            }

            return;
        }

        ppcart_register_legacy_download_upload_roots();
    }

    /**
     * Dual-register leftover shortcode tags when Compatibility Mode is on.
     *
     * Same hooks as AJAX plus Elementor / price-formatting retries so tags
     * registered after bootstrap still get leftover copies.
     *
     * @return void
     */
    public static function maybe_register_shortcodes()
    {
        if (! function_exists('ppcart_register_legacy_shortcodes')) {
            return;
        }

        if (! self::is_enabled()) {
            if (function_exists('ppcart_unregister_legacy_shortcodes')) {
                ppcart_unregister_legacy_shortcodes();
            }

            return;
        }

        ppcart_register_legacy_shortcodes();
    }

    /**
     * Dual-register leftover Gutenberg checkout blocks when Compatibility Mode is on.
     *
     * Runs on init after Cart registers the canonical checkout block.
     *
     * @return void
     */
    public static function maybe_register_gutenberg_blocks()
    {
        if (! function_exists('ppcart_register_legacy_gutenberg_blocks')) {
            return;
        }

        if (! self::is_enabled()) {
            if (function_exists('ppcart_unregister_legacy_gutenberg_blocks')) {
                ppcart_unregister_legacy_gutenberg_blocks();
            }

            return;
        }

        ppcart_register_legacy_gutenberg_blocks();
    }

    /**
     * Register always-on cron hook drain bridges.
     *
     * @return void
     */
    public static function maybe_register_cron_hook_bridges()
    {
        if (self::$cron_hook_bridges_registered || ! function_exists('ppcart_register_cron_hook_bridges')) {
            return;
        }

        ppcart_register_cron_hook_bridges();
        self::$cron_hook_bridges_registered = true;
    }

    /**
     * Register legacy hook API bridges when Compatibility Mode is on.
     *
     * @return void
     */
    public static function maybe_register_hooks()
    {
        if (! self::is_enabled()) {
            return;
        }

        if (! function_exists('ppcart_register_legacy_hook_bridges')) {
            return;
        }

        if (self::$legacy_hooks_registered) {
            ppcart_register_legacy_hook_bridges();
            return;
        }

        self::maybe_register_cron_hook_bridges();
        ppcart_register_legacy_hook_bridges();
        self::$legacy_hooks_registered = true;
    }

    /**
     * Whether legacy hook API bridges were registered for this request.
     *
     * @return bool
     */
    public static function legacy_hooks_registered()
    {
        return self::$legacy_hooks_registered;
    }

    /**
     * Whether always-on cron hook bridges were registered for this request.
     *
     * @return bool
     */
    public static function cron_hook_bridges_registered()
    {
        return self::$cron_hook_bridges_registered;
    }

    /**
     * Copy inbound legacy constants and register outbound aliases when Compatibility Mode is on.
     *
     * Safe to call more than once: define() is guarded with defined(). Do not
     * latch the whole method — bootstrap resolves before defaults exist, then
     * aliases after canonical names are defined.
     *
     * @return void
     */
    public static function maybe_register_constants()
    {
        if (! self::is_enabled()) {
            return;
        }

        if (function_exists('ncs_cart_resolve_canonical_constants')) {
            ncs_cart_resolve_canonical_constants();
        }

        if (function_exists('ncs_cart_register_legacy_constants')) {
            ncs_cart_register_legacy_constants();
        }

        self::$legacy_constants_registered = true;
    }

    /**
     * Whether the constant bridge ran while Compatibility Mode was on.
     *
     * @return bool
     */
    public static function legacy_constants_registered()
    {
        return self::$legacy_constants_registered;
    }

    /**
     * Alias legacy runtime globals onto canonical names when Compatibility Mode is on.
     *
     * @return void
     */
    public static function maybe_register_globals()
    {
        if (! self::is_enabled() || self::$legacy_globals_registered) {
            return;
        }

        if (! function_exists('ncs_cart_register_legacy_globals')) {
            return;
        }

        ncs_cart_register_legacy_globals();
        self::$legacy_globals_registered = true;
    }

    /**
     * Whether legacy runtime-global aliases were registered for this request.
     *
     * @return bool
     */
    public static function legacy_globals_registered()
    {
        return self::$legacy_globals_registered;
    }

    /**
     * Leftover StudioCart CSS for leftover CPT body classes and HTML ids.
     *
     * @param string $hook_suffix Current admin hook suffix.
     * @return void
     */
    public static function enqueue_legacy_admin_styles($hook_suffix = '')
    {
        $on_compat_screen = class_exists('PPCartComp_Studiocart_Admin', false)
            && PPCartComp_Studiocart_Admin::is_screen(is_string($hook_suffix) ? $hook_suffix : '');
        $on_plugin_screen = class_exists('PPCart_Admin_Screens', false)
            && PPCart_Admin_Screens::is_plugin_screen($hook_suffix);

        if (! $on_compat_screen && ! $on_plugin_screen) {
            return;
        }

        $version = defined('PPCARTCOMP_VERSION') ? PPCARTCOMP_VERSION : ( defined('PPCART_VERSION') ? PPCART_VERSION : '1.0.0' );
        $deps    = [];
        if (function_exists('wp_style_is') && wp_style_is('ppcart', 'registered')) {
            $deps[] = 'ppcart';
        }

        wp_enqueue_style(
            'ppcart-compat-admin',
            plugin_dir_url(__FILE__) . 'css/ppcart-compat-admin.css',
            $deps,
            $version,
            'all'
        );
    }

    /**
     * Leftover StudioCart CSS for leftover storefront markup.
     *
     * @return void
     */
    public static function enqueue_legacy_public_styles()
    {
        if (! function_exists('wp_style_is') || ! wp_style_is('ppcart', 'enqueued')) {
            return;
        }

        if (wp_style_is('ppcart-compat-public', 'enqueued')) {
            return;
        }

        $version = defined('PPCART_VERSION') ? PPCART_VERSION : '1.0.0';
        wp_enqueue_style(
            'ppcart-compat-public',
            plugin_dir_url(__FILE__) . 'css/ppcart-compat-public.css',
            [ 'ppcart' ],
            $version,
            'all'
        );

        if (self::is_enabled() && function_exists('wp_script_is') && wp_script_is('ppcart', 'registered')) {
            wp_enqueue_script(
                'ppcart-compat-js-globals',
                plugin_dir_url(__FILE__) . 'js/ppcart-compat-js-globals.js',
                [ 'ppcart' ],
                $version,
                true
            );
        }
    }

    /**
     * Leftover Gutenberg checkout block editor CSS/JS while Compatibility Mode is on.
     *
     * @return void
     */
    public static function enqueue_legacy_gutenberg_editor_assets()
    {
        if (! self::is_enabled() || ! function_exists('wp_enqueue_script')) {
            return;
        }

        $version = defined('PPCARTCOMP_VERSION') ? PPCARTCOMP_VERSION : ( defined('PPCART_VERSION') ? PPCART_VERSION : '1.0.0' );
        $base    = plugin_dir_url(__FILE__);

        $style_deps = [];
        if (function_exists('wp_style_is') && wp_style_is('ppcart-checkout-form-editor-style', 'registered')) {
            $style_deps[] = 'ppcart-checkout-form-editor-style';
        }

        wp_enqueue_style(
            'ppcart-compat-checkout-editor',
            $base . 'css/ppcart-compat-checkout-editor.css',
            $style_deps,
            $version
        );

        $script_deps = [ 'wp-blocks', 'wp-i18n' ];
        if (function_exists('wp_script_is') && wp_script_is('ppcart-checkout-form-editor', 'registered')) {
            $script_deps[] = 'ppcart-checkout-form-editor';
        }

        wp_enqueue_script(
            'ppcart-compat-legacy-checkout-block',
            $base . 'js/ppcart-compat-legacy-checkout-block.js',
            $script_deps,
            $version,
            true
        );
    }
}
