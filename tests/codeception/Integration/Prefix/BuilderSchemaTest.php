<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class BuilderSchemaTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_elementor_widgets')) {
            ppcart_unregister_legacy_elementor_widgets();
        }

        if (function_exists('ppcart_unregister_legacy_divi_modules')) {
            ppcart_unregister_legacy_divi_modules();
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('ppcart_unregister_legacy_elementor_widgets')) {
            ppcart_unregister_legacy_elementor_widgets();
        }

        if (function_exists('ppcart_unregister_legacy_divi_modules')) {
            ppcart_unregister_legacy_divi_modules();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-363
     */
    public function test_IT_363_compat_off_does_not_register_leftover_builder_schema(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_elementor_widgets();
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_divi_modules();

        $elementor = function_exists('ncs_cart_legacy_elementor_widgets_registered')
            ? ncs_cart_legacy_elementor_widgets_registered()
            : [];
        $divi = function_exists('ncs_cart_legacy_divi_modules_registered')
            ? ncs_cart_legacy_divi_modules_registered()
            : [];

        $this->assertSame([], $elementor);
        $this->assertSame([], $divi);
    }

    /**
     * @test-id IT-363
     */
    public function test_IT_363_compat_on_dual_registers_when_builders_exist(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_elementor_widgets();
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_divi_modules();

        $elementor = ncs_cart_legacy_elementor_widgets_registered();
        $divi      = ncs_cart_legacy_divi_modules_registered();

        if (class_exists('\Elementor\Plugin', false) && class_exists('PPCart_Pro_Elementor_OrderForm', false)) {
            $this->assertArrayHasKey('sc-orderform', $elementor);
        } else {
            $this->assertSame([], $elementor);
        }

        if (class_exists('ET_Builder_Module', false) && class_exists('PPCart_Pro_Divi_Order_Form_Module', false)) {
            $this->assertArrayHasKey('stof_studiocart_order_form', $divi);
        } else {
            $this->assertSame([], $divi);
        }
    }
}
