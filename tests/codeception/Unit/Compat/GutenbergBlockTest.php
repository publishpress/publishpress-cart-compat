<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class GutenbergBlockTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-295
     */
    public function test_UT_295_gutenberg_block_map_covers_leftover_checkout_block(): void
    {
        $map = ncs_cart_get_legacy_gutenberg_block_map();

        $this->assertSame(
            'sc-products-shortcode/product-shortcode',
            $map['publishpress-cart/checkout-form']
        );
    }
}
