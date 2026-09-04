<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/maps.php';
require_once __DIR__ . '/state.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/migrator.php';
require_once __DIR__ . '/admin.php';

ppcart_register_live_meta_table();
add_action('plugins_loaded', 'ppcart_register_live_meta_table', 1);

if (function_exists('ppcart_custom_table_migration_admin_init')) {
    ppcart_custom_table_migration_admin_init();
}
