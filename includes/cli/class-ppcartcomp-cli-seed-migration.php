<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Dev/QA helper: seed leftover StudioCart rows for the Data migration wizard.
 *
 * @package PPCartComp
 */
class PPCartComp_CLI_Seed_Migration
{
    public const ROLE_CAPS = 'ppcartcomp_seed_mig_caps';

    /**
     * Seed leftover StudioCart data so Cart → StudioCart Data migration has work.
     *
     * ## OPTIONS
     *
     * [--count=<n>]
     * : How many leftover posts per CPT (and meta rows scale with this).
     * ---
     * default: 3
     * ---
     *
     * [--skip-tables]
     * : Do not create leftover ncs_* custom tables.
     *
     * ## EXAMPLES
     *
     *     wp ppcartcomp seed-migration
     *     wp ppcartcomp seed-migration --count=10
     *     ddev seed-migration
     *
     * @when after_wp_load
     *
     * @param array<int, string>    $args       Positional.
     * @param array<string, mixed>  $assoc_args Flags.
     * @return void
     */
    public function __invoke($args, $assoc_args)
    {
        if (! defined('WP_CLI') || ! WP_CLI) {
            return;
        }

        if (! function_exists('ppcartcomp_cart_is_loaded') || ! ppcartcomp_cart_is_loaded()) {
            WP_CLI::error('PublishPress Cart must be active before seeding migration data.');
        }

        if (! function_exists('ppcart_cpt_slug_migration_save_state')) {
            WP_CLI::error('CPT slug migration package is not loaded. Activate PublishPress Cart Compatibility with Cart.');
        }

        $count = isset($assoc_args['count']) ? max(1, absint($assoc_args['count'])) : 3;
        $skip_tables = isset($assoc_args['skip-tables']);

        $summary = [
            'posts'      => 0,
            'terms'      => 0,
            'templates'  => 0,
            'roles'      => 0,
            'users'      => 0,
            'postmeta'   => 0,
            'usermeta'   => 0,
            'shortcodes' => 0,
            'tables'     => 0,
        ];

        $this->reset_migration_state();

        $product_ids = $this->seed_cpt_posts($count, $summary);
        $this->seed_taxonomies($summary);
        $this->seed_template($summary);
        $this->seed_roles_and_caps($summary);
        $this->seed_meta($product_ids, $count, $summary);
        $this->seed_shortcodes($product_ids, $summary);

        if (! $skip_tables) {
            $this->seed_custom_tables($summary);
        }

        WP_CLI::success(
            sprintf(
                'Seeded migration leftovers: %d posts, %d terms, %d templates, %d roles, %d users, %d postmeta, %d usermeta, %d shortcode surfaces, %d custom tables. Open Cart → StudioCart → Start migration.',
                $summary['posts'],
                $summary['terms'],
                $summary['templates'],
                $summary['roles'],
                $summary['users'],
                $summary['postmeta'],
                $summary['usermeta'],
                $summary['shortcodes'],
                $summary['tables']
            )
        );
    }

    /**
     * @return void
     */
    private function reset_migration_state()
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

        if (function_exists('ppcart_meta_key_migration_save_state')) {
            ppcart_meta_key_migration_save_state(
                [
                    'ack'            => '',
                    'mode'           => 'legacy',
                    'step1'          => 'idle',
                    'stop_requested' => false,
                    'cursor'         => null,
                ]
            );
        }

        if (function_exists('ppcart_shortcode_tag_migration_save_state')) {
            ppcart_shortcode_tag_migration_save_state(
                [
                    'ack'            => '',
                    'mode'           => 'legacy',
                    'step1'          => 'idle',
                    'stop_requested' => false,
                    'cursor'         => null,
                ]
            );
        }

        if (function_exists('ppcart_custom_table_migration_option')) {
            delete_option(ppcart_custom_table_migration_option());
        }

        if (function_exists('ppcart_flush_live_table_cache')) {
            ppcart_flush_live_table_cache();
        }
    }

    /**
     * @param int                  $count   Count.
     * @param array<string, int>   $summary Summary.
     * @return array<int, int>
     */
    private function seed_cpt_posts($count, array &$summary)
    {
        $types = [ 'sc_product', 'sc_order', 'sc_subscription' ];
        $ids   = [];

        foreach ($types as $type) {
            for ($i = 1; $i <= $count; $i++) {
                $id = (int) wp_insert_post(
                    [
                        'post_title'  => sprintf('Seed %s #%d', $type, $i),
                        'post_type'   => $type,
                        'post_status' => 'publish',
                    ],
                    true
                );
                if ($id > 0) {
                    $ids[] = $id;
                    $summary['posts']++;
                }
            }
        }

        return $ids;
    }

    /**
     * @param array<string, int> $summary Summary.
     * @return void
     */
    private function seed_taxonomies(array &$summary)
    {
        foreach ([ 'sc_product_cat' => 'Seed Category', 'sc_product_tag' => 'Seed Tag' ] as $taxonomy => $name) {
            $this->ensure_taxonomy($taxonomy);
            $result = wp_insert_term($name . ' ' . wp_generate_password(4, false), $taxonomy);
            if (! is_wp_error($result)) {
                $summary['terms']++;
            }
        }
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

        register_taxonomy(
            $taxonomy,
            [ 'sc_product', 'ppcart_product' ],
            [ 'public' => false ]
        );
    }

    /**
     * @param array<string, int> $summary Summary.
     * @return void
     */
    private function seed_template(array &$summary)
    {
        $id = (int) wp_insert_post(
            [
                'post_title'  => 'Single leftover product',
                'post_name'   => 'single-sc_product',
                'post_type'   => 'wp_template',
                'post_status' => 'publish',
            ],
            true
        );
        if ($id > 0) {
            $summary['templates']++;
        }
    }

    /**
     * @param array<string, int> $summary Summary.
     * @return void
     */
    private function seed_roles_and_caps(array &$summary)
    {
        remove_role(self::ROLE_CAPS);
        add_role(self::ROLE_CAPS, 'Compat seed caps', [ 'read' => true ]);
        $role = get_role(self::ROLE_CAPS);
        if ($role) {
            $role->add_cap('sc_manager_option');
            $role->add_cap('sc_manage_orders');
            $summary['roles']++;
        }

        foreach ([ 'sc_cart_manager' => 'Cart Manager (seed)', 'sc_cart_administrator' => 'Cart Administrator (seed)' ] as $slug => $label) {
            if (! get_role($slug)) {
                add_role($slug, $label, [ 'read' => true ]);
                $summary['roles']++;
            }
            $user_id = (int) wp_insert_user(
                [
                    'user_login' => $slug . '_' . wp_generate_password(6, false),
                    'user_pass'  => wp_generate_password(16),
                    'user_email' => $slug . '_' . wp_generate_password(6, false) . '@example.test',
                    'role'       => $slug,
                ]
            );
            if ($user_id > 0 && ! is_wp_error($user_id)) {
                $summary['users']++;
            }
        }
    }

    /**
     * @param array<int, int>    $product_ids Post IDs.
     * @param int                $count       Count.
     * @param array<string, int> $summary     Summary.
     * @return void
     */
    private function seed_meta(array $product_ids, $count, array &$summary)
    {
        $keys = [ '_sc_amount', '_sc_product_id', '_sc_status', '_sc_customer_email' ];

        foreach (array_slice($product_ids, 0, max(1, $count)) as $post_id) {
            foreach ($keys as $key) {
                add_post_meta($post_id, $key, 'seed-' . wp_generate_password(4, false), false);
                $summary['postmeta']++;
            }
        }

        $user_id = (int) get_current_user_id();
        if ($user_id <= 0) {
            $admins = get_users([ 'role' => 'administrator', 'number' => 1, 'fields' => 'ID' ]);
            $user_id = ! empty($admins[0]) ? (int) $admins[0] : 0;
        }
        if ($user_id > 0) {
            foreach ([ '_sc_customer_id', '_sc_stripe_customer_id' ] as $key) {
                update_user_meta($user_id, $key, 'seed-' . $user_id);
                $summary['usermeta']++;
            }
        }
    }

    /**
     * @param array<int, int>    $product_ids Post IDs.
     * @param array<string, int> $summary     Summary.
     * @return void
     */
    private function seed_shortcodes(array $product_ids, array &$summary)
    {
        $legacy = 'studiocart-form';
        if (function_exists('ppcart_shortcode_tag_migration_pairs')) {
            $pairs = ppcart_shortcode_tag_migration_pairs();
            if (! empty($pairs) && is_array($pairs)) {
                $legacy = (string) array_key_first($pairs);
            }
        }

        $content = sprintf('[%1$s id="1"] Seeded leftover shortcode [%1$s]', $legacy);
        $post_id = ! empty($product_ids[0]) ? (int) $product_ids[0] : 0;
        if ($post_id > 0) {
            wp_update_post(
                [
                    'ID'           => $post_id,
                    'post_content' => $content,
                ]
            );
            $summary['shortcodes']++;
        }

        update_option('_sc_email_confirmation_body', 'Thanks ' . $content, false);
        $summary['shortcodes']++;

        if ($post_id > 0) {
            update_post_meta(
                $post_id,
                '_sc_notifications',
                [
                    [
                        'body' => $content,
                    ],
                ]
            );
            $summary['shortcodes']++;
        }
    }

    /**
     * @param array<string, int> $summary Summary.
     * @return void
     */
    private function seed_custom_tables(array &$summary)
    {
        global $wpdb;

        if (! function_exists('ppcart_custom_table_family_map') || ! isset($wpdb->prefix)) {
            WP_CLI::warning('Custom table helpers unavailable; skipped tables.');

            return;
        }

        foreach (array_keys(ppcart_custom_table_family_map()) as $family) {
            $names = function_exists('ppcart_custom_table_prefixed_names')
                ? ppcart_custom_table_prefixed_names($family)
                : null;
            if (! $names || empty($names['leftover'])) {
                continue;
            }

            $leftover = $names['leftover'];
            $canonical = $names['canonical'];

            if (function_exists('ppcart_custom_table_exists') && ppcart_custom_table_exists($leftover)) {
                $summary['tables']++;
                continue;
            }

            if (function_exists('ppcart_custom_table_exists') && ppcart_custom_table_exists($canonical)) {
                WP_CLI::warning(sprintf('Skipping %s: canonical %s already exists.', $leftover, $canonical));
                continue;
            }

            $quoted = '`' . str_replace('`', '``', $leftover) . '`';
            $sql    = "CREATE TABLE IF NOT EXISTS {$quoted} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                seed_note varchar(191) NOT NULL DEFAULT '',
                PRIMARY KEY (id)
            ) {$wpdb->get_charset_collate()}";

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange,WordPress.DB.PreparedSQL.NotPrepared -- Dev seed of plugin-owned leftover table names.
            $ok = $wpdb->query($sql);
            if (false === $ok) {
                WP_CLI::warning(sprintf('Could not create %s: %s', $leftover, $wpdb->last_error));
                continue;
            }

            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Seed row.
            $wpdb->insert($leftover, [ 'seed_note' => 'ppcartcomp seed-migration' ]);
            $summary['tables']++;
        }

        if (function_exists('ppcart_flush_live_table_cache')) {
            ppcart_flush_live_table_cache();
        }
    }
}

if (defined('WP_CLI') && WP_CLI && class_exists('WP_CLI', false)) {
    WP_CLI::add_command('ppcartcomp seed-migration', 'PPCartComp_CLI_Seed_Migration');
}
