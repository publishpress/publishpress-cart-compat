<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class AffiliateCookiesTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var array<string, mixed>
     */
    private $originalCookie = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalCookie = $_COOKIE;
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_cookie_bridges')) {
            ppcart_unregister_legacy_cookie_bridges();
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('ppcart_unregister_legacy_cookie_bridges')) {
            ppcart_unregister_legacy_cookie_bridges();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $_COOKIE = $this->originalCookie;

        parent::tearDown();
    }

    /**
     * @test-id IT-365
     */
    public function test_IT_365_leftover_affiliate_cookies_copy_only_when_compatibility_mode_is_on(): void
    {
        $_COOKIE = [ 'sc_affiliate_id' => '9' ];

        ppcart_copy_legacy_affiliate_cookies();

        $this->assertArrayNotHasKey('ppcart_affiliate_id', $_COOKIE);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        if (function_exists('ppcart_unregister_legacy_cookie_bridges')) {
            ppcart_unregister_legacy_cookie_bridges();
        }

        $_COOKIE = [ 'sc_affiliate_id' => '9' ];
        ppcart_copy_legacy_affiliate_cookies();

        $this->assertSame('9', $_COOKIE['ppcart_affiliate_id']);
        $this->assertSame('9', $_COOKIE['sc_affiliate_id']);
    }
}
