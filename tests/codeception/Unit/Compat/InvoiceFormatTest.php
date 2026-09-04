<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class InvoiceFormatTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-265
     */
    public function test_UT_265_invoice_format_map_covers_leftover_values(): void
    {
        $map = ncs_cart_get_legacy_invoice_format_value_map();

        $this->assertSame([ 'sc_pns' ], $map['ppcart_pns']);
        $this->assertSame([ 'sc_pn' ], $map['ppcart_pn']);
        $this->assertSame([ 'sc_ns' ], $map['ppcart_ns']);
        $this->assertSame([ 'sc_n' ], $map['ppcart_n']);

        $this->assertSame('ppcart_pns', ppcart_map_legacy_invoice_format('sc_pns'));
        $this->assertSame('ppcart_pn', ppcart_map_legacy_invoice_format('sc_pn'));
        $this->assertSame('ppcart_ns', ppcart_map_legacy_invoice_format('sc_ns'));
        $this->assertSame('ppcart_n', ppcart_map_legacy_invoice_format('sc_n'));
        $this->assertSame('ppcart_pns', ppcart_map_legacy_invoice_format('ppcart_pns'));
        $this->assertSame('ppcart_pn', ppcart_map_legacy_invoice_format('ppcart_pn'));
        $this->assertSame('unknown', ppcart_map_legacy_invoice_format('unknown'));
    }
}
