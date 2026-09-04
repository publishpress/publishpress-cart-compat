<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCart_Paypal;
use PPCart_Public;
use PPCart_Public_Account_Controller;
use PPCart_Public_Asset_Controller;
use PPCart_Public_Checkout_Controller;
use PPCart_Public_Hosted_Checkout_Controller;
use PPCart_Public_Order_Controller;
use PPCart_Public_Page_Controller;
use PPCart_Public_Payment_Controller;
use PPCart_Public_Subscription_Checkout_Controller;

class PublicTest extends WPTestCase
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

    public function test_IT_179_ppcart_public_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Public::class, 'NCS_Cart_Public');
    }

    public function test_IT_180_ppcart_paypal_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Paypal::class, 'NCS_Cart_Paypal');
    }

    public function test_IT_181_ppcart_public_account_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Public_Account_Controller::class,
            'NCS_Cart_Public_Account_Controller'
        );
    }

    public function test_IT_182_ppcart_public_asset_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Public_Asset_Controller::class,
            'NCS_Cart_Public_Asset_Controller'
        );
    }

    public function test_IT_183_ppcart_public_checkout_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Public_Checkout_Controller::class,
            'NCS_Cart_Public_Checkout_Controller'
        );
    }

    public function test_IT_184_ppcart_public_hosted_checkout_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Public_Hosted_Checkout_Controller::class,
            'NCS_Cart_Public_Hosted_Checkout_Controller'
        );
    }

    public function test_IT_185_ppcart_public_order_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Public_Order_Controller::class,
            'NCS_Cart_Public_Order_Controller'
        );
    }

    public function test_IT_186_ppcart_public_page_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Public_Page_Controller::class,
            'NCS_Cart_Public_Page_Controller'
        );
    }

    public function test_IT_187_ppcart_public_payment_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Public_Payment_Controller::class,
            'NCS_Cart_Public_Payment_Controller'
        );
    }

    public function test_ppcart_public_subscription_checkout_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Public_Subscription_Checkout_Controller::class,
            'NCS_Cart_Public_Subscription_Checkout_Controller'
        );
    }

    private function assertCanonicalWithoutLegacy(string $canonical, string $legacy): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists($canonical, false), sprintf('%s must be loaded.', $canonical));
        $this->assertFalse(class_exists($legacy, false), sprintf('%s must not be aliased while Compatibility Mode is off.', $legacy));
    }
}
