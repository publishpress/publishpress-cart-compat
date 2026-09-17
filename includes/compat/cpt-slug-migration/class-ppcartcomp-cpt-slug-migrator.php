<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Chunked CPT/taxonomy/capability migrator. Never runs unless asked.
 */
class PPCartComp_Cpt_Slug_Migrator
{
    use PPCartComp_Cpt_Slug_Migrator_Restore_Trait;

    public const CHUNK_SIZE = 500;
    public const TABLE_SUFFIX = 'ppcart_cpt_migration_backup';
    public const STEP1_LOCK = 'ppcart_cpt_slug_migration_step1_lock';

    /**
     * @return string
     */
    public static function table_name()
    {
        global $wpdb;

        return $wpdb->prefix . self::TABLE_SUFFIX;
    }

    /**
     * @return void
     */
    public static function maybe_create_table()
    {
        global $wpdb;

        $table           = self::table_name();
        $charset_collate = $wpdb->get_charset_collate();
        $sql             = "CREATE TABLE {$table} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			object_kind varchar(32) NOT NULL,
			object_id varchar(191) NOT NULL,
			old_value longtext NOT NULL,
			new_value varchar(191) NOT NULL,
			step tinyint(3) unsigned NOT NULL DEFAULT 1,
			migrated_at datetime NOT NULL,
			PRIMARY KEY  (id),
			KEY object_kind (object_kind),
			KEY step (step)
		) {$charset_collate};";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);
    }

    /**
     * Run one step-1 chunk. Returns progress payload.
     *
     * @return array<string, mixed>
     */
    public static function run_step1_chunk()
    {
        if (! current_user_can('manage_options')) {
            return [ 'ok' => false, 'error' => 'forbidden' ];
        }

        $state = ppcart_cpt_slug_migration_get_state();
        if ('complete' === $state['step1']) {
            return [ 'ok' => true, 'done' => true, 'step' => 1 ];
        }

        if (! empty($state['stop_requested']) && 'running' === $state['step1']) {
            ppcart_cpt_slug_migration_save_state(
                [
                    'step1'          => 'paused',
                    'stop_requested' => false,
                ]
            );

            return [ 'ok' => true, 'paused' => true, 'step' => 1 ];
        }

        if (! self::acquire_step1_lock()) {
            return [ 'ok' => false, 'error' => 'busy' ];
        }

        try {
            return self::run_step1_chunk_body();
        } finally {
            self::release_step1_lock();
        }
    }

    /**
     * @return array<string, mixed>
     */
    private static function run_step1_chunk_body()
    {
        self::maybe_create_table();
        ppcart_cpt_slug_migration_save_state([ 'step1' => 'running', 'mode' => 'legacy' ]);

        $remaining = self::count_step1_remaining();
        if (0 === $remaining['posts'] && 0 === $remaining['terms'] && 0 === $remaining['templates'] && 0 === $remaining['caps']) {
            ppcart_cpt_slug_migration_save_state(
                [
                    'step1'  => 'complete',
                    'cursor' => null,
                ]
            );
            if (function_exists('flush_rewrite_rules')) {
                flush_rewrite_rules(false);
            }

            return [ 'ok' => true, 'done' => true, 'step' => 1, 'remaining' => $remaining ];
        }

        if ($remaining['posts'] > 0) {
            self::migrate_post_chunk();
        } elseif ($remaining['terms'] > 0) {
            self::migrate_term_chunk();
        } elseif ($remaining['templates'] > 0) {
            self::migrate_template_chunk();
        } else {
            self::migrate_capabilities();
        }

        $remaining = self::count_step1_remaining();
        $done      = ( 0 === $remaining['posts'] && 0 === $remaining['terms'] && 0 === $remaining['templates'] && 0 === $remaining['caps'] );

        if ($done) {
            ppcart_cpt_slug_migration_save_state(
                [
                    'step1'  => 'complete',
                    'cursor' => null,
                ]
            );
            if (function_exists('flush_rewrite_rules')) {
                flush_rewrite_rules(false);
            }
        }

        return [
            'ok'        => true,
            'done'      => $done,
            'step'      => 1,
            'remaining' => $remaining,
        ];
    }

    /**
     * @return bool
     */
    private static function acquire_step1_lock()
    {
        if (function_exists('get_transient') && get_transient(self::STEP1_LOCK)) {
            return false;
        }

        if (function_exists('set_transient')) {
            set_transient(self::STEP1_LOCK, 1, 60);
        }

        return true;
    }

    /**
     * @return void
     */
    private static function release_step1_lock()
    {
        if (function_exists('delete_transient')) {
            delete_transient(self::STEP1_LOCK);
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function run_step2()
    {
        if (! current_user_can('manage_options')) {
            return [ 'ok' => false, 'error' => 'forbidden' ];
        }

        if (! ppcart_cpt_slug_migration_step1_complete()) {
            return [ 'ok' => false, 'error' => 'step1_incomplete' ];
        }

        $state = ppcart_cpt_slug_migration_get_state();
        if ('complete' === $state['step2']) {
            return [ 'ok' => true, 'done' => true, 'step' => 2 ];
        }

        self::maybe_create_table();
        self::migrate_roles();
        ppcart_cpt_slug_migration_save_state([ 'step2' => 'complete' ]);

        return [ 'ok' => true, 'done' => true, 'step' => 2 ];
    }

    /**
     * @return array{posts: int, terms: int, templates: int, caps: int}
     */
    public static function count_step1_remaining()
    {
        global $wpdb;

        $maps       = ppcart_cpt_slug_migration_maps();
        $post_types = [];
        foreach (ppcart_cpt_slug_migration_all_post_type_pairs() as $pair) {
            $post_types[] = $pair['legacy'];
        }
        $placeholders = implode(',', array_fill(0, count($post_types), '%s'));
        $posts        = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type IN ($placeholders)", $post_types));

        $taxonomies       = [];
        foreach ($maps['taxonomies'] as $pair) {
            $taxonomies[] = $pair['legacy'];
        }
        $tax_placeholders = implode(',', array_fill(0, count($taxonomies), '%s'));
        $terms            = (int) $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM {$wpdb->term_taxonomy} WHERE taxonomy IN ($tax_placeholders)", $taxonomies));

        $templates = (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s",
                'wp_template',
                'single-sc_product'
            )
        );

        $caps = self::roles_needing_cap_swap() ? 1 : 0;

        return [
            'posts'     => $posts,
            'terms'     => $terms,
            'templates' => $templates,
            'caps'      => $caps,
        ];
    }

    /**
     * @return void
     */
    private static function migrate_post_chunk()
    {
        global $wpdb;

        foreach (ppcart_cpt_slug_migration_all_post_type_pairs() as $pair) {
            $ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s ORDER BY ID ASC LIMIT %d",
                    $pair['legacy'],
                    self::CHUNK_SIZE
                )
            );
            if (! is_array($ids) || [] === $ids) {
                continue;
            }

            foreach ($ids as $id) {
                self::backup_and_update_post((int) $id, $pair['legacy'], $pair['canonical']);
            }

            return;
        }
    }

    /**
     * @param int    $id         Post ID.
     * @param string $legacy     Old slug.
     * @param string $canonical  New slug.
     * @return void
     */
    private static function backup_and_update_post($id, $legacy, $canonical)
    {
        global $wpdb;

        self::insert_backup('post', (string) $id, $legacy, $canonical, 1);
        $wpdb->update($wpdb->posts, [ 'post_type' => $canonical ], [ 'ID' => $id ], [ '%s' ], [ '%d' ]);
        if (function_exists('clean_post_cache')) {
            clean_post_cache($id);
        }
    }

    /**
     * @return void
     */
    private static function migrate_term_chunk()
    {
        global $wpdb;

        $maps = ppcart_cpt_slug_migration_maps();
        foreach ($maps['taxonomies'] as $pair) {
            $ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT term_taxonomy_id FROM {$wpdb->term_taxonomy} WHERE taxonomy = %s ORDER BY term_taxonomy_id ASC LIMIT %d",
                    $pair['legacy'],
                    self::CHUNK_SIZE
                )
            );
            if (! is_array($ids) || [] === $ids) {
                continue;
            }

            foreach ($ids as $id) {
                self::insert_backup('term_taxonomy', (string) $id, $pair['legacy'], $pair['canonical'], 1);
                $wpdb->update(
                    $wpdb->term_taxonomy,
                    [ 'taxonomy' => $pair['canonical'] ],
                    [ 'term_taxonomy_id' => (int) $id ],
                    [ '%s' ],
                    [ '%d' ]
                );
                if (function_exists('clean_term_cache')) {
                    clean_term_cache((int) $id, '', false);
                }
            }

            return;
        }
    }

    /**
     * @return void
     */
    private static function migrate_template_chunk()
    {
        global $wpdb;

        $ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_type = %s AND post_name = %s",
                'wp_template',
                'single-sc_product'
            )
        );
        if (! is_array($ids)) {
            return;
        }

        foreach ($ids as $id) {
            self::insert_backup('template', (string) $id, 'single-sc_product', 'single-ppcart_product', 1);
            $wpdb->update($wpdb->posts, [ 'post_name' => 'single-ppcart_product' ], [ 'ID' => (int) $id ], [ '%s' ], [ '%d' ]);
            if (function_exists('clean_post_cache')) {
                clean_post_cache((int) $id);
            }
        }
    }

    /**
     * @return void
     */
    private static function migrate_capabilities()
    {
        $maps = ppcart_cpt_slug_migration_maps();
        foreach (self::roles_needing_cap_swap() as $role_name) {
            $role = get_role($role_name);
            if (! $role) {
                continue;
            }
            foreach ($maps['capabilities'] as $legacy => $canonical) {
                if (! $role->has_cap($legacy)) {
                    continue;
                }
                self::insert_backup('role_cap', $role_name, $legacy, $canonical, 1);
                $role->add_cap($canonical);
                $role->remove_cap($legacy);
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private static function roles_needing_cap_swap()
    {
        $maps  = ppcart_cpt_slug_migration_maps();
        $names = [];
        $roles = wp_roles();
        if (! $roles || empty($roles->role_objects)) {
            return [];
        }

        foreach ($roles->role_objects as $name => $role) {
            foreach ($maps['capabilities'] as $legacy => $canonical) {
                if ($role->has_cap($legacy) && ! $role->has_cap($canonical)) {
                    $names[] = (string) $name;
                    break;
                }
            }
        }

        return $names;
    }

    /**
     * @return void
     */
    private static function migrate_roles()
    {
        $maps = ppcart_cpt_slug_migration_maps();
        foreach ($maps['roles'] as $legacy => $canonical) {
            $old_role = get_role($legacy);
            if (! $old_role) {
                continue;
            }

            $caps = $old_role->capabilities;
            self::insert_backup(
                'role',
                $legacy,
                wp_json_encode(
                    [
                        'label' => $old_role->name,
                        'caps'  => $caps,
                    ]
                ),
                $canonical,
                2
            );
            add_role($canonical, $old_role->name, $caps);

            do {
                $users = get_users(
                    [
                        'role'   => $legacy,
                        'fields' => 'ID',
                        'number' => self::CHUNK_SIZE,
                    ]
                );
                if (! is_array($users) || [] === $users) {
                    break;
                }
                foreach ($users as $user_id) {
                    $user_id = (int) $user_id;
                    self::insert_backup('user_role', (string) $user_id, $legacy, $canonical, 2);
                    $user = get_user_by('id', $user_id);
                    if ($user) {
                        $user->add_role($canonical);
                        $user->remove_role($legacy);
                    }
                }
            } while (true);

            remove_role($legacy);
        }
    }

    /**
     * @param string $kind      Kind.
     * @param string $object_id Object id.
     * @param string $old       Old value.
     * @param string $new       New value.
     * @param int    $step      Step.
     * @return void
     */
    private static function insert_backup($kind, $object_id, $old, $new, $step)
    {
        global $wpdb;

        $wpdb->insert(
            self::table_name(),
            [
                'object_kind' => $kind,
                'object_id'   => $object_id,
                'old_value'   => $old,
                'new_value'   => $new,
                'step'        => $step,
                'migrated_at' => current_time('mysql'),
            ],
            [ '%s', '%s', '%s', '%s', '%d', '%s' ]
        );
    }
}
