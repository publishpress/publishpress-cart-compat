<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Compatibility Mode leftover meta-key API bridges.
 *
 * Third parties calling get_post_meta( $id, '_sc_amount' ) hit the canonical
 * row after copy. First-party code must use ppcart_* helpers, not leftover strings.
 *
 * @return void
 */
function ppcart_register_legacy_metadata_api_bridges()
{
    static $registered = false;

    if (function_exists('has_filter')) {
        if (false !== has_filter('get_post_metadata', 'ppcartcomp_get_post_metadata')) {
            $registered = true;
            return;
        }
    } elseif ($registered) {
        return;
    }

    add_filter('get_post_metadata', 'ppcartcomp_get_post_metadata', 10, 4);
    add_filter('update_post_metadata', 'ppcartcomp_update_post_metadata', 10, 5);
    add_filter('delete_post_metadata', 'ppcartcomp_delete_post_metadata', 10, 5);
    add_filter('add_post_metadata', 'ppcartcomp_add_post_metadata', 10, 5);
    add_filter('get_user_metadata', 'ppcartcomp_get_user_metadata', 10, 4);
    add_filter('update_user_metadata', 'ppcartcomp_update_user_metadata', 10, 5);
    add_filter('delete_user_metadata', 'ppcartcomp_delete_user_metadata', 10, 5);
    add_filter('add_user_metadata', 'ppcartcomp_add_user_metadata', 10, 5);

    $registered = true;
}

/**
 * @return bool
 */
function ppcart_meta_bridge_is_active()
{
    $state = &ppcart_meta_bridge_depth();

    return $state > 0;
}

/**
 * @return int
 */
function &ppcart_meta_bridge_depth()
{
    static $depth = 0;

    return $depth;
}

/**
 * @return void
 */
function ppcart_meta_bridge_enter()
{
    $depth = &ppcart_meta_bridge_depth();
    ++$depth;
}

/**
 * @return void
 */
function ppcart_meta_bridge_leave()
{
    $depth = &ppcart_meta_bridge_depth();
    if ($depth > 0) {
        --$depth;
    }
}

/**
 * @param mixed  $check     Pre-filtered value.
 * @param int    $object_id Object ID.
 * @param string $meta_key  Requested key.
 * @param bool   $single    Single.
 * @param string $meta_type post|user.
 * @return mixed
 */
function ppcartcomp_get_metadata($check, $object_id, $meta_key, $single, $meta_type)
{
    if (null !== $check || ppcart_meta_bridge_is_active() || ! ppcart_is_legacy_meta_key($meta_key)) {
        return $check;
    }

    $canonical = ppcart_meta_key($meta_key);
    if ($canonical === $meta_key) {
        return $check;
    }

    ppcart_meta_bridge_enter();
    if ('user' === $meta_type) {
        $value  = get_user_meta($object_id, $canonical, $single);
        $exists = ppcart_meta_key_exists($object_id, 'user', $canonical);
    } else {
        $value  = get_post_meta($object_id, $canonical, $single);
        $exists = ppcart_meta_key_exists($object_id, 'post', $canonical);
    }
    ppcart_meta_bridge_leave();

    if (! $exists) {
        return $check;
    }

    if ($single) {
        return [ $value ];
    }

    return is_array($value) ? $value : [ $value ];
}

/**
 * @param mixed  $check     Pre-filtered value.
 * @param int    $object_id Object ID.
 * @param string $meta_key  Requested key.
 * @param bool   $single    Single.
 * @return mixed
 */
function ppcartcomp_get_post_metadata($check, $object_id, $meta_key, $single)
{
    return ppcartcomp_get_metadata($check, $object_id, $meta_key, $single, 'post');
}

/**
 * @param mixed  $check     Pre-filtered value.
 * @param int    $object_id Object ID.
 * @param string $meta_key  Requested key.
 * @param bool   $single    Single.
 * @return mixed
 */
function ppcartcomp_get_user_metadata($check, $object_id, $meta_key, $single)
{
    return ppcartcomp_get_metadata($check, $object_id, $meta_key, $single, 'user');
}

/**
 * @param mixed  $check      Pre-filtered value.
 * @param int    $object_id  Object ID.
 * @param string $meta_key   Requested key.
 * @param mixed  $meta_value Value.
 * @param mixed  $prev_value Previous value.
 * @return mixed
 */
function ppcartcomp_update_post_metadata($check, $object_id, $meta_key, $meta_value, $prev_value)
{
    return ppcartcomp_write_metadata($check, $object_id, $meta_key, $meta_value, $prev_value, 'post', 'update');
}

/**
 * @param mixed  $check      Pre-filtered value.
 * @param int    $object_id  Object ID.
 * @param string $meta_key   Requested key.
 * @param mixed  $meta_value Value.
 * @param mixed  $prev_value Previous value.
 * @return mixed
 */
function ppcartcomp_update_user_metadata($check, $object_id, $meta_key, $meta_value, $prev_value)
{
    return ppcartcomp_write_metadata($check, $object_id, $meta_key, $meta_value, $prev_value, 'user', 'update');
}

/**
 * @param mixed  $check      Pre-filtered value.
 * @param int    $object_id  Object ID.
 * @param string $meta_key   Requested key.
 * @param mixed  $meta_value Value.
 * @param bool   $unique     Unique.
 * @return mixed
 */
function ppcartcomp_add_post_metadata($check, $object_id, $meta_key, $meta_value, $unique)
{
    return ppcartcomp_write_metadata($check, $object_id, $meta_key, $meta_value, $unique, 'post', 'add');
}

/**
 * @param mixed  $check      Pre-filtered value.
 * @param int    $object_id  Object ID.
 * @param string $meta_key   Requested key.
 * @param mixed  $meta_value Value.
 * @param bool   $unique     Unique.
 * @return mixed
 */
function ppcartcomp_add_user_metadata($check, $object_id, $meta_key, $meta_value, $unique)
{
    return ppcartcomp_write_metadata($check, $object_id, $meta_key, $meta_value, $unique, 'user', 'add');
}

/**
 * @param mixed  $check      Pre-filtered value.
 * @param int    $object_id  Object ID.
 * @param string $meta_key   Requested key.
 * @param mixed  $meta_value Value.
 * @param bool   $delete_all Delete all matching values.
 * @return mixed
 */
function ppcartcomp_delete_post_metadata($check, $object_id, $meta_key, $meta_value, $delete_all)
{
    return ppcartcomp_write_metadata($check, $object_id, $meta_key, $meta_value, $delete_all, 'post', 'delete');
}

/**
 * @param mixed  $check      Pre-filtered value.
 * @param int    $object_id  Object ID.
 * @param string $meta_key   Requested key.
 * @param mixed  $meta_value Value.
 * @param bool   $delete_all Delete all matching values.
 * @return mixed
 */
function ppcartcomp_delete_user_metadata($check, $object_id, $meta_key, $meta_value, $delete_all)
{
    return ppcartcomp_write_metadata($check, $object_id, $meta_key, $meta_value, $delete_all, 'user', 'delete');
}

/**
 * @param mixed  $check     Pre-filtered value.
 * @param int    $object_id Object ID.
 * @param string $meta_key  Requested key.
 * @param mixed  $value     Value / prev / unique / delete_all depending on $op.
 * @param mixed  $extra     Extra arg.
 * @param string $meta_type post|user.
 * @param string $op        update|add|delete.
 * @return mixed
 */
function ppcartcomp_write_metadata($check, $object_id, $meta_key, $value, $extra, $meta_type, $op)
{
    if (null !== $check || ppcart_meta_bridge_is_active() || ! ppcart_is_legacy_meta_key($meta_key)) {
        return $check;
    }

    $canonical = ppcart_meta_key($meta_key);
    if ($canonical === $meta_key) {
        return $check;
    }

    ppcart_meta_bridge_enter();
    if ('user' === $meta_type) {
        if ('add' === $op) {
            $result = add_user_meta($object_id, $canonical, $value, (bool) $extra);
        } elseif ('delete' === $op) {
            $result = delete_user_meta($object_id, $canonical, $value);
            delete_user_meta($object_id, $meta_key, $value);
        } else {
            $result = update_user_meta($object_id, $canonical, $value, $extra);
            delete_user_meta($object_id, $meta_key);
        }
    } elseif ('add' === $op) {
        $result = add_post_meta($object_id, $canonical, $value, (bool) $extra);
    } elseif ('delete' === $op) {
        $result = delete_post_meta($object_id, $canonical, $value);
        delete_post_meta($object_id, $meta_key, $value);
    } else {
        $result = update_post_meta($object_id, $canonical, $value, $extra);
        delete_post_meta($object_id, $meta_key);
    }
    ppcart_meta_bridge_leave();

    return false === $result ? false : true;
}
