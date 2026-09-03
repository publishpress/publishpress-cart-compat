<?php

if (! defined('ABSPATH')) {
    exit;
}

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

        $map_file = __DIR__ . '/maps/hooks.php';

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
        if (function_exists('ppcart_cpt_slug_migration_maps')) {
            $pairs = [];
            foreach (ppcart_cpt_slug_migration_maps()['post_types'] as $pair) {
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
            return $config['override'][ $legacy ];
        }

        if (ncs_cart_hook_name_is_excluded($legacy)) {
            return null;
        }

        if (isset($config['cron'][ $legacy ])) {
            return $config['cron'][ $legacy ];
        }

        if (0 === strpos($legacy, '_sc_')) {
            return '_ppcart_' . substr($legacy, 4);
        }

        if (0 === strpos($legacy, 'sc_')) {
            return 'ppcart_' . substr($legacy, 3);
        }

        if (0 === strpos($legacy, 'ncs_')) {
            return 'ppcart_' . substr($legacy, 4);
        }

        if (0 === strpos($legacy, 'nsc_')) {
            return 'ppcart_' . substr($legacy, 4);
        }

        if (0 === strpos($legacy, 'studiocart_')) {
            return 'ppcart_' . substr($legacy, 11);
        }

        if (0 === strpos($legacy, 'ncs-cart-')) {
            return 'ppcart-' . substr($legacy, 9);
        }

        $derived = ncs_cart_rewrite_derived_cpt_hook($legacy, true);
        if (null !== $derived) {
            return $derived;
        }

        return null;
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
                'ncs_' . $suffix,
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

if (! function_exists('ncs_cart_hook_bridge_state')) {
    /**
     * @return array{depth: int}
     */
    function &ncs_cart_hook_bridge_state()
    {
        static $state = [ 'depth' => 0 ];

        return $state;
    }
}

if (! function_exists('ncs_cart_hook_bridge_depth')) {
    /**
     * @return int
     */
    function ncs_cart_hook_bridge_depth()
    {
        return ncs_cart_hook_bridge_state()['depth'];
    }
}

if (! function_exists('ncs_cart_hook_bridge_enter')) {
    /**
     * @return void
     */
    function ncs_cart_hook_bridge_enter()
    {
        ++ncs_cart_hook_bridge_state()['depth'];
    }
}

if (! function_exists('ncs_cart_hook_bridge_leave')) {
    /**
     * @return void
     */
    function ncs_cart_hook_bridge_leave()
    {
        $state = &ncs_cart_hook_bridge_state();

        if ($state['depth'] > 0) {
            --$state['depth'];
        }
    }
}

if (! function_exists('ncs_cart_hook_bridge_is_active')) {
    /**
     * @return bool
     */
    function ncs_cart_hook_bridge_is_active()
    {
        return ncs_cart_hook_bridge_depth() > 0;
    }
}

if (! function_exists('ncs_cart_hook_is_canonical_name')) {
    /**
     * @param string $hook Hook name.
     * @return bool
     */
    function ncs_cart_hook_is_canonical_name($hook)
    {
        return is_string($hook)
            && (
                0 === strpos($hook, 'ppcart_')
                || 0 === strpos($hook, '_ppcart_')
                || 0 === strpos($hook, 'ppcart-')
            );
    }
}

if (! function_exists('ncs_cart_hook_is_legacy_name')) {
    /**
     * @param string $hook Hook name.
     * @return bool
     */
    function ncs_cart_hook_is_legacy_name($hook)
    {
        if (! is_string($hook) || '' === $hook || ncs_cart_hook_name_is_excluded($hook)) {
            return false;
        }

        return null !== ncs_cart_legacy_to_canonical_hook_name($hook);
    }
}

if (! function_exists('ncs_cart_hook_bridge_compat_enabled')) {
    /**
     * @return bool
     */
    function ncs_cart_hook_bridge_compat_enabled()
    {
        return class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            && PPCartComp_Studiocart_Compatibility_Mode::is_enabled();
    }
}

if (! function_exists('ppcart_hook_bridge_forward_to_canonical')) {
    /**
     * Cron drain: legacy scheduled hook name forwards to canonical listeners.
     *
     * @param string $legacy Legacy hook name.
     * @return void
     */
    function ppcart_hook_bridge_forward_to_canonical($legacy)
    {
        if (ncs_cart_hook_bridge_is_active()) {
            return;
        }

        $canonical = ncs_cart_legacy_to_canonical_hook_name($legacy);

        if (null === $canonical || $canonical === $legacy) {
            return;
        }

        ncs_cart_hook_bridge_enter();
        do_action_ref_array($canonical, array_slice(func_get_args(), 1));
        ncs_cart_hook_bridge_leave();
    }
}

if (! function_exists('ncs_cart_hook_bridge_is_doing_action')) {
    /**
     * Whether WordPress is executing a hook via do_action (not apply_filters).
     *
     * @param string $hook Hook name.
     * @return bool
     */
    function ncs_cart_hook_bridge_is_doing_action($hook)
    {
        global $wp_filter;

        if (! is_string($hook) || '' === $hook || ! isset($wp_filter[ $hook ])) {
            return false;
        }

        $hook_object = $wp_filter[ $hook ];

        if (! $hook_object instanceof WP_Hook) {
            return false;
        }

        static $reflection = null;

        if (null === $reflection) {
            $reflection = new \ReflectionProperty(WP_Hook::class, 'doing_action');
            $reflection->setAccessible(true);
        }

        return (bool) $reflection->getValue($hook_object);
    }
}

if (! function_exists('ppcart_hook_bridge_fire_legacy_from_canonical')) {
    /**
     * @param mixed ...$args Hook arguments.
     * @return mixed First filter value when invoked during a filter.
     */
    function ppcart_hook_bridge_fire_legacy_from_canonical(...$args)
    {
        $canonical = array_shift($args);
        $value = $args[0] ?? null;

        if (
            ncs_cart_hook_bridge_is_active()
            || ! ncs_cart_hook_bridge_compat_enabled()
            || ! is_string($canonical)
            || '' === $canonical
            || ! ncs_cart_hook_bridge_is_doing_action($canonical)
        ) {
            return $value;
        }

        $legacy_names = ncs_cart_canonical_to_legacy_hook_names($canonical);

        if ([] === $legacy_names) {
            return;
        }

        ncs_cart_hook_bridge_enter();

        foreach ($legacy_names as $legacy) {
            if ($legacy === $canonical) {
                continue;
            }

            do_action_ref_array($legacy, $args);
        }

        ncs_cart_hook_bridge_leave();

        return $value;
    }
}

if (! function_exists('ppcart_hook_bridge_filter_legacy_from_canonical')) {
    /**
     * @param mixed $value Filter value.
     * @return mixed
     */
    function ppcart_hook_bridge_filter_legacy_from_canonical($value)
    {
        $args = func_get_args();
        array_shift($args);
        $canonical = array_shift($args);

        if (
            ncs_cart_hook_bridge_is_active()
            || ! ncs_cart_hook_bridge_compat_enabled()
            || ! is_string($canonical)
            || '' === $canonical
            || ncs_cart_hook_bridge_is_doing_action($canonical)
        ) {
            return $value;
        }

        $legacy_names = ncs_cart_canonical_to_legacy_hook_names($canonical);

        if ([] === $legacy_names) {
            return $value;
        }

        ncs_cart_hook_bridge_enter();

        foreach ($legacy_names as $legacy) {
            if ($legacy === $canonical) {
                continue;
            }

            $value = apply_filters_ref_array($legacy, array_merge([ $value ], $args));
        }

        ncs_cart_hook_bridge_leave();

        return $value;
    }
}

if (! function_exists('ppcart_hook_bridge_fire_canonical_from_legacy')) {
    /**
     * @param mixed ...$args Hook arguments.
     * @return mixed First filter value when invoked during a filter.
     */
    function ppcart_hook_bridge_fire_canonical_from_legacy(...$args)
    {
        $legacy = array_shift($args);
        $value = $args[0] ?? null;

        if (
            ncs_cart_hook_bridge_is_active()
            || ! ncs_cart_hook_bridge_compat_enabled()
            || ! is_string($legacy)
            || '' === $legacy
            || ! ncs_cart_hook_bridge_is_doing_action($legacy)
        ) {
            return $value;
        }

        $canonical = ncs_cart_legacy_to_canonical_hook_name($legacy);

        if (null === $canonical || $canonical === $legacy) {
            return;
        }

        ncs_cart_hook_bridge_enter();
        do_action_ref_array($canonical, $args);
        ncs_cart_hook_bridge_leave();

        return $value;
    }
}

if (! function_exists('ppcart_hook_bridge_filter_canonical_from_legacy')) {
    /**
     * @param mixed $value Filter value.
     * @return mixed
     */
    function ppcart_hook_bridge_filter_canonical_from_legacy($value)
    {
        $args = func_get_args();
        array_shift($args);
        $legacy = array_shift($args);

        if (
            ncs_cart_hook_bridge_is_active()
            || ! ncs_cart_hook_bridge_compat_enabled()
            || ! is_string($legacy)
            || '' === $legacy
            || ncs_cart_hook_bridge_is_doing_action($legacy)
        ) {
            return $value;
        }

        $canonical = ncs_cart_legacy_to_canonical_hook_name($legacy);

        if (null === $canonical || $canonical === $legacy) {
            return $value;
        }

        ncs_cart_hook_bridge_enter();
        $value = apply_filters_ref_array($canonical, array_merge([ $value ], $args));
        ncs_cart_hook_bridge_leave();

        return $value;
    }
}

if (! function_exists('ppcart_hook_bridge_maybe_attach')) {
    /**
     * Lazily attach canonical <=> legacy bridge callbacks for one hook tag.
     *
     * @param string $hook Hook name currently firing.
     * @return void
     */
    function ppcart_hook_bridge_maybe_attach($hook)
    {
        $attached = &ncs_cart_hook_bridge_attached_hooks();

        if (! is_string($hook) || '' === $hook || isset($attached[ $hook ])) {
            return;
        }

        $attached[ $hook ] = true;

        if (ncs_cart_hook_is_canonical_name($hook)) {
            $priority = PHP_INT_MAX - 1;

            add_filter(
                $hook,
                static function ($value, ...$args) use ($hook) {
                    return ppcart_hook_bridge_filter_legacy_from_canonical($value, $hook, ...$args);
                },
                $priority,
                99
            );

            add_action(
                $hook,
                static function (...$args) use ($hook) {
                    return ppcart_hook_bridge_fire_legacy_from_canonical($hook, ...$args);
                },
                $priority,
                99
            );

            return;
        }

        if (! ncs_cart_hook_is_legacy_name($hook)) {
            return;
        }

        add_filter(
            $hook,
            static function ($value, ...$args) use ($hook) {
                return ppcart_hook_bridge_filter_canonical_from_legacy($value, $hook, ...$args);
            },
            0,
            99
        );

        add_action(
            $hook,
            static function (...$args) use ($hook) {
                return ppcart_hook_bridge_fire_canonical_from_legacy($hook, ...$args);
            },
            0,
            99
        );
    }
}

if (! function_exists('ncs_cart_hook_bridge_is_candidate')) {
    /**
     * Cheap prefix gate before compat checks on the `all` hook.
     *
     * @param string $hook Hook name.
     * @return bool
     */
    function ncs_cart_hook_bridge_is_candidate($hook)
    {
        if (! is_string($hook) || '' === $hook) {
            return false;
        }

        $prefixes = [
            'ppcart_',
            '_ppcart_',
            'ppcart-',
            'sc_',
            '_sc_',
            'ncs_',
            'nsc_',
            'studiocart_',
            'ncs-cart-',
        ];

        foreach ($prefixes as $prefix) {
            if (0 === strpos($hook, $prefix)) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('ppcart_hook_bridge_on_all')) {
    /**
     * @param string $hook Hook name.
     * @return void
     */
    function ppcart_hook_bridge_on_all($hook)
    {
        if (ncs_cart_hook_bridge_is_active() || ! ncs_cart_hook_bridge_is_candidate($hook)) {
            return;
        }

        if (! ncs_cart_hook_bridge_compat_enabled()) {
            return;
        }

        ppcart_hook_bridge_maybe_attach($hook);
    }
}

if (! function_exists('ppcart_prepare_registered_hook_bridges')) {
    /**
     * Eagerly attach bridge pairs for hooks that already have listeners.
     *
     * The `all` hook cannot affect the first filter call when the source hook
     * had no callbacks before apply_filters() began. Preparing both names here
     * keeps first-call filter values bridgeable in either direction.
     *
     * @return void
     */
    function ppcart_prepare_registered_hook_bridges()
    {
        global $wp_filter;

        if (! is_array($wp_filter)) {
            return;
        }

        foreach (array_keys($wp_filter) as $hook) {
            if (! ncs_cart_hook_bridge_is_candidate($hook)) {
                continue;
            }

            ppcart_hook_bridge_maybe_attach($hook);

            if (ncs_cart_hook_is_canonical_name($hook)) {
                foreach (ncs_cart_canonical_to_legacy_hook_names($hook) as $legacy) {
                    ppcart_hook_bridge_maybe_attach($legacy);
                }

                continue;
            }

            $canonical = ncs_cart_legacy_to_canonical_hook_name($hook);

            if (null !== $canonical) {
                ppcart_hook_bridge_maybe_attach($canonical);
            }
        }
    }
}

if (! function_exists('ncs_cart_hook_bridge_attached_hooks')) {
    /**
     * @return array<string, bool>
     */
    function &ncs_cart_hook_bridge_attached_hooks()
    {
        static $attached = [];

        return $attached;
    }
}

if (! function_exists('ncs_cart_hook_bridge_cron_registered_hooks')) {
    /**
     * @return array<string, bool>
     */
    function &ncs_cart_hook_bridge_cron_registered_hooks()
    {
        static $registered = [];

        return $registered;
    }
}

if (! function_exists('ncs_cart_hook_bridge_legacy_all_registered')) {
    /**
     * @return bool
     */
    function &ncs_cart_hook_bridge_legacy_all_registered()
    {
        static $registered = false;

        return $registered;
    }
}

if (! function_exists('ncs_cart_reset_hook_bridge_lazy_attach_state')) {
    /**
     * Clear lazy-attach bookkeeping (integration tests).
     *
     * @return void
     */
    function ncs_cart_reset_hook_bridge_lazy_attach_state()
    {
        $attached = &ncs_cart_hook_bridge_attached_hooks();
        $attached = [];

        $registered = &ncs_cart_hook_bridge_cron_registered_hooks();
        $registered = [];

        $legacy_all_registered = &ncs_cart_hook_bridge_legacy_all_registered();
        $legacy_all_registered = false;

        if (function_exists('remove_action')) {
            remove_action('all', 'ppcart_hook_bridge_on_all', 0);
        }
    }
}

if (! function_exists('ppcart_register_cron_hook_bridges')) {
    /**
     * Always-on cron drain: legacy scheduled hook names forward to canonical.
     *
     * @return void
     */
    function ppcart_register_cron_hook_bridges()
    {
        $registered = &ncs_cart_hook_bridge_cron_registered_hooks();
        $config = ncs_cart_get_legacy_hook_map_config();

        foreach ($config['cron'] as $legacy => $canonical) {
            if (! is_string($legacy) || '' === $legacy || ! is_string($canonical) || '' === $canonical) {
                continue;
            }

            if (isset($registered[ $legacy ])) {
                continue;
            }

            add_action($legacy, static function () use ($legacy) {
                ppcart_hook_bridge_forward_to_canonical($legacy, ...func_get_args());
            }, 10, 99);

            $registered[ $legacy ] = true;
        }
    }
}

if (! function_exists('ppcart_register_legacy_hook_bridges')) {
    /**
     * Compat-gated legacy <=> canonical hook bridges.
     *
     * @return void
     */
    function ppcart_register_legacy_hook_bridges()
    {
        $registered = &ncs_cart_hook_bridge_legacy_all_registered();

        if (! $registered) {
            add_action('all', 'ppcart_hook_bridge_on_all', 0, 1);
            $registered = true;
        }

        ppcart_prepare_registered_hook_bridges();
    }
}
