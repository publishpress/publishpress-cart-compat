<?php

/*
 * Integration suite bootstrap. WordPress and plugins are already loaded.
 */

if (! class_exists('Tests\NoTransactionWPTestCase')) {
    require_once __DIR__ . '/NoTransactionWPTestCase.php';
}

if (! class_exists('Tests\Support\Integration\CptSlugMigrationTestCase')) {
    require_once dirname(__DIR__) . '/Support/Integration/CptSlugMigrationTestCase.php';
}

if (! defined('PPCARTCOMP_PLUGIN_ROOT')) {
    define('PPCARTCOMP_PLUGIN_ROOT', dirname(__DIR__, 3) . '/');
}

if (! defined('PPCART_PLUGIN_ROOT')) {
    $ppcart_sibling = dirname(PPCARTCOMP_PLUGIN_ROOT) . '/publishpress-cart/';
    define('PPCART_PLUGIN_ROOT', is_dir($ppcart_sibling) ? $ppcart_sibling : PPCARTCOMP_PLUGIN_ROOT);
}
