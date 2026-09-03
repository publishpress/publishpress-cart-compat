<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Bootstrap and core class aliases (canonical => legacy names).
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart'                         => [ 'NCS_Cart' ],
    'PPCart_Loader'                  => [ 'NCS_Cart_Loader' ],
    'PPCart_Activator'               => [ 'NCS_Cart_Activator' ],
    'PPCart_Deactivator'             => [ 'NCS_Cart_Deactivator' ],
    'PPCart_I18n'                    => [ 'NCS_Cart_I18n' ],
    'PPCart_Upgrade'                 => [ 'NCS_Cart_Upgrade' ],
    'PPCart_Admin_Screens'           => [ 'NCS_Cart_Admin_Screens' ],
    'PPCart_Version_Notices'         => [ 'NCS_Cart_Version_Notices' ],
    'PPCart_Post_Types'              => [ 'NCS_Cart_Post_Types' ],
    'PPCart_Dependency_Loader'       => [ 'NCS_Cart_Dependency_Loader' ],
    'PPCart_Admin_Hook_Registrar'    => [ 'NCS_Cart_Admin_Hook_Registrar' ],
    'PPCart_Public_Hook_Registrar'   => [ 'NCS_Cart_Public_Hook_Registrar' ],
    'PPCart_Status_Labels'           => [ 'NCS_Cart_Status_Labels' ],
    'PPCart_Sanitize'                => [ 'NCS_Cart_Sanitize' ],
    'PPCart_Product_Duplicator'      => [ 'NCS_Cart_Product_Duplicator' ],
    'PPCart_Order_Refunds'           => [ 'NCS_Cart_Order_Refunds' ],
    'PPCart_Currencies'              => [ 'NCS_Cart_Currencies' ],
];
