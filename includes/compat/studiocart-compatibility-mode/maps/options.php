<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Option bridge exclusions and explicit legacy => canonical overrides.
 *
 * Most keys are derived by ncs_cart_legacy_to_canonical_option_name(). Entries
 * here are frozen (excluded) or non-rule mappings only.
 */
return [
    'exclude' => [
        // Leftover Cart-era toggle. Keep excluded so storage bridges do not
        // persist `_ppcart_studiocart_compatibility_mode` onto this leftover row.
        '_sc_studiocart_compatibility_mode',
    ],
    'override' => [
        '_my_account' => '_ppcart_myaccount_page_id',
    ],
];
