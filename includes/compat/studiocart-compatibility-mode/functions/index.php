<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart function wrapper catalog.
 *
 * Loaded only when Compatibility Mode is on
 * (`PPCartComp_Studiocart_Compatibility_Mode::maybe_register_functions()`).
 *
 * Prefixes: `ncs_*`, `sc_*` (plus a few `ncs_cart_*` / `studiocart_*` names in
 * the shards). Each wrapper is a one-line delegate to the matching `ppcart_*`
 * helper. Do not add PHPDoc or logic on individual wrappers — change the
 * canonical function instead.
 *
 * Shards: ncs.php, sc-integrations.php, sc-cart.php, sc-checkout.php,
 * sc-do.php, sc-email.php, sc-format.php, sc-get.php, sc-is.php,
 * sc-order.php, sc-misc-a.php, sc-misc-b.php.
 */

require_once __DIR__ . '/ncs.php';
require_once __DIR__ . '/sc-integrations.php';
require_once __DIR__ . '/sc-cart.php';
require_once __DIR__ . '/sc-checkout.php';
require_once __DIR__ . '/sc-do.php';
require_once __DIR__ . '/sc-email.php';
require_once __DIR__ . '/sc-format.php';
require_once __DIR__ . '/sc-get.php';
require_once __DIR__ . '/sc-is.php';
require_once __DIR__ . '/sc-order.php';
require_once __DIR__ . '/sc-misc-a.php';
require_once __DIR__ . '/sc-misc-b.php';
