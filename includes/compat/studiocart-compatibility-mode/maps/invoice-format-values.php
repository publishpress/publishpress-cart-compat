<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical invoice-format option values mapped to leftover stored strings.
 *
 * Compatibility Mode maps leftover sc_* codes onto canonical ppcart_* codes
 * via ppcart_invoice_format. The stored option row is not rewritten.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_pns' => [ 'sc_pns' ],
    'ppcart_pn' => [ 'sc_pn' ],
    'ppcart_ns' => [ 'sc_ns' ],
    'ppcart_n' => [ 'sc_n' ],
];
