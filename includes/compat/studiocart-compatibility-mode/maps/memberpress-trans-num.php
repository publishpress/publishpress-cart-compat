<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical MemberPress trans_num prefix mapped to leftover StudioCart names.
 *
 * Compatibility Mode leftover lookup only. First-party writes stay canonical.
 * Stored leftover rows stay until merchant Data migration.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart-txn-' => [ 'sc-txn-' ],
];
