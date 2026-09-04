<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ppcartcomp_maybe_drop_migration_backups_for_blog')) {
    /**
     * Drop this plugin's reverse-map backup tables when the merchant opted in.
     *
     * No-op when the flag is unset, '0', or any value other than string '1'.
     * Does not touch Cart store tables, leftover StudioCart rows, Compatibility
     * Mode, or wizard migration-state options.
     *
     * @return void
     */
    function ppcartcomp_maybe_drop_migration_backups_for_blog()
    {
        if ('1' !== (string) get_option('ppcartcomp_drop_migration_backups_on_uninstall', '0')) {
            return;
        }

        global $wpdb;

        $suffixes = [
            'ppcart_cpt_migration_backup',
            'ppcart_meta_key_migration_backup',
            'ppcart_shortcode_tag_migration_backup',
        ];

        foreach ($suffixes as $suffix) {
            $table = $wpdb->prefix . $suffix;
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange, WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Opt-in uninstall drop of plugin-owned reverse-map backup tables.
            $wpdb->query('DROP TABLE IF EXISTS ' . $table);
        }

        delete_option('ppcartcomp_drop_migration_backups_on_uninstall');
    }
}
