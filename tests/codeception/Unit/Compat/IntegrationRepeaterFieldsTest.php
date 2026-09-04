<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class IntegrationRepeaterFieldsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-263
     */
    public function test_UT_263_integration_repeater_map_covers_leftover_field_ids(): void
    {
        $map = ncs_cart_get_legacy_integration_repeater_field_map();

        $this->assertSame([ 'sc_sub_prod_id' ], $map['ppcart_sub_prod_id']);
        $this->assertSame([ 'sc_sub_plan_id' ], $map['ppcart_sub_plan_id']);
        $this->assertSame([ 'sc_sub_cancel' ], $map['ppcart_sub_cancel']);

        $copied = ppcart_copy_legacy_integration_repeater_rows(
            [
                [
                    'services' => 'sc_subscription',
                    'sc_sub_prod_id' => '10',
                    'sc_sub_plan_id' => 'plan_a',
                    'sc_sub_cancel' => 'yes',
                ],
            ]
        );

        $this->assertSame('10', $copied[0]['ppcart_sub_prod_id']);
        $this->assertSame('plan_a', $copied[0]['ppcart_sub_plan_id']);
        $this->assertSame('yes', $copied[0]['ppcart_sub_cancel']);
        $this->assertSame('10', $copied[0]['sc_sub_prod_id']);
    }
}
