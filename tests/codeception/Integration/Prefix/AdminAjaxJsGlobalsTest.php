<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class AdminAjaxJsGlobalsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var array<string, mixed>
     */
    private $originalPost = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalPost = $_POST;

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('wp_dequeue_script')) {
            wp_dequeue_script('ppcart-compat-js-globals');
        }
        if (function_exists('wp_deregister_script')) {
            wp_deregister_script('ppcart-compat-js-globals');
        }

        $_POST = $this->originalPost;

        parent::tearDown();
    }

    /**
     * @test-id IT-352
     */
    public function test_IT_352_compat_off_does_not_copy_leftover_admin_ajax_post_keys(): void
    {
        $_POST = [
            'ncs_ajax_nonce' => 'legacy-nonce',
            'ncs_action' => 'save_table_tax',
        ];

        ppcart_copy_legacy_request_fields();

        $this->assertArrayNotHasKey('ppcart_ajax_nonce', $_POST);
        $this->assertArrayNotHasKey('ppcart_action', $_POST);
    }

    /**
     * @test-id IT-352
     */
    public function test_IT_352_compat_on_copies_leftover_admin_ajax_post_keys(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        ppcart_unregister_legacy_request_field_bridges();

        $_POST = [
            'ncs_ajax_nonce' => 'legacy-nonce',
            'ncs_action' => 'save_table_tax',
        ];

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_request_fields();

        $this->assertSame('legacy-nonce', $_POST['ppcart_ajax_nonce']);
        $this->assertSame('save_table_tax', $_POST['ppcart_action']);
    }

    /**
     * @test-id IT-352
     */
    public function test_IT_352_compat_on_registers_js_globals_bridge_script(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        wp_register_style('ppcart', includes_url('css/dashicons.min.css'), [], '1.0.0');
        wp_enqueue_style('ppcart');
        wp_register_script('ppcart', includes_url('js/jquery/jquery.js'), [], '1.0.0', true);

        PPCartComp_Studiocart_Compatibility_Mode::enqueue_legacy_public_styles();

        $this->assertTrue(wp_script_is('ppcart-compat-js-globals', 'registered'));
        $this->assertTrue(wp_script_is('ppcart-compat-js-globals', 'enqueued'));
    }

    /**
     * @test-id IT-352
     */
    public function test_IT_352_compat_off_does_not_register_js_globals_bridge_script(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());

        wp_register_style('ppcart', includes_url('css/dashicons.min.css'), [], '1.0.0');
        wp_enqueue_style('ppcart');
        wp_register_script('ppcart', includes_url('js/jquery/jquery.js'), [], '1.0.0', true);

        PPCartComp_Studiocart_Compatibility_Mode::enqueue_legacy_public_styles();

        $this->assertFalse(wp_script_is('ppcart-compat-js-globals', 'registered'));
    }
}
