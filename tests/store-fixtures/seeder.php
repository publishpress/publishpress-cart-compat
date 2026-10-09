<?php

namespace Tests\StoreFixtures;

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Inserts leftover StudioCart rows for local QA. Not loaded by the plugin.
 */
class Seeder
{
    public const REGISTRY_OPTION = 'ppcartcomp_test_store_seed';

    /**
     * @param array<string, mixed> $args Counts and flags.
     * @return array<string, mixed>
     */
    public function run(array $args)
    {
        $reset = ! empty($args['reset']);
        if ($reset) {
            $this->clean();
        }

        $this->prepare_environment();

        $products_count      = isset($args['products']) ? max(1, (int) $args['products']) : Catalog::DEFAULT_PRODUCTS;
        $customers_count     = isset($args['customers']) ? max(1, (int) $args['customers']) : Catalog::DEFAULT_CUSTOMERS;
        $orders_count        = isset($args['orders']) ? max(0, (int) $args['orders']) : Catalog::DEFAULT_ORDERS;
        $subscriptions_count = isset($args['subscriptions']) ? max(0, (int) $args['subscriptions']) : Catalog::DEFAULT_SUBSCRIPTIONS;

        $summary = [
            'products'      => [],
            'pages'         => [],
            'customers'     => [],
            'orders'        => [],
            'subscriptions' => [],
            'terms'         => [],
            'password'      => Catalog::CUSTOMER_PASSWORD,
        ];

        $this->seed_options();
        $this->seed_roles();
        $this->seed_taxonomies($summary);
        $product_map = $this->seed_products($products_count, $summary);
        $this->seed_pages($product_map, $summary);
        $this->seed_elementor_pages($product_map, $summary);
        $this->seed_leftover_shortcode_surfaces($product_map);
        $customers = $this->seed_customers($customers_count, $summary);
        $this->seed_orders($orders_count, $product_map, $customers, $summary);
        $this->seed_subscriptions($subscriptions_count, $product_map, $customers, $summary);

        if (function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules(false);
        }

        return $summary;
    }

    /**
     * @return array<string, int>
     */
    public function clean()
    {
        $registry = $this->get_registry();
        $removed  = [
            'products'      => 0,
            'pages'         => 0,
            'orders'        => 0,
            'subscriptions' => 0,
            'users'         => 0,
            'terms'         => 0,
        ];

        require_once ABSPATH . 'wp-admin/includes/user.php';

        foreach ([ 'subscriptions', 'orders', 'pages', 'products' ] as $type) {
            foreach (array_keys(isset($registry[ $type ]) ? $registry[ $type ] : []) as $id) {
                if (wp_delete_post((int) $id, true)) {
                    $removed[ $type ]++;
                }
            }
        }

        $current = function_exists('get_current_user_id') ? (int) get_current_user_id() : 0;
        foreach (array_keys(isset($registry['users']) ? $registry['users'] : []) as $id) {
            $id = (int) $id;
            if ($id > 0 && $id !== $current && wp_delete_user($id, 1)) {
                $removed['users']++;
            }
        }

        foreach (isset($registry['terms']) ? $registry['terms'] : [] as $term) {
            $term_id  = isset($term['term_id']) ? (int) $term['term_id'] : 0;
            $taxonomy = isset($term['taxonomy']) ? (string) $term['taxonomy'] : '';
            if ($term_id > 0 && $taxonomy && ! is_wp_error(wp_delete_term($term_id, $taxonomy))) {
                $removed['terms']++;
            }
        }

        delete_option(self::REGISTRY_OPTION);

        return $removed;
    }

    /**
     * @return void
     */
    private function prepare_environment()
    {
        if (function_exists('ppcart_cpt_slug_migration_save_state')) {
            ppcart_cpt_slug_migration_save_state(
                [
                    'ack'            => '',
                    'mode'           => 'legacy',
                    'step1'          => 'idle',
                    'step2'          => 'idle',
                    'stop_requested' => false,
                    'cursor'         => null,
                ]
            );
        }

        $this->ensure_post_type(Catalog::PRODUCT_POST_TYPE, true);
        $this->ensure_post_type(Catalog::ORDER_POST_TYPE, false);
        $this->ensure_post_type(Catalog::SUBSCRIPTION_POST_TYPE, false);
        $this->ensure_taxonomy(Catalog::PRODUCT_CAT_TAXONOMY);
        $this->ensure_taxonomy(Catalog::PRODUCT_TAG_TAXONOMY);
    }

    /**
     * @param string $slug   Post type.
     * @param bool   $public Public.
     * @return void
     */
    private function ensure_post_type($slug, $public)
    {
        if (post_type_exists($slug)) {
            return;
        }

        register_post_type(
            $slug,
            [
                'public'   => $public,
                'label'    => $slug,
                'supports' => [ 'title', 'editor', 'thumbnail' ],
                'show_ui'  => true,
            ]
        );
    }

    /**
     * @param string $taxonomy Taxonomy.
     * @return void
     */
    private function ensure_taxonomy($taxonomy)
    {
        if (taxonomy_exists($taxonomy)) {
            return;
        }

        register_taxonomy($taxonomy, [ Catalog::PRODUCT_POST_TYPE ], [ 'public' => true ]);
    }

    /**
     * @return void
     */
    private function seed_options()
    {
        foreach (Catalog::leftover_store_options() as $name => $value) {
            update_option($name, $value, false);
        }
    }

    /**
     * @return void
     */
    private function seed_roles()
    {
        foreach ([ 'sc_cart_manager' => 'Cart Manager', 'sc_cart_administrator' => 'Cart Administrator' ] as $slug => $label) {
            if (! get_role($slug)) {
                add_role($slug, $label, [ 'read' => true ]);
            }
        }

        $admin = get_role('administrator');
        if ($admin) {
            $admin->add_cap('sc_manager_option');
            $admin->add_cap('sc_manage_orders');
        }
    }

    /**
     * @param array<string, mixed> $summary Summary.
     * @return void
     */
    private function seed_taxonomies(array &$summary)
    {
        $summary['term_map'] = [
            Catalog::PRODUCT_CAT_TAXONOMY => [],
            Catalog::PRODUCT_TAG_TAXONOMY => [],
        ];

        foreach (
            [
                Catalog::PRODUCT_CAT_TAXONOMY => Catalog::categories(),
                Catalog::PRODUCT_TAG_TAXONOMY => Catalog::tags(),
            ] as $taxonomy => $names
        ) {
            $this->ensure_taxonomy($taxonomy);
            foreach ($names as $name) {
                $existing = term_exists($name, $taxonomy);
                if (is_array($existing) && ! empty($existing['term_id'])) {
                    $term_id = (int) $existing['term_id'];
                } else {
                    $result = wp_insert_term($name, $taxonomy);
                    if (is_wp_error($result) || empty($result['term_id'])) {
                        continue;
                    }
                    $term_id = (int) $result['term_id'];
                }
                $this->track('terms', $term_id, [ 'taxonomy' => $taxonomy ]);
                $summary['terms'][] = $term_id;
                $summary['term_map'][ $taxonomy ][ $name ] = $term_id;
            }
        }
    }

    /**
     * @param int                  $count   Count.
     * @param array<string, mixed> $summary Summary.
     * @return array<string, int>
     */
    private function seed_products($count, array &$summary)
    {
        $map     = [];
        $catalog = Catalog::product_catalog_for_count($count);
        $common  = Catalog::product_common_meta();
        $cats    = isset($summary['term_map'][ Catalog::PRODUCT_CAT_TAXONOMY ]) ? $summary['term_map'][ Catalog::PRODUCT_CAT_TAXONOMY ] : [];
        $tags    = isset($summary['term_map'][ Catalog::PRODUCT_TAG_TAXONOMY ]) ? $summary['term_map'][ Catalog::PRODUCT_TAG_TAXONOMY ] : [];

        foreach ($catalog as $definition) {
            $id = $this->insert_or_reuse_post(
                [
                    'post_type'    => Catalog::PRODUCT_POST_TYPE,
                    'post_status'  => 'publish',
                    'post_title'   => $definition['title'],
                    'post_name'    => $definition['slug'],
                    'post_content' => $definition['title'] . "\n\n" . Catalog::checkout_shortcode(0),
                ]
            );
            if ($id <= 0) {
                continue;
            }

            foreach ($common as $key => $value) {
                update_post_meta($id, $key, $value);
            }
            foreach ($definition['meta'] as $key => $value) {
                update_post_meta($id, $key, $value);
            }

            wp_update_post(
                [
                    'ID'           => $id,
                    'post_content' => $definition['title'] . "\n\n" . Catalog::checkout_shortcode($id),
                ]
            );

            $cat_name = isset($definition['category']) ? $definition['category'] : '';
            $tag_name = isset($definition['tag']) ? $definition['tag'] : '';
            if ($cat_name && ! empty($cats[ $cat_name ])) {
                wp_set_object_terms($id, [ (int) $cats[ $cat_name ] ], Catalog::PRODUCT_CAT_TAXONOMY);
            }
            if ($tag_name && ! empty($tags[ $tag_name ])) {
                wp_set_object_terms($id, [ (int) $tags[ $tag_name ] ], Catalog::PRODUCT_TAG_TAXONOMY);
            }

            $this->track('products', $id, [ 'key' => $definition['key'] ]);
            $map[ $definition['key'] ] = $id;
            $summary['products'][]     = $id;
        }

        if (! empty($map['bump-main']) && ! empty($map['bump-offer'])) {
            $main = $map['bump-main'];
            $bump = $map['bump-offer'];
            update_post_meta($main, '_sc_ob_product', (string) $bump);
            update_post_meta($main, '_sc_ob_price', '5');
            update_post_meta(
                $main,
                '_sc_order_bump_options',
                [
                    [
                        'order_bump'     => '1',
                        'ob_product'     => (string) $bump,
                        'ob_price'       => '5',
                        'ob_headline'    => 'Add Atlas Local Pack',
                        'ob_description' => 'Local SEO add-on for this Atlas SEO license',
                        'ob_cb_label'    => 'Yes, add Local Pack',
                    ],
                ]
            );
        }

        return $map;
    }

    /**
     * Extra leftover shortcode rows the Data migration wizard will rewrite.
     *
     * @param array<string, int> $product_map Products.
     * @return void
     */
    private function seed_leftover_shortcode_surfaces(array $product_map)
    {
        $intro = isset($product_map['intro']) ? (int) $product_map['intro'] : 0;
        if ($intro <= 0) {
            return;
        }

        update_post_meta(
            $intro,
            '_sc_notifications',
            [
                [
                    'body' => 'Your access: ' . Catalog::checkout_shortcode($intro),
                ],
            ]
        );
    }

    /**
     * Elementor-built pages whose leftover shortcodes live only in `_elementor_data`.
     *
     * @param array<string, int>   $product_map Product IDs.
     * @param array<string, mixed> $summary     Summary.
     * @return void
     */
    private function seed_elementor_pages(array $product_map, array &$summary)
    {
        $pages = [
            'elementor-account' => [
                'title'   => 'Northstar Account (Elementor)',
                'widgets' => Catalog::elementor_account_widgets(),
            ],
        ];

        $first = reset($product_map);
        if ($first) {
            $pages['elementor-checkout'] = [
                'title'   => get_the_title($first) . ' Checkout (Elementor)',
                'widgets' => Catalog::elementor_checkout_widgets((int) $first),
            ];
        }

        foreach ($pages as $key => $page) {
            $page_id = $this->insert_page($page['title'], 'seed-' . $key, '');
            if ($page_id <= 0) {
                continue;
            }

            update_post_meta($page_id, '_elementor_edit_mode', 'builder');
            update_post_meta($page_id, '_elementor_template_type', 'wp-page');
            update_post_meta($page_id, '_elementor_data', wp_slash((string) wp_json_encode(Catalog::elementor_data($page['widgets']))));
            $summary['pages'][ $key ] = $page_id;
        }
    }

    /**
     * @param array<string, int>   $product_map Product IDs.
     * @param array<string, mixed> $summary     Summary.
     * @return void
     */
    private function seed_pages(array $product_map, array &$summary)
    {
        $store_id = $this->insert_page('Northstar Plugins', 'seed-store', '[' . Catalog::STORE_SHORTCODE . ']');
        if ($store_id > 0) {
            $summary['pages']['store'] = $store_id;
        }

        $account_id = $this->insert_page('Northstar Account', 'seed-account', '[' . Catalog::ACCOUNT_SHORTCODE . ']');
        if ($account_id > 0) {
            $summary['pages']['account'] = $account_id;
            update_option('_sc_myaccount_page_id', $account_id, false);
            update_option('_my_account', $account_id, false);
        }

        $receipt_id = $this->insert_page('Northstar Receipt', 'seed-receipt', '[' . Catalog::RECEIPT_SHORTCODE . ']');
        if ($receipt_id > 0) {
            $summary['pages']['receipt'] = $receipt_id;
        }

        foreach ($product_map as $key => $product_id) {
            $page_id = $this->insert_page(
                get_the_title($product_id) . ' Checkout',
                'seed-checkout-' . $key,
                Catalog::checkout_shortcode($product_id)
            );
            if ($page_id > 0) {
                $summary['pages'][ 'checkout-' . $key ] = $page_id;
            }
        }
    }

    /**
     * @param int                  $count   Count.
     * @param array<string, mixed> $summary Summary.
     * @return array<int, array<string, mixed>>
     */
    private function seed_customers($count, array &$summary)
    {
        $profiles = Catalog::customer_profiles_for_count($count);
        $records  = [];

        for ($i = 0; $i < $count; $i++) {
            $profile = $profiles[ $i ];
            $n       = $i + 1;
            $login   = sprintf('%s%03d', Catalog::CUSTOMER_LOGIN_PREFIX, $n);
            $email   = sprintf('sc-seed-customer-%03d@example.test', $n);
            $user_id = $this->insert_or_reuse_user($login, $email, $profile);
            if ($user_id <= 0) {
                continue;
            }

            update_user_meta($user_id, '_sc_customer_id', 'cus_seed_' . $user_id);
            if (0 === $i % 2) {
                update_user_meta($user_id, '_sc_stripe_customer_id', 'cus_stripe_seed_' . $user_id);
            }
            $this->seed_customer_leftover_usermeta($user_id, $profile);
            $this->track('users', $user_id, [ 'login' => $login ]);
            $records[]              = [
                'user_id' => $user_id,
                'profile' => $profile,
                'email'   => $email,
            ];
            $summary['customers'][] = $user_id;
        }

        return $records;
    }

    /**
     * Write leftover StudioCart account-profile user-meta for migration QA.
     *
     * @param int                   $user_id User ID.
     * @param array<string, string> $profile Profile fields.
     * @return void
     */
    private function seed_customer_leftover_usermeta($user_id, array $profile)
    {
        $user_id = (int) $user_id;
        if ($user_id <= 0) {
            return;
        }

        $map = [
            'phone'     => '_sc_phone',
            'address_1' => '_sc_address_1',
            'address_2' => '_sc_address_2',
            'city'      => '_sc_city',
            'state'     => '_sc_state',
            'zip'       => '_sc_zip',
            'country'   => '_sc_country',
        ];

        foreach ($map as $field => $meta_key) {
            if (! isset($profile[ $field ]) || '' === (string) $profile[ $field ]) {
                continue;
            }
            update_user_meta($user_id, $meta_key, (string) $profile[ $field ]);
        }

        if (! empty($profile['address_1'])) {
            update_user_meta($user_id, '_sc_address', '1');
        }
    }

    /**
     * @param int                          $count       Count.
     * @param array<string, int>           $product_map Products.
     * @param array<int, array<string, mixed>> $customers Customers.
     * @param array<string, mixed>         $summary     Summary.
     * @return void
     */
    private function seed_orders($count, array $product_map, array $customers, array &$summary)
    {
        if ($count < 1 || empty($product_map) || empty($customers)) {
            return;
        }

        $repeaters   = array_slice($customers, 0, max(1, (int) ceil(count($customers) * 0.25)));
        $browsers    = (int) floor(count($customers) * 0.15);
        $buyers      = array_slice($customers, 0, max(1, count($customers) - $browsers));
        $product_ids = array_values($product_map);
        $sale_id     = isset($product_map['sale']) ? (int) $product_map['sale'] : 0;
        $tax_id      = isset($product_map['taxable']) ? (int) $product_map['taxable'] : 0;
        $bump_id     = isset($product_map['bump-main']) ? (int) $product_map['bump-main'] : 0;
        $coupon_id   = isset($product_map['coupon']) ? (int) $product_map['coupon'] : 0;
        $free_id     = isset($product_map['workshop']) ? (int) $product_map['workshop'] : 0;

        for ($i = 0; $i < $count; $i++) {
            $customer = ( 0 === $i % 4 && ! empty($repeaters) )
                ? $repeaters[ $i % count($repeaters) ]
                : $buyers[ $i % count($buyers) ];

            if (0 === $i % 11 && $sale_id > 0) {
                $product_id = $sale_id;
            } elseif (0 === $i % 7 && $tax_id > 0) {
                $product_id = $tax_id;
            } elseif (0 === $i % 9 && $bump_id > 0) {
                $product_id = $bump_id;
            } elseif (0 === $i % 8 && $coupon_id > 0) {
                $product_id = $coupon_id;
            } elseif (0 === $i % 17 && $free_id > 0) {
                $product_id = $free_id;
            } else {
                $product_id = (int) $product_ids[ $i % count($product_ids) ];
            }

            $product = get_post($product_id);
            $status  = $this->order_status_for_index($i);
            $qty     = ( 0 === $i % 8 ) ? 2 : 1;
            $amount  = $this->order_amount_for_product($product_id, $product_map, $i, $qty);
            $extra   = $this->order_extra_meta($i, $status, $product_id, $tax_id, $bump_id, $coupon_id, $amount, $qty);
            if (0 === $i % 19) {
                $extra['meta']['_sc_user_account'] = 0;
            }

            $id = $this->insert_commerce_post(
                Catalog::ORDER_POST_TYPE,
                $status,
                $product ? $product->post_title . ' Order' : 'Seed Order',
                $customer,
                $product_id,
                $amount,
                'orders',
                Catalog::month_post_date($i, $count),
                $extra
            );
            if ($id > 0) {
                $summary['orders'][] = $id;
            }
        }
    }

    /**
     * @param int                          $count       Count.
     * @param array<string, int>           $product_map Products.
     * @param array<int, array<string, mixed>> $customers Customers.
     * @param array<string, mixed>         $summary     Summary.
     * @return void
     */
    private function seed_subscriptions($count, array $product_map, array $customers, array &$summary)
    {
        if ($count < 1 || empty($customers)) {
            return;
        }

        $recurring = [];
        foreach (Catalog::product_catalog_for_count(count($product_map)) as $definition) {
            if ('recurring' === $definition['type'] && ! empty($product_map[ $definition['key'] ])) {
                $recurring[] = $product_map[ $definition['key'] ];
            }
        }
        if (empty($recurring)) {
            $recurring = array_values($product_map);
        }

        $statuses = [ 'active', 'active', 'active', 'trialing', 'past_due', 'paused', 'canceled', 'completed' ];

        for ($i = 0; $i < $count; $i++) {
            $customer   = $customers[ $i % count($customers) ];
            $product_id = (int) $recurring[ $i % count($recurring) ];
            $product    = get_post($product_id);
            $status     = $statuses[ $i % count($statuses) ];
            $amount     = 29 + ( $i % 4 ) * 10;
            $started    = Catalog::month_post_date($i, $count);
            $id         = $this->insert_commerce_post(
                Catalog::SUBSCRIPTION_POST_TYPE,
                $status,
                $product ? $product->post_title . ' Subscription' : 'Seed Subscription',
                $customer,
                $product_id,
                $amount,
                'subscriptions',
                $started
            );
            if ($id <= 0) {
                continue;
            }

            $interval = ( 0 === $i % 5 ) ? 'year' : 'month';
            update_post_meta($id, '_sc_sub_status', $status);
            update_post_meta($id, '_sc_subscription_id', 'sub_seed_' . $id);
            update_post_meta($id, '_sc_sub_installments', ( 0 === $i % 3 ) ? '6' : '-1');
            update_post_meta($id, '_sc_sub_interval', $interval);
            update_post_meta($id, '_sc_sub_frequency', 1);
            update_post_meta($id, '_sc_sub_amount', $amount);
            update_post_meta($id, '_sc_sub_next_bill_date', strtotime($started . ' +1 ' . $interval));
            $summary['subscriptions'][] = $id;

            $first = $this->insert_commerce_post(
                Catalog::ORDER_POST_TYPE,
                in_array($status, [ 'canceled', 'past_due' ], true) ? 'failed' : 'paid',
                $product ? $product->post_title . ' First payment' : 'Seed First payment',
                $customer,
                $product_id,
                $amount,
                'orders',
                $started,
                [ 'meta' => [ '_sc_subscription_id' => $id ] ]
            );
            if ($first > 0) {
                $summary['orders'][] = $first;
            }

            $start_ts = strtotime($started);
            if ($start_ts && ( time() - $start_ts ) > ( 14 * 86400 ) && 'canceled' !== $status) {
                $renewal_date = gmdate('Y-m-d H:i:s', $start_ts + ( 14 * 86400 ));
                $renewal      = $this->insert_commerce_post(
                    Catalog::ORDER_POST_TYPE,
                    'paid',
                    $product ? $product->post_title . ' Renewal' : 'Seed Renewal',
                    $customer,
                    $product_id,
                    $amount,
                    'orders',
                    $renewal_date,
                    [
                        'meta' => [
                            '_sc_subscription_id' => $id,
                            '_sc_renewal_order'   => 1,
                            '_sc_order_type'      => 'renewal',
                        ],
                    ]
                );
                if ($renewal > 0) {
                    $summary['orders'][] = $renewal;
                }
            }
        }
    }

    /**
     * @param int $index Index.
     * @return string
     */
    private function order_status_for_index($index)
    {
        if (0 === $index % 13) {
            return 'refunded';
        }
        if (0 === $index % 11) {
            return 'failed';
        }
        if (0 === $index % 9) {
            return 'pending-payment';
        }
        if (0 === $index % 6) {
            return 'completed';
        }

        return 'paid';
    }

    /**
     * @param int                  $product_id   Product.
     * @param array<string, int>   $product_map  Map.
     * @param int                  $index        Index.
     * @param int                  $qty          Qty.
     * @return float
     */
    private function order_amount_for_product($product_id, array $product_map, $index, $qty)
    {
        $amounts = [
            'intro'     => 49,
            'membership'=> 29,
            'coaching'  => 99,
            'workshop'  => 0,
            'coupon'    => 19,
            'taxable'   => 79,
            'bump-offer'=> 5,
            'bump-main' => 149,
            'annual'    => 199,
            'sale'      => 69,
            'team'      => 249,
            'pwyw'      => 15 + ( $index % 4 ) * 5,
            'closed'    => 399,
            'terms'     => 129,
        ];
        $key = array_search($product_id, $product_map, true);
        $base = ( false !== $key && isset($amounts[ $key ]) ) ? $amounts[ $key ] : ( 19 + ( $index % 7 ) * 10 );

        return (float) $base * max(1, (int) $qty);
    }

    /**
     * @param int    $index     Index.
     * @param string $status    Status.
     * @param int    $product_id Product.
     * @param int    $tax_id    Taxable product.
     * @param int    $bump_id   Bump product.
     * @param int    $coupon_id Coupon product.
     * @param float  $amount    Amount.
     * @param int    $qty       Qty.
     * @return array<string, mixed>
     */
    private function order_extra_meta($index, $status, $product_id, $tax_id, $bump_id, $coupon_id, $amount, $qty)
    {
        $meta = [ '_sc_quantity' => $qty ];

        if ($product_id === $tax_id) {
            $tax = round($amount * 0.085, 2);
            $meta['_sc_tax_amount']      = $tax;
            $meta['_sc_pre_tax_amount']  = $amount;
            $meta['_sc_invoice_total']   = $amount + $tax;
            $meta['_sc_address1']        = '123 Market St';
            $meta['_sc_city']            = 'San Francisco';
            $meta['_sc_state']           = 'CA';
            $meta['_sc_zip']             = '94105';
            $meta['_sc_country']         = 'US';
        }

        if ($product_id === $coupon_id || 0 === $index % 5) {
            $code = ( 0 === $index % 10 ) ? Catalog::COUPON_SAVE : Catalog::COUPON_LAUNCH;
            $meta['_sc_coupon']    = $code;
            $meta['_sc_coupon_id'] = $code;
        }

        if ($product_id === $bump_id) {
            $meta['_sc_order_bumps'] = [
                [
                    'amount' => 5,
                    'name'   => 'Atlas Local Pack',
                ],
            ];
            $meta['_sc_invoice_total'] = $amount + 5;
        }

        if ('refunded' === $status) {
            $meta['_sc_refund_log'] = [
                [
                    'date'   => Catalog::month_post_date($index, Catalog::DEFAULT_ORDERS),
                    'amount' => $amount,
                    'reason' => 'Seed month refund',
                ],
            ];
        }

        if (0 === $amount) {
            $meta['_sc_pay_method'] = 'free';
        }

        return [ 'meta' => $meta, 'quantity' => $qty ];
    }

    /**
     * @param string $title   Title.
     * @param string $slug    Slug.
     * @param string $content Content.
     * @return int
     */
    private function insert_page($title, $slug, $content)
    {
        $id = $this->insert_or_reuse_post(
            [
                'post_type'    => 'page',
                'post_status'  => 'publish',
                'post_title'   => $title,
                'post_name'    => $slug,
                'post_content' => $content,
            ]
        );
        if ($id > 0) {
            $this->track('pages', $id, [ 'slug' => $slug ]);
        }

        return $id;
    }

    /**
     * @param string               $post_type  Type.
     * @param string               $status     Status.
     * @param string               $title      Title.
     * @param array<string, mixed> $customer   Customer.
     * @param int                  $product_id Product.
     * @param float                $amount     Amount.
     * @param string               $track_type Registry key.
     * @param string               $post_date  MySQL datetime.
     * @param array<string, mixed> $extra      Extra meta wrapper.
     * @return int
     */
    private function insert_commerce_post($post_type, $status, $title, array $customer, $product_id, $amount, $track_type, $post_date = '', array $extra = [])
    {
        $product = get_post($product_id);
        $profile = $customer['profile'];
        $args    = [
            'post_type'   => $post_type,
            'post_status' => $status,
            'post_title'  => $title,
        ];
        if ('' !== $post_date) {
            $args['post_date']     = $post_date;
            $args['post_date_gmt'] = $post_date;
        }

        $id = $this->insert_or_reuse_post($args);
        if ($id <= 0) {
            return 0;
        }

        $first = $profile['first_name'];
        $last  = $profile['last_name'];
        $qty   = isset($extra['quantity']) ? (int) $extra['quantity'] : 1;
        $meta  = [
            '_sc_product_id'     => $product_id,
            '_sc_product_name'   => $product ? $product->post_title : $title,
            '_sc_item_name'      => 'Seed Plan',
            '_sc_option_id'      => 'seed_plan',
            '_sc_amount'         => $amount,
            '_sc_main_offer_amt' => $amount,
            '_sc_invoice_total'  => $amount,
            '_sc_email'          => $customer['email'],
            '_sc_first_name'     => $first,
            '_sc_last_name'      => $last,
            '_sc_customer_name'  => trim($first . ' ' . $last),
            '_sc_user_account'   => $customer['user_id'],
            '_sc_pay_method'     => 'cod',
            '_sc_currency'       => 'USD',
            '_sc_quantity'       => max(1, $qty),
            '_sc_status'         => $status,
        ];
        if (isset($extra['meta']) && is_array($extra['meta'])) {
            $meta = array_merge($meta, $extra['meta']);
        }

        foreach ($meta as $key => $value) {
            update_post_meta($id, $key, $value);
        }

        update_post_meta(
            $id,
            '_sc_plan',
            (object) [
                'name'      => isset($meta['_sc_item_name']) ? $meta['_sc_item_name'] : 'Seed Plan',
                'price'     => $amount,
                'type'      => Catalog::SUBSCRIPTION_POST_TYPE === $post_type ? 'recurring' : '',
                'stripe_id' => isset($meta['_sc_option_id']) ? $meta['_sc_option_id'] : 'seed_plan',
            ]
        );

        $this->track($track_type, $id, [ 'product_id' => $product_id ]);

        return $id;
    }

    /**
     * @param string                $login   Login.
     * @param string                $email   Email.
     * @param array<string, string> $profile Names.
     * @return int
     */
    private function insert_or_reuse_user($login, $email, array $profile)
    {
        $existing = get_user_by('login', $login);
        if (! $existing instanceof \WP_User) {
            $existing = get_user_by('email', $email);
        }

        $payload = [
            'user_email'   => $email,
            'user_pass'    => Catalog::CUSTOMER_PASSWORD,
            'first_name'   => $profile['first_name'],
            'last_name'    => $profile['last_name'],
            'display_name' => trim($profile['first_name'] . ' ' . $profile['last_name']),
            'role'         => 'subscriber',
        ];

        if ($existing instanceof \WP_User) {
            $payload['ID'] = (int) $existing->ID;
            $updated       = wp_update_user($payload);

            return is_wp_error($updated) ? 0 : (int) $updated;
        }

        $payload['user_login'] = $login;
        $created               = wp_insert_user($payload);

        return is_wp_error($created) ? 0 : (int) $created;
    }

    /**
     * @param array<string, mixed> $args Post args.
     * @return int
     */
    private function insert_or_reuse_post(array $args)
    {
        $post_type = isset($args['post_type']) ? (string) $args['post_type'] : 'post';
        $slug      = isset($args['post_name']) ? (string) $args['post_name'] : '';
        if ('' !== $slug) {
            $existing = get_posts(
                [
                    'name'           => $slug,
                    'post_type'      => $post_type,
                    'post_status'    => 'any',
                    'posts_per_page' => 1,
                    'fields'         => 'ids',
                ]
            );
            if (! empty($existing[0])) {
                $args['ID'] = (int) $existing[0];
            }
        }

        $id = wp_insert_post($args, true);

        return is_wp_error($id) ? 0 : (int) $id;
    }

    /**
     * @param string               $type Type.
     * @param int                  $id   ID.
     * @param array<string, mixed> $meta Meta.
     * @return void
     */
    private function track($type, $id, array $meta = [])
    {
        $id = (int) $id;
        if ($id <= 0) {
            return;
        }

        $registry = $this->get_registry();
        if (! isset($registry[ $type ]) || ! is_array($registry[ $type ])) {
            $registry[ $type ] = [];
        }
        $registry[ $type ][ (string) $id ] = $meta;
        update_option(self::REGISTRY_OPTION, $registry, false);
    }

    /**
     * @return array<string, mixed>
     */
    private function get_registry()
    {
        $registry = get_option(self::REGISTRY_OPTION, []);

        return is_array($registry) ? $registry : [];
    }
}
