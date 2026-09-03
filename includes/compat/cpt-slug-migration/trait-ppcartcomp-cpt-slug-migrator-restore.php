<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Reverse-map restore and backup-table teardown for the CPT slug migrator.
 */
trait PPCartComp_Cpt_Slug_Migrator_Restore_Trait
{
    /**
     * @return array<string, mixed>
     */
    public static function restore_step1()
    {
        if (! current_user_can('manage_options')) {
            return [ 'ok' => false, 'error' => 'forbidden' ];
        }

        self::maybe_create_table();
        self::restore_step_rows(1);

        ppcart_cpt_slug_migration_save_state(
            [
                'step1'          => 'idle',
                'mode'           => 'legacy',
                'stop_requested' => false,
                'cursor'         => null,
            ]
        );

        if (function_exists('flush_rewrite_rules')) {
            flush_rewrite_rules(false);
        }

        return [ 'ok' => true ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function restore_step2()
    {
        if (! current_user_can('manage_options')) {
            return [ 'ok' => false, 'error' => 'forbidden' ];
        }

        self::maybe_create_table();
        self::restore_step_rows(2, 'role');
        self::restore_step_rows(2);

        ppcart_cpt_slug_migration_save_state([ 'step2' => 'idle' ]);

        return [ 'ok' => true ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function delete_backup()
    {
        if (! current_user_can('manage_options')) {
            return [ 'ok' => false, 'error' => 'forbidden' ];
        }

        global $wpdb;

        $wpdb->query('DROP TABLE IF EXISTS ' . self::table_name());

        return [ 'ok' => true ];
    }

    /**
     * @param int    $step Step.
     * @param string $kind Optional object_kind filter.
     * @return void
     */
    private static function restore_step_rows($step, $kind = '')
    {
        global $wpdb;

        $table = self::table_name();

        do {
            if ('' !== $kind) {
                $rows = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$table} WHERE step = %d AND object_kind = %s ORDER BY id ASC LIMIT %d",
                        $step,
                        $kind,
                        self::CHUNK_SIZE
                    )
                );
            } else {
                $rows = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM {$table} WHERE step = %d ORDER BY id ASC LIMIT %d",
                        $step,
                        self::CHUNK_SIZE
                    )
                );
            }

            if (! is_array($rows) || [] === $rows) {
                break;
            }

            $ids = [];
            foreach ($rows as $row) {
                self::restore_backup_row($row);
                $ids[] = (int) $row->id;
            }

            $ids = array_filter($ids);
            if ($ids) {
                $placeholders = implode(',', array_fill(0, count($ids), '%d'));
                $wpdb->query($wpdb->prepare("DELETE FROM {$table} WHERE id IN ($placeholders)", $ids));
            }
        } while (true);
    }

    /**
     * @param object $row Backup row.
     * @return void
     */
    private static function restore_backup_row($row)
    {
        global $wpdb;

        $kind = (string) $row->object_kind;
        $id   = $row->object_id;
        $old  = (string) $row->old_value;

        if ('post' === $kind) {
            $wpdb->update($wpdb->posts, [ 'post_type' => $old ], [ 'ID' => (int) $id ], [ '%s' ], [ '%d' ]);
            if (function_exists('clean_post_cache')) {
                clean_post_cache((int) $id);
            }
            return;
        }

        if ('term_taxonomy' === $kind) {
            $wpdb->update($wpdb->term_taxonomy, [ 'taxonomy' => $old ], [ 'term_taxonomy_id' => (int) $id ], [ '%s' ], [ '%d' ]);
            if (function_exists('clean_term_cache')) {
                clean_term_cache((int) $id, '', false);
            }
            return;
        }

        if ('template' === $kind) {
            $wpdb->update($wpdb->posts, [ 'post_name' => $old ], [ 'ID' => (int) $id ], [ '%s' ], [ '%d' ]);
            if (function_exists('clean_post_cache')) {
                clean_post_cache((int) $id);
            }
            return;
        }

        if ('role_cap' === $kind) {
            $role = get_role((string) $id);
            if ($role) {
                $role->remove_cap((string) $row->new_value);
                $role->add_cap($old);
            }
            return;
        }

        if ('role' === $kind) {
            if (get_role((string) $id)) {
                return;
            }
            $data  = json_decode($old, true);
            $label = (string) $id;
            $caps  = [];
            if (is_array($data)) {
                $label = isset($data['label']) ? (string) $data['label'] : $label;
                $caps  = isset($data['caps']) && is_array($data['caps']) ? $data['caps'] : [];
            }
            add_role((string) $id, $label, $caps);
            return;
        }

        if ('user_role' === $kind) {
            $user = get_user_by('id', (int) $id);
            if ($user) {
                $user->remove_role((string) $row->new_value);
                $user->add_role($old);
            }
        }
    }
}
