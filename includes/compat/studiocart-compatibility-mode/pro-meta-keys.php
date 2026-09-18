<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_pro_meta_key_map')) {
    /**
     * Canonical Pro meta keys mapped to leftover names that skip `_sc_*`.
     *
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_pro_meta_key_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/pro-meta-keys.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_legacy_to_canonical_pro_meta_key')) {
    /**
     * @param string $legacy Leftover meta key.
     * @return string|null
     */
    function ncs_cart_legacy_to_canonical_pro_meta_key($legacy)
    {
        if (! is_string($legacy) || '' === $legacy) {
            return null;
        }

        foreach (ncs_cart_get_pro_meta_key_map() as $canonical => $leftovers) {
            if (! is_string($canonical) || ! is_array($leftovers)) {
                continue;
            }

            if (in_array($legacy, $leftovers, true)) {
                return $canonical;
            }
        }

        return null;
    }
}

if (! function_exists('ncs_cart_canonical_to_legacy_pro_meta_keys')) {
    /**
     * @param string $canonical Canonical meta key.
     * @return array<int, string>
     */
    function ncs_cart_canonical_to_legacy_pro_meta_keys($canonical)
    {
        if (! is_string($canonical) || '' === $canonical) {
            return [];
        }

        $map = ncs_cart_get_pro_meta_key_map();

        if (! isset($map[ $canonical ]) || ! is_array($map[ $canonical ])) {
            return [];
        }

        return array_values(array_filter($map[ $canonical ], 'is_string'));
    }
}

if (! function_exists('ncs_cart_pro_meta_leftover_keys')) {
    /**
     * @return array<int, string>
     */
    function ncs_cart_pro_meta_leftover_keys()
    {
        $keys = [];

        foreach (ncs_cart_get_pro_meta_key_map() as $leftovers) {
            if (! is_array($leftovers)) {
                continue;
            }

            foreach ($leftovers as $leftover) {
                if (is_string($leftover) && '' !== $leftover) {
                    $keys[] = $leftover;
                }
            }
        }

        return array_values(array_unique($keys));
    }
}

if (! function_exists('ppcartcomp_pro_meta_storage_get')) {
    /**
     * Always-on leftover storage read-through for Pro keys without `_sc_`.
     *
     * Canonical wins. Does not copy or delete leftover rows.
     *
     * @param mixed  $check     Pre-filtered value.
     * @param int    $object_id Object ID.
     * @param string $meta_key  Requested key.
     * @param bool   $single    Single.
     * @return mixed
     */
    function ppcartcomp_pro_meta_storage_get($check, $object_id, $meta_key, $single)
    {
        static $depth = 0;

        if (null !== $check || $depth > 0 || ( function_exists('ppcart_meta_bridge_is_active') && ppcart_meta_bridge_is_active() )) {
            return $check;
        }

        $leftovers = ncs_cart_canonical_to_legacy_pro_meta_keys($meta_key);

        if ([] === $leftovers) {
            return $check;
        }

        ++$depth;

        if (function_exists('ppcart_meta_bridge_enter')) {
            ppcart_meta_bridge_enter();
        }

        if (function_exists('ppcart_meta_key_exists') && ppcart_meta_key_exists($object_id, 'post', $meta_key)) {
            if (function_exists('ppcart_meta_bridge_leave')) {
                ppcart_meta_bridge_leave();
            }

            --$depth;

            return $check;
        }

        foreach ($leftovers as $leftover) {
            if (! function_exists('ppcart_meta_key_exists') || ! ppcart_meta_key_exists($object_id, 'post', $leftover)) {
                continue;
            }

            $value = get_post_meta($object_id, $leftover, $single);

            if (function_exists('ppcart_meta_bridge_leave')) {
                ppcart_meta_bridge_leave();
            }

            --$depth;

            if ($single) {
                return [ $value ];
            }

            return is_array($value) ? $value : [ $value ];
        }

        if (function_exists('ppcart_meta_bridge_leave')) {
            ppcart_meta_bridge_leave();
        }

        --$depth;

        return $check;
    }
}

if (! function_exists('ppcartcomp_pro_meta_api_get')) {
    /**
     * Compat-on leftover API: after Data migration, leftover key reads hit canonical.
     *
     * @param mixed  $check     Pre-filtered value.
     * @param int    $object_id Object ID.
     * @param string $meta_key  Requested key.
     * @param bool   $single    Single.
     * @return mixed
     */
    function ppcartcomp_pro_meta_api_get($check, $object_id, $meta_key, $single)
    {
        static $depth = 0;

        if (null !== $check || $depth > 0 || ( function_exists('ppcart_meta_bridge_is_active') && ppcart_meta_bridge_is_active() )) {
            return $check;
        }

        $canonical = ncs_cart_legacy_to_canonical_pro_meta_key($meta_key);

        if (null === $canonical || $canonical === $meta_key) {
            return $check;
        }

        ++$depth;

        if (function_exists('ppcart_meta_bridge_enter')) {
            ppcart_meta_bridge_enter();
        }

        if (function_exists('ppcart_meta_key_exists') && ppcart_meta_key_exists($object_id, 'post', $meta_key)) {
            if (function_exists('ppcart_meta_bridge_leave')) {
                ppcart_meta_bridge_leave();
            }

            --$depth;

            return $check;
        }

        if (! function_exists('ppcart_meta_key_exists') || ! ppcart_meta_key_exists($object_id, 'post', $canonical)) {
            if (function_exists('ppcart_meta_bridge_leave')) {
                ppcart_meta_bridge_leave();
            }

            --$depth;

            return $check;
        }

        $value = get_post_meta($object_id, $canonical, $single);

        if (function_exists('ppcart_meta_bridge_leave')) {
            ppcart_meta_bridge_leave();
        }

        --$depth;

        if ($single) {
            return [ $value ];
        }

        return is_array($value) ? $value : [ $value ];
    }
}

if (! function_exists('ppcart_register_pro_meta_storage_bridges')) {
    /**
     * Always-on leftover storage read-through. Does not copy or delete rows.
     *
     * @return void
     */
    function ppcart_register_pro_meta_storage_bridges()
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        add_filter('get_post_metadata', 'ppcartcomp_pro_meta_storage_get', 5, 4);

        $registered = true;
    }
}

if (! function_exists('ppcart_register_legacy_pro_meta_api_bridges')) {
    /**
     * Compat-gated leftover API for Pro keys without `_sc_`.
     *
     * @return void
     */
    function ppcart_register_legacy_pro_meta_api_bridges()
    {
        static $registered = false;

        if (function_exists('has_filter')) {
            if (false !== has_filter('get_post_metadata', 'ppcartcomp_pro_meta_api_get')) {
                $registered = true;
                return;
            }
        } elseif ($registered) {
            return;
        }

        add_filter('get_post_metadata', 'ppcartcomp_pro_meta_api_get', 10, 4);

        $registered = true;
    }
}
