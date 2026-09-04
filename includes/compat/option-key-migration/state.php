<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Persisted option for the opt-in option-key Data migration.
 *
 * @return string
 */
function ppcart_option_key_migration_option()
{
    return 'ppcart_option_key_migration';
}

/**
 * @return array<string, mixed>
 */
function ppcart_option_key_migration_state()
{
    $state = get_option(ppcart_option_key_migration_option(), []);

    return is_array($state) ? $state : [];
}

/**
 * @param array<string, mixed> $state State to merge/store.
 * @return void
 */
function ppcart_option_key_migration_save_state(array $state)
{
    update_option(ppcart_option_key_migration_option(), $state, false);
}

/**
 * Leftover option names that map onto a canonical key.
 *
 * @return string[]
 */
function ppcart_option_key_migration_pending_names()
{
    global $wpdb;

    if (! isset($wpdb->options) || ! function_exists('ncs_cart_legacy_to_canonical_option_name')) {
        return [];
    }

    if (! is_object($wpdb) || ! method_exists($wpdb, 'get_col') || ! method_exists($wpdb, 'esc_like')) {
        return [];
    }

    $like_underscore_sc        = $wpdb->esc_like('_sc_') . '%';
    $like_sc                   = $wpdb->esc_like('sc_') . '%';
    $like_ncs                  = $wpdb->esc_like('ncs_') . '%';
    $like_transient_sc         = $wpdb->esc_like('_transient_sc_') . '%';
    $like_transient_timeout_sc = $wpdb->esc_like('_transient_timeout_sc_') . '%';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- One leftover-prefix scan for Data migration status.
    $names = $wpdb->get_col(
        $wpdb->prepare(
            "SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name LIKE %s OR option_name IN (%s, %s)",
            $like_underscore_sc,
            $like_sc,
            $like_ncs,
            $like_transient_sc,
            $like_transient_timeout_sc,
            'current_schedule_val',
            '_my_account'
        )
    );

    if (! is_array($names)) {
        return [];
    }

    $pending = [];

    foreach ($names as $legacy) {
        if (! ppcart_legacy_option_name_is_copyable($legacy)) {
            continue;
        }

        $pending[] = (string) $legacy;
    }

    return $pending;
}

/**
 * Whether the merchant still needs the opt-in option-key Data migration.
 *
 * @return bool
 */
function ppcart_option_key_needs_migration()
{
    return [] !== ppcart_option_key_migration_pending_names();
}
