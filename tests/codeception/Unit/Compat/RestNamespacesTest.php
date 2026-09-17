<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class RestNamespacesTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-309
     */
    public function test_UT_309_rest_namespace_map_covers_leftover_sc_v1(): void
    {
        $map = ncs_cart_get_legacy_rest_namespace_map();

        $this->assertArrayHasKey('publishpress-cart/v1', $map);
        $this->assertSame('sc/v1', $map['publishpress-cart/v1']['legacy']);
        $this->assertSame(
            [ '/webhook', '/orders', '/products', '/customers', '/subscriptions' ],
            $map['publishpress-cart/v1']['route_prefixes']
        );
    }

    /**
     * @test-id UT-309
     */
    public function test_UT_309_prefix_matcher_copies_pro_routes_not_gutenberg(): void
    {
        $prefixes = ncs_cart_get_legacy_rest_namespace_map()['publishpress-cart/v1']['route_prefixes'];

        $this->assertTrue(ncs_cart_legacy_rest_route_matches_prefixes('/webhook/zap-validate', $prefixes));
        $this->assertTrue(ncs_cart_legacy_rest_route_matches_prefixes('/orders', $prefixes));
        $this->assertTrue(ncs_cart_legacy_rest_route_matches_prefixes('/orders/(?P<id>[\d]+)', $prefixes));
        $this->assertTrue(ncs_cart_legacy_rest_route_matches_prefixes('/products', $prefixes));
        $this->assertTrue(ncs_cart_legacy_rest_route_matches_prefixes('/customers/(?P<id>[\d]+)', $prefixes));
        $this->assertTrue(ncs_cart_legacy_rest_route_matches_prefixes('/subscriptions', $prefixes));

        $this->assertFalse(ncs_cart_legacy_rest_route_matches_prefixes('/checkout-block/products', $prefixes));
        $this->assertFalse(ncs_cart_legacy_rest_route_matches_prefixes('/account-block/detail', $prefixes));
        $this->assertFalse(ncs_cart_legacy_rest_route_matches_prefixes('', $prefixes));
        $this->assertFalse(ncs_cart_legacy_rest_route_matches_prefixes('/', $prefixes));
    }

    /**
     * @test-id UT-309
     */
    public function test_UT_309_reregister_flattens_keyed_methods_map(): void
    {
        $copy = ncs_cart_legacy_rest_handlers_for_reregister(
            [
                [
                    'methods'  => [ 'POST' => true, 'GET' => true ],
                    'callback' => 'stub',
                ],
                'schema' => 'not-a-handler',
            ]
        );

        $this->assertCount(1, $copy);
        $this->assertSame('POST,GET', $copy[0]['methods']);
        $this->assertSame('stub', $copy[0]['callback']);
    }
}
