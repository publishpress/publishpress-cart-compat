<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Test fixture helper class aliases (canonical => legacy names).
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart_Fixtures'                 => [ 'NCS_Cart_Fixtures' ],
    'PPCart_Fixtures_Admin'           => [ 'NCS_Cart_Fixtures_Admin' ],
    'PPCart_Regression_Fixtures'      => [ 'NCS_Cart_Regression_Fixtures' ],
    'PPCart_Smoke_Fixtures'           => [ 'NCS_Cart_Smoke_Fixtures' ],
    'PPCart_Admin_Fixtures'           => [ 'NCS_Cart_Admin_Fixtures' ],
    'PPCart_Regression_PayPal_Mock'          => [ 'NCS_Cart_Regression_PayPal_Mock' ],
    'PPCart_Regression_Stripe_Mock'          => [ 'NCS_Cart_Regression_Stripe_Mock' ],
    'PPCart_Regression_Stripe_Http_Client'   => [ 'NCS_Cart_Regression_Stripe_Http_Client' ],
    'PPCart_Admin_Workflow_State'            => [ 'NCS_Cart_Admin_Workflow_State' ],
];
