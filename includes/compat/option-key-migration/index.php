<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Copy leftover StudioCart option rows onto canonical ppcart keys.
 *
 * Call only from merchant Data migration (explicit UI). Never from boot,
 * Compatibility Mode registration, or option read-through. Storage bridges
 * read leftover rows in place; they must not invoke these helpers.
 */

require_once __DIR__ . '/state.php';
require_once __DIR__ . '/admin.php';

/**
 * @return array{bulk_ran: bool}
 */
function &ppcart_option_key_migration_runtime()
{
    static $state = [ 'bulk_ran' => false ];

    return $state;
}

/**
 * Whether a leftover option name should be copied onto a canonical key.
 *
 * @param string $legacy Leftover option name.
 * @return bool
 */
function ppcart_legacy_option_name_is_copyable($legacy)
{
    if (! is_string($legacy) || '' === $legacy || ! function_exists('ncs_cart_legacy_to_canonical_option_name')) {
        return false;
    }

    $canonical = ncs_cart_legacy_to_canonical_option_name($legacy);

    return is_string($canonical) && '' !== $canonical && $canonical !== $legacy;
}

/**
 * @param string $option Option name.
 * @return array{option_name: string, option_value: string, autoload: string}|null
 */
function ppcart_get_option_storage_row($option)
{
    global $wpdb;

    if (! isset($wpdb->options) || ! is_string($option) || '' === $option) {
        return null;
    }

    if (! is_object($wpdb) || ! method_exists($wpdb, 'get_row') || ! method_exists($wpdb, 'prepare')) {
        return null;
    }

    $output = defined('ARRAY_A') ? ARRAY_A : 'ARRAY_A';

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Reads one option row without get_option filters.
    $row = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT option_name, option_value, autoload FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
            $option
        ),
        $output
    );

    if (! is_array($row) || ! isset($row['option_name'])) {
        return null;
    }

    return $row;
}

/**
 * Copy one leftover option row onto its canonical name, then delete leftover.
 *
 * Canonical wins when both rows exist. Copies autoload and the stored blob
 * as-is (including secret ciphertext).
 *
 * @param string $legacy    Leftover option name.
 * @param string $canonical Canonical option name.
 * @return bool Whether a leftover row was removed.
 */
function ppcart_copy_leftover_option_row($legacy, $canonical)
{
    global $wpdb;

    if (! isset($wpdb->options) || ! is_string($legacy) || ! is_string($canonical) || $legacy === $canonical) {
        return false;
    }

    if (! is_object($wpdb) || ! method_exists($wpdb, 'insert') || ! method_exists($wpdb, 'delete')) {
        return false;
    }

    $legacy_row = ppcart_get_option_storage_row($legacy);

    if (null === $legacy_row) {
        return false;
    }

    if (null === ppcart_get_option_storage_row($canonical)) {
        $autoload = isset($legacy_row['autoload']) ? (string) $legacy_row['autoload'] : 'no';

        if (! in_array($autoload, [ 'yes', 'no', 'on', 'off', 'auto' ], true)) {
            $autoload = 'no';
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Copies leftover blob onto the canonical key without re-serializing.
        $wpdb->insert(
            $wpdb->options,
            [
                'option_name'  => $canonical,
                'option_value' => $legacy_row['option_value'],
                'autoload'     => $autoload,
            ],
            [ '%s', '%s', '%s' ]
        );
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Removes the leftover row after canonical exists.
    $wpdb->delete($wpdb->options, [ 'option_name' => $legacy ], [ '%s' ]);

    if (function_exists('wp_cache_delete')) {
        wp_cache_delete($legacy, 'options');
        wp_cache_delete($canonical, 'options');
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
    }

    return true;
}

/**
 * Copy every leftover mapped option row onto its canonical key.
 *
 * Low-level helper. Prefer ppcart_migrate_option_keys() from Data migration.
 * Do not call from boot or shims.
 *
 * @param bool $force Re-run in this request (tests / explicit migration).
 * @return int Number of leftover rows processed.
 */
function ppcart_maybe_copy_leftover_option_rows($force = false)
{
    $state = &ppcart_option_key_migration_runtime();

    if ($state['bulk_ran'] && ! $force) {
        return 0;
    }

    $state['bulk_ran'] = true;

    if (! function_exists('ncs_cart_legacy_to_canonical_option_name')) {
        return 0;
    }

    $names = ppcart_option_key_migration_pending_names();
    $copied = 0;

    foreach ($names as $legacy) {
        $canonical = ncs_cart_legacy_to_canonical_option_name($legacy);

        if (! is_string($canonical) || '' === $canonical || $canonical === $legacy) {
            continue;
        }

        if (ppcart_copy_leftover_option_row($legacy, $canonical)) {
            ++$copied;
        }
    }

    return $copied;
}

/**
 * Opt-in leftover → canonical option-key migration for Data migration UI.
 *
 * Never called from activate, boot, or Compatibility Mode.
 *
 * @return array{copied: int, completed_at: int, pending_before: int}
 */
function ppcart_migrate_option_keys()
{
    $pending_before = count(ppcart_option_key_migration_pending_names());
    $copied         = ppcart_maybe_copy_leftover_option_rows(true);
    $completed_at   = time();

    $state = [
        'completed_at'   => $completed_at,
        'copied'         => $copied,
        'pending_before' => $pending_before,
    ];
    ppcart_option_key_migration_save_state($state);

    return $state;
}

if (function_exists('ppcart_option_key_migration_admin_init')) {
    ppcart_option_key_migration_admin_init();
}
