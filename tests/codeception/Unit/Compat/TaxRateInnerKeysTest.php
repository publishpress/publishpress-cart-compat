<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class TaxRateInnerKeysTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-267
     */
    public function test_UT_267_tax_rate_inner_key_map_covers_leftover_keys(): void
    {
        $map = ncs_cart_get_legacy_tax_rate_inner_key_map();

        $this->assertSame([ '_sc_tax_rate_title' ], $map['_ppcart_tax_rate_title']);
        $this->assertSame([ '_sc_tax_rate_slug' ], $map['_ppcart_tax_rate_slug']);
        $this->assertSame([ '_sc_tax_rate' ], $map['_ppcart_tax_rate']);

        $copied = ppcart_copy_legacy_tax_rate_inner_keys(
            [
                '_sc_tax_rate_title' => [ 'VAT' ],
                '_sc_tax_rate_slug' => [ 'vat' ],
                '_sc_tax_rate' => [ '20' ],
            ]
        );

        $this->assertSame([ 'VAT' ], $copied['_ppcart_tax_rate_title']);
        $this->assertSame([ 'vat' ], $copied['_ppcart_tax_rate_slug']);
        $this->assertSame([ '20' ], $copied['_ppcart_tax_rate']);
        $this->assertSame([ 'VAT' ], $copied['_sc_tax_rate_title']);
    }
}
