<?php

if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

require_once __DIR__ . '/includes/ppcartcomp-drop-migration-backups.php';

if (is_multisite()) {
    $site_ids = get_sites(
        [
            'fields' => 'ids',
            'number' => 0,
        ]
    );

    foreach ($site_ids as $site_id) {
        switch_to_blog((int) $site_id);
        ppcartcomp_maybe_drop_migration_backups_for_blog();
        restore_current_blog();
    }

    return;
}

ppcartcomp_maybe_drop_migration_backups_for_blog();
