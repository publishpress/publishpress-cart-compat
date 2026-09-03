<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/maps.php';
require_once __DIR__ . '/state.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/trait-ppcartcomp-cpt-slug-migrator-restore.php';
require_once __DIR__ . '/class-ppcartcomp-cpt-slug-migrator.php';
require_once __DIR__ . '/class-ppcartcomp-cpt-slug-migration-admin.php';

/**
 * Boot the CPT slug migration package. Does not run a migrate.
 *
 * @return void
 */
function ppcart_cpt_slug_migration_init()
{
    PPCartComp_Cpt_Slug_Migration_Admin::init();
}

add_action('plugins_loaded', 'ppcart_cpt_slug_migration_maybe_seed_state', 4);
add_action('plugins_loaded', 'ppcart_cpt_slug_migration_init', 5);
