<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Data migration settings card, notice, and AJAX. Isolated from core plugin classes.
 */
class PPCartComp_Cpt_Slug_Migration_Admin
{
    public const PAGE_SLUG         = 'ppcart-migration';
    public const SETTINGS_PAGE     = 'ppcart';
    public const SETTINGS_SECTION  = 'data-migration';
    public const SETTINGS_TAB      = 'studiocart';
    public const NONCE_ACTION      = 'ppcart_cpt_slug_migration';
    public const AJAX_RUN          = 'ppcart_cpt_slug_migration_run';
    public const AJAX_STOP         = 'ppcart_cpt_slug_migration_stop';
    public const AJAX_ACK          = 'ppcart_cpt_slug_migration_ack';
    public const AJAX_RESTORE      = 'ppcart_cpt_slug_migration_restore';
    public const AJAX_DELETE       = 'ppcart_cpt_slug_migration_delete_backup';

    /**
     * @return void
     */
    public static function init()
    {
        add_action('admin_init', [ self::class, 'maybe_redirect_legacy_migration_page' ]);
        add_action('admin_init', [ self::class, 'maybe_redirect_legacy_admin_query' ]);
        add_action('admin_notices', [ self::class, 'render_notice' ]);
        add_action('admin_enqueue_scripts', [ self::class, 'enqueue_assets' ]);
        add_action('wp_ajax_' . self::AJAX_RUN, [ self::class, 'ajax_run' ]);
        add_action('wp_ajax_' . self::AJAX_STOP, [ self::class, 'ajax_stop' ]);
        add_action('wp_ajax_' . self::AJAX_ACK, [ self::class, 'ajax_ack' ]);
        add_action('wp_ajax_' . self::AJAX_RESTORE, [ self::class, 'ajax_restore' ]);
        add_action('wp_ajax_' . self::AJAX_DELETE, [ self::class, 'ajax_delete_backup' ]);
    }

    /**
     * Send leftover bookmarks for the retired Data migration submenu to the Cart StudioCart screen.
     *
     * @return void
     */
    public static function maybe_redirect_legacy_migration_page()
    {
        if (! is_admin() || self::is_ajax_or_rest_request()) {
            return;
        }

        if (! isset($_GET['page']) || ! is_string($_GET['page'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page check.
            return;
        }

        if (self::PAGE_SLUG !== sanitize_key(wp_unslash($_GET['page']))) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page check.
            return;
        }

        wp_safe_redirect(self::settings_url());
        exit;
    }

    /**
     * @return string
     */
    public static function settings_url()
    {
        if (class_exists('PPCartComp_Studiocart_Admin', false)) {
            return PPCartComp_Studiocart_Admin::screen_url();
        }

        return admin_url('admin.php?page=ppcart-studiocart');
    }

    /**
     * After step 1, send leftover CPT/taxonomy admin query strings to canonical slugs.
     *
     * @return void
     */
    public static function maybe_redirect_legacy_admin_query()
    {
        if (! is_admin() || ! ppcart_cpt_slug_migration_step1_complete()) {
            return;
        }

        if (self::is_ajax_or_rest_request()) {
            return;
        }

        $args = self::legacy_admin_query_args($_GET);
        if ([] === $args) {
            return;
        }

        wp_safe_redirect(add_query_arg($args));
        exit;
    }

    /**
     * Map leftover CPT/taxonomy admin query vars to canonical slugs.
     *
     * @param array<string, mixed> $get Query vars, usually $_GET.
     * @return array<string, string>
     */
    public static function legacy_admin_query_args(array $get)
    {
        $maps = ppcart_cpt_slug_migration_maps();
        $args = [];

        if (isset($get['post_type']) && is_string($get['post_type'])) {
            $post_type = sanitize_key(wp_unslash($get['post_type']));
            foreach ($maps['post_types'] as $pair) {
                if ($post_type === $pair['legacy']) {
                    $args['post_type'] = $pair['canonical'];
                    break;
                }
            }
        }

        if (isset($get['taxonomy']) && is_string($get['taxonomy'])) {
            $taxonomy = sanitize_key(wp_unslash($get['taxonomy']));
            foreach ($maps['taxonomies'] as $pair) {
                if ($taxonomy === $pair['legacy']) {
                    $args['taxonomy'] = $pair['canonical'];
                    break;
                }
            }
        }

        return $args;
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
        } else {
            $doing_rest = (bool) apply_filters('wp_doing_rest', $doing_rest);
        }

        return $doing_ajax || $doing_rest;
    }

    /**
     * @return bool
     */
    public static function should_show_notice()
    {
        if (! current_user_can('manage_options')) {
            return false;
        }

        if (self::is_migration_page()) {
            return false;
        }

        $state = ppcart_cpt_slug_migration_get_state();
        if ('later' === $state['ack']) {
            return false;
        }

        return ppcart_cpt_slug_migration_has_leftover_rows();
    }

    /**
     * @return bool
     */
    private static function is_migration_page()
    {
        if (class_exists('PPCartComp_Studiocart_Admin', false) && PPCartComp_Studiocart_Admin::is_screen()) {
            return true;
        }

        if (! isset($_GET['page']) || ! is_string($_GET['page'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only admin page check.
            return false;
        }

        $page = sanitize_key(wp_unslash($_GET['page']));

        return self::PAGE_SLUG === $page || 'ppcart-studiocart' === $page;
    }

    /**
     * @return void
     */
    public static function render_notice()
    {
        if (! self::should_show_notice()) {
            return;
        }

        $url   = self::settings_url();
        $nonce = wp_create_nonce(self::NONCE_ACTION);
        ?>
        <div class="notice notice-warning ppcart-cpt-migration-notice">
            <p>
                <strong><?php esc_html_e('PublishPress Cart needs a data migration', 'publishpress-cart'); ?></strong>
            </p>
            <p>
                <?php esc_html_e('This site still stores products, orders, or subscriptions with StudioCart post type names. That is separate from StudioCart Compatibility Mode, which only keeps old PHP APIs. Migrating writes a restore map, then renames rows. It will not run until you start it.', 'publishpress-cart'); ?>
            </p>
            <p>
                <a class="button button-primary" href="<?php echo esc_url($url); ?>"><?php esc_html_e('Open data migration', 'publishpress-cart'); ?></a>
                <button type="button" class="button ppcart-cpt-migration-later" data-nonce="<?php echo esc_attr($nonce); ?>"><?php esc_html_e('I’ll do it later', 'publishpress-cart'); ?></button>
            </p>
        </div>
        <?php
    }

    /**
     * @param string $hook_suffix Hook.
     * @return void
     */
    public static function enqueue_assets($hook_suffix)
    {
        $on_page = class_exists('PPCartComp_Studiocart_Admin', false)
            && PPCartComp_Studiocart_Admin::is_screen(is_string($hook_suffix) ? $hook_suffix : '');
        $notice  = self::should_show_notice();
        if (! $on_page && ! $notice) {
            return;
        }

        wp_enqueue_script(
            'ppcart-cpt-slug-migration',
            plugins_url('admin.js', __FILE__),
            [ 'jquery' ],
            defined('PPCARTCOMP_VERSION') ? PPCARTCOMP_VERSION : ( defined('PPCART_VERSION') ? PPCART_VERSION : '1.0.0' ),
            true
        );
        wp_localize_script(
            'ppcart-cpt-slug-migration',
            'ppcartCptSlugMigration',
            [
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce(self::NONCE_ACTION),
                'actions' => [
                    'run'     => self::AJAX_RUN,
                    'stop'    => self::AJAX_STOP,
                    'ack'     => self::AJAX_ACK,
                    'restore' => self::AJAX_RESTORE,
                    'delete'  => self::AJAX_DELETE,
                ],
                'i18n'    => [
                    'running' => __('Running…', 'publishpress-cart'),
                    'paused'  => __('Paused. Remaining rows can be restarted.', 'publishpress-cart'),
                    'done'    => __('This step is complete.', 'publishpress-cart'),
                    'deleted' => __('Backup deleted.', 'publishpress-cart'),
                    'error'   => __('The migration request failed.', 'publishpress-cart'),
                ],
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

        $state     = ppcart_cpt_slug_migration_get_state();
        $remaining = [
            'posts'     => 0,
            'terms'     => 0,
            'templates' => 0,
            'caps'      => 0,
        ];
        if (class_exists('PPCartComp_Cpt_Slug_Migrator', false) && isset($GLOBALS['wpdb'])) {
            $remaining = PPCartComp_Cpt_Slug_Migrator::count_step1_remaining();
        }

        include __DIR__ . '/templates/page.php';
    }

    /**
     * @return void
     */
    public static function ajax_run()
    {
        self::require_ajax();
        $step = isset($_POST['step']) ? absint(wp_unslash($_POST['step'])) : 1;
        if (2 === $step) {
            wp_send_json(PPCartComp_Cpt_Slug_Migrator::run_step2());
        }
        wp_send_json(PPCartComp_Cpt_Slug_Migrator::run_step1_chunk());
    }

    /**
     * @return void
     */
    public static function ajax_stop()
    {
        self::require_ajax();
        ppcart_cpt_slug_migration_save_state([ 'stop_requested' => true ]);
        wp_send_json([ 'ok' => true ]);
    }

    /**
     * @return void
     */
    public static function ajax_ack()
    {
        self::require_ajax();
        ppcart_cpt_slug_migration_save_state([ 'ack' => 'later' ]);
        wp_send_json([ 'ok' => true ]);
    }

    /**
     * @return void
     */
    public static function ajax_restore()
    {
        self::require_ajax();
        $step = isset($_POST['step']) ? absint(wp_unslash($_POST['step'])) : 1;
        if (2 === $step) {
            wp_send_json(PPCartComp_Cpt_Slug_Migrator::restore_step2());
        }
        wp_send_json(PPCartComp_Cpt_Slug_Migrator::restore_step1());
    }

    /**
     * @return void
     */
    public static function ajax_delete_backup()
    {
        self::require_ajax();
        wp_send_json(PPCartComp_Cpt_Slug_Migrator::delete_backup());
    }

    /**
     * @return string
     */
    public static function settings_section_id()
    {
        return self::SETTINGS_PAGE . '-' . self::SETTINGS_SECTION;
    }

    /**
     * @return void
     */
    public static function register_settings_section()
    {
        if (! function_exists('add_settings_section') || ! function_exists('add_settings_field')) {
            return;
        }

        add_settings_section(
            self::settings_section_id(),
            esc_html__('Data migration', 'publishpress-cart'),
            '__return_false',
            self::SETTINGS_PAGE
        );

        add_settings_field(
            'ppcart-data-migration-ui',
            '',
            '__return_null',
            self::SETTINGS_PAGE,
            self::settings_section_id()
        );
    }

    /**
     * @return void
     */
    private static function require_ajax()
    {
        if (! current_user_can('manage_options')) {
            wp_send_json([ 'ok' => false, 'error' => 'forbidden' ], 403);
        }

        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (! function_exists('ppcart_verify_nonce') || ! ppcart_verify_nonce($nonce, self::NONCE_ACTION)) {
            wp_send_json([ 'ok' => false, 'error' => 'nonce' ], 403);
        }
    }
}
