<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Admin controller and dashboard class aliases (canonical => legacy names).
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart_Admin_Order_Controller'           => [ 'NCS_Cart_Admin_Order_Controller' ],
    'PPCart_Admin_Order_List_Controller'      => [ 'NCS_Cart_Admin_Order_List_Controller' ],
    'PPCart_Admin_Order_Refund_Controller'    => [ 'NCS_Cart_Admin_Order_Refund_Controller' ],
    'PPCart_Admin_Subscription_Controller'    => [ 'NCS_Cart_Admin_Subscription_Controller' ],
    'PPCart_Admin_Test_Mode_Notice_Controller' => [ 'NCS_Cart_Admin_Test_Mode_Notice_Controller' ],
    'PPCart_Admin_Page_Notices'               => [ 'NCS_Cart_Admin_Page_Notices' ],
    'PPCart_Dashboard_Widget'                 => [ 'NCS_Cart_Dashboard_Widget' ],
    'PPCart_Dashboard_Data'                     => [ 'NCS_Cart_Dashboard_Data' ],
    'PPCart_Dashboard_Renderer'               => [ 'NCS_Cart_Dashboard_Renderer' ],
    'PPCart_Dashboard_Order_Data'               => [ 'NCS_Cart_Dashboard_Order_Data' ],
    'PPCart_Dashboard_Plan_Data'                => [ 'NCS_Cart_Dashboard_Plan_Data' ],
    'PPCart_Dashboard_Subscription_Data'        => [ 'NCS_Cart_Dashboard_Subscription_Data' ],
];
