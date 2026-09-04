<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class GutenbergTest extends WPTestCase
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

    public function test_IT_170_ppcart_gutenberg_bootstrap_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy('PPCart_Gutenberg_Bootstrap', 'NCS_Cart_Gutenberg_Bootstrap');
    }

    public function test_IT_171_ppcart_gutenberg_assets_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy('PPCart_Gutenberg_Assets', 'NCS_Cart_Gutenberg_Assets');
    }

    public function test_IT_172_ppcart_checkout_renderer_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy('PPCart_Checkout_Renderer', 'NCS_Cart_Checkout_Renderer');
    }

    public function test_IT_173_ppcart_checkout_preview_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            'PPCart_Checkout_Preview_Controller',
            'NCS_Cart_Checkout_Preview_Controller'
        );
    }

    public function test_IT_174_ppcart_account_styles_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy('PPCart_Account_Styles', 'NCS_Cart_Account_Styles');
    }

    public function test_IT_175_ppcart_account_renderer_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy('PPCart_Account_Renderer', 'NCS_Cart_Account_Renderer');
    }

    public function test_IT_176_ppcart_account_detail_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            'PPCart_Account_Detail_Controller',
            'NCS_Cart_Account_Detail_Controller'
        );
    }

    public function test_IT_177_ppcart_account_context_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy('PPCart_Account_Context', 'NCS_Cart_Account_Context');
    }

    public function test_ppcart_product_template_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy('PPCart_Product_Template', 'NCS_Cart_Product_Template');
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
