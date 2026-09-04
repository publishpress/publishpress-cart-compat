<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Option key for post/user-meta key migration state.
 *
 * @return string
 */
function ppcart_meta_key_migration_option_name()
{
    return 'ppcart_meta_key_migration';
}

/**
 * Default state. Never implies an automatic migrate.
 *
 * @return array<string, mixed>
 */
function ppcart_meta_key_migration_default_state()
{
    return [
        'ack'            => '',
        'mode'           => 'legacy',
        'step1'          => 'idle',
        'stop_requested' => false,
        'cursor'         => null,
    ];
}

/**
 * @return array<string, mixed>
 */
function ppcart_meta_key_migration_get_state()
{
    $state = [];

    if (function_exists('get_option')) {
        $stored = get_option(ppcart_meta_key_migration_option_name(), []);
        if (is_array($stored)) {
            $state = $stored;
        }
    }

    return array_merge(ppcart_meta_key_migration_default_state(), $state);
}

/**
 * @param array<string, mixed> $state State.
 * @return void
 */
function ppcart_meta_key_migration_save_state(array $state)
{
    if (! function_exists('update_option')) {
        return;
    }

    update_option(ppcart_meta_key_migration_option_name(), array_merge(ppcart_meta_key_migration_get_state(), $state), false);
}

/**
 * @return bool
 */
function ppcart_meta_key_migration_complete()
{
    $state = ppcart_meta_key_migration_get_state();

    return 'canonical' === $state['mode'] || 'complete' === $state['step1'];
}

/**
 * @return bool
 */
function ppcart_meta_key_migration_mixed()
{
    $state = ppcart_meta_key_migration_get_state();

    return in_array($state['step1'], [ 'running', 'paused', 'failed' ], true);
}

/**
 * Detect leftover `_sc_*` post/user-meta rows. Never migrates.
 *
 * @return bool
 */
function ppcart_meta_key_migration_has_leftover_rows()
{
    global $wpdb;

    if (! isset($wpdb) || ! is_object($wpdb) || empty($wpdb->postmeta)) {
        return false;
    }

    $like = method_exists($wpdb, 'esc_like') ? $wpdb->esc_like('_sc_') . '%' : '_sc_%';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Leftover-key presence check.
    $found = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key LIKE %s LIMIT 1",
            $like
        )
    );
    if ($found) {
        return true;
    }

    if (empty($wpdb->usermeta)) {
        return false;
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Leftover-key presence check.
    $user_found = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT umeta_id FROM {$wpdb->usermeta} WHERE meta_key LIKE %s LIMIT 1",
            $like
        )
    );

    return (bool) $user_found;
}

/**
 * Seed state on activate/upgrade. Does not rename anything.
 *
 * @return void
 */
function ppcart_meta_key_migration_maybe_seed_state()
{
    $has_saved = false;

    if (function_exists('get_option')) {
        $has_saved = false !== get_option(ppcart_meta_key_migration_option_name(), false);
    }

    $state = ppcart_meta_key_migration_get_state();

    if ($has_saved && 'canonical' === $state['mode']) {
        return;
    }

    if ($has_saved && in_array($state['step1'], [ 'complete', 'running', 'paused' ], true)) {
        return;
    }

    if (ppcart_meta_key_migration_has_leftover_rows()) {
        ppcart_meta_key_migration_save_state(
            [
                'mode'  => 'legacy',
                'step1' => 'idle',
            ]
        );

        return;
    }

    ppcart_meta_key_migration_save_state(
        [
            'mode'  => 'canonical',
            'step1' => 'complete',
            'ack'   => 'later',
        ]
    );
}
