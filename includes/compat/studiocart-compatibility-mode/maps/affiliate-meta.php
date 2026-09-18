<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical Pro affiliate meta keys mapped to leftover StudioCart names.
 *
 * Always-on storage read-through and Compatibility Mode leftover API use this
 * map. Stored leftover rows stay until merchant Data migration.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_submission_id' => [ 'sc_submission_id' ],
];
