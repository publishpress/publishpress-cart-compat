<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class ShortcodesTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_shortcodes')) {
            ppcart_unregister_legacy_shortcodes();
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('ppcart_unregister_legacy_shortcodes')) {
            ppcart_unregister_legacy_shortcodes();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-282
     */
    public function test_IT_282_compat_off_registers_canonical_tags_and_not_leftover(): void
    {
        $this->assertTrue(shortcode_exists('ppcart_form'));
        $this->assertTrue(shortcode_exists('ppcart_receipt'));
        $this->assertTrue(shortcode_exists('ppcart_account_order_detail'));
        $this->assertTrue(shortcode_exists('ppcart_order_detail'));
        $this->assertFalse(shortcode_exists('studiocart-form'));
        $this->assertFalse(shortcode_exists('studiocart-receipt'));
        $this->assertFalse(shortcode_exists('studiocart_order_detail'));
        $this->assertFalse(shortcode_exists('sc_order_detail'));
    }

    /**
     * @test-id IT-282
     */
    public function test_IT_282_compat_on_registers_leftover_tags_on_the_same_callback(): void
    {
        global $shortcode_tags;

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_shortcodes();

        $this->assertTrue(shortcode_exists('studiocart-form'));
        $this->assertTrue(shortcode_exists('sc_order_detail'));
        $this->assertSame($shortcode_tags['ppcart_form'], $shortcode_tags['studiocart-form']);
        $this->assertSame($shortcode_tags['ppcart_order_detail'], $shortcode_tags['sc_order_detail']);
    }

    /**
     * @test-id IT-282
     */
    public function test_IT_282_account_and_order_detail_tags_stay_distinct(): void
    {
        global $shortcode_tags;

        $this->assertTrue(shortcode_exists('ppcart_account_order_detail'));
        $this->assertTrue(shortcode_exists('ppcart_order_detail'));
        $this->assertNotSame(
            $shortcode_tags['ppcart_account_order_detail'],
            $shortcode_tags['ppcart_order_detail']
        );

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_shortcodes();

        $this->assertSame(
            $shortcode_tags['ppcart_account_order_detail'],
            $shortcode_tags['studiocart_order_detail']
        );
        $this->assertSame(
            $shortcode_tags['ppcart_order_detail'],
            $shortcode_tags['sc_order_detail']
        );
    }
}
