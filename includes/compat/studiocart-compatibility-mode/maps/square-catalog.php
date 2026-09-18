<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical Square catalog plan-id prefix mapped to leftover StudioCart prefix.
 *
 * Compatibility Mode leftover lookup returns `#sc_plan_` for `ppcart-plan-`.
 * First-party writes stay `#ppcart-plan-`. This is not a class-alias map.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart-plan-' => [ '#sc_plan_' ],
];
