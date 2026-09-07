<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class IntegrationServiceIdsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var int
     */
    private $productId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        $this->productId = (int) wp_insert_post(
            [
                'post_title' => 'Slice 49 integration service ids product',
                'post_type' => 'ppcart_product',
                'post_status' => 'publish',
            ]
        );

        update_post_meta(
            $this->productId,
            '_ppcart_integrations',
            [
                [
                    'services' => 'sc_subscription',
                    'sc_sub_prod_id' => '10',
                ],
                [
                    'services' => 'sc_refund_order',
                ],
                [
                    'services' => 'sc_wpdomainchecker',
                ],
            ]
        );
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if ($this->productId > 0) {
            wp_delete_post($this->productId, true);
        }

        parent::tearDown();
    }

    /**
     * @test-id IT-347
     */
    public function test_IT_347_leftover_integration_service_ids_copy_only_when_compatibility_mode_is_on(): void
    {
        $off = ppcart_get_post_meta($this->productId, 'integrations', true);

        $this->assertIsArray($off);
        $this->assertSame('sc_subscription', $off[0]['services']);
        $this->assertSame('sc_refund_order', $off[1]['services']);
        $this->assertSame('sc_wpdomainchecker', $off[2]['services']);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $on = ppcart_get_post_meta($this->productId, 'integrations', true);

        $this->assertSame('ppcart_subscription', $on[0]['services']);
        $this->assertSame('ppcart_refund_order', $on[1]['services']);
        $this->assertSame('ppcart_wpdomainchecker', $on[2]['services']);
        $this->assertSame('10', $on[0]['sc_sub_prod_id']);

        $stored = get_post_meta($this->productId, '_ppcart_integrations', true);
        $this->assertSame('sc_subscription', $stored[0]['services']);
        $this->assertSame('sc_refund_order', $stored[1]['services']);
        $this->assertSame('sc_wpdomainchecker', $stored[2]['services']);
    }
}
