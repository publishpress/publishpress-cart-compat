<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/state.php';
require_once __DIR__ . '/metadata-bridges.php';
require_once __DIR__ . '/class-ppcartcomp-meta-key-migrator.php';
require_once __DIR__ . '/class-ppcartcomp-meta-key-migration-admin.php';

/**
 * Boot the meta-key migration package. Does not run a migrate.
 *
 * @return void
 */
function ppcart_meta_key_migration_init()
{
    PPCartComp_Meta_Key_Migration_Admin::init();
}

add_action('plugins_loaded', 'ppcart_meta_key_migration_maybe_seed_state', 4);
add_action('plugins_loaded', 'ppcart_meta_key_migration_init', 5);
add_action('pre_get_posts', 'ppcart_expand_wp_query_meta_keys', 9);

/**
 * Expand leftover/canonical meta keys in WP_Query when Compatibility Mode is on.
 *
 * @param WP_Query $query Query.
 * @return void
 */
function ppcart_expand_wp_query_meta_keys($query)
{
    if (! is_object($query) || ! method_exists($query, 'get') || ! method_exists($query, 'set')) {
        return;
    }

    $meta_key = $query->get('meta_key');
    if (is_string($meta_key) && '' !== $meta_key && (ppcart_is_legacy_meta_key($meta_key) || 0 === strpos($meta_key, '_ppcart_'))) {
        $keys = ppcart_query_meta_keys($meta_key);
        if (count($keys) > 1) {
            $value   = $query->get('meta_value');
            $compare = $query->get('meta_compare');
            $clause  = ppcart_meta_query_for(
                $meta_key,
                ( '' === $value || null === $value ) ? null : $value,
                is_string($compare) && '' !== $compare ? $compare : '='
            );
            $existing = $query->get('meta_query');
            $merged   = is_array($existing) && $existing !== []
                ? [ 'relation' => 'AND', $clause, $existing ]
                : $clause;
            $query->set('meta_key', '');
            $query->set('meta_value', '');
            $query->set('meta_compare', '');
            $query->set('meta_query', is_array($merged) && isset($merged['key']) ? [ $merged ] : $merged);
        } else {
            $query->set('meta_key', $keys[0]);
        }
    }

    $meta_query = $query->get('meta_query');
    if (is_array($meta_query) && $meta_query !== []) {
        $query->set('meta_query', ppcart_expand_meta_query_keys($meta_query));
    }
}

/**
 * @param array<string|int, mixed> $meta_query Meta query.
 * @return array<string|int, mixed>
 */
function ppcart_expand_meta_query_keys(array $meta_query)
{
    $expanded = [];
    foreach ($meta_query as $key => $clause) {
        if ('relation' === $key) {
            $expanded[ $key ] = $clause;
            continue;
        }
        if (! is_array($clause)) {
            $expanded[ $key ] = $clause;
            continue;
        }
        if (isset($clause['key']) && is_string($clause['key'])) {
            if (! ppcart_is_legacy_meta_key($clause['key']) && 0 !== strpos($clause['key'], '_ppcart_')) {
                $expanded[ $key ] = $clause;
                continue;
            }
            $keys = ppcart_query_meta_keys($clause['key']);
            if (count($keys) > 1) {
                $group = [ 'relation' => 'OR' ];
                foreach ($keys as $meta_key) {
                    $copy         = $clause;
                    $copy['key']  = $meta_key;
                    $group[]      = $copy;
                }
                $expanded[ $key ] = $group;
                continue;
            }
            $clause['key']    = $keys[0];
            $expanded[ $key ] = $clause;
            continue;
        }
        $expanded[ $key ] = ppcart_expand_meta_query_keys($clause);
    }

    return $expanded;
}
