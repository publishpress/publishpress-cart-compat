<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Leftover `_sc_*` hydration for product/order setup bags.
 *
 * Free reads only `_ppcart_*` from get_post_custom(). When cart-compat is
 * loaded, these filters fill leftover keys first; Free then overlays canonical.
 */

add_filter('ppcart_setup_product_from_meta', 'ppcartcomp_setup_product_from_leftover_meta', 10, 3);
add_filter('ppcart_setup_order_from_meta', 'ppcartcomp_setup_order_from_leftover_meta', 10, 3);

/**
 * @param array                $arr  Setup bag.
 * @param array<string, mixed> $meta get_post_custom() map.
 * @param int                  $id   Product post ID.
 * @return array
 */
function ppcartcomp_setup_product_from_leftover_meta($arr, $meta, $id)
{
    unset($id);

    if (! is_array($arr) || ! is_array($meta)) {
        return $arr;
    }

    foreach ($meta as $k => $v) {
        if (! is_string($k) || 0 !== strpos($k, '_sc_')) {
            continue;
        }

        $value  = array_shift($meta[ $k ]);
        $suffix = substr($k, 4);
        if (function_exists('ppcartcomp_canonical_meta_suffix')) {
            $suffix = ppcartcomp_canonical_meta_suffix($suffix);
        }
        $arr[ $suffix ] = maybe_unserialize($value);
    }

    return $arr;
}

/**
 * @param array                $arr  Setup bag.
 * @param array<string, mixed> $meta get_post_custom() map.
 * @param int                  $id   Order/subscription post ID.
 * @return array
 */
function ppcartcomp_setup_order_from_leftover_meta($arr, $meta, $id)
{
    unset($id);

    if (! is_array($arr) || ! is_array($meta)) {
        return $arr;
    }

    if (! isset($arr['invoice_total'])) {
        $arr['invoice_total'] = 0;
    }

    foreach ($meta as $k => $v) {
        if (! is_string($k) || 0 !== strpos($k, '_sc_')) {
            continue;
        }

        if (ppcart_is_meta_field_id($k, 'order_child')) {
            if (function_exists('ppcart_expand_order_child_meta_rows')) {
                $arr['order_child'] = ppcart_expand_order_child_meta_rows($v, $arr['invoice_total']);
            }
            continue;
        }

        $value  = array_shift($meta[ $k ]);
        $suffix = substr($k, 4);
        if (function_exists('ppcartcomp_canonical_meta_suffix')) {
            $suffix = ppcartcomp_canonical_meta_suffix($suffix);
        }
        $arr[ $suffix ] = maybe_unserialize($value);
    }

    return $arr;
}
