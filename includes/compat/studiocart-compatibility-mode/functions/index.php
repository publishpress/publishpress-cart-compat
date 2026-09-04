<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Loads StudioCart function wrapper shards when Compatibility Mode is on.
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
