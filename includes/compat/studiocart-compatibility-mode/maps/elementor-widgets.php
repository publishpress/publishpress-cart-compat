<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Canonical Elementor widget name => leftover StudioCart widget name.
 *
 * Compatibility Mode dual-registers leftover names onto Pro widgets.
 * Control-key rewrite (sc_countdown_* / sc_template) is a separate helper.
 *
 * @return array<string, string>
 */
return [
    'ppcart-countdown'     => 'sc-countdown',
    'ppcart-orderform'     => 'sc-orderform',
    'ppcart-bump-heading'  => 'bump-heading',
    'ppcart-bump-checkbox' => 'bump-checkbox',
    'ppcart-bump-text'     => 'bump-text',
    'ppcart-bump-image'    => 'bump-image',
];
