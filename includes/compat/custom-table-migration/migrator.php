<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Opt-in leftover → canonical custom table migration.
 *
 * Never called from activate/upgrade. Merchants run this from Settings →
 * Maintenance → Data migration.
 *
 * @return array<string, mixed>
 */
function ppcart_migrate_custom_tables()
{
    global $wpdb;

    $renamed = [];
    $skipped = [];
    $errors  = [];

    foreach (ppcart_custom_table_family_map() as $family => $spec) {
        $names = ppcart_custom_table_prefixed_names($family);
        if (! $names) {
            continue;
        }

        $leftover  = $names['leftover'];
        $canonical = $names['canonical'];
        $leftover_exists  = ppcart_custom_table_exists($leftover);
        $canonical_exists = ppcart_custom_table_exists($canonical);

        if (! $leftover_exists) {
            $skipped[ $family ] = $canonical_exists ? 'already_canonical' : 'missing';
            continue;
        }

        if ($canonical_exists) {
            $skipped[ $family ] = 'conflict_both_exist';
            $errors[ $family ]  = sprintf(
                /* translators: 1: leftover table, 2: canonical table. */
                __('Skipped %1$s because %2$s already exists.', 'publishpress-cart'),
                $leftover,
                $canonical
            );
            continue;
        }

        $from = ppcart_custom_table_quote_identifier($leftover);
        $to   = ppcart_custom_table_quote_identifier($canonical);
        if ('' === $from || '' === $to) {
            $errors[ $family ] = __('Refusing to rename a table whose name is not in the allowlist.', 'publishpress-cart');
            continue;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Opt-in merchant migration of plugin-owned leftover tables.
        $result = $wpdb->query('RENAME TABLE ' . $from . ' TO ' . $to);
        if (false === $result) {
            $errors[ $family ] = $wpdb->last_error
                ? $wpdb->last_error
                : __('RENAME TABLE failed.', 'publishpress-cart');
            continue;
        }

        $renamed[ $family ] = [
            'from' => $leftover,
            'to'   => $canonical,
        ];
    }

    ppcart_flush_live_table_cache();

    $column = ppcart_migrate_order_item_id_column();
    $affiliate_column = ppcart_migrate_object_id_column('affiliatemeta');
    ppcart_flush_live_table_cache();
    ppcart_register_live_meta_table();

    $state = [
        'completed_at' => time(),
        'renamed'      => $renamed,
        'skipped'      => $skipped,
        'errors'       => $errors,
        'column'       => $column,
        'affiliate_column' => $affiliate_column,
    ];
    update_option(ppcart_custom_table_migration_option(), $state, false);

    return $state;
}

/**
 * Renames ncs_order_item_id → ppcart_order_item_id on the canonical meta table only.
 *
 * Does not ALTER a leftover table. Metadata type and object-id column stay paired
 * until the meta table itself has been renamed.
 *
 * @return array<string, mixed>
 */
function ppcart_migrate_order_item_id_column()
{
    return ppcart_migrate_object_id_column('order_itemmeta');
}

/**
 * Renames leftover object-id column → canonical on the canonical table only.
 *
 * Does not ALTER a leftover table. Metadata type and object-id column stay paired
 * until the meta table itself has been renamed.
 *
 * @param string $family Family key with an object_id_column spec.
 * @return array<string, mixed>
 */
function ppcart_migrate_object_id_column($family)
{
    global $wpdb;

    $spec  = ppcart_custom_table_family($family);
    $names = ppcart_custom_table_prefixed_names($family);
    if (! $spec || ! $names || empty($spec['object_id_column'])) {
        return [ 'status' => 'skipped', 'reason' => 'missing_map' ];
    }

    $leftover_column  = $spec['object_id_column']['leftover'];
    $canonical_column = $spec['object_id_column']['canonical'];
    $canonical_table  = $names['canonical'];

    if (! ppcart_custom_table_exists($canonical_table)) {
        return [ 'status' => 'skipped', 'reason' => 'canonical_table_missing' ];
    }

    if (ppcart_custom_table_column_exists($canonical_table, $canonical_column)) {
        return [ 'status' => 'already_canonical', 'table' => $canonical_table ];
    }

    if (! ppcart_custom_table_column_exists($canonical_table, $leftover_column)) {
        return [ 'status' => 'skipped', 'reason' => 'leftover_column_missing' ];
    }

    $definition = ppcart_custom_table_column_change_definition($canonical_table, $leftover_column);
    if ('' === $definition) {
        $definition = ('affiliatemeta' === $family)
            ? 'bigint(20) unsigned NOT NULL'
            : 'bigint(20) NOT NULL';
    }

    $table_sql = ppcart_custom_table_quote_identifier($canonical_table);
    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Opt-in merchant migration of plugin-owned leftover column.
    $result = $wpdb->query(
        'ALTER TABLE ' . $table_sql . ' CHANGE `' . $leftover_column . '` `' . $canonical_column . '` ' . $definition
    );

    if (false === $result) {
        return [
            'status' => 'error',
            'error'  => $wpdb->last_error ? $wpdb->last_error : __('ALTER TABLE failed.', 'publishpress-cart'),
        ];
    }

    return [
        'status' => 'renamed',
        'from'   => $leftover_column,
        'to'     => $canonical_column,
        'table'  => $canonical_table,
    ];
}

/**
 * Type clause for ALTER TABLE ... CHANGE, copied from the leftover column.
 *
 * @param string $table  Prefixed table name.
 * @param string $column Column name.
 * @return string Empty when the definition cannot be trusted.
 */
function ppcart_custom_table_column_change_definition($table, $column)
{
    global $wpdb;

    if (! isset($wpdb) || ! is_object($wpdb) || ! method_exists($wpdb, 'get_row') || ! method_exists($wpdb, 'prepare')) {
        return '';
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema inspection of a plugin-owned leftover column.
    $row = $wpdb->get_row($wpdb->prepare('SHOW COLUMNS FROM %i LIKE %s', $table, $column));
    if (! is_object($row) || empty($row->Type) || ! is_string($row->Type)) {
        return '';
    }

    if (! preg_match('/^[a-z0-9]+(?:\([0-9,]+(?:\s+unsigned)?\))?(?:\s+unsigned)?$/i', trim($row->Type))) {
        return '';
    }

    $sql = trim($row->Type);
    if (isset($row->Null) && 'NO' === strtoupper((string) $row->Null)) {
        $sql .= ' NOT NULL';
    }

    return $sql;
}

/**
 * Quotes an allowlisted prefixed table name for DDL.
 *
 * @param string $table Prefixed table name.
 * @return string Backtick-quoted identifier, or empty when not allowlisted.
 */
function ppcart_custom_table_quote_identifier($table)
{
    $allowed = [];
    foreach (array_keys(ppcart_custom_table_family_map()) as $family) {
        $names = ppcart_custom_table_prefixed_names($family);
        if (! $names) {
            continue;
        }
        $allowed[] = $names['leftover'];
        $allowed[] = $names['canonical'];
    }

    if (! in_array($table, $allowed, true)) {
        return '';
    }

    return '`' . str_replace('`', '', $table) . '`';
}
