<?php

/**
 * Canonical leftover table suffixes for Compatibility Mode SQL rewrite.
 *
 * Runtime leftover → live names come from ppcart_custom_table_family_map().
 * This map is the Compatibility Mode surface for tests and the glob skip list.
 *
 * @return array<string, array<int, string>>
 */
return [
    'ppcart_tax_rate'              => [ 'ncs_tax_rate' ],
    'ppcart_affiliates'            => [ 'ncs_affiliates' ],
    'ppcart_affiliatemeta'         => [ 'ncs_affiliatemeta' ],
    'ppcart_affiliate_commissions' => [ 'ncs_affiliate_commissions' ],
    'ppcart_affiliate_payouts'     => [ 'ncs_affiliate_payouts' ],
    'ppcart_affiliate_visits'      => [ 'ncs_affiliate_visits' ],
    'ppcart_affiliate_product_rel' => [ 'ncs_affiliate_product_rel' ],
];
