<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_affiliate_meta_key_map')) {
    /**
     * Canonical affiliate meta keys mapped to leftover names.
     *
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_affiliate_meta_key_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/affiliate-meta.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_legacy_to_canonical_affiliate_meta_key')) {
    /**
     * @param string $legacy Leftover meta key.
     * @return string|null
     */
    function ncs_cart_legacy_to_canonical_affiliate_meta_key($legacy)
    {
        if (! is_string($legacy) || '' === $legacy) {
            return null;
        }

        foreach (ncs_cart_get_affiliate_meta_key_map() as $canonical => $leftovers) {
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

if (! function_exists('ncs_cart_canonical_to_legacy_affiliate_meta_keys')) {
    /**
     * @param string $canonical Canonical meta key.
     * @return array<int, string>
     */
    function ncs_cart_canonical_to_legacy_affiliate_meta_keys($canonical)
    {
        if (! is_string($canonical) || '' === $canonical) {
            return [];
        }

        $map = ncs_cart_get_affiliate_meta_key_map();

        if (! isset($map[ $canonical ]) || ! is_array($map[ $canonical ])) {
            return [];
        }

        return array_values(array_filter($map[ $canonical ], 'is_string'));
    }
}

if (! function_exists('ncs_cart_affiliate_meta_leftover_keys')) {
    /**
     * @return array<int, string>
     */
    function ncs_cart_affiliate_meta_leftover_keys()
    {
        $keys = [];

        foreach (ncs_cart_get_affiliate_meta_key_map() as $leftovers) {
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

if (! function_exists('ppcartcomp_affiliate_meta_types')) {
    /**
     * Live and leftover affiliate metadata types.
     *
     * @return array<int, string>
     */
    function ppcartcomp_affiliate_meta_types()
    {
        $types = [ 'ppcart_affiliate' ];

        if (function_exists('ppcart_pro_live_affiliate_metadata_type')) {
            $live = ppcart_pro_live_affiliate_metadata_type();
            if (is_string($live) && '' !== $live) {
                array_unshift($types, $live);
            }
        } elseif (function_exists('ppcart_live_affiliate_metadata_type')) {
            $live = ppcart_live_affiliate_metadata_type();
            if (is_string($live) && '' !== $live) {
                array_unshift($types, $live);
            }
        }

        if (function_exists('ppcart_custom_table_affiliate_metadata_type_map')) {
            $map = ppcart_custom_table_affiliate_metadata_type_map();
            if (isset($map['leftover']) && is_string($map['leftover']) && '' !== $map['leftover']) {
                $types[] = $map['leftover'];
            }
            if (isset($map['canonical']) && is_string($map['canonical']) && '' !== $map['canonical']) {
                $types[] = $map['canonical'];
            }
        } else {
            $types[] = 'ncs_affiliate';
        }

        return array_values(array_unique($types));
    }
}

if (! function_exists('ppcartcomp_affiliate_meta_storage_get')) {
    /**
     * Always-on leftover storage read-through for affiliate meta keys.
     *
     * Canonical wins. Does not copy or delete leftover rows.
     *
     * @param mixed  $check     Pre-filtered value.
     * @param int    $object_id Object ID.
     * @param string $meta_key  Requested key.
     * @param bool   $single    Single.
     * @return mixed
     */
    function ppcartcomp_affiliate_meta_storage_get($check, $object_id, $meta_key, $single)
    {
        static $depth = 0;

        if (null !== $check || $depth > 0) {
            return $check;
        }

        $leftovers = ncs_cart_canonical_to_legacy_affiliate_meta_keys($meta_key);
        if ([] === $leftovers) {
            return $check;
        }

        ++$depth;

        $types = ppcartcomp_affiliate_meta_types();
        foreach ($types as $meta_type) {
            if (function_exists('metadata_exists') && metadata_exists($meta_type, $object_id, $meta_key)) {
                --$depth;

                return $check;
            }
        }

        foreach ($types as $meta_type) {
            foreach ($leftovers as $leftover) {
                if (! function_exists('metadata_exists') || ! metadata_exists($meta_type, $object_id, $leftover)) {
                    continue;
                }

                $value = get_metadata($meta_type, $object_id, $leftover, $single);
                --$depth;

                if ($single) {
                    return [ $value ];
                }

                return is_array($value) ? $value : [ $value ];
            }
        }

        --$depth;

        return $check;
    }
}

if (! function_exists('ppcartcomp_affiliate_meta_api_get')) {
    /**
     * Compat-on leftover API: leftover key reads hit canonical after Data migration.
     *
     * @param mixed  $check     Pre-filtered value.
     * @param int    $object_id Object ID.
     * @param string $meta_key  Requested key.
     * @param bool   $single    Single.
     * @return mixed
     */
    function ppcartcomp_affiliate_meta_api_get($check, $object_id, $meta_key, $single)
    {
        static $depth = 0;

        if (null !== $check || $depth > 0) {
            return $check;
        }

        $canonical = ncs_cart_legacy_to_canonical_affiliate_meta_key($meta_key);
        if (null === $canonical || $canonical === $meta_key) {
            return $check;
        }

        ++$depth;

        $types = ppcartcomp_affiliate_meta_types();
        foreach ($types as $meta_type) {
            if (function_exists('metadata_exists') && metadata_exists($meta_type, $object_id, $meta_key)) {
                --$depth;

                return $check;
            }
        }

        foreach ($types as $meta_type) {
            if (! function_exists('metadata_exists') || ! metadata_exists($meta_type, $object_id, $canonical)) {
                continue;
            }

            $value = get_metadata($meta_type, $object_id, $canonical, $single);
            --$depth;

            if ($single) {
                return [ $value ];
            }

            return is_array($value) ? $value : [ $value ];
        }

        --$depth;

        return $check;
    }
}

if (! function_exists('ppcart_register_affiliate_meta_storage_bridges')) {
    /**
     * Always-on leftover storage read-through. Does not copy or delete rows.
     *
     * @return void
     */
    function ppcart_register_affiliate_meta_storage_bridges()
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        foreach (ppcartcomp_affiliate_meta_types() as $meta_type) {
            add_filter('get_' . $meta_type . '_metadata', 'ppcartcomp_affiliate_meta_storage_get', 5, 4);
        }

        $registered = true;
    }
}

if (! function_exists('ppcart_register_legacy_affiliate_meta_api_bridges')) {
    /**
     * Compat-gated leftover API for affiliate meta keys.
     *
     * @return void
     */
    function ppcart_register_legacy_affiliate_meta_api_bridges()
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        foreach (ppcartcomp_affiliate_meta_types() as $meta_type) {
            add_filter('get_' . $meta_type . '_metadata', 'ppcartcomp_affiliate_meta_api_get', 10, 4);
        }

        $registered = true;
    }
}
