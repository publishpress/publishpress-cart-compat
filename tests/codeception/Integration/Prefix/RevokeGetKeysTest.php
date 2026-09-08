<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class RevokeGetKeysTest extends WPTestCase
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
     * @test-id IT-360
     */
    public function test_IT_360_leftover_revoke_get_keys_copy_only_when_compatibility_mode_is_on(): void
    {
        $_POST = [];
        $_GET = [
            'sc-revoke' => '12',
            'sc-revoked' => 'license.pdf',
        ];
        $_REQUEST = $_GET;

        ppcart_copy_legacy_request_fields();

        $this->assertArrayNotHasKey('ppcart-revoke', $_GET);
        $this->assertArrayNotHasKey('ppcart-revoked', $_GET);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        ppcart_unregister_legacy_request_field_bridges();

        $_POST = [];
        $_GET = [
            'sc-revoke' => '12',
            'sc-revoked' => 'license.pdf',
        ];
        $_REQUEST = $_GET;

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_request_fields();

        $this->assertSame('12', $_GET['ppcart-revoke']);
        $this->assertSame('license.pdf', $_GET['ppcart-revoked']);
        $this->assertSame('12', $_GET['sc-revoke']);
        $this->assertSame('license.pdf', $_GET['sc-revoked']);
    }
}
