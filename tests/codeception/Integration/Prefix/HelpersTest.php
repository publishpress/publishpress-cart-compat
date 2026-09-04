<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCart_Helper;
use PPCart_Order_Helper;
use PPCart_Price_Format;
use PPCart_Stripe;

class HelpersTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        parent::tearDown();
    }

    public function test_IT_189_PPCart_Helper_legacy_aliases_unregistered_when_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists('PPCart_Helper', false));
        $this->assertFalse(class_exists('NCS_Cart_Helper', false));
        $this->assertFalse(class_exists('NCS_Helper', false));
    }

    public function test_IT_190_PPCart_Order_Helper_legacy_aliases_unregistered_when_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists('PPCart_Order_Helper', false));
        $this->assertFalse(class_exists('NCS_Cart_Order_Helper', false));
        $this->assertFalse(class_exists('NCS_Order_Helper', false));
    }

    public function test_IT_191_PPCart_Stripe_legacy_aliases_unregistered_when_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists('PPCart_Stripe', false));
        $this->assertFalse(class_exists('NCS_Cart_Stripe', false));
        $this->assertFalse(class_exists('NCS_Stripe', false));
    }

    public function test_IT_192_PPCart_Price_Format_legacy_aliases_unregistered_when_mode_is_off(): void
    {
        $this->ensurePriceFormatClassLoaded();

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists('PPCart_Price_Format', false));
        $this->assertFalse(class_exists('NCS_Cart_Price_Format', false));
        $this->assertFalse(class_exists('NCS_Price_Format', false));
    }

    private function ensurePriceFormatClassLoaded(): void
    {
        if (! class_exists('PPCart_Price_Format', false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/class-ppcart-price-format.php';
        }
    }
}
