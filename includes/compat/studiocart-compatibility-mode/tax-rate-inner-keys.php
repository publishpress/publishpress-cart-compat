<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_tax_rate_inner_key_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_tax_rate_inner_key_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/tax-rate-inner-keys.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ppcart_copy_legacy_tax_rate_inner_keys')) {
    /**
     * Copy leftover inner keys onto canonical keys. Canonical wins.
     *
     * Leftover keys stay in place. Does not write stored rows.
     *
     * @param mixed $tax_rates Tax-rate option array.
     * @return mixed
     */
    function ppcart_copy_legacy_tax_rate_inner_keys($tax_rates)
    {
        if (! is_array($tax_rates)) {
            return $tax_rates;
        }

        $map = ncs_cart_get_legacy_tax_rate_inner_key_map();

        foreach ($map as $canonical => $leftovers) {
            if (! is_string($canonical) || '' === $canonical || ! is_array($leftovers)) {
                continue;
            }

            if (array_key_exists($canonical, $tax_rates)) {
                continue;
            }

            foreach ($leftovers as $leftover) {
                if (! is_string($leftover) || '' === $leftover) {
                    continue;
                }

                if (! array_key_exists($leftover, $tax_rates)) {
                    continue;
                }

                $tax_rates[ $canonical ] = $tax_rates[ $leftover ];
                break;
            }
        }

        return $tax_rates;
    }
}

if (! function_exists('ppcart_maybe_copy_legacy_tax_rate_inner_keys')) {
    /**
     * Copy leftover inner keys only while Compatibility Mode is on.
     *
     * @param mixed $tax_rates Tax-rate option array.
     * @return mixed
     */
    function ppcart_maybe_copy_legacy_tax_rate_inner_keys($tax_rates)
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return $tax_rates;
        }

        return ppcart_copy_legacy_tax_rate_inner_keys($tax_rates);
    }
}

add_filter('ppcart_tax_rates', 'ppcart_maybe_copy_legacy_tax_rate_inner_keys');
