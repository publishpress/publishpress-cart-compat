<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Helper and service class aliases (canonical => legacy names).
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart_Helper'                      => [ 'NCS_Cart_Helper', 'NCS_Helper' ],
    'PPCart_Order_Helper'                => [ 'NCS_Cart_Order_Helper', 'NCS_Order_Helper' ],
    'PPCart_Stripe'                      => [ 'NCS_Cart_Stripe', 'NCS_Stripe' ],
    'PPCart_Price_Format'                => [ 'NCS_Cart_Price_Format', 'NCS_Price_Format' ],
    'PPCart_Elementor_OrderForm'         => [ 'NCS_Cart_Elementor_OrderForm', 'NCS_Elementor_OrderForm' ],
    'PPCart_Elementor_Countdown_Timer'   => [ 'NCS_Cart_Elementor_Countdown_Timer', 'NCS_Elementor_Countdown_Timer' ],
    'PPCart_Bump_Heading'                => [ 'NCS_Cart_Bump_Heading', 'SC_Bump_Heading' ],
    'PPCart_Bump_Text'                   => [ 'NCS_Cart_Bump_Text', 'SC_Bump_Text' ],
    'PPCart_Bump_Checkbox'               => [ 'NCS_Cart_Bump_Checkbox', 'SC_Bump_Checkbox' ],
    'PPCart_Bump_Image'                  => [ 'NCS_Cart_Bump_Image', 'SC_Bump_Image' ],
];
