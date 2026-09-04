<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Stripe sync, logging, secrets, and files class aliases (canonical => legacy names).
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart_Stripe_Sync'          => [ 'NCS_Cart_Stripe_Sync' ],
    'PPCart_Stripe_Sync_Context'  => [ 'NCS_Cart_Stripe_Sync_Context' ],
    'PPCart_Stripe_Save_Helper'   => [ 'NCS_Cart_Stripe_Save_Helper' ],
    'PPCart_Stripe_Webhook_Logger' => [ 'NCS_Cart_Stripe_Webhook_Logger' ],
    'PPCart_Debug_Logger'         => [ 'NCS_Cart_Debug_Logger' ],
    'PPCart_Debug_Log_Viewer'     => [ 'NCS_Cart_Debug_Log_Viewer' ],
    'PPCart_Secrets'              => [ 'NCS_Cart_Secrets' ],
    'PPCart_Files'                => [ 'NCS_Cart_Files' ],
];
