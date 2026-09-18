<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical cookie names mapped to leftover names.
 *
 * Compatibility Mode copies leftover cookies onto canonical keys in $_COOKIE
 * (and a matching document.cookie write). Leftover cookies stay in place.
 * No row migrator.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_affiliate_id' => [ 'sc_affiliate_id', 'affiliate_id' ],
];
