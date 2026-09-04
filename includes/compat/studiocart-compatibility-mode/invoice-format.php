<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_invoice_format_value_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_invoice_format_value_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/invoice-format-values.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ppcart_map_legacy_invoice_format')) {
    /**
     * Map a leftover invoice-format code onto its canonical sibling.
     *
     * Canonical values pass through. Unknown values are unchanged.
     *
     * @param mixed $format Stored format code.
     * @return mixed
     */
    function ppcart_map_legacy_invoice_format($format)
    {
        if (! is_string($format) || '' === $format) {
            return $format;
        }

        $map = ncs_cart_get_legacy_invoice_format_value_map();

        foreach ($map as $canonical => $leftovers) {
            if (! is_string($canonical) || '' === $canonical) {
                continue;
            }

            if ($format === $canonical) {
                return $canonical;
            }

            if (! is_array($leftovers)) {
                continue;
            }

            foreach ($leftovers as $leftover) {
                if (is_string($leftover) && '' !== $leftover && $format === $leftover) {
                    return $canonical;
                }
            }
        }

        return $format;
    }
}

if (! function_exists('ppcart_maybe_map_legacy_invoice_format')) {
    /**
     * Map leftover invoice-format codes only while Compatibility Mode is on.
     *
     * @param mixed $format Stored format code.
     * @return mixed
     */
    function ppcart_maybe_map_legacy_invoice_format($format)
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return $format;
        }

        return ppcart_map_legacy_invoice_format($format);
    }
}

add_filter('ppcart_invoice_format', 'ppcart_maybe_map_legacy_invoice_format');
