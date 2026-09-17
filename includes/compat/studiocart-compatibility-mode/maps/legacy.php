<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * StudioCart leftover class aliases for Pro affiliate classes.
 *
 * Compatibility Mode on only. Canonical classes load from Pro.
 *
 * @return array<string, array<int, string>>
 */
return [
    'PPCart_Pro_Affiliate'                => [ 'SC_Affiliate' ],
    'PPCart_Pro_Affiliate_Tracking'       => [ 'SC_Affiliate_Tracking' ],
    'PPCart_Pro_Affiliate_Coupons'        => [ 'SC_Affiliate_Coupons' ],
    'PPCart_Pro_Affiliate_Admin'          => [ 'NCS_Affiliate_Admin' ],
    'PPCart_Pro_Affiliate_Public'         => [ 'NCS_Affiliate_Public' ],
    'PPCart_Pro_Affiliate_Dashboard'      => [ 'NCS_Affiliate_Dashboard' ],
    'PPCart_Pro_Affiliate_Integration'    => [ 'NCS_Affiliate_Integration' ],
    'PPCart_Pro_Affiliate_Helper'         => [ 'NCS_Affiliate_Helper' ],
    'PPCart_Pro_Affiliates_Table'         => [ 'NCS_Affiliates_Table' ],
    'PPCart_Pro_Affiliate_Visits_Table'   => [ 'NCS_Affiliate_Visits_Table' ],
    'PPCart_Pro_Commissions_Checker'      => [ 'NCS_Commissions_Checker' ],
    'PPCart_Pro_Payouts_Table'            => [ 'Payouts_Table' ],
    'PPCart_Pro_Commission_Table'         => [ 'Commission_Table' ],
];
