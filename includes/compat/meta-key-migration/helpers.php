<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical post/user-meta key for a suffix or leftover/canonical name.
 *
 * @param string $name Suffix (`amount`), leftover (`_sc_amount`), or canonical (`_ppcart_amount`).
 * @return string
 */
function ppcart_meta_key($name)
{
    return '_ppcart_' . ppcart_meta_key_suffix($name);
}

/**
 * Leftover StudioCart post/user-meta key.
 *
 * @param string $name Suffix or leftover/canonical name.
 * @return string
 */
function ppcart_legacy_meta_key($name)
{
    return '_sc_' . ppcart_meta_key_suffix($name);
}

/**
 * @param string $name Meta key or suffix.
 * @return string
 */
function ppcart_meta_key_suffix($name)
{
    $name = (string) $name;

    if (0 === strpos($name, '_ppcart_')) {
        return substr($name, 8);
    }

    if (0 === strpos($name, '_sc_')) {
        return substr($name, 4);
    }

    if (0 === strpos($name, 'ppcart_')) {
        return substr($name, 7);
    }

    if (0 === strpos($name, 'sc_')) {
        return substr($name, 3);
    }

    return $name;
}

/**
 * @param string $name Meta key.
 * @return bool
 */
function ppcart_is_legacy_meta_key($name)
{
    return is_string($name) && 0 === strpos($name, '_sc_');
}

/**
 * Whether leftover `_sc_*` keys should be included in WP_Query / SQL IN lists.
 *
 * Dual-read get helpers still hydrate leftover rows without this toggle.
 * Query expansion only considers leftovers while Compatibility Mode is on
 * and the merchant-run meta-key migration is not complete.
 *
 * @return bool
 */
function ppcart_query_meta_keys_include_leftover()
{
    if (function_exists('ppcart_meta_key_migration_complete') && ppcart_meta_key_migration_complete()) {
        return false;
    }

    return class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
        && PPCartComp_Studiocart_Compatibility_Mode::is_enabled();
}

/**
 * Meta keys to use in WP_Query / SQL IN lists.
 *
 * @param string $name Suffix or leftover/canonical name.
 * @return array<int, string>
 */
function ppcart_query_meta_keys($name)
{
    $canonical = ppcart_meta_key($name);

    if (! ppcart_query_meta_keys_include_leftover()) {
        return [ $canonical ];
    }

    $legacy = ppcart_legacy_meta_key($name);

    if ($legacy === $canonical) {
        return [ $canonical ];
    }

    return [ $canonical, $legacy ];
}

/**
 * Whether a metabox field id matches leftover or canonical for a suffix.
 *
 * @param string $id   Field id / meta key.
 * @param string $name Suffix or leftover/canonical name.
 * @return bool
 */
function ppcart_is_meta_field_id($id, $name)
{
    $id = (string) $id;

    return $id === ppcart_meta_key($name)
        || $id === ppcart_legacy_meta_key($name)
        || $id === ppcart_meta_key_suffix($name);
}

/**
 * SQL fragment of quoted live/query meta keys for IN (...).
 *
 * @param string $name Suffix or leftover/canonical name.
 * @return string
 */
function ppcart_sql_in_meta_keys($name)
{
    global $wpdb;

    $keys = ppcart_query_meta_keys($name);
    if (! $keys) {
        return "''";
    }

    $placeholders = implode(',', array_fill(0, count($keys), '%s'));

    if (isset($wpdb) && is_object($wpdb) && method_exists($wpdb, 'prepare')) {
        return $wpdb->prepare($placeholders, $keys);
    }

    return implode(',', array_map(static function ($key) {
        return "'" . str_replace("'", "''", (string) $key) . "'";
    }, $keys));
}

/**
 * WP_Query meta_query clause that matches leftover and/or canonical keys.
 *
 * @param string $name    Suffix or leftover/canonical name.
 * @param mixed  $value   Compared value, or null to match key presence only.
 * @param string $compare Compare operator.
 * @param string $type    Value type.
 * @return array<string, mixed>
 */
function ppcart_meta_query_for($name, $value = null, $compare = '=', $type = '')
{
    $keys    = ppcart_query_meta_keys($name);
    $clauses = [];

    foreach ($keys as $key) {
        $clause = [ 'key' => $key ];
        if (null !== $value) {
            $clause['value']   = $value;
            $clause['compare'] = $compare;
            if ('' !== $type) {
                $clause['type'] = $type;
            }
        }
        $clauses[] = $clause;
    }

    if (1 === count($clauses)) {
        return $clauses[0];
    }

    $clauses['relation'] = 'OR';

    return $clauses;
}

/**
 * @param int    $object_id Object ID.
 * @param string $meta_type post|user.
 * @param string $key       Canonical or leftover key.
 * @return bool
 */
function ppcart_meta_key_exists($object_id, $meta_type, $key)
{
    if (function_exists('metadata_exists')) {
        return (bool) metadata_exists($meta_type, $object_id, $key);
    }

    if ('user' === $meta_type && function_exists('get_user_meta')) {
        $value = get_user_meta($object_id, $key, false);

        return is_array($value) && $value !== [];
    }

    if (function_exists('get_post_meta')) {
        $value = get_post_meta($object_id, $key, false);

        return is_array($value) && $value !== [];
    }

    return false;
}

/**
 * @param int    $post_id Post ID.
 * @param string $name    Suffix or leftover/canonical name.
 * @param bool   $single  Single value.
 * @return mixed
 */
function ppcart_get_post_meta($post_id, $name, $single = true)
{
    $canonical = ppcart_meta_key($name);
    $legacy    = ppcart_legacy_meta_key($name);

    if (ppcart_meta_key_exists($post_id, 'post', $canonical)) {
        return get_post_meta($post_id, $canonical, $single);
    }

    if ($legacy !== $canonical && ppcart_meta_key_exists($post_id, 'post', $legacy)) {
        return get_post_meta($post_id, $legacy, $single);
    }

    return get_post_meta($post_id, $canonical, $single);
}

/**
 * @param int    $post_id    Post ID.
 * @param string $name       Suffix or leftover/canonical name.
 * @param mixed  $meta_value Value.
 * @param mixed  $prev_value Previous value.
 * @return int|bool
 */
function ppcart_update_post_meta($post_id, $name, $meta_value, $prev_value = '')
{
    $canonical = ppcart_meta_key($name);
    $result    = update_post_meta($post_id, $canonical, $meta_value, $prev_value);

    ppcart_delete_leftover_meta_row($post_id, $name, 'post');

    return $result;
}

/**
 * @param int    $post_id    Post ID.
 * @param string $name       Suffix or leftover/canonical name.
 * @param mixed  $meta_value Unique value to delete, or empty for all.
 * @return bool
 */
function ppcart_delete_post_meta($post_id, $name, $meta_value = '')
{
    $canonical = ppcart_meta_key($name);
    $deleted   = delete_post_meta($post_id, $canonical, $meta_value);
    $deleted   = ppcart_delete_leftover_meta_row($post_id, $name, 'post', $meta_value) || $deleted;

    return $deleted;
}

/**
 * @param int    $post_id    Post ID.
 * @param string $name       Suffix or leftover/canonical name.
 * @param mixed  $meta_value Value.
 * @param bool   $unique     Unique.
 * @return int|false
 */
function ppcart_add_post_meta($post_id, $name, $meta_value, $unique = false)
{
    return add_post_meta($post_id, ppcart_meta_key($name), $meta_value, $unique);
}

/**
 * @param int    $user_id    User ID.
 * @param string $name       Suffix or leftover/canonical name.
 * @param mixed  $meta_value Value.
 * @param bool   $unique     Unique.
 * @return int|false
 */
function ppcart_add_user_meta($user_id, $name, $meta_value, $unique = false)
{
    return add_user_meta($user_id, ppcart_meta_key($name), $meta_value, $unique);
}

/**
 * @param int    $user_id User ID.
 * @param string $name    Suffix or leftover/canonical name.
 * @param bool   $single  Single value.
 * @return mixed
 */
function ppcart_get_user_meta($user_id, $name, $single = true)
{
    $canonical = ppcart_meta_key($name);
    $legacy    = ppcart_legacy_meta_key($name);

    if (ppcart_meta_key_exists($user_id, 'user', $canonical)) {
        return get_user_meta($user_id, $canonical, $single);
    }

    if ($legacy !== $canonical && ppcart_meta_key_exists($user_id, 'user', $legacy)) {
        return get_user_meta($user_id, $legacy, $single);
    }

    return get_user_meta($user_id, $canonical, $single);
}

/**
 * @param int    $user_id    User ID.
 * @param string $name       Suffix or leftover/canonical name.
 * @param mixed  $meta_value Value.
 * @param mixed  $prev_value Previous value.
 * @return int|bool
 */
function ppcart_update_user_meta($user_id, $name, $meta_value, $prev_value = '')
{
    $canonical = ppcart_meta_key($name);
    $result    = update_user_meta($user_id, $canonical, $meta_value, $prev_value);

    ppcart_delete_leftover_meta_row($user_id, $name, 'user');

    return $result;
}

/**
 * @param int    $user_id    User ID.
 * @param string $name       Suffix or leftover/canonical name.
 * @param mixed  $meta_value Unique value to delete, or empty for all.
 * @return bool
 */
function ppcart_delete_user_meta($user_id, $name, $meta_value = '')
{
    $canonical = ppcart_meta_key($name);
    $deleted   = delete_user_meta($user_id, $canonical, $meta_value);
    $deleted   = ppcart_delete_leftover_meta_row($user_id, $name, 'user', $meta_value) || $deleted;

    return $deleted;
}

/**
 * Delete a leftover `_sc_*` row without Compatibility Mode remapping it to canonical.
 *
 * @param int    $object_id  Object ID.
 * @param string $name       Suffix or leftover/canonical name.
 * @param string $meta_type  post|user.
 * @param mixed  $meta_value Unique value to delete, or empty for all.
 * @return bool
 */
function ppcart_delete_leftover_meta_row($object_id, $name, $meta_type = 'post', $meta_value = '')
{
    $canonical = ppcart_meta_key($name);
    $legacy    = ppcart_legacy_meta_key($name);

    if ($legacy === $canonical) {
        return false;
    }

    if (function_exists('ppcart_meta_bridge_enter')) {
        ppcart_meta_bridge_enter();
    }

    if ('user' === $meta_type) {
        $deleted = function_exists('delete_user_meta') ? delete_user_meta($object_id, $legacy, $meta_value) : false;
    } else {
        $deleted = function_exists('delete_post_meta') ? delete_post_meta($object_id, $legacy, $meta_value) : false;
    }

    if (function_exists('ppcart_meta_bridge_leave')) {
        ppcart_meta_bridge_leave();
    }

    return (bool) $deleted;
}

/**
 * First matching value list from get_post_custom(), canonical then leftover.
 *
 * @param array<string, mixed> $custom Custom meta array.
 * @param string               $name   Suffix or leftover/canonical name.
 * @return array<int, mixed>|null
 */
function ppcart_custom_meta_values(array $custom, $name)
{
    $canonical = ppcart_meta_key($name);
    $legacy    = ppcart_legacy_meta_key($name);

    if (isset($custom[ $canonical ]) && is_array($custom[ $canonical ]) && $custom[ $canonical ] !== []) {
        return $custom[ $canonical ];
    }

    if ($legacy !== $canonical && isset($custom[ $legacy ]) && is_array($custom[ $legacy ]) && $custom[ $legacy ] !== []) {
        return $custom[ $legacy ];
    }

    return null;
}

/**
 * Shift the first stored custom-meta blob (canonical wins).
 *
 * @param array<string, mixed> $custom Custom meta array.
 * @param string               $name   Suffix or leftover/canonical name.
 * @return mixed|null
 */
function ppcart_shift_custom_meta(array $custom, $name)
{
    $values = ppcart_custom_meta_values($custom, $name);
    if (! is_array($values) || [] === $values) {
        return null;
    }

    return array_shift($values);
}
