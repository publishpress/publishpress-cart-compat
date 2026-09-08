<?php

declare(strict_types=1);

namespace Tests\Unit\Compat;

use Codeception\Test\Unit;

class LeftoverRecognitionTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-298
     */
    public function test_UT_298_compat_maps_my_account_option_to_canonical(): void
    {
        $this->assertSame(
            '_ppcart_myaccount_page_id',
            ncs_cart_legacy_to_canonical_option_name('_my_account')
        );
    }

    /**
     * @test-id UT-298
     */
    public function test_UT_298_compat_meta_key_suffix_accepts_leftover_prefix(): void
    {
        $this->assertSame('status', ppcart_meta_key_suffix('_sc_status'));
        $this->assertSame('_ppcart_status', ppcart_meta_key('_sc_status'));
    }
}
