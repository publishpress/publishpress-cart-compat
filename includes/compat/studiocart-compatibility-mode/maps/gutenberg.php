<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Gutenberg integration class aliases (canonical => legacy names).
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart_Gutenberg_Bootstrap'           => [ 'NCS_Cart_Gutenberg_Bootstrap' ],
    'PPCart_Gutenberg_Assets'              => [ 'NCS_Cart_Gutenberg_Assets' ],
    'PPCart_Checkout_Renderer'             => [ 'NCS_Cart_Checkout_Renderer' ],
    'PPCart_Checkout_Preview_Controller'   => [ 'NCS_Cart_Checkout_Preview_Controller' ],
    'PPCart_Account_Styles'                => [ 'NCS_Cart_Account_Styles' ],
    'PPCart_Account_Renderer'              => [ 'NCS_Cart_Account_Renderer' ],
    'PPCart_Account_Detail_Controller'     => [ 'NCS_Cart_Account_Detail_Controller' ],
    'PPCart_Account_Context'               => [ 'NCS_Cart_Account_Context' ],
    'PPCart_Product_Template'              => [ 'NCS_Cart_Product_Template' ],
];
