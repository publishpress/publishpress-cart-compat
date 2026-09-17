<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Legacy wp-admin ?page= slugs mapped to canonical Cart slugs.
 *
 * @return array<string, string> legacy slug => canonical slug
 */
return [
    'sc-admin'                        => 'ppcart-settings',
    'sc-white-label'                  => 'ppcart-white-label',
    'sc-affiliates'                   => 'ppcart-affiliates',
    'sc_affiliate_dashboard_callback' => 'ppcart-affiliates',
    'ppcart_affiliate_dashboard'      => 'ppcart-affiliates',
    'studiocart'                      => 'ppcart',
    'studiocart-account'              => 'ppcart-account',
    'studiocart-affiliation'          => 'ppcart-affiliation',
];
