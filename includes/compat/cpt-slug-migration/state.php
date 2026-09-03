<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Option key for CPT slug migration state (canonical prefix).
 *
 * @return string
 */
function ppcart_cpt_slug_migration_option_name()
{
    return 'ppcart_cpt_slug_migration';
}

/**
 * Default state. Never implies an automatic migrate.
 *
 * @return array<string, mixed>
 */
function ppcart_cpt_slug_migration_default_state()
{
    return [
        'ack'            => '',
        'mode'           => 'legacy',
        'step1'          => 'idle',
        'step2'          => 'idle',
        'stop_requested' => false,
        'cursor'         => null,
    ];
}

/**
 * @return array<string, mixed>
 */
function ppcart_cpt_slug_migration_get_state()
{
    $state = [];

    if (function_exists('get_option')) {
        $stored = get_option(ppcart_cpt_slug_migration_option_name(), []);
        if (is_array($stored)) {
            $state = $stored;
        }
    }

    return array_merge(ppcart_cpt_slug_migration_default_state(), $state);
}

/**
 * @param array<string, mixed> $state State.
 * @return void
 */
function ppcart_cpt_slug_migration_save_state(array $state)
{
    if (! function_exists('update_option')) {
        return;
    }

    update_option(ppcart_cpt_slug_migration_option_name(), array_merge(ppcart_cpt_slug_migration_get_state(), $state), false);
}

/**
 * @return bool
 */
function ppcart_cpt_slug_migration_step1_complete()
{
    $state = ppcart_cpt_slug_migration_get_state();

    return 'canonical' === $state['mode'] || 'complete' === $state['step1'];
}

/**
 * @return bool
 */
function ppcart_cpt_slug_migration_step1_mixed()
{
    $state = ppcart_cpt_slug_migration_get_state();

    return in_array($state['step1'], [ 'running', 'paused', 'failed' ], true);
}

/**
 * @return bool
 */
function ppcart_cpt_slug_migration_step2_complete()
{
    return 'complete' === ppcart_cpt_slug_migration_get_state()['step2'];
}

/**
 * Detect leftover Free CPT/taxonomy rows. Never migrates.
 *
 * @return bool
 */
function ppcart_cpt_slug_migration_has_leftover_rows()
{
    global $wpdb;

    if (! isset($wpdb) || ! is_object($wpdb) || empty($wpdb->posts)) {
        return false;
    }

    $maps       = ppcart_cpt_slug_migration_maps();
    $post_types = [];
    foreach ($maps['post_types'] as $pair) {
        $post_types[] = $pair['legacy'];
    }
    $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
    $sql          = "SELECT ID FROM {$wpdb->posts} WHERE post_type IN ($placeholders) LIMIT 1";
    $found        = $wpdb->get_var($wpdb->prepare($sql, $post_types));
    if ($found) {
        return true;
    }

    $taxonomies = [];
    foreach ($maps['taxonomies'] as $pair) {
        $taxonomies[] = $pair['legacy'];
    }
    $tax_placeholders = implode(',', array_fill(0, count($taxonomies), '%s'));
    $tax_sql          = "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ($tax_placeholders) LIMIT 1";
    if ($wpdb->get_var($wpdb->prepare($tax_sql, $taxonomies))) {
        return true;
    }

    return ppcart_cpt_slug_migration_has_leftover_roles_or_caps();
}

/**
 * Leftover role slugs or capability strings still assigned to a role.
 *
 * @return bool
 */
function ppcart_cpt_slug_migration_has_leftover_roles_or_caps()
{
    $maps = ppcart_cpt_slug_migration_maps();

    if (function_exists('get_role')) {
        foreach ($maps['roles'] as $legacy => $_canonical) {
            if (get_role($legacy)) {
                return true;
            }
        }
    }

    if (! function_exists('wp_roles')) {
        return false;
    }

    $roles = wp_roles();
    if (! $roles || empty($roles->role_objects)) {
        return false;
    }

    foreach ($roles->role_objects as $role) {
        foreach ($maps['capabilities'] as $legacy => $_canonical) {
            if ($role->has_cap($legacy)) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Seed state on activate/upgrade. Does not rename anything.
 *
 * @return void
 */
function ppcart_cpt_slug_migration_maybe_seed_state()
{
    $has_saved = false;

    if (function_exists('get_option')) {
        $has_saved = false !== get_option(ppcart_cpt_slug_migration_option_name(), false);
    }

    $state = ppcart_cpt_slug_migration_get_state();

    if ($has_saved && 'canonical' === $state['mode']) {
        return;
    }

    if ($has_saved && in_array($state['step1'], [ 'complete', 'running', 'paused' ], true)) {
        return;
    }

    if (ppcart_cpt_slug_migration_has_leftover_rows()) {
        ppcart_cpt_slug_migration_save_state(
            [
                'mode'  => 'legacy',
                'step1' => 'idle',
            ]
        );

        return;
    }

    ppcart_cpt_slug_migration_save_state(
        [
            'mode'  => 'canonical',
            'step1' => 'complete',
            'step2' => 'complete',
            'ack'   => 'later',
        ]
    );
}
