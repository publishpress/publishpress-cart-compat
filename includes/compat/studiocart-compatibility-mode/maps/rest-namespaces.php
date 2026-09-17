<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical REST namespace => leftover namespace and Pro developer route prefixes.
 *
 * Gutenberg checkout/account block routes stay canonical-only (Free slice 29).
 * Compatibility Mode dual-registers leftover `sc/v1` for Pro Zapier/bookmarks.
 *
 * @return array<string, array{legacy: string, route_prefixes: array<int, string>}>
 */
return [
    'publishpress-cart/v1' => [
        'legacy'         => 'sc/v1',
        'route_prefixes' => [
            '/webhook',
            '/orders',
            '/products',
            '/customers',
            '/subscriptions',
        ],
    ],
];
