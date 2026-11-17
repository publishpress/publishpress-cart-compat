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
        'ack'                    => '',
        'mode'                   => 'legacy',
        'step1'                  => 'idle',
        'stop_requested'         => false,
        'cursor'                 => null,
        'elementor_cursor'       => 0,
        'posts_done'             => false,
        'notification_meta_done' => false,
        'elementor_widgets_checked' => false,
        'elementor_widgets_reopened' => false,
        'upsell_links_checked'       => false,
        'upsell_links_reopened'      => false,
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

/**
 * Reopen a finished shortcode step once when Elementor pages still hold
 * leftover StudioCart widgets.
 *
 * Earlier releases rewrote shortcodes inside Elementor data but not the
 * StudioCart widgets themselves, so those pages only render with
 * Compatibility Mode on. This only resets the step state; the merchant
 * still runs the step from the Data migration screen. Posts, options and
 * notification meta were already handled, so only Elementor rows rescan.
 *
 * @return void
 */
function ppcart_shortcode_tag_migration_maybe_reopen_for_elementor_widgets()
{
    if (! function_exists('get_option') || false === get_option(ppcart_shortcode_tag_migration_option_name(), false)) {
        return;
    }

    $state = ppcart_shortcode_tag_migration_get_state();
    if (! empty($state['elementor_widgets_checked'])) {
        return;
    }

    // Save first so concurrent requests skip the check while it runs.
    ppcart_shortcode_tag_migration_save_state([ 'elementor_widgets_checked' => true ]);

    if ('complete' !== $state['step1'] || ! class_exists('PPCartComp_Shortcode_Tag_Elementor', false)) {
        return;
    }

    if (! PPCartComp_Shortcode_Tag_Elementor::site_has_leftover_widgets()) {
        return;
    }

    ppcart_shortcode_tag_migration_save_state(
        [
            'step1'                      => 'idle',
            'mode'                       => 'legacy',
            'elementor_cursor'           => 0,
            'posts_done'                 => true,
            'notification_meta_done'     => true,
            'elementor_widgets_reopened' => true,
        ]
    );
}

/**
 * Whether the step was reopened for Elementor widgets and the merchant has
 * not run it again yet.
 *
 * @param array<string, mixed> $state Shortcode step state.
 * @return bool
 */
function ppcartcomp_shortcode_step_awaits_elementor_widgets(array $state)
{
    if (empty($state['elementor_widgets_reopened'])) {
        return false;
    }

    $mode  = isset($state['mode']) ? $state['mode'] : '';
    $step1 = isset($state['step1']) ? $state['step1'] : '';

    return 'canonical' !== $mode && 'complete' !== $step1;
}

/**
 * Whether the step was reopened for upsell links and the merchant has not
 * run it again yet.
 *
 * @param array<string, mixed> $state Shortcode step state.
 * @return bool
 */
function ppcartcomp_shortcode_step_awaits_upsell_links(array $state)
{
    if (empty($state['upsell_links_reopened'])) {
        return false;
    }

    $mode  = isset($state['mode']) ? $state['mode'] : '';
    $step1 = isset($state['step1']) ? $state['step1'] : '';

    return 'canonical' !== $mode && 'complete' !== $step1;
}

/**
 * Reopen a finished shortcode step once when pages still hold leftover
 * StudioCart upsell links (`?sc-upsell-offer=`) or upsell link classes.
 *
 * Earlier releases did not rewrite them, and Cart Pro only handles the new
 * names. This only resets the step state; the merchant still runs the step
 * from the Data migration screen. Only the places that hold a leftover link
 * are rescanned.
 *
 * @return void
 */
function ppcart_shortcode_tag_migration_maybe_reopen_for_upsell_links()
{
    if (! function_exists('get_option') || false === get_option(ppcart_shortcode_tag_migration_option_name(), false)) {
        return;
    }

    $state = ppcart_shortcode_tag_migration_get_state();
    if (! empty($state['upsell_links_checked'])) {
        return;
    }

    // Save first so concurrent requests skip the check while it runs.
    ppcart_shortcode_tag_migration_save_state([ 'upsell_links_checked' => true ]);

    if ('complete' !== $state['step1']) {
        return;
    }

    $in_posts     = ppcart_shortcode_tag_migration_has_leftover_upsell_links('posts');
    $in_elementor = ppcart_shortcode_tag_migration_has_leftover_upsell_links('elementor');
    if (! $in_posts && ! $in_elementor) {
        return;
    }

    $reopen = [
        'step1'                  => 'idle',
        'mode'                   => 'legacy',
        'notification_meta_done' => true,
        'upsell_links_reopened'  => true,
    ];
    $reopen['posts_done'] = ! $in_posts;
    if ($in_elementor) {
        $reopen['elementor_cursor'] = 0;
    }

    ppcart_shortcode_tag_migration_save_state($reopen);
}

/**
 * Whether posts or Elementor data still hold a leftover upsell link token.
 *
 * @param string $where `posts` or `elementor`.
 * @return bool
 */
function ppcart_shortcode_tag_migration_has_leftover_upsell_links($where)
{
    global $wpdb;

    if (! is_object($wpdb) || ! function_exists('ppcart_upsell_link_migration_pairs')) {
        return false;
    }

    $columns = 'posts' === $where ? [ 'post_content', 'post_excerpt' ] : [ 'meta_value' ];
    $likes   = [];
    $args    = [];
    foreach (array_keys(ppcart_upsell_link_migration_pairs()) as $legacy) {
        foreach ($columns as $column) {
            $likes[] = "{$column} LIKE %s";
            $args[]  = '%' . $wpdb->esc_like($legacy) . '%';
        }
    }

    if ('posts' === $where) {
        if (empty($wpdb->posts)) {
            return false;
        }
        $sql = "SELECT ID FROM {$wpdb->posts} WHERE (" . implode(' OR ', $likes) . ') LIMIT 1';
    } else {
        if (empty($wpdb->postmeta)) {
            return false;
        }
        array_unshift($args, '_elementor_data');
        $sql = "SELECT meta_id FROM {$wpdb->postmeta} WHERE meta_key = %s AND (" . implode(' OR ', $likes) . ') LIMIT 1';
    }

    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared -- One-time check; placeholders built above.
    $found = $wpdb->get_var($wpdb->prepare($sql, $args));

    return null !== $found;
}

