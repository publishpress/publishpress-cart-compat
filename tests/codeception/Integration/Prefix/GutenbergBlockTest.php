<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class GutenbergBlockTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    private const CANONICAL_BLOCK = 'publishpress-cart/checkout-form';
    private const LEFTOVER_BLOCK  = 'sc-products-shortcode/product-shortcode';

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_gutenberg_blocks')) {
            ppcart_unregister_legacy_gutenberg_blocks();
        }

        if (! did_action('init')) {
            do_action('init');
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('ppcart_unregister_legacy_gutenberg_blocks')) {
            ppcart_unregister_legacy_gutenberg_blocks();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-351
     */
    public function test_IT_351_compat_off_does_not_register_leftover_checkout_block(): void
    {
        $registry = \WP_Block_Type_Registry::get_instance();

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue($registry->is_registered(self::CANONICAL_BLOCK));
        $this->assertFalse($registry->is_registered(self::LEFTOVER_BLOCK));
    }

    /**
     * @test-id IT-351
     */
    public function test_IT_351_compat_on_dual_registers_leftover_checkout_block(): void
    {
        $registry = \WP_Block_Type_Registry::get_instance();

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_gutenberg_blocks();

        $this->assertTrue($registry->is_registered(self::LEFTOVER_BLOCK));

        $canonical = $registry->get_registered(self::CANONICAL_BLOCK);
        $leftover  = $registry->get_registered(self::LEFTOVER_BLOCK);

        $this->assertInstanceOf(\WP_Block_Type::class, $canonical);
        $this->assertInstanceOf(\WP_Block_Type::class, $leftover);
        $this->assertSame($canonical->render_callback, $leftover->render_callback);
        $this->assertFalse($leftover->supports['inserter'] ?? true);
    }

    /**
     * @test-id IT-351
     */
    public function test_IT_351_compat_on_renders_leftover_checkout_block(): void
    {
        $productId = wp_insert_post(
            [
                'post_type'   => function_exists('ppcart_live_post_type')
                    ? ppcart_live_post_type('product')
                    : 'ppcart_product',
                'post_status' => 'publish',
                'post_title'  => 'Compat leftover checkout block ' . wp_generate_uuid4(),
            ]
        );

        if (! $productId || is_wp_error($productId)) {
            $this->markTestSkipped('Leftover checkout block rendering requires a temporary product.');
        }

        update_post_meta(
            $productId,
            '_ppcart_pay_options',
            [
                [
                    'option_id'      => 'compat_plan',
                    'option_name'    => 'Compat Plan',
                    'price'          => '100',
                    'frequency'      => '1',
                    'sale_frequency' => '1',
                    'interval'       => 'day',
                    'sale_interval'  => 'day',
                    'installments'   => '-1',
                ],
            ]
        );

        if (function_exists('ppcart_update_post_meta')) {
            ppcart_update_post_meta($productId, 'plan_heading', 'Payment Plan');
            ppcart_update_post_meta($productId, 'button_color', '#000000');
            ppcart_update_post_meta($productId, 'button_text', 'Order Now');
            ppcart_update_post_meta($productId, 'checkout_ended_action', 'message');
            ppcart_update_post_meta($productId, 'checkout_ended_message', 'Sorry, this product is no longer for sale.');
        }

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_gutenberg_blocks();

        $previousPost     = isset($GLOBALS['post']) ? $GLOBALS['post'] : null;
        $GLOBALS['post']  = get_post($productId);
        setup_postdata($GLOBALS['post']);

        if (function_exists('ppcart_checkout_reset_request_render_guard')) {
            ppcart_checkout_reset_request_render_guard();
        }

        $output = do_blocks(
            '<!-- wp:sc-products-shortcode/product-shortcode {"template":"true","hide_labels":true} /-->'
        );

        wp_reset_postdata();
        if ($previousPost) {
            $GLOBALS['post'] = $previousPost;
        } else {
            unset($GLOBALS['post']);
        }

        wp_delete_post((int) $productId, true);

        $this->assertStringContainsString('publishpress-cart-checkout-form', $output);
        $this->assertStringNotContainsString('[ppcart_form', $output);
        $this->assertStringNotContainsString('[studiocart-form', $output);
    }

    /**
     * @test-id IT-351
     */
    public function test_IT_351_compat_on_enqueues_leftover_checkout_editor_assets(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_gutenberg_blocks();
        PPCartComp_Studiocart_Compatibility_Mode::enqueue_legacy_gutenberg_editor_assets();

        $this->assertTrue(wp_script_is('ppcart-compat-legacy-checkout-block', 'enqueued'));
        $this->assertTrue(wp_style_is('ppcart-compat-checkout-editor', 'enqueued'));
    }
}
