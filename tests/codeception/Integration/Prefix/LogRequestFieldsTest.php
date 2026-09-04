<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class LogRequestFieldsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var array<string, mixed>
     */
    private $originalPost = [];

    /**
     * @var array<string, mixed>
     */
    private $originalGet = [];

    /**
     * @var array<string, mixed>
     */
    private $originalRequest = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalPost = $_POST;
        $this->originalGet = $_GET;
        $this->originalRequest = $_REQUEST;

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

        $_POST = $this->originalPost;
        $_GET = $this->originalGet;
        $_REQUEST = $this->originalRequest;

        parent::tearDown();
    }

    /**
     * @test-id IT-335
     */
    public function test_IT_335_leftover_log_get_and_nonce_copy_only_when_compatibility_mode_is_on(): void
    {
        $_GET = [
            'sc_view_log' => '1',
            'sc_view_debug_log_nonce' => 'leftover-nonce',
        ];
        $_POST = [];
        $_REQUEST = $_GET;

        ppcart_copy_legacy_request_fields();

        $this->assertArrayNotHasKey('ppcart_view_log', $_GET);
        $this->assertArrayNotHasKey('ppcart_view_debug_log_nonce', $_GET);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        ppcart_unregister_legacy_request_field_bridges();

        $_GET = [
            'sc_view_log' => '1',
            'sc_view_debug_log_nonce' => 'leftover-nonce',
        ];
        $_POST = [];
        $_REQUEST = $_GET;

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_request_fields();

        $this->assertSame('1', $_GET['ppcart_view_log']);
        $this->assertSame('1', $_GET['sc_view_log']);
        $this->assertSame('leftover-nonce', $_GET['ppcart_view_debug_log_nonce']);
        $this->assertSame('leftover-nonce', $_GET['sc_view_debug_log_nonce']);
    }
}
