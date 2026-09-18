<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical Pro meta keys that do not follow `_sc_*` → `_ppcart_*`.
 *
 * Compatibility Mode leftover API and always-on storage read-through use this
 * map. Stored leftover rows stay in place until merchant Data migration.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_custom_meta' => [ 'sc_custom_meta' ],
    'ppcart_sq_cc_id' => [ 'sc_sq_cc_id' ],
];
