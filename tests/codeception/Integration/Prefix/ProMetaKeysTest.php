<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class ProMetaKeysTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var int
     */
    private $postId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        $this->postId = (int) wp_insert_post(
            [
                'post_title' => 'Slice 20 leftover Pro meta',
                'post_type' => 'ppcart_order',
                'post_status' => 'publish',
            ]
        );

        update_post_meta($this->postId, 'sc_sq_cc_id', 'card_legacy');
        update_post_meta($this->postId, 'sc_custom_meta', [ [ 'label' => 'Gift', 'value' => 'Yes' ] ]);
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if ($this->postId > 0) {
            wp_delete_post($this->postId, true);
        }

        parent::tearDown();
    }

    /**
     * @test-id IT-364
     */
    public function test_IT_364_canonical_pro_meta_reads_leftover_rows_without_copying(): void
    {
        $this->assertSame('card_legacy', get_post_meta($this->postId, 'ppcart_sq_cc_id', true));
        $custom = get_post_meta($this->postId, 'ppcart_custom_meta', true);
        $this->assertIsArray($custom);
        $this->assertSame('Gift', $custom[0]['label']);

        $this->assertSame('card_legacy', get_post_meta($this->postId, 'sc_sq_cc_id', true));
        $this->assertSame('', get_post_meta($this->postId, 'ppcart_sq_cc_id_missing', true));
    }
}
