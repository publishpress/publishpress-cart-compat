<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Model class aliases (canonical => legacy names).
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart_Order'          => [ 'NCS_Cart_Order', 'ScrtOrder' ],
    'PPCart_Subscription'   => [ 'NCS_Cart_Subscription', 'ScrtSubscription' ],
    'PPCart_Order_Item'     => [ 'NCS_Cart_Order_Item', 'ScrtOrderItem' ],
    'PPCart_Order_Items'    => [ 'NCS_Cart_Order_Items' ],
    'PPCart_Collection'     => [ 'NCS_Cart_Collection', 'ScrtCollection' ],
];
