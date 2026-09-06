<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCart_Public_Asset_Controller;
use PPCartComp_Studiocart_Compatibility_Mode;

class ShortcodeDetectorsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var array<int, int>
     */
    private $postIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->isolateAssetDetectionContext();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
    }

    protected function tearDown(): void
    {
        foreach ($this->postIds as $postId) {
            wp_delete_post($postId, true);
        }

        unset($GLOBALS['post']);
        $this->isolateAssetDetectionContext();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-343
     */
    public function test_IT_343_leftover_shortcode_detectors_run_only_when_compatibility_mode_is_on(): void
    {
        $leftoverId = $this->insertPage('[studiocart-form id="1"]');
        $this->assertFalse($this->assetsNeededForPost($leftoverId));

        $GLOBALS['post'] = get_post($this->insertPage('[studiocart_account]'));
        $this->assertSame([], ppcartcomp_maybe_add_leftover_account_body_class([]));
        $this->assertFalse(
            ppcartcomp_maybe_skip_default_order_summary_for_leftover(false, '[sc_order_summary_items_view]')
        );

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $this->assertTrue($this->assetsNeededForPost($leftoverId));

        $GLOBALS['post'] = get_post($this->insertPage('[studiocart_account]'));
        $this->assertSame(['account-page'], ppcartcomp_maybe_add_leftover_account_body_class([]));
        $this->assertTrue(
            ppcartcomp_maybe_skip_default_order_summary_for_leftover(false, '[sc_order_summary_items_view]')
        );
    }

    /**
     * @param string $content Post content.
     * @return int
     */
    private function insertPage($content)
    {
        $id = (int) wp_insert_post(
            [
                'post_title'   => 'Shortcode detectors',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => $content,
            ]
        );
        $this->postIds[] = $id;

        return $id;
    }

    /**
     * @param int $postId Post ID.
     * @return bool
     */
    private function assetsNeededForPost($postId)
    {
        $this->isolateAssetDetectionContext();
        $GLOBALS['post'] = get_post($postId);

        $controller = new PPCart_Public_Asset_Controller();

        return $controller->frontend_assets_needed();
    }

    /**
     * Cart returns true before shortcode checks when a product global or
     * checkout query args remain from an earlier integration test.
     *
     * @return void
     */
    private function isolateAssetDetectionContext()
    {
        unset($GLOBALS['ppcart_product']);
        unset(
            $_GET['ppcart-order'],
            $_GET['ppcart-pid'],
            $_GET['ppcart-oto'],
            $_GET['ppcart-preview'],
            $_GET['ppcart-plan']
        );
        unset($_POST['ppcart_purchase_amount']);
    }
}
