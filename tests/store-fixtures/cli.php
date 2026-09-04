<?php

/**
 * Test-only WP-CLI entry for leftover StudioCart store fixtures.
 *
 * Loaded with `wp --require=.../tests/store-fixtures/cli.php`. Never included
 * from publishpress-cart-compat.php.
 *
 * @package Tests\StoreFixtures
 */

namespace Tests\StoreFixtures;

if (! defined('WP_CLI') || ! WP_CLI) {
    return;
}

/**
 * Seed a leftover StudioCart store for local QA (products, checkout pages, COD).
 *
 * ## OPTIONS
 *
 * [--products=<n>]
 * : How many leftover sc_product posts.
 * ---
 * default: 14
 * ---
 *
 * [--customers=<n>]
 * : How many subscriber accounts (password: studiocart).
 * ---
 * default: 40
 * ---
 *
 * [--orders=<n>]
 * : How many leftover sc_order posts across the last 30 days.
 * ---
 * default: 96
 * ---
 *
 * [--subscriptions=<n>]
 * : How many leftover sc_subscription posts (plus first/renewal orders).
 * ---
 * default: 20
 * ---
 *
 * [--reset]
 * : Delete the previous test seed first, then seed again.
 *
 * [--clean]
 * : Delete the previous test seed and exit (do not seed).
 *
 * ## EXAMPLES
 *
 *     wp --require=tests/store-fixtures/cli.php ppcartcomp seed-store
 *     wp --require=tests/store-fixtures/cli.php ppcartcomp seed-store --reset
 *     ddev seed-store
 *
 * @when after_wp_load
 */
class Command
{
    /**
     * @param array<int, string>   $args       Positional.
     * @param array<string, mixed> $assoc_args Flags.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        require_once __DIR__ . '/catalog.php';
        require_once __DIR__ . '/seeder.php';

        $seeder = new Seeder();

        if (isset($assoc_args['clean'])) {
            $removed = $seeder->clean();
            \WP_CLI::success(
                sprintf(
                    'Removed test store seed: %d products, %d pages, %d orders, %d subscriptions, %d users, %d terms.',
                    $removed['products'],
                    $removed['pages'],
                    $removed['orders'],
                    $removed['subscriptions'],
                    $removed['users'],
                    $removed['terms']
                )
            );

            return;
        }

        $summary = $seeder->run(
            [
                'reset'         => isset($assoc_args['reset']),
                'products'      => isset($assoc_args['products']) ? $assoc_args['products'] : Catalog::DEFAULT_PRODUCTS,
                'customers'     => isset($assoc_args['customers']) ? $assoc_args['customers'] : Catalog::DEFAULT_CUSTOMERS,
                'orders'        => isset($assoc_args['orders']) ? $assoc_args['orders'] : Catalog::DEFAULT_ORDERS,
                'subscriptions' => isset($assoc_args['subscriptions']) ? $assoc_args['subscriptions'] : Catalog::DEFAULT_SUBSCRIPTIONS,
            ]
        );

        $store_id = isset($summary['pages']['store']) ? (int) $summary['pages']['store'] : 0;
        $store    = $store_id > 0 ? get_permalink($store_id) : '';

        \WP_CLI::success(
            sprintf(
                'Seeded leftover StudioCart store: %d products, %d customers (password: %s), %d orders, %d subscriptions, %d pages.%s',
                count($summary['products']),
                count($summary['customers']),
                $summary['password'],
                count($summary['orders']),
                count($summary['subscriptions']),
                count($summary['pages']),
                $store ? ' Store: ' . $store : ''
            )
        );
    }
}

\WP_CLI::add_command('ppcartcomp seed-store', Command::class);
