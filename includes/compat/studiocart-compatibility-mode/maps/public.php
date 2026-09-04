<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Public-facing class aliases (canonical => legacy names).
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart_Public'                                  => [ 'NCS_Cart_Public' ],
    'PPCart_Paypal'                                  => [ 'NCS_Cart_Paypal' ],
    'PPCart_Public_Account_Controller'               => [ 'NCS_Cart_Public_Account_Controller' ],
    'PPCart_Public_Asset_Controller'                 => [ 'NCS_Cart_Public_Asset_Controller' ],
    'PPCart_Public_Checkout_Controller'              => [ 'NCS_Cart_Public_Checkout_Controller' ],
    'PPCart_Public_Hosted_Checkout_Controller'       => [ 'NCS_Cart_Public_Hosted_Checkout_Controller' ],
    'PPCart_Public_Order_Controller'                 => [ 'NCS_Cart_Public_Order_Controller' ],
    'PPCart_Public_Page_Controller'                  => [ 'NCS_Cart_Public_Page_Controller' ],
    'PPCart_Public_Payment_Controller'               => [ 'NCS_Cart_Public_Payment_Controller' ],
    'PPCart_Public_Subscription_Checkout_Controller' => [ 'NCS_Cart_Public_Subscription_Checkout_Controller' ],
];
