<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Prefixed live table name for a custom-table family.
 *
 * Leftover installs keep leftover names until Data migration runs.
 * Fresh installs (no leftover table) use canonical names. An empty canonical
 * table beside a leftover table with rows does not count as migrated.
 *
 * Pro `PPCart_Tax` and affiliate SQL must call this (via `ppcart_pro_live_table()`)
 * instead of hardcoding leftover or canonical table names.
 *
 * @param string $family Family key from ppcart_custom_table_family_map().
 * @return string Prefixed table name, or empty string for an unknown family.
 */
function ppcart_live_table($family)
{
    $family = (string) $family;

    if (isset($GLOBALS['ppcart_live_table_cache'][ $family ])) {
        return $GLOBALS['ppcart_live_table_cache'][ $family ];
    }

    $names = ppcart_custom_table_prefixed_names($family);
    if (! $names) {
        return '';
    }

    $live = ppcart_custom_table_leftover_is_live($family)
        ? $names['leftover']
        : $names['canonical'];

    if (! isset($GLOBALS['ppcart_live_table_cache']) || ! is_array($GLOBALS['ppcart_live_table_cache'])) {
        $GLOBALS['ppcart_live_table_cache'] = [];
    }
    $GLOBALS['ppcart_live_table_cache'][ $family ] = $live;

    return $live;
}

/**
 * Unprefixed live table suffix for a family.
 *
 * @param string $family Family key.
 * @return string
 */
function ppcart_live_table_suffix($family)
{
    global $wpdb;

    $live = ppcart_live_table($family);
    if ('' === $live || ! isset($wpdb->prefix) || 0 !== strpos($live, $wpdb->prefix)) {
        $spec = ppcart_custom_table_family($family);

        return $spec ? (string) $spec['canonical'] : '';
    }

    return substr($live, strlen($wpdb->prefix));
}

/**
 * Live metadata type for order item meta.
 *
 * Paired with ppcart_live_order_item_id_column() and the live order_itemmeta table.
 *
 * @return string
 */
function ppcart_live_metadata_type()
{
    $map = ppcart_custom_table_metadata_type_map();

    return ppcart_custom_table_leftover_is_live('order_itemmeta')
        ? $map['leftover']
        : $map['canonical'];
}

/**
 * Live object-id column on the order item meta table.
 *
 * @return string
 */
function ppcart_live_order_item_id_column()
{
    $spec = ppcart_custom_table_family('order_itemmeta');
    $map  = $spec && isset($spec['object_id_column']) ? $spec['object_id_column'] : [
        'leftover'  => 'ncs_order_item_id',
        'canonical' => 'ppcart_order_item_id',
    ];

    return 'ncs_order_item' === ppcart_live_metadata_type()
        ? $map['leftover']
        : $map['canonical'];
}

/**
 * Registers $wpdb->{live_type}meta for the WordPress metadata API.
 *
 * Unsets the unused leftover/canonical property so a post-migration request
 * cannot keep a stale pointer. Compatibility Mode may re-add leftover
 * `$wpdb` properties for third-party leftover SQL after migration.
 *
 * @return void
 */
function ppcart_register_live_meta_table()
{
    global $wpdb;

    if (! isset($wpdb) || ! is_object($wpdb)) {
        return;
    }

    $map  = ppcart_custom_table_metadata_type_map();
    $live = ppcart_live_metadata_type();
    $table = ppcart_live_table('order_itemmeta');

    foreach ([ $map['leftover'] . 'meta', $map['canonical'] . 'meta' ] as $property) {
        unset($wpdb->$property);
    }

    $property = $live . 'meta';
    $wpdb->$property = $table;

    ppcart_register_live_affiliate_meta_table();
}

/**
 * Live metadata type for affiliate meta.
 *
 * @return string
 */
function ppcart_live_affiliate_metadata_type()
{
    $map = ppcart_custom_table_affiliate_metadata_type_map();

    return ppcart_custom_table_leftover_is_live('affiliatemeta')
        ? $map['leftover']
        : $map['canonical'];
}

/**
 * Live object-id column on the affiliate meta table.
 *
 * @return string
 */
function ppcart_live_affiliate_id_column()
{
    $spec = ppcart_custom_table_family('affiliatemeta');
    $map  = $spec && isset($spec['object_id_column']) ? $spec['object_id_column'] : [
        'leftover'  => 'ncs_affiliate_id',
        'canonical' => 'ppcart_affiliate_id',
    ];

    return 'ncs_affiliate' === ppcart_live_affiliate_metadata_type()
        ? $map['leftover']
        : $map['canonical'];
}

/**
 * Registers $wpdb->{live_affiliate_type}meta for the WordPress metadata API.
 *
 * @return void
 */
function ppcart_register_live_affiliate_meta_table()
{
    global $wpdb;

    if (! isset($wpdb) || ! is_object($wpdb)) {
        return;
    }

    $map   = ppcart_custom_table_affiliate_metadata_type_map();
    $live  = ppcart_live_affiliate_metadata_type();
    $table = ppcart_live_table('affiliatemeta');

    foreach ([ $map['leftover'] . 'meta', $map['canonical'] . 'meta' ] as $property) {
        unset($wpdb->$property);
    }

    $property = $live . 'meta';
    $wpdb->$property = $table;

    if (
        class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
        && PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        && $map['leftover'] !== $live
    ) {
        $leftover_property = $map['leftover'] . 'meta';
        $wpdb->$leftover_property = $table;
    }
}
