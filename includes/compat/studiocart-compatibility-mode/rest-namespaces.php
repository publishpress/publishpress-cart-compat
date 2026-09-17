<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_rest_namespace_map')) {
    /**
     * Canonical REST namespace => leftover namespace and copyable route prefixes.
     *
     * @return array<string, array{legacy: string, route_prefixes: array<int, string>}>
     */
    function ncs_cart_get_legacy_rest_namespace_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/rest-namespaces.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_legacy_rest_route_matches_prefixes')) {
    /**
     * Whether a route suffix under a canonical namespace is a Pro developer route.
     *
     * @param string               $suffix   Route after the canonical namespace, e.g. `/orders/(?P<id>[\d]+)`.
     * @param array<int, string>   $prefixes Copyable prefixes from the map (`/webhook`, `/orders`, …).
     * @return bool
     */
    function ncs_cart_legacy_rest_route_matches_prefixes($suffix, $prefixes)
    {
        if (! is_string($suffix) || '' === $suffix || '/' === $suffix || ! is_array($prefixes)) {
            return false;
        }

        foreach ($prefixes as $prefix) {
            if (! is_string($prefix) || '' === $prefix) {
                continue;
            }

            if ($suffix === $prefix || 0 === strpos($suffix, $prefix . '/')) {
                return true;
            }
        }

        return false;
    }
}

if (! function_exists('ncs_cart_legacy_rest_handlers_for_reregister')) {
    /**
     * Flatten get_routes() handlers so register_rest_route() can re-normalize methods.
     *
     * @param array<int|string, mixed> $handlers Route handlers from WP_REST_Server::get_routes().
     * @return array<int, array<string, mixed>>
     */
    function ncs_cart_legacy_rest_handlers_for_reregister($handlers)
    {
        $copy = [];

        if (! is_array($handlers)) {
            return $copy;
        }

        foreach ($handlers as $index => $handler) {
            if (! is_int($index) || ! is_array($handler)) {
                continue;
            }

            if (isset($handler['methods']) && is_array($handler['methods'])) {
                $handler['methods'] = implode(',', array_keys($handler['methods']));
            }

            $copy[] = $handler;
        }

        return $copy;
    }
}

if (! function_exists('ncs_cart_legacy_rest_namespaces_registered')) {
    /**
     * Leftover REST routes registered this request (full path keys).
     *
     * @return array<string, bool>
     */
    function &ncs_cart_legacy_rest_namespaces_registered()
    {
        static $registered = [];

        return $registered;
    }
}

if (! function_exists('ppcart_register_legacy_rest_namespaces')) {
    /**
     * Dual-register leftover `sc/v1` onto Pro developer REST routes.
     *
     * Copies only mapped prefixes. Gutenberg `/checkout-block` and
     * `/account-block` stay canonical-only.
     *
     * @return void
     */
    function ppcart_register_legacy_rest_namespaces()
    {
        if (! function_exists('rest_get_server') || ! function_exists('register_rest_route')) {
            return;
        }

        $server = rest_get_server();
        if (! is_object($server) || ! method_exists($server, 'get_routes')) {
            return;
        }

        $routes     = $server->get_routes();
        $registered = &ncs_cart_legacy_rest_namespaces_registered();

        if (! is_array($routes)) {
            return;
        }

        foreach (ncs_cart_get_legacy_rest_namespace_map() as $canonical_ns => $entry) {
            if (! is_string($canonical_ns) || '' === $canonical_ns || ! is_array($entry)) {
                continue;
            }

            $legacy_ns = isset($entry['legacy']) && is_string($entry['legacy']) ? $entry['legacy'] : '';
            $prefixes  = isset($entry['route_prefixes']) && is_array($entry['route_prefixes'])
                ? $entry['route_prefixes']
                : [];

            if ('' === $legacy_ns) {
                continue;
            }

            $canonical_prefix = '/' . $canonical_ns;

            foreach ($routes as $route => $handlers) {
                if (! is_string($route) || ! is_array($handlers)) {
                    continue;
                }

                if ($route !== $canonical_prefix && 0 !== strpos($route, $canonical_prefix . '/')) {
                    continue;
                }

                $suffix = substr($route, strlen($canonical_prefix));

                if (! ncs_cart_legacy_rest_route_matches_prefixes($suffix, $prefixes)) {
                    continue;
                }

                $legacy_route = '/' . $legacy_ns . $suffix;

                if (isset($registered[ $legacy_route ])) {
                    continue;
                }

                if (isset($routes[ $legacy_route ])) {
                    continue;
                }

                $copy = ncs_cart_legacy_rest_handlers_for_reregister($handlers);

                if ([] === $copy) {
                    continue;
                }

                register_rest_route($legacy_ns, $suffix, $copy, false);
                $registered[ $legacy_route ] = true;
            }
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_rest_namespaces')) {
    /**
     * Remove leftover REST routes Compat registered this request.
     *
     * Test helper only. Production never calls this: leftover routes exist
     * only while Compatibility Mode is on, for the current request.
     *
     * @return void
     */
    function ppcart_unregister_legacy_rest_namespaces()
    {
        $registered = &ncs_cart_legacy_rest_namespaces_registered();

        if (function_exists('rest_get_server') && class_exists('WP_REST_Server', false)) {
            $server = rest_get_server();

            if ($server instanceof WP_REST_Server) {
                $legacy_namespaces = [];

                foreach (ncs_cart_get_legacy_rest_namespace_map() as $entry) {
                    if (! is_array($entry) || empty($entry['legacy']) || ! is_string($entry['legacy'])) {
                        continue;
                    }

                    $legacy_namespaces[] = $entry['legacy'];
                }

                foreach ([ 'endpoints', 'namespaces', 'route_options' ] as $property) {
                    try {
                        $ref = new ReflectionProperty($server, $property);
                        $ref->setAccessible(true);
                        $value = $ref->getValue($server);

                        if (! is_array($value)) {
                            continue;
                        }

                        if ('endpoints' === $property || 'route_options' === $property) {
                            foreach (array_keys($registered) as $route) {
                                unset($value[ $route ]);
                            }
                        } else {
                            foreach ($legacy_namespaces as $legacy_ns) {
                                if (! isset($value[ $legacy_ns ]) || ! is_array($value[ $legacy_ns ])) {
                                    continue;
                                }

                                foreach (array_keys($registered) as $route) {
                                    unset($value[ $legacy_ns ][ $route ]);
                                }

                                if (empty($value[ $legacy_ns ])) {
                                    unset($value[ $legacy_ns ]);
                                }
                            }
                        }

                        $ref->setValue($server, $value);
                    } catch (ReflectionException $e) {
                        unset($e);
                    }
                }
            }
        }

        $registered = [];
    }
}
