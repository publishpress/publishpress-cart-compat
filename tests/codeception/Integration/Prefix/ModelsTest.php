<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCart_Order;
use PPCart_Order_Item;
use PPCart_Order_Items;
use PPCart_Subscription;

class ModelsTest extends WPTestCase
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

    public function test_IT_166_PPCart_Order_legacy_aliases_unregistered_when_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists('PPCart_Order', false));
        $this->assertFalse(class_exists('NCS_Cart_Order', false));
        $this->assertFalse(class_exists('ScrtOrder', false));
    }

    public function test_IT_167_PPCart_Subscription_legacy_aliases_unregistered_when_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists('PPCart_Subscription', false));
        $this->assertFalse(class_exists('NCS_Cart_Subscription', false));
        $this->assertFalse(class_exists('ScrtSubscription', false));
    }

    public function test_IT_168_PPCart_Order_Item_legacy_aliases_unregistered_when_mode_is_off(): void
    {
        $this->ensureOrderItemClassesLoaded();

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists('PPCart_Order_Item', false));
        $this->assertFalse(class_exists('NCS_Cart_Order_Item', false));
        $this->assertFalse(class_exists('ScrtOrderItem', false));
    }

    public function test_IT_169_PPCart_Order_Items_legacy_aliases_unregistered_when_mode_is_off(): void
    {
        $this->ensureOrderItemClassesLoaded();

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists('PPCart_Order_Items', false));
        $this->assertFalse(class_exists('NCS_Cart_Order_Items', false));
    }

    private function ensureOrderItemClassesLoaded(): void
    {
        if (! class_exists('PPCart_Order_Items', false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/order-items/class-ppcart-order-items.php';
        }

        if (! class_exists('PPCart_Order_Item', false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/order-items/class-ppcart-order-item.php';
        }
    }
}
