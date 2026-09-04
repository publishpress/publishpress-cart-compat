<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Registers the option-key Data migration AJAX handler.
 *
 * @return void
 */
function ppcart_option_key_migration_admin_init()
{
    add_action('wp_ajax_ppcart_migrate_option_keys', 'ppcart_ajax_migrate_option_keys');
}

/**
 * AJAX handler for the option-key Data migration step.
 *
 * @return void
 */
function ppcart_ajax_migrate_option_keys()
{
    check_ajax_referer('ppcart_migrate_option_keys', 'nonce');

    if (! current_user_can('manage_options')) {
        wp_send_json_error(
            [ 'message' => __('You do not have permission to migrate option keys.', 'publishpress-cart-compat') ],
            403
        );
    }

    $result = ppcart_migrate_option_keys();

    wp_send_json_success(
        [
            'message' => sprintf(
                /* translators: %d: number of leftover option rows processed. */
                __('Option key migration complete. Rows processed: %d.', 'publishpress-cart-compat'),
                isset($result['copied']) ? (int) $result['copied'] : 0
            ),
            'result'  => $result,
        ]
    );
}
