<?php

if (! defined('ABSPATH')) {
    exit;
}

require_once __DIR__ . '/aliases.php';
require_once __DIR__ . '/options.php';
require_once dirname(__DIR__) . '/option-key-migration/index.php';
require_once __DIR__ . '/hooks.php';
require_once __DIR__ . '/ajax.php';
require_once __DIR__ . '/request-fields.php';
require_once __DIR__ . '/cookies.php';
require_once __DIR__ . '/custom-tables.php';
require_once __DIR__ . '/pro-meta-keys.php';
require_once __DIR__ . '/stripe-metadata.php';
require_once __DIR__ . '/mollie-metadata.php';
require_once __DIR__ . '/razorpay-notes.php';
require_once __DIR__ . '/square-catalog.php';
require_once __DIR__ . '/memberpress-trans-num.php';
require_once __DIR__ . '/affiliate-meta.php';
require_once dirname(__DIR__) . '/third-party-key-migration/index.php';
require_once __DIR__ . '/integration-repeater-fields.php';
require_once __DIR__ . '/invoice-format.php';
require_once __DIR__ . '/tax-rate-inner-keys.php';
require_once __DIR__ . '/theme-templates.php';
require_once __DIR__ . '/cpt-taxonomy.php';
require_once __DIR__ . '/download-upload-directory.php';
require_once __DIR__ . '/shortcodes.php';
require_once __DIR__ . '/gutenberg-blocks.php';
require_once __DIR__ . '/rest-namespaces.php';
require_once __DIR__ . '/elementor-widgets.php';
require_once __DIR__ . '/divi-modules.php';
require_once __DIR__ . '/class-ppcartcomp-studiocart-compatibility-mode.php';
require_once __DIR__ . '/shortcode-detectors.php';
require_once __DIR__ . '/constants.php';
require_once __DIR__ . '/globals.php';
require_once __DIR__ . '/class-ppcartcomp-studiocart-public-query-compatibility.php';
require_once __DIR__ . '/class-ppcartcomp-studiocart-admin-page-compatibility.php';

PPCartComp_Studiocart_Compatibility_Mode::init();
