<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCart_Public_Asset_Controller;
use PPCartComp_Studiocart_Compatibility_Mode;

class ShortcodeUsageTest extends WPTestCase
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

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
    }

    protected function tearDown(): void
    {
        foreach ($this->postIds as $postId) {
            wp_delete_post($postId, true);
        }

        unset($GLOBALS['post']);
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-283
     */
    public function test_IT_283_enqueue_treats_leftover_form_tag_as_a_hit(): void
    {
        $leftoverId = $this->insertPage('[studiocart-form id="1"]');
        $this->assertTrue($this->assetsNeededForPost($leftoverId));
    }

    /**
     * @test-id IT-283
     */
    public function test_IT_283_email_helper_replaces_leftover_download_tag_only_when_compat_is_on(): void
    {
        $notification = [
            'send_to'       => 'purchaser',
            'send_to_email' => '',
            'from_name'     => '',
            'from_email'    => '',
            'subject'       => 'Hi',
            'message'       => 'A [ppcart_order_downloads] B [studiocart-order-downloads] C',
            'reply_to'      => '',
            'bcc'           => '',
        ];
        $orderInfo = [
            'ID'           => 1,
            'email'        => 'buyer@example.com',
            'product_id'   => 1,
            'product_name' => 'Test product',
            'item_name'    => '',
            'amount'       => 10,
            'quantity'     => 1,
            'date'         => '2026-01-01',
        ];

        $off = ppcart_build_product_notification_email($notification, $orderInfo);
        $this->assertStringNotContainsString('[ppcart_order_downloads]', $off['body']);
        $this->assertStringContainsString('[studiocart-order-downloads]', $off['body']);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        $on = ppcart_build_product_notification_email($notification, $orderInfo);
        $this->assertStringNotContainsString('[ppcart_order_downloads]', $on['body']);
        $this->assertStringNotContainsString('[studiocart-order-downloads]', $on['body']);
    }

    /**
     * @param string $content Post content.
     * @return int
     */
    private function insertPage($content)
    {
        $id = (int) wp_insert_post(
            [
                'post_title'   => 'Shortcode usage',
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
        $GLOBALS['post'] = get_post($postId);

        $controller = new PPCart_Public_Asset_Controller();

        return $controller->frontend_assets_needed();
    }
}
