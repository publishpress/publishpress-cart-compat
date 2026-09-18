<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class IntegrationRepeaterFieldsTest extends WPTestCase
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
                'post_title' => 'Slice 36 cancel-subscription product',
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
                    'sc_sub_plan_id' => 'plan_a',
                    'sc_sub_cancel' => 'yes',
                    'sc_refund_prod_id' => '77',
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
     * @test-id IT-339
     */
    public function test_IT_339_leftover_integration_repeater_keys_copy_only_when_compatibility_mode_is_on(): void
    {
        $off = ppcart_get_post_meta($this->productId, 'integrations', true);

        $this->assertIsArray($off);
        $this->assertSame('10', $off[0]['sc_sub_prod_id']);
        $this->assertArrayNotHasKey('ppcart_sub_prod_id', $off[0]);
        $this->assertArrayNotHasKey('ppcart_sub_plan_id', $off[0]);
        $this->assertArrayNotHasKey('ppcart_sub_cancel', $off[0]);
        $this->assertArrayNotHasKey('ppcart_refund_prod_id', $off[0]);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $on = ppcart_get_post_meta($this->productId, 'integrations', true);

        $this->assertSame('10', $on[0]['ppcart_sub_prod_id']);
        $this->assertSame('plan_a', $on[0]['ppcart_sub_plan_id']);
        $this->assertSame('yes', $on[0]['ppcart_sub_cancel']);
        $this->assertSame('77', $on[0]['ppcart_refund_prod_id']);
        $this->assertSame('10', $on[0]['sc_sub_prod_id']);

        $stored = get_post_meta($this->productId, '_ppcart_integrations', true);
        $this->assertSame('10', $stored[0]['sc_sub_prod_id']);
        $this->assertSame('77', $stored[0]['sc_refund_prod_id']);
        $this->assertArrayNotHasKey('ppcart_sub_prod_id', $stored[0]);
        $this->assertArrayNotHasKey('ppcart_refund_prod_id', $stored[0]);
    }

    /**
     * @test-id IT-339
     */
    public function test_IT_339_merchant_data_migration_copies_inner_keys_without_deleting_leftover(): void
    {
        $moved = ppcart_migrate_integration_repeater_inner_keys();

        $this->assertSame(1, $moved);

        $stored = get_post_meta($this->productId, '_ppcart_integrations', true);
        $this->assertSame('77', $stored[0]['ppcart_refund_prod_id']);
        $this->assertSame('77', $stored[0]['sc_refund_prod_id']);
        $this->assertSame('10', $stored[0]['ppcart_sub_prod_id']);
        $this->assertSame('10', $stored[0]['sc_sub_prod_id']);
    }
}
