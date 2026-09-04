<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_option_map_config')) {
    /**
     * @return array{exclude: array<int, string>, override: array<string, string>}
     */
    function ncs_cart_get_legacy_option_map_config()
    {
        static $config = null;

        if (null !== $config) {
            return $config;
        }

        $config = [
            'exclude'  => [],
            'override' => [],
        ];

        $map_file = __DIR__ . '/maps/options.php';

        if (! is_readable($map_file)) {
            return $config;
        }

        $entries = include $map_file;

        if (! is_array($entries)) {
            return $config;
        }

        if (isset($entries['exclude']) && is_array($entries['exclude'])) {
            $config['exclude'] = array_values(array_filter($entries['exclude'], 'is_string'));
        }

        if (isset($entries['override']) && is_array($entries['override'])) {
            foreach ($entries['override'] as $legacy => $canonical) {
                if (! is_string($legacy) || '' === $legacy || ! is_string($canonical) || '' === $canonical) {
                    continue;
                }

                $config['override'][ $legacy ] = $canonical;
            }
        }

        return $config;
    }
}

if (! function_exists('ncs_cart_legacy_to_canonical_option_name')) {
    /**
     * @param string $legacy Legacy option name.
     * @return string|null Canonical option name, or null when not migrated.
     */
    function ncs_cart_legacy_to_canonical_option_name($legacy)
    {
        if (! is_string($legacy) || '' === $legacy) {
            return null;
        }

        $config = ncs_cart_get_legacy_option_map_config();

        if (in_array($legacy, $config['exclude'], true)) {
            return null;
        }

        if (isset($config['override'][ $legacy ])) {
            return $config['override'][ $legacy ];
        }

        // Timeout prefix is longer than `_transient_`; unwrap it first.
        if (0 === strpos($legacy, '_transient_timeout_')) {
            $inner = substr($legacy, strlen('_transient_timeout_'));
            $canonical_inner = ncs_cart_legacy_to_canonical_option_name($inner);

            return null === $canonical_inner ? null : '_transient_timeout_' . $canonical_inner;
        }

        if (0 === strpos($legacy, '_transient_')) {
            $inner = substr($legacy, strlen('_transient_'));
            $canonical_inner = ncs_cart_legacy_to_canonical_option_name($inner);

            return null === $canonical_inner ? null : '_transient_' . $canonical_inner;
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

        if ('current_schedule_val' === $legacy) {
            return 'ppcart_current_schedule_val';
        }

        return null;
    }
}

if (! function_exists('ncs_cart_canonical_to_legacy_option_name')) {
    /**
     * @param string $canonical Canonical option name.
     * @return string|null Legacy storage option name, or null when not mapped.
     */
    function ncs_cart_canonical_to_legacy_option_name($canonical)
    {
        if (! is_string($canonical) || '' === $canonical) {
            return null;
        }

        $config = ncs_cart_get_legacy_option_map_config();

        foreach ($config['override'] as $legacy => $mapped_canonical) {
            if ($mapped_canonical === $canonical) {
                return $legacy;
            }
        }

        if (0 === strpos($canonical, '_transient_timeout_')) {
            $inner = substr($canonical, strlen('_transient_timeout_'));
            $legacy_inner = ncs_cart_canonical_to_legacy_option_name($inner);

            return null === $legacy_inner ? null : '_transient_timeout_' . $legacy_inner;
        }

        if (0 === strpos($canonical, '_transient_')) {
            $inner = substr($canonical, strlen('_transient_'));
            $legacy_inner = ncs_cart_canonical_to_legacy_option_name($inner);

            return null === $legacy_inner ? null : '_transient_' . $legacy_inner;
        }

        if (0 === strpos($canonical, '_ppcart_')) {
            $legacy = '_sc_' . substr($canonical, 8);

            if (in_array($legacy, $config['exclude'], true)) {
                return null;
            }

            return $legacy;
        }

        if (0 === strpos($canonical, 'ppcart_')) {
            $suffix = substr($canonical, 7);

            if ('current_schedule_val' === $suffix) {
                return 'current_schedule_val';
            }

            $legacy_sc = 'sc_' . $suffix;

            if (in_array($legacy_sc, $config['exclude'], true)) {
                return null;
            }

            return $legacy_sc;
        }

        return null;
    }
}

if (! function_exists('ncs_cart_option_bridge_state')) {
    /**
     * @return array{depth: int}
     */
    function &ncs_cart_option_bridge_state()
    {
        static $state = [ 'depth' => 0 ];

        return $state;
    }
}

if (! function_exists('ncs_cart_option_bridge_depth')) {
    /**
     * @return int
     */
    function ncs_cart_option_bridge_depth()
    {
        return ncs_cart_option_bridge_state()['depth'];
    }
}

if (! function_exists('ncs_cart_option_bridge_enter')) {
    /**
     * @return void
     */
    function ncs_cart_option_bridge_enter()
    {
        ++ncs_cart_option_bridge_state()['depth'];
    }
}

if (! function_exists('ncs_cart_option_bridge_leave')) {
    /**
     * @return void
     */
    function ncs_cart_option_bridge_leave()
    {
        $state = &ncs_cart_option_bridge_state();

        if ($state['depth'] > 0) {
            --$state['depth'];
        }
    }
}

if (! function_exists('ncs_cart_option_bridge_is_active')) {
    /**
     * @return bool
     */
    function ncs_cart_option_bridge_is_active()
    {
        return ncs_cart_option_bridge_depth() > 0;
    }
}

if (! function_exists('ppcart_read_option_from_db')) {
    /**
     * @param string $option Option name.
     * @return mixed False when missing.
     */
    function ppcart_read_option_from_db($option)
    {
        global $wpdb;

        if (! isset($wpdb->options)) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bridge reads the legacy storage row without filter recursion.
        $value = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
                $option
            )
        );

        if (null === $value) {
            return false;
        }

        return maybe_unserialize($value);
    }
}

if (! function_exists('ppcart_write_option_to_db')) {
    /**
     * @param string $option  Option name.
     * @param mixed  $value   Option value.
     * @return bool
     */
    function ppcart_write_option_to_db($option, $value)
    {
        global $wpdb;

        if (! isset($wpdb->options)) {
            return false;
        }

        $serialized = maybe_serialize($value);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bridge writes the legacy storage row without filter recursion.
        $updated = $wpdb->update(
            $wpdb->options,
            [ 'option_value' => $serialized ],
            [ 'option_name' => $option ],
            [ '%s' ],
            [ '%s' ]
        );

        if (false === $updated) {
            return false;
        }

        if (0 === $updated) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Distinguishes an unchanged row from a missing row before inserting.
            $option_id = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s LIMIT 1",
                    $option
                )
            );

            if (null === $option_id) {
                // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Insert legacy row when it does not exist yet.
                $inserted = $wpdb->insert(
                    $wpdb->options,
                    [
                        'option_name'  => $option,
                        'option_value' => $serialized,
                        'autoload'     => 'no',
                    ],
                    [ '%s', '%s', '%s' ]
                );

                if (false === $inserted) {
                    return false;
                }
            }
        }

        wp_cache_delete($option, 'options');
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');

        return true;
    }
}

if (! function_exists('ppcart_delete_option_from_db')) {
    /**
     * @param string $option Option name.
     * @return bool
     */
    function ppcart_delete_option_from_db($option)
    {
        global $wpdb;

        if (! isset($wpdb->options)) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Bridge deletes the legacy storage row without filter recursion.
        return (bool) $wpdb->delete($wpdb->options, [ 'option_name' => $option ], [ '%s' ]);
    }
}

if (! function_exists('ppcart_register_option_storage_bridges')) {
    /**
     * Always-on leftover option read-through. Does not copy or delete rows.
     *
     * Writes use canonical wp_options rows. Key renames are Data migration only.
     *
     * @return void
     */
    function ppcart_register_option_storage_bridges()
    {
        static $registered = false;

        if ($registered) {
            return;
        }

        add_filter('pre_option', 'ppcart_option_storage_bridge_pre_option', 5, 3);

        $registered = true;
    }
}

if (! function_exists('ppcart_register_legacy_option_api_bridges')) {
    /**
     * Compat-gated legacy => canonical API bridges.
     *
     * @return void
     */
    function ppcart_register_legacy_option_api_bridges()
    {
        static $registered = false;

        if (function_exists('has_filter')) {
            if (false !== has_filter('pre_option', 'ppcart_option_api_bridge_pre_option')) {
                $registered = true;
                return;
            }
        } elseif ($registered) {
            return;
        }

        add_filter('pre_option', 'ppcart_option_api_bridge_pre_option', 10, 3);
        add_filter('pre_update_option', 'ppcart_option_api_bridge_pre_update_option', 10, 3);
        add_filter('pre_add_option', 'ppcart_option_api_bridge_pre_add_option', 10, 3);
        add_filter('pre_delete_option', 'ppcart_option_api_bridge_pre_delete_option', 10, 2);

        $registered = true;
    }
}

if (! function_exists('ppcart_option_storage_bridge_pre_option')) {
    /**
     * Read a leftover row for a canonical key without copying or deleting it.
     *
     * @param mixed  $pre_option Pre-filtered value.
     * @param string $option     Option name.
     * @param mixed  $default    Default value.
     * @return mixed
     */
    function ppcart_option_storage_bridge_pre_option($pre_option, $option, $default)
    {
        if (false !== $pre_option || ncs_cart_option_bridge_is_active()) {
            return $pre_option;
        }

        $legacy = ncs_cart_canonical_to_legacy_option_name($option);

        if (null === $legacy || $legacy === $option) {
            return $pre_option;
        }

        if (function_exists('ppcart_get_option_storage_row') && null !== ppcart_get_option_storage_row($option)) {
            return $pre_option;
        }

        ncs_cart_option_bridge_enter();
        $value = ppcart_read_option_from_db($legacy);
        ncs_cart_option_bridge_leave();

        return $value;
    }
}

if (! function_exists('ppcart_option_api_bridge_pre_option')) {
    /**
     * @param mixed  $pre_option Pre-filtered value.
     * @param string $option     Option name.
     * @param mixed  $default    Default value.
     * @return mixed
     */
    function ppcart_option_api_bridge_pre_option($pre_option, $option, $default)
    {
        if (false !== $pre_option || ncs_cart_option_bridge_is_active()) {
            return $pre_option;
        }

        $canonical = ncs_cart_legacy_to_canonical_option_name($option);

        if (null === $canonical || $canonical === $option) {
            return $pre_option;
        }

        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return $pre_option;
        }

        ncs_cart_option_bridge_enter();
        $value = get_option($canonical, $default);
        ncs_cart_option_bridge_leave();

        return $value;
    }
}

if (! function_exists('ppcart_option_api_bridge_pre_update_option')) {
    /**
     * @param mixed  $value     New value.
     * @param string $option    Option name.
     * @param mixed  $old_value Old value.
     * @return mixed
     */
    function ppcart_option_api_bridge_pre_update_option($value, $option, $old_value)
    {
        if (ncs_cart_option_bridge_is_active()) {
            return $value;
        }

        $canonical = ncs_cart_legacy_to_canonical_option_name($option);

        if (null === $canonical || $canonical === $option) {
            return $value;
        }

        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return $value;
        }

        ncs_cart_option_bridge_enter();
        update_option($canonical, $value);
        ncs_cart_option_bridge_leave();

        return $old_value;
    }
}

if (! function_exists('ppcart_option_api_bridge_pre_add_option')) {
    /**
     * @param mixed  $value    Value to add.
     * @param string $option   Option name.
     * @param string $autoload Autoload flag.
     * @return bool|null
     */
    function ppcart_option_api_bridge_pre_add_option($value, $option, $autoload)
    {
        if (ncs_cart_option_bridge_is_active()) {
            return null;
        }

        $canonical = ncs_cart_legacy_to_canonical_option_name($option);

        if (null === $canonical || $canonical === $option) {
            return null;
        }

        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return null;
        }

        ncs_cart_option_bridge_enter();
        add_option($canonical, $value, '', $autoload);
        ncs_cart_option_bridge_leave();

        return true;
    }
}

if (! function_exists('ppcart_option_api_bridge_pre_delete_option')) {
    /**
     * @param bool   $delete Whether to delete.
     * @param string $option Option name.
     * @return bool
     */
    function ppcart_option_api_bridge_pre_delete_option($delete, $option)
    {
        if (ncs_cart_option_bridge_is_active()) {
            return $delete;
        }

        $canonical = ncs_cart_legacy_to_canonical_option_name($option);

        if (null === $canonical || $canonical === $option) {
            return $delete;
        }

        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return $delete;
        }

        ncs_cart_option_bridge_enter();
        delete_option($canonical);
        ncs_cart_option_bridge_leave();

        return true;
    }
}
