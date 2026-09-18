<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class AffiliateCookiesTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var array<string, mixed>
     */
    private $originalCookie = [];

    protected function _before(): void
    {
        $this->originalCookie = $_COOKIE;
        $this->resetCookieBridges();

        WordPressStubContext::setState('options', []);
        WordPressStubContext::set(
            'get_option',
            static function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                return array_key_exists($option, $options) ? $options[ $option ] : $default;
            }
        );
        WordPressStubContext::set(
            'add_action',
            static function () {
                return true;
            }
        );
        WordPressStubContext::set(
            'remove_action',
            static function () {
                return true;
            }
        );
    }

    protected function _after(): void
    {
        $this->resetCookieBridges();
        $_COOKIE = $this->originalCookie;
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-312
     */
    public function test_UT_312_cookie_map_covers_leftover_affiliate_ids(): void
    {
        $map = ncs_cart_get_legacy_cookie_map();

        $this->assertSame([ 'sc_affiliate_id', 'affiliate_id' ], $map['ppcart_affiliate_id']);
    }

    /**
     * @test-id UT-312
     */
    public function test_UT_312_cookie_copy_is_gated_canonical_wins_and_leaves_leftover(): void
    {
        $_COOKIE = [ 'sc_affiliate_id' => '9' ];

        ppcart_copy_legacy_affiliate_cookies();

        $this->assertArrayNotHasKey('ppcart_affiliate_id', $_COOKIE);

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );
        $this->resetCookieBridges();

        $_COOKIE = [
            'sc_affiliate_id' => '9',
            'ppcart_affiliate_id' => '3',
        ];

        ppcart_copy_legacy_affiliate_cookies();

        $this->assertSame('3', $_COOKIE['ppcart_affiliate_id']);
        $this->assertSame('9', $_COOKIE['sc_affiliate_id']);

        $this->resetCookieBridges();
        $_COOKIE = [ 'affiliate_id' => '4' ];

        ppcart_copy_legacy_affiliate_cookies();

        $this->assertSame('4', $_COOKIE['ppcart_affiliate_id']);
        $this->assertSame('4', $_COOKIE['affiliate_id']);
    }

    private function resetCookieBridges(): void
    {
        if (function_exists('ppcart_unregister_legacy_cookie_bridges')) {
            ppcart_unregister_legacy_cookie_bridges();
        }
    }
}
