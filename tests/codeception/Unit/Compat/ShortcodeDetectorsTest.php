<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class ShortcodeDetectorsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-272
     */
    public function test_UT_272_leftover_shortcode_detectors_hook_cart_filters(): void
    {
        $source = (string) file_get_contents(
            PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/studiocart-compatibility-mode/shortcode-detectors.php'
        );

        $this->assertStringContainsString(
            "add_filter('ppcart_frontend_assets_needed', 'ppcartcomp_maybe_need_frontend_assets_for_leftover_shortcodes')",
            $source
        );
        $this->assertStringContainsString(
            "add_filter('body_class', 'ppcartcomp_maybe_add_leftover_account_body_class')",
            $source
        );
        $this->assertStringContainsString(
            "add_filter('ppcart_skip_default_order_summary', 'ppcartcomp_maybe_skip_default_order_summary_for_leftover'",
            $source
        );
        $this->assertStringContainsString(
            "add_filter('ppcart_order_download_shortcode_tags', 'ppcartcomp_maybe_add_leftover_order_download_shortcode_tag')",
            $source
        );

        $this->assertTrue(ppcartcomp_content_has_leftover_account_shortcode('[studiocart_account]'));
        $this->assertTrue(ppcartcomp_content_has_leftover_account_shortcode('[studiocart_account id="1"]'));
        $this->assertFalse(ppcartcomp_content_has_leftover_account_shortcode('[studiocart_account_link]'));
        $this->assertTrue(
            ppcartcomp_content_has_leftover_order_summary_shortcode('[sc_order_summary_items_view]')
        );

        $assetTags = ppcartcomp_frontend_asset_leftover_shortcode_tags();
        $this->assertContains('studiocart-form', $assetTags);
        $this->assertNotContains('sc_customer_bought_product', $assetTags);
        $this->assertNotContains('sc_customer_has_subscription', $assetTags);
        $this->assertTrue(ppcartcomp_content_has_leftover_shortcode_opening('[studiocart-form id="1"]'));
        $this->assertFalse(ppcartcomp_content_has_leftover_shortcode_opening('[studiocart-form-extra]'));
    }
}
