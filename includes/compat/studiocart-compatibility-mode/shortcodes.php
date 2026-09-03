<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_shortcode_map')) {
    /**
     * Canonical shortcode tag => leftover tag (string or late-callback row).
     *
     * @return array<string, string|array{legacy: string, callback?: string}>
     */
    function ncs_cart_get_legacy_shortcode_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/shortcodes.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_normalize_shortcode_map_entry')) {
    /**
     * @param mixed $entry Leftover tag string or late-callback row.
     * @return array{legacy: string, callback: string}|null
     */
    function ncs_cart_normalize_shortcode_map_entry($entry)
    {
        if (is_string($entry) && '' !== $entry) {
            return [
                'legacy'   => $entry,
                'callback' => '',
            ];
        }

        if (! is_array($entry) || empty($entry['legacy']) || ! is_string($entry['legacy'])) {
            return null;
        }

        $callback = isset($entry['callback']) && is_string($entry['callback'])
            ? $entry['callback']
            : '';

        return [
            'legacy'   => $entry['legacy'],
            'callback' => $callback,
        ];
    }
}

if (! function_exists('ncs_cart_legacy_shortcode_tag')) {
    /**
     * @param mixed $entry Leftover tag string or late-callback row.
     * @return string
     */
    function ncs_cart_legacy_shortcode_tag($entry)
    {
        $normalized = ncs_cart_normalize_shortcode_map_entry($entry);

        return $normalized ? $normalized['legacy'] : '';
    }
}

if (! function_exists('ncs_cart_legacy_shortcodes_registered')) {
    /**
     * @return array<string, bool>
     */
    function &ncs_cart_legacy_shortcodes_registered()
    {
        static $registered = [];

        return $registered;
    }
}

if (! function_exists('ppcart_register_legacy_shortcodes')) {
    /**
     * Dual-register leftover shortcode tags onto the same callbacks.
     *
     * Copies $shortcode_tags[$canonical] when first-party already registered
     * the canonical tag. Late Pro tag uses an explicit function callback so
     * leftover can register before checkout1.php.
     *
     * @return void
     */
    function ppcart_register_legacy_shortcodes()
    {
        global $shortcode_tags;

        $registered = &ncs_cart_legacy_shortcodes_registered();

        foreach (ncs_cart_get_legacy_shortcode_map() as $canonical => $entry) {
            if (! is_string($canonical) || '' === $canonical) {
                continue;
            }

            $normalized = ncs_cart_normalize_shortcode_map_entry($entry);

            if (null === $normalized) {
                continue;
            }

            $legacy = $normalized['legacy'];

            if (isset($registered[ $legacy ])) {
                continue;
            }

            $callback = null;

            if (isset($shortcode_tags[ $canonical ])) {
                $callback = $shortcode_tags[ $canonical ];
            } elseif ('' !== $normalized['callback'] && function_exists($normalized['callback'])) {
                $callback = $normalized['callback'];
            }

            if (null === $callback) {
                continue;
            }

            add_shortcode($legacy, $callback);
            $registered[ $legacy ] = true;
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_shortcodes')) {
    /**
     * Remove leftover shortcode tags. Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_shortcodes()
    {
        global $shortcode_tags;

        $registered = &ncs_cart_legacy_shortcodes_registered();

        foreach (array_keys($registered) as $legacy) {
            if (function_exists('remove_shortcode')) {
                remove_shortcode($legacy);
            } elseif (isset($shortcode_tags[ $legacy ])) {
                unset($shortcode_tags[ $legacy ]);
            }
        }

        $registered = [];
    }
}
