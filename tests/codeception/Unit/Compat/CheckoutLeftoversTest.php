<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class CheckoutLeftoversTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-258
     */
    public function test_UT_258_request_field_map_covers_leftover_checkout_qty(): void
    {
        $map = ncs_cart_get_legacy_request_field_map();

        $this->assertArrayNotHasKey('action', $map);
        $this->assertSame([ 'sc_qty' ], $map['ppcart_qty']);
        $this->assertSame([ 'sc_d' ], $map['ppcart_d']);
    }
}
