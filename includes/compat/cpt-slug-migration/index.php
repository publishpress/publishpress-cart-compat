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
require_once dirname(__DIR__, 2) . '/admin/class-ppcartcomp-data-migration-wizard.php';

/**
 * Boot the CPT slug migration package. Does not run a migrate.
 *
 * @return void
 */
function ppcart_cpt_slug_migration_init()
{
    PPCartComp_Cpt_Slug_Migration_Admin::init();
    if (class_exists('PPCartComp_Data_Migration_Wizard', false)) {
        PPCartComp_Data_Migration_Wizard::init();
    }
}

add_action('init', 'ppcart_cpt_slug_migration_maybe_seed_state', -1);
add_action('plugins_loaded', 'ppcart_cpt_slug_migration_init', 5);
