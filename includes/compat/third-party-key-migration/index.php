<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Copy leftover third-party stored keys onto canonical names.
 *
 * Call only from merchant Data migration (explicit UI). Never from boot,
 * Compatibility Mode registration, or leftover read-through.
 */

/**
 * Option that records the last opt-in third-party key migration run.
 *
 * @return string
 */
function ppcart_third_party_key_migration_option()
{
    return '_ppcart_third_party_key_migration';
}

/**
 * @return array<string, mixed>
 */
function ppcart_third_party_key_migration_state()
{
    $state = get_option(ppcart_third_party_key_migration_option(), []);

    return is_array($state) ? $state : [];
}

/**
 * Prefixed MemberPress transactions table when that plugin is installed.
 *
 * @return string
 */
function ppcart_third_party_memberpress_transactions_table()
{
    global $wpdb;

    if (! isset($wpdb) || ! is_object($wpdb) || ! isset($wpdb->prefix)) {
        return '';
    }

    $table = $wpdb->prefix . 'mepr_transactions';
    if (function_exists('ppcart_custom_table_exists')) {
        return ppcart_custom_table_exists($table) ? $table : '';
    }

    if (! method_exists($wpdb, 'get_var')) {
        return '';
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema probe for an optional third-party table.
    $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));

    return ($found === $table) ? $table : '';
}

/**
 * Live affiliatemeta table (canonical or leftover).
 *
 * @return string
 */
function ppcart_third_party_affiliatemeta_table()
{
    global $wpdb;

    if (function_exists('ppcart_custom_table_prefixed_names') && function_exists('ppcart_custom_table_exists')) {
        $names = ppcart_custom_table_prefixed_names('affiliatemeta');
        if (is_array($names)) {
            if (! empty($names['canonical']) && ppcart_custom_table_exists($names['canonical'])) {
                return $names['canonical'];
            }
            if (! empty($names['leftover']) && ppcart_custom_table_exists($names['leftover'])) {
                return $names['leftover'];
            }
        }
    }

    if (function_exists('ppcart_pro_live_table')) {
        $live = ppcart_pro_live_table('affiliatemeta');
        if (is_string($live) && '' !== $live) {
            return $live;
        }
    }

    if (isset($wpdb) && is_object($wpdb) && isset($wpdb->prefix)) {
        return $wpdb->prefix . 'ppcart_affiliatemeta';
    }

    return '';
}

/**
 * Rewrite leftover MemberPress trans_num prefixes onto the canonical prefix.
 *
 * Canonical wins when both prefixes already exist for the same suffix.
 * Does not run on boot.
 *
 * @return array<string, mixed>
 */
function ppcart_migrate_memberpress_trans_nums()
{
    global $wpdb;

    $table = ppcart_third_party_memberpress_transactions_table();
    if ('' === $table || ! isset($wpdb) || ! is_object($wpdb) || ! method_exists($wpdb, 'query')) {
        return [ 'status' => 'skipped', 'reason' => 'table_missing', 'moved' => 0 ];
    }

    $prefixes = function_exists('ncs_cart_get_legacy_memberpress_trans_num_names')
        ? ncs_cart_get_legacy_memberpress_trans_num_names('ppcart-txn-')
        : [ 'sc-txn-' ];

    $moved = 0;
    $skipped = 0;

    foreach ($prefixes as $leftover) {
        if (! is_string($leftover) || '' === $leftover) {
            continue;
        }

        $like = $wpdb->esc_like($leftover) . '%';

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Opt-in merchant rewrite of leftover MemberPress trans_num values.
        $rows = $wpdb->get_results(
            $wpdb->prepare(
                'SELECT id, trans_num FROM %i WHERE trans_num LIKE %s',
                $table,
                $like
            ),
            ARRAY_A
        );

        if (! is_array($rows)) {
            continue;
        }

        foreach ($rows as $row) {
            $current = isset($row['trans_num']) ? (string) $row['trans_num'] : '';
            if (0 !== strpos($current, $leftover)) {
                continue;
            }

            $canonical = 'ppcart-txn-' . substr($current, strlen($leftover));
            if ($canonical === $current) {
                continue;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Conflict check before rewriting a unique trans_num.
            $exists = $wpdb->get_var(
                $wpdb->prepare(
                    'SELECT id FROM %i WHERE trans_num = %s LIMIT 1',
                    $table,
                    $canonical
                )
            );

            if ($exists) {
                ++$skipped;
                continue;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Opt-in merchant rewrite of one leftover trans_num.
            $updated = $wpdb->update(
                $table,
                [ 'trans_num' => $canonical ],
                [ 'id' => isset($row['id']) ? (int) $row['id'] : 0 ],
                [ '%s' ],
                [ '%d' ]
            );

            if (false !== $updated) {
                ++$moved;
            }
        }
    }

    return [
        'status'  => 'ok',
        'table'   => $table,
        'moved'   => $moved,
        'skipped' => $skipped,
    ];
}

/**
 * Rename leftover affiliate meta keys onto canonical names.
 *
 * Canonical wins when both keys exist for the same affiliate. Leftover rows
 * stay in that conflict case. Does not run on boot.
 *
 * @return array<string, mixed>
 */
function ppcart_migrate_affiliate_submission_ids()
{
    global $wpdb;

    $table = ppcart_third_party_affiliatemeta_table();
    if ('' === $table || ! isset($wpdb) || ! is_object($wpdb) || ! method_exists($wpdb, 'query')) {
        return [ 'status' => 'skipped', 'reason' => 'table_missing', 'moved' => 0 ];
    }

    $map = function_exists('ncs_cart_get_affiliate_meta_key_map')
        ? ncs_cart_get_affiliate_meta_key_map()
        : [ 'ppcart_submission_id' => [ 'sc_submission_id' ] ];

    $object_col = 'ppcart_affiliate_id';
    if (function_exists('ppcart_custom_table_column_exists')) {
        if (ppcart_custom_table_column_exists($table, 'ppcart_affiliate_id')) {
            $object_col = 'ppcart_affiliate_id';
        } elseif (ppcart_custom_table_column_exists($table, 'ncs_affiliate_id')) {
            $object_col = 'ncs_affiliate_id';
        }
    } elseif (function_exists('ppcart_pro_live_affiliate_id_column')) {
        $live = ppcart_pro_live_affiliate_id_column();
        if (is_string($live) && '' !== $live) {
            $object_col = $live;
        }
    }

    $moved = 0;
    $skipped = 0;

    foreach ($map as $canonical => $leftovers) {
        if (! is_string($canonical) || ! is_array($leftovers)) {
            continue;
        }

        foreach ($leftovers as $leftover) {
            if (! is_string($leftover) || '' === $leftover || $leftover === $canonical) {
                continue;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Opt-in merchant scan of leftover affiliate meta keys.
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    'SELECT meta_id, %i AS object_id FROM %i WHERE meta_key = %s',
                    $object_col,
                    $table,
                    $leftover
                ),
                ARRAY_A
            );

            if (! is_array($rows)) {
                continue;
            }

            foreach ($rows as $row) {
                $object_id = isset($row['object_id']) ? (int) $row['object_id'] : 0;
                $meta_id   = isset($row['meta_id']) ? (int) $row['meta_id'] : 0;
                if ($meta_id < 1) {
                    continue;
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Canonical-wins conflict check.
                $exists = $wpdb->get_var(
                    $wpdb->prepare(
                        'SELECT meta_id FROM %i WHERE %i = %d AND meta_key = %s LIMIT 1',
                        $table,
                        $object_col,
                        $object_id,
                        $canonical
                    )
                );

                if ($exists) {
                    ++$skipped;
                    continue;
                }

                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Opt-in merchant rename of one leftover affiliate meta key.
                $updated = $wpdb->update(
                    $table,
                    [ 'meta_key' => $canonical ],
                    [ 'meta_id' => $meta_id ],
                    [ '%s' ],
                    [ '%d' ]
                );

                if (false !== $updated) {
                    ++$moved;
                }
            }
        }
    }

    return [
        'status'  => 'ok',
        'table'   => $table,
        'moved'   => $moved,
        'skipped' => $skipped,
    ];
}

/**
 * Opt-in leftover → canonical third-party stored key migration.
 *
 * Never called from activate, boot, or Compatibility Mode.
 *
 * @return array<string, mixed>
 */
function ppcart_migrate_third_party_stored_keys()
{
    $memberpress = ppcart_migrate_memberpress_trans_nums();
    $affiliate   = ppcart_migrate_affiliate_submission_ids();

    $state = [
        'completed_at' => time(),
        'memberpress'  => $memberpress,
        'affiliate'    => $affiliate,
    ];
    update_option(ppcart_third_party_key_migration_option(), $state, false);

    return $state;
}
