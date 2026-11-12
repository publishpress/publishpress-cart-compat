<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * @return string
 */
function ppcart_shortcode_tag_migration_option_name()
{
    return 'ppcart_shortcode_tag_migration';
}

/**
 * @return array<string, mixed>
 */
function ppcart_shortcode_tag_migration_default_state()
{
    return [
        'ack'              => '',
        'mode'             => 'legacy',
        'step1'            => 'idle',
        'stop_requested'   => false,
        'cursor'           => null,
        'elementor_cursor' => 0,
    ];
}

/**
 * @return array<string, mixed>
 */
function ppcart_shortcode_tag_migration_get_state()
{
    $state = [];

    if (function_exists('get_option')) {
        $stored = get_option(ppcart_shortcode_tag_migration_option_name(), []);
        if (is_array($stored)) {
            $state = $stored;
        }
    }

    return array_merge(ppcart_shortcode_tag_migration_default_state(), $state);
}

/**
 * @param array<string, mixed> $state State.
 * @return void
 */
function ppcart_shortcode_tag_migration_save_state(array $state)
{
    if (! function_exists('update_option')) {
        return;
    }

    update_option(
        ppcart_shortcode_tag_migration_option_name(),
        array_merge(ppcart_shortcode_tag_migration_get_state(), $state),
        false
    );
}

/**
 * @return bool
 */
function ppcart_shortcode_tag_migration_complete()
{
    $state = ppcart_shortcode_tag_migration_get_state();

    return 'canonical' === $state['mode'] || 'complete' === $state['step1'];
}

/**
 * @return bool
 */
function ppcart_shortcode_tag_migration_has_leftover_rows()
{
    if (class_exists('PPCartComp_Shortcode_Tag_Migrator', false)) {
        $remaining = PPCartComp_Shortcode_Tag_Migrator::count_remaining();

        return ( $remaining['posts'] + $remaining['options'] + $remaining['postmeta'] ) > 0;
    }

    return false;
}

/**
 * Seed state on activate/upgrade. Does not rewrite anything.
 *
 * @return void
 */
function ppcart_shortcode_tag_migration_maybe_seed_state()
{
    $has_saved = false;

    if (function_exists('get_option')) {
        $has_saved = false !== get_option(ppcart_shortcode_tag_migration_option_name(), false);
    }

    // The leftover scan reads every post's content, so it must run once, not on every request.
    if ($has_saved) {
        return;
    }

    // Save first so concurrent requests skip the scan while it runs.
    ppcart_shortcode_tag_migration_save_state(
        [
            'mode'  => 'legacy',
            'step1' => 'idle',
        ]
    );

    if (ppcart_shortcode_tag_migration_has_leftover_rows()) {
        return;
    }

    ppcart_shortcode_tag_migration_save_state(
        [
            'mode'  => 'canonical',
            'step1' => 'complete',
            'ack'   => 'later',
        ]
    );
}
