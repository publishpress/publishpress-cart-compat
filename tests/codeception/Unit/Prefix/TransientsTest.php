<?php

namespace unit\Prefix;

use Codeception\Test\Unit;
use UnitTester;

class TransientsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-229
     */
    public function test_UT_229_option_mapper_unwraps_leftover_transient_option_names(): void
    {
        $this->assertSame(
            '_transient_ppcart_stripe_evt_evt_x',
            ncs_cart_legacy_to_canonical_option_name('_transient_sc_stripe_evt_evt_x')
        );
        $this->assertSame(
            '_transient_timeout_ppcart_stripe_evt_evt_x',
            ncs_cart_legacy_to_canonical_option_name('_transient_timeout_sc_stripe_evt_evt_x')
        );
        $this->assertSame(
            '_transient_sc_stripe_evt_evt_x',
            ncs_cart_canonical_to_legacy_option_name('_transient_ppcart_stripe_evt_evt_x')
        );
        $this->assertSame(
            '_transient_timeout_sc_stripe_evt_evt_x',
            ncs_cart_canonical_to_legacy_option_name('_transient_timeout_ppcart_stripe_evt_evt_x')
        );
        $this->assertTrue(ppcart_legacy_option_name_is_copyable('_transient_sc_hosted_session_cs_x'));
        $this->assertTrue(ppcart_legacy_option_name_is_copyable('_transient_timeout_sc_hosted_session_cs_x'));
        $this->assertNull(ncs_cart_legacy_to_canonical_option_name('_transient_unrelated_plugin_key'));
    }
}
