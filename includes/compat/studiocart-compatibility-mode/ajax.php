<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_ajax_action_map')) {
    /**
     * @return array<string, array{legacy: array<int, string>, auth: bool, nopriv: bool}>
     */
    function ncs_cart_get_legacy_ajax_action_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/ajax.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_get_legacy_nonce_action_map')) {
    /**
     * @return array{
     *     exact: array<string, array<int, string>>,
     *     prefix: array<string, array<int, string>>
     * }
     */
    function ncs_cart_get_legacy_nonce_action_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/nonces.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        if (! is_array($map)) {
            $map = [];
        }

        $map += [
            'exact'  => [],
            'prefix' => [],
        ];

        return $map;
    }
}

if (! function_exists('ncs_cart_get_legacy_nonce_actions')) {
    /**
     * Resolve legacy actions for one canonical nonce action.
     *
     * @param string $canonical_action Canonical nonce action.
     * @return array<int, string>
     */
    function ncs_cart_get_legacy_nonce_actions($canonical_action)
    {
        if (! is_string($canonical_action) || '' === $canonical_action) {
            return [];
        }

        $map = ncs_cart_get_legacy_nonce_action_map();

        if (isset($map['exact'][ $canonical_action ]) && is_array($map['exact'][ $canonical_action ])) {
            return array_values(array_filter($map['exact'][ $canonical_action ], 'is_string'));
        }

        foreach ($map['prefix'] as $canonical_prefix => $legacy_prefixes) {
            if (
                ! is_string($canonical_prefix)
                || 0 !== strpos($canonical_action, $canonical_prefix)
                || ! is_array($legacy_prefixes)
            ) {
                continue;
            }

            $suffix = substr($canonical_action, strlen($canonical_prefix));
            $legacy_actions = [];

            foreach ($legacy_prefixes as $legacy_prefix) {
                if (is_string($legacy_prefix) && '' !== $legacy_prefix) {
                    $legacy_actions[] = $legacy_prefix . $suffix;
                }
            }

            return $legacy_actions;
        }

        return [];
    }
}

if (! function_exists('ppcart_verify_legacy_nonce_action')) {
    /**
     * Compatibility filter for ppcart_verify_nonce().
     *
     * @param int|false $result           Existing verification result.
     * @param string    $nonce            Nonce value.
     * @param string    $canonical_action Canonical nonce action.
     * @return int|false
     */
    function ppcart_verify_legacy_nonce_action($result, $nonce, $canonical_action)
    {
        if (
            false !== $result
            || ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return $result;
        }

        foreach (ncs_cart_get_legacy_nonce_actions($canonical_action) as $legacy_action) {
            $legacy_result = wp_verify_nonce($nonce, $legacy_action);

            if (false !== $legacy_result) {
                return $legacy_result;
            }
        }

        return false;
    }
}

if (! function_exists('ncs_cart_legacy_ajax_registered_callbacks')) {
    /**
     * @return array<string, callable>
     */
    function &ncs_cart_legacy_ajax_registered_callbacks()
    {
        static $callbacks = [];

        return $callbacks;
    }
}

if (! function_exists('ncs_cart_legacy_ajax_dispatch_depth')) {
    /**
     * @return int
     */
    function &ncs_cart_legacy_ajax_dispatch_depth()
    {
        static $depth = 0;

        return $depth;
    }
}

if (! function_exists('ppcart_dispatch_legacy_ajax_action')) {
    /**
     * Dispatch one legacy request through its canonical WordPress hook.
     *
     * @param string $canonical_action Canonical AJAX action value.
     * @param bool   $nopriv           Whether to dispatch the unauthenticated hook.
     * @return void
     */
    function ppcart_dispatch_legacy_ajax_action($canonical_action, $nopriv)
    {
        $depth = &ncs_cart_legacy_ajax_dispatch_depth();

        if ($depth > 0 || ! is_string($canonical_action) || 0 !== strpos($canonical_action, 'ppcart_')) {
            return;
        }

        $request_sources = [ '_REQUEST', '_POST', '_GET' ];
        $original_actions = [];

        foreach ($request_sources as $source) {
            $original_actions[ $source ] = [
                'exists' => isset($GLOBALS[ $source ]['action']),
                'value'  => $GLOBALS[ $source ]['action'] ?? null,
            ];

            if ($original_actions[ $source ]['exists']) {
                $GLOBALS[ $source ]['action'] = $canonical_action;
            }
        }

        ++$depth;

        try {
            do_action(($nopriv ? 'wp_ajax_nopriv_' : 'wp_ajax_') . $canonical_action);
        } finally {
            --$depth;

            foreach ($original_actions as $source => $original) {
                if ($original['exists']) {
                    $GLOBALS[ $source ]['action'] = $original['value'];
                } else {
                    unset($GLOBALS[ $source ]['action']);
                }
            }
        }
    }
}

if (! function_exists('ppcart_register_legacy_ajax_bridges')) {
    /**
     * Register gated legacy AJAX routes with exact auth/nopriv parity.
     *
     * @return void
     */
    function ppcart_register_legacy_ajax_bridges()
    {
        $registered = &ncs_cart_legacy_ajax_registered_callbacks();

        foreach (ncs_cart_get_legacy_ajax_action_map() as $canonical => $contract) {
            if (! is_string($canonical) || ! is_array($contract) || empty($contract['legacy'])) {
                continue;
            }

            foreach ([ 'auth' => false, 'nopriv' => true ] as $exposure => $nopriv) {
                if (empty($contract[ $exposure ])) {
                    continue;
                }

                foreach ((array) $contract['legacy'] as $legacy) {
                    if (! is_string($legacy) || '' === $legacy) {
                        continue;
                    }

                    $hook = ($nopriv ? 'wp_ajax_nopriv_' : 'wp_ajax_') . $legacy;

                    if (isset($registered[ $hook ])) {
                        continue;
                    }

                    $callback = static function () use ($canonical, $nopriv) {
                        if (
                            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
                            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
                        ) {
                            return;
                        }

                        ppcart_dispatch_legacy_ajax_action($canonical, $nopriv);
                    };

                    add_action($hook, $callback, 0);
                    $registered[ $hook ] = $callback;
                }
            }
        }

        if (function_exists('ppcart_nonce_compatibility_verifier')) {
            ppcart_nonce_compatibility_verifier('ppcart_verify_legacy_nonce_action', true);
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_ajax_bridges')) {
    /**
     * Remove AJAX and nonce bridges. Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_ajax_bridges()
    {
        $registered = &ncs_cart_legacy_ajax_registered_callbacks();

        foreach ($registered as $hook => $callback) {
            remove_action($hook, $callback, 0);
        }

        $registered = [];

        if (function_exists('ppcart_nonce_compatibility_verifier')) {
            ppcart_nonce_compatibility_verifier(null, true);
        }

        $depth = &ncs_cart_legacy_ajax_dispatch_depth();
        $depth = 0;
    }
}
