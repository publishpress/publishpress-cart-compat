<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Option that records the last opt-in custom table migration run.
 *
 * @return string
 */
function ppcart_custom_table_migration_option()
{
    return '_sc_custom_table_migration';
}

/**
 * Flushes per-request SHOW TABLES / live-name caches.
 *
 * @return void
 */
function ppcart_flush_live_table_cache()
{
    unset($GLOBALS['ppcart_custom_table_list_cache']);
    unset($GLOBALS['ppcart_live_table_cache']);
}

/**
 * Prefixed leftover and canonical names for a family.
 *
 * @param string $family Family key.
 * @return array{leftover: string, canonical: string}|null
 */
function ppcart_custom_table_prefixed_names($family)
{
    global $wpdb;

    $spec = ppcart_custom_table_family($family);
    if (! $spec || ! isset($wpdb->prefix)) {
        return null;
    }

    return [
        'leftover'  => $wpdb->prefix . $spec['leftover'],
        'canonical' => $wpdb->prefix . $spec['canonical'],
    ];
}

/**
 * @return string[]
 */
function ppcart_custom_table_list()
{
    global $wpdb;

    if (isset($GLOBALS['ppcart_custom_table_list_cache']) && is_array($GLOBALS['ppcart_custom_table_list_cache'])) {
        return $GLOBALS['ppcart_custom_table_list_cache'];
    }

    $tables = [];
    if (isset($wpdb) && is_object($wpdb) && method_exists($wpdb, 'get_col')) {
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Cached SHOW TABLES for leftover vs canonical live names.
        $found = $wpdb->get_col('SHOW TABLES');
        if (is_array($found)) {
            $tables = array_map('strval', $found);
        }
    }

    $GLOBALS['ppcart_custom_table_list_cache'] = $tables;

    return $tables;
}

/**
 * @param string $table Prefixed table name.
 * @return bool
 */
function ppcart_custom_table_exists($table)
{
    return in_array((string) $table, ppcart_custom_table_list(), true);
}

/**
 * @param string $table  Prefixed table name.
 * @param string $column Column name.
 * @return bool
 */
function ppcart_custom_table_column_exists($table, $column)
{
    global $wpdb;

    if (! ppcart_custom_table_exists($table)) {
        return false;
    }

    if (! isset($wpdb) || ! is_object($wpdb) || ! method_exists($wpdb, 'get_row')) {
        return false;
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema inspection of a plugin-owned table.
    $row = $wpdb->get_row($wpdb->prepare('SHOW COLUMNS FROM %i LIKE %s', $table, $column));

    return ! empty($row);
}

/**
 * Families that still have a leftover table and no canonical table.
 *
 * @return string[]
 */
function ppcart_custom_table_pending_families()
{
    $pending = [];

    foreach (array_keys(ppcart_custom_table_family_map()) as $family) {
        $names = ppcart_custom_table_prefixed_names($family);
        if (! $names) {
            continue;
        }

        if (ppcart_custom_table_exists($names['leftover']) && ! ppcart_custom_table_exists($names['canonical'])) {
            $pending[] = $family;
        }
    }

    return $pending;
}

/**
 * Whether the merchant still needs the opt-in custom table Data migration.
 *
 * @return bool
 */
function ppcart_custom_table_needs_migration()
{
    if (ppcart_custom_table_pending_families()) {
        return true;
    }

    $names = ppcart_custom_table_prefixed_names('order_itemmeta');
    if (! $names) {
        return false;
    }

    $spec = ppcart_custom_table_family('order_itemmeta');
    $leftover_column = $spec['object_id_column']['leftover'];

    return ppcart_custom_table_column_exists($names['canonical'], $leftover_column);
}

/**
 * @return array<string, mixed>
 */
function ppcart_custom_table_migration_state()
{
    $state = get_option(ppcart_custom_table_migration_option(), []);

    return is_array($state) ? $state : [];
}
