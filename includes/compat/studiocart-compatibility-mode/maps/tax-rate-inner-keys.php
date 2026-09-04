<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical tax-rate option inner keys mapped to leftover stored keys.
 *
 * Compatibility Mode copies leftover _sc_tax_rate_* keys onto canonical
 * _ppcart_tax_rate_* keys via ppcart_tax_rates. The stored option row is
 * not rewritten.
 *
 * @return array<string, array<int, string>>
 */
return [
    '_ppcart_tax_rate_title' => [ '_sc_tax_rate_title' ],
    '_ppcart_tax_rate_slug' => [ '_sc_tax_rate_slug' ],
    '_ppcart_tax_rate' => [ '_sc_tax_rate' ],
];
