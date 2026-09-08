<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class RequestNameGetTest extends WPTestCase
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
     * @test-id IT-353
     */
    public function test_IT_353_leftover_slm_order_copies_only_when_compatibility_mode_is_on(): void
    {
        $_POST = [];
        $_GET = [ 'sc-slm-order' => '42' ];
        $_REQUEST = $_GET;

        ppcart_copy_legacy_request_fields();

        $this->assertArrayNotHasKey('ppcart-slm-order', $_GET);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        ppcart_unregister_legacy_request_field_bridges();

        $_POST = [];
        $_GET = [ 'sc-slm-order' => '42' ];
        $_REQUEST = $_GET;

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_request_fields();

        $this->assertSame('42', $_GET['ppcart-slm-order']);
        $this->assertSame('42', $_GET['sc-slm-order']);
    }
}
