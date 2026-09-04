<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Shortcode-tag migration settings card and AJAX.
 */
class PPCartComp_Shortcode_Tag_Migration_Admin
{
    public const NONCE_ACTION = 'ppcart_shortcode_tag_migration';
    public const AJAX_RUN     = 'ppcart_shortcode_tag_migration_run';
    public const AJAX_STOP    = 'ppcart_shortcode_tag_migration_stop';
    public const AJAX_RESTORE = 'ppcart_shortcode_tag_migration_restore';
    public const AJAX_DELETE  = 'ppcart_shortcode_tag_migration_delete_backup';

    /**
     * @return void
     */
    public static function init()
    {
        add_action('wp_ajax_' . self::AJAX_RUN, [ self::class, 'ajax_run' ]);
        add_action('wp_ajax_' . self::AJAX_STOP, [ self::class, 'ajax_stop' ]);
        add_action('wp_ajax_' . self::AJAX_RESTORE, [ self::class, 'ajax_restore' ]);
        add_action('wp_ajax_' . self::AJAX_DELETE, [ self::class, 'ajax_delete' ]);
    }

    /**
     * @return void
     */
    public static function render_block()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        $state     = ppcart_shortcode_tag_migration_get_state();
        $remaining = [
            'posts'    => 0,
            'options'  => 0,
            'postmeta' => 0,
        ];
        if (class_exists('PPCartComp_Shortcode_Tag_Migrator', false) && isset($GLOBALS['wpdb'])) {
            $remaining = PPCartComp_Shortcode_Tag_Migrator::count_remaining();
        }

        include __DIR__ . '/templates/page.php';
    }

    /**
     * @return void
     */
    public static function ajax_run()
    {
        self::require_ajax();
        wp_send_json(PPCartComp_Shortcode_Tag_Migrator::run_chunk());
    }

    /**
     * @return void
     */
    public static function ajax_stop()
    {
        self::require_ajax();
        ppcart_shortcode_tag_migration_save_state([ 'stop_requested' => true ]);
        wp_send_json([ 'ok' => true ]);
    }

    /**
     * @return void
     */
    public static function ajax_restore()
    {
        self::require_ajax();
        wp_send_json(PPCartComp_Shortcode_Tag_Migrator::restore());
    }

    /**
     * @return void
     */
    public static function ajax_delete()
    {
        self::require_ajax();
        wp_send_json(PPCartComp_Shortcode_Tag_Migrator::delete_backup());
    }

    /**
     * @return void
     */
    private static function require_ajax()
    {
        if (! current_user_can('manage_options')) {
            wp_send_json([ 'ok' => false, 'error' => 'forbidden' ], 403);
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Missing -- Nonce is read here and verified on the next line.
        $nonce = isset($_POST['nonce']) ? sanitize_text_field(wp_unslash($_POST['nonce'])) : '';
        if (! function_exists('ppcart_verify_nonce') || ! ppcart_verify_nonce($nonce, self::NONCE_ACTION)) {
            wp_send_json([ 'ok' => false, 'error' => 'nonce' ], 403);
        }
    }
}
