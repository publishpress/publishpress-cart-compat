<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Admin screen class aliases (canonical => legacy names).
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart_Admin'                          => [ 'NCS_Cart_Admin' ],
    'PPCart_Admin_Ajax'                     => [ 'NCS_Cart_Admin_Ajax' ],
    'PPCart_Admin_Filters'                  => [ 'NCS_Cart_Admin_Filters' ],
    'PPCart_Admin_Settings'                 => [ 'NCS_Cart_Admin_Settings' ],
    'PPCart_Admin_Stripe_Connect_Settings'  => [ 'NCS_Cart_Admin_Stripe_Connect_Settings' ],
    'PPCart_Admin_Stripe_Webhook_Settings'  => [ 'NCS_Cart_Admin_Stripe_Webhook_Settings' ],
    'PPCart_Admin_Reports'                  => [ 'NCS_Cart_Admin_Reports' ],
    'PPCart_Customer_Reports'               => [ 'NCS_Cart_Customer_Reports' ],
    'PPCart_Contacts_Page'                  => [ 'NCS_Cart_Contacts_Page' ],
    'PPCart_Extension_Page'                 => [ 'NCS_Cart_Extension_Page' ],
    'PPCart_Order_Admin'                    => [ 'NCS_Cart_Order_Admin' ],
    'PPCart_Product_Admin'                  => [ 'NCS_Cart_Product_Admin' ],
    'PPCart_Product_Metaboxes'              => [ 'NCS_Cart_Product_Metaboxes' ],
    'PPCart_Product_Metabox_Option_Sources' => [ 'NCS_Cart_Product_Metabox_Option_Sources' ],
    'PPCart_Order_Metaboxes'                => [ 'NCS_Cart_Order_Metaboxes' ],
];
