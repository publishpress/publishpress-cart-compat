<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCart_Admin;
use PPCart_Admin_Ajax;
use PPCart_Admin_Filters;
use PPCart_Admin_Reports;
use PPCart_Admin_Settings;
use PPCart_Admin_Stripe_Connect_Settings;
use PPCart_Admin_Stripe_Webhook_Settings;
use PPCart_Contacts_Page;
use PPCart_Customer_Reports;
use PPCart_Extension_Page;
use PPCart_Order_Admin;
use PPCart_Order_Metaboxes;
use PPCart_Product_Admin;
use PPCart_Product_Metabox_Option_Sources;
use PPCart_Product_Metaboxes;

class AdminTest extends WPTestCase
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

    public function test_IT_193_ppcart_admin_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Admin::class, 'NCS_Cart_Admin');
    }

    public function test_IT_194_ppcart_admin_ajax_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Admin_Ajax::class, 'NCS_Cart_Admin_Ajax');
    }

    public function test_IT_195_ppcart_admin_filters_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Admin_Filters::class, 'NCS_Cart_Admin_Filters');
    }

    public function test_IT_196_ppcart_admin_settings_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Admin_Settings::class, 'NCS_Cart_Admin_Settings');
    }

    public function test_IT_197_ppcart_admin_stripe_connect_settings_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Admin_Stripe_Connect_Settings::class,
            'NCS_Cart_Admin_Stripe_Connect_Settings'
        );
    }

    public function test_IT_198_ppcart_admin_stripe_webhook_settings_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Admin_Stripe_Webhook_Settings::class,
            'NCS_Cart_Admin_Stripe_Webhook_Settings'
        );
    }

    public function test_IT_199_ppcart_admin_reports_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Admin_Reports::class, 'NCS_Cart_Admin_Reports');
    }

    public function test_IT_200_ppcart_customer_reports_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Customer_Reports::class, 'NCS_Cart_Customer_Reports');
    }

    public function test_IT_201_ppcart_contacts_page_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Contacts_Page::class, 'NCS_Cart_Contacts_Page');
    }

    public function test_IT_202_ppcart_extension_page_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Extension_Page::class, 'NCS_Cart_Extension_Page');
    }

    public function test_IT_203_ppcart_order_admin_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Order_Admin::class, 'NCS_Cart_Order_Admin');
    }

    public function test_IT_204_ppcart_product_admin_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Product_Admin::class, 'NCS_Cart_Product_Admin');
    }

    public function test_IT_205_ppcart_product_metaboxes_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Product_Metaboxes::class, 'NCS_Cart_Product_Metaboxes');
    }

    public function test_IT_206_ppcart_product_metabox_option_sources_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Product_Metabox_Option_Sources::class,
            'NCS_Cart_Product_Metabox_Option_Sources'
        );
    }

    public function test_ppcart_order_metaboxes_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Order_Metaboxes::class, 'NCS_Cart_Order_Metaboxes');
    }

    private function assertCanonicalWithoutLegacy(string $canonical, string $legacy): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists($canonical, false), sprintf('%s must be loaded.', $canonical));
        $this->assertFalse(
            class_exists($legacy, false),
            sprintf('%s must not be aliased while Compatibility Mode is off.', $legacy)
        );
    }
}
