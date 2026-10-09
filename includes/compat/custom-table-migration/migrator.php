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

        if ($canonical_exists && ppcart_custom_table_leftover_is_live($family)) {
            $result = ppcart_custom_table_replace_empty_canonical($leftover, $canonical);
            if (is_wp_error($result) && 'ppcart_custom_table_replaced_has_rows' !== $result->get_error_code()) {
                $errors[ $family ] = $result->get_error_message();
                continue;
            }

            $renamed[ $family ] = [
                'from'           => $leftover,
                'to'             => $canonical,
                'replaced_empty' => true,
            ];
            if (is_wp_error($result)) {
                $errors[ $family ] = $result->get_error_message();
            }
            continue;
        }

        if ($canonical_exists && ppcart_custom_table_has_rows($canonical) && ppcart_custom_table_has_rows($leftover)) {
            $skipped[ $family ] = 'conflict_both_exist';
            $errors[ $family ]  = sprintf(
                /* translators: 1: leftover table, 2: canonical table. */
                __('Skipped %1$s because %2$s already has rows.', 'publishpress-cart'),
                $leftover,
                $canonical
            );
            continue;
        }

        if ($canonical_exists) {
            $skipped[ $family ] = 'leftover_empty';
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
 * Swaps an empty canonical table out and renames the leftover table in.
 *
 * Cart activation creates empty canonical tables beside the leftover ones.
 * One RENAME TABLE moves both, so no request sees a missing table. The old
 * canonical table is dropped only if it is still empty.
 *
 * @param string $leftover  Prefixed leftover table name.
 * @param string $canonical Prefixed canonical table name.
 * @return true|WP_Error `ppcart_custom_table_replaced_has_rows` means the swap
 *                       ran but `*_replaced` was kept.
 */
function ppcart_custom_table_replace_empty_canonical($leftover, $canonical)
{
    global $wpdb;

    $from = ppcart_custom_table_quote_identifier($leftover);
    $to   = ppcart_custom_table_quote_identifier($canonical);
    if ('' === $from || '' === $to) {
        return new WP_Error('ppcart_custom_table_not_allowed', __('Refusing to rename a table whose name is not in the allowlist.', 'publishpress-cart'));
    }

    $replaced     = $canonical . '_replaced';
    $replaced_sql = '`' . str_replace('`', '', $replaced) . '`';

    ppcart_flush_live_table_cache();
    if (ppcart_custom_table_exists($replaced)) {
        return new WP_Error(
            'ppcart_custom_table_replaced_exists',
            sprintf(
                /* translators: %s: table name. */
                __('Skipped because %s already exists. Check it and drop it, then run this step again.', 'publishpress-cart'),
                $replaced
            )
        );
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Opt-in merchant migration of plugin-owned tables.
    $result = $wpdb->query('RENAME TABLE ' . $to . ' TO ' . $replaced_sql . ', ' . $from . ' TO ' . $to);
    if (false === $result) {
        return new WP_Error(
            'ppcart_custom_table_rename_failed',
            $wpdb->last_error ? $wpdb->last_error : __('RENAME TABLE failed.', 'publishpress-cart')
        );
    }

    ppcart_flush_live_table_cache();
    if (ppcart_custom_table_has_rows($replaced)) {
        return new WP_Error(
            'ppcart_custom_table_replaced_has_rows',
            sprintf(
                /* translators: %s: table name. */
                __('Kept %s because it has rows. Check it before you drop it.', 'publishpress-cart'),
                $replaced
            )
        );
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Drops the empty canonical table this step just swapped out.
    $wpdb->query('DROP TABLE ' . $replaced_sql);

    return true;
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
