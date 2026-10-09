<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/maps.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/state.php';
require_once __DIR__ . '/class-ppcartcomp-shortcode-tag-migrator.php';
require_once __DIR__ . '/class-ppcartcomp-shortcode-tag-elementor.php';
require_once __DIR__ . '/class-ppcartcomp-shortcode-tag-migration-admin.php';

/**
 * Boot the shortcode-tag migration package. Does not run a migrate.
 *
 * @return void
 */
function ppcart_shortcode_tag_migration_init()
{
    PPCartComp_Shortcode_Tag_Migration_Admin::init();
}

add_action('init', 'ppcart_shortcode_tag_migration_maybe_seed_state', -1);
add_action('plugins_loaded', 'ppcart_shortcode_tag_migration_init', 5);
