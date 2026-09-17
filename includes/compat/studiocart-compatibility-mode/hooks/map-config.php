<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart hook bridge helpers — map config.
 * Loaded from hooks/index.php.
 */

if (! function_exists('ncs_cart_get_legacy_hook_map_config')) {
    /**
     * @return array{
     *     exclude: array<int, string>,
     *     override: array<string, string>,
     *     extra: array<string, array<int, string>>,
     *     cron: array<string, string>
     * }
     */
    function ncs_cart_get_legacy_hook_map_config()
    {
        static $config = null;

        if (null !== $config) {
            return $config;
        }

        $config = [
            'exclude'  => [],
            'override' => [],
            'extra'    => [],
            'cron'     => [],
        ];

        $map_file = dirname(__DIR__) . '/maps/hooks.php';

        if (! is_readable($map_file)) {
            return $config;
        }

        $entries = include $map_file;

        if (! is_array($entries)) {
            return $config;
        }

        foreach ([ 'exclude', 'override', 'extra', 'cron' ] as $key) {
            if (! isset($entries[ $key ]) || ! is_array($entries[ $key ])) {
                continue;
            }

            if ('exclude' === $key) {
                $config['exclude'] = array_values(array_filter($entries['exclude'], 'is_string'));
                continue;
            }

            if ('extra' === $key) {
                foreach ($entries['extra'] as $canonical => $legacy_names) {
                    if (! is_string($canonical) || '' === $canonical || ! is_array($legacy_names)) {
                        continue;
                    }

                    $config['extra'][ $canonical ] = array_values(array_filter($legacy_names, 'is_string'));
                }
                continue;
            }

            foreach ($entries[ $key ] as $legacy => $canonical) {
                if (! is_string($legacy) || '' === $legacy || ! is_string($canonical) || '' === $canonical) {
                    continue;
                }

                $config[ $key ][ $legacy ] = $canonical;
            }
        }

        return $config;
    }
}
if (! function_exists('ncs_cart_hook_name_is_excluded')) {
    /**
     * @param string $hook Hook name.
     * @return bool
     */
    function ncs_cart_hook_name_is_excluded($hook)
    {
        if (! is_string($hook) || '' === $hook) {
            return true;
        }

        $config = ncs_cart_get_legacy_hook_map_config();

        foreach ($config['exclude'] as $prefix) {
            if (0 === strpos($hook, $prefix)) {
                return true;
            }
        }

        return false;
    }
}
if (! function_exists('ncs_cart_rewrite_derived_cpt_hook')) {
    /**
     * Map WordPress hooks that embed Free CPT slugs (save_post_sc_order, manage_sc_product_posts_columns, …).
     *
     * @param string $hook         Hook name.
     * @param bool   $to_canonical True to rewrite leftover → canonical.
     * @return string|null
     */
    function ncs_cart_rewrite_derived_cpt_hook($hook, $to_canonical)
    {
        if (! is_string($hook) || '' === $hook) {
            return null;
        }

        $prefixes = [
            'save_post_',
            'manage_edit-',
            'manage_',
            'bulk_actions-edit-',
            'handle_bulk_actions-edit-',
            'views_edit-',
        ];
        $matched = false;
        foreach ($prefixes as $prefix) {
            if (0 === strpos($hook, $prefix)) {
                $matched = true;
                break;
            }
        }
        if (! $matched) {
            return null;
        }

        $pairs = [
            [ 'sc_product', 'ppcart_product' ],
            [ 'sc_order', 'ppcart_order' ],
            [ 'sc_subscription', 'ppcart_subscription' ],
        ];
        if (function_exists('ppcart_cpt_slug_migration_all_post_type_pairs')) {
            $pairs = [];
            foreach (ppcart_cpt_slug_migration_all_post_type_pairs() as $pair) {
                $pairs[] = [ $pair['legacy'], $pair['canonical'] ];
            }
        }

        foreach ($pairs as $pair) {
            $from = $to_canonical ? $pair[0] : $pair[1];
            $to   = $to_canonical ? $pair[1] : $pair[0];
            if (false !== strpos($hook, $from)) {
                $rewritten = str_replace($from, $to, $hook);

                return $rewritten !== $hook ? $rewritten : null;
            }
        }

        return null;
    }
}
if (! function_exists('ncs_cart_legacy_to_canonical_hook_name')) {
    /**
     * @param string $legacy Legacy hook name.
     * @return string|null Canonical hook name, or null when not bridged.
     */
    function ncs_cart_legacy_to_canonical_hook_name($legacy)
    {
        if (! is_string($legacy) || '' === $legacy) {
            return null;
        }

        $config = ncs_cart_get_legacy_hook_map_config();

        if (isset($config['override'][ $legacy ])) {
            return ncs_cart_collapse_doubled_cart_hook_prefix($config['override'][ $legacy ]);
        }

        if (ncs_cart_hook_name_is_excluded($legacy)) {
            return null;
        }

        if (isset($config['cron'][ $legacy ])) {
            return ncs_cart_collapse_doubled_cart_hook_prefix($config['cron'][ $legacy ]);
        }

        if (0 === strpos($legacy, '_sc_')) {
            return '_ppcart_' . substr($legacy, 4);
        }

        if (0 === strpos($legacy, 'sc_')) {
            return ncs_cart_collapse_doubled_cart_hook_prefix('ppcart_' . substr($legacy, 3));
        }

        if (0 === strpos($legacy, 'ncs_')) {
            return ncs_cart_collapse_doubled_cart_hook_prefix('ppcart_' . substr($legacy, 4));
        }

        if (0 === strpos($legacy, 'nsc_')) {
            return ncs_cart_collapse_doubled_cart_hook_prefix('ppcart_' . substr($legacy, 4));
        }

        if (0 === strpos($legacy, 'studiocart_')) {
            return ncs_cart_collapse_doubled_cart_hook_prefix('ppcart_' . substr($legacy, 11));
        }

        if (0 === strpos($legacy, 'ncs-cart-')) {
            return 'ppcart-' . substr($legacy, 9);
        }

        $derived = ncs_cart_rewrite_derived_cpt_hook($legacy, true);
        if (null !== $derived) {
            return ncs_cart_collapse_doubled_cart_hook_prefix($derived);
        }

        return null;
    }
}
if (! function_exists('ncs_cart_collapse_doubled_cart_hook_prefix')) {
    /**
     * sc_cart_* / ncs_cart_* mechanically become ppcart_cart_*; canonical drops the extra cart_.
     *
     * @param string $canonical Mapped hook name.
     * @return string
     */
    function ncs_cart_collapse_doubled_cart_hook_prefix($canonical)
    {
        if (! is_string($canonical) || 0 !== strpos($canonical, 'ppcart_cart_')) {
            return $canonical;
        }

        return 'ppcart_' . substr($canonical, 12);
    }
}
if (! function_exists('ncs_cart_canonical_to_legacy_hook_names')) {
    /**
     * @param string $canonical Canonical hook name.
     * @return array<int, string>
     */
    function ncs_cart_canonical_to_legacy_hook_names($canonical)
    {
        if (! is_string($canonical) || '' === $canonical) {
            return [];
        }

        $config = ncs_cart_get_legacy_hook_map_config();
        $legacy_names = [];

        foreach ($config['override'] as $legacy => $mapped_canonical) {
            if ($mapped_canonical === $canonical && ! ncs_cart_hook_name_is_excluded($legacy)) {
                $legacy_names[] = $legacy;
            }
        }

        foreach ($config['cron'] as $legacy => $mapped_canonical) {
            if ($mapped_canonical === $canonical && ! ncs_cart_hook_name_is_excluded($legacy)) {
                $legacy_names[] = $legacy;
            }
        }

        if (0 === strpos($canonical, '_ppcart_')) {
            $legacy = '_sc_' . substr($canonical, 8);

            if (! ncs_cart_hook_name_is_excluded($legacy)) {
                $legacy_names[] = $legacy;
            }
        } elseif (0 === strpos($canonical, 'ppcart_')) {
            $suffix = substr($canonical, 7);
            $candidates = [
                'sc_' . $suffix,
                'sc_cart_' . $suffix,
                'ncs_' . $suffix,
                'ncs_cart_' . $suffix,
                'nsc_' . $suffix,
                'studiocart_' . $suffix,
            ];

            foreach ($candidates as $legacy) {
                if (! ncs_cart_hook_name_is_excluded($legacy)) {
                    $legacy_names[] = $legacy;
                }
            }
        } elseif (0 === strpos($canonical, 'ppcart-')) {
            $legacy_names[] = 'ncs-cart-' . substr($canonical, 7);
        }

        if (isset($config['extra'][ $canonical ]) && is_array($config['extra'][ $canonical ])) {
            foreach ($config['extra'][ $canonical ] as $legacy) {
                if (is_string($legacy) && '' !== $legacy && ! ncs_cart_hook_name_is_excluded($legacy)) {
                    $legacy_names[] = $legacy;
                }
            }
        }

        $derived_legacy = ncs_cart_rewrite_derived_cpt_hook($canonical, false);
        if (null !== $derived_legacy && ! ncs_cart_hook_name_is_excluded($derived_legacy)) {
            $legacy_names[] = $derived_legacy;
        }

        return array_values(array_unique($legacy_names));
    }
}
