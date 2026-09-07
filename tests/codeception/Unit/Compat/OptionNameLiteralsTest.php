<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class OptionNameLiteralsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        WordPressStubContext::set(
            'get_option',
            function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                if (array_key_exists($option, $options)) {
                    return $options[$option];
                }

                return $default;
            }
        );
        WordPressStubContext::setState('options', []);
    }

    protected function _after(): void
    {
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-285
     */
    public function test_UT_285_leftover_option_names_are_sensitive_only_via_compat_filters(): void
    {
        $copied = ppcart_append_legacy_sensitive_option_names(
            [
                '_ppcart_mailchimp_api',
                '_ppcart_drip_api_key',
            ]
        );

        $this->assertContains('_ppcart_mailchimp_api', $copied);
        $this->assertContains('_sc_mailchimp_api', $copied);
        $this->assertContains('_ppcart_drip_api_key', $copied);
        $this->assertContains('_sc_drip_api_key', $copied);

        $this->assertTrue(ppcart_is_legacy_sensitive_option('_sc_custom_api_key'));
        $this->assertFalse(ppcart_is_legacy_sensitive_option('_sc_currency'));
        $this->assertFalse(ppcart_is_legacy_sensitive_option('_ppcart_mailchimp_api'));

        $canonical = [ '_ppcart_mailchimp_api' ];
        $this->assertSame($canonical, ppcart_maybe_append_legacy_sensitive_option_names($canonical));
        $this->assertFalse(ppcart_maybe_mark_legacy_sensitive_option(false, '_sc_custom_api_key'));
        $this->assertTrue(ppcart_maybe_mark_legacy_sensitive_option(true, '_sc_currency'));

        WordPressStubContext::setState(
            'options',
            [
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1',
            ]
        );

        $this->assertContains(
            '_sc_mailchimp_api',
            ppcart_maybe_append_legacy_sensitive_option_names($canonical)
        );
        $this->assertTrue(ppcart_maybe_mark_legacy_sensitive_option(false, '_sc_custom_api_key'));
        $this->assertFalse(ppcart_maybe_mark_legacy_sensitive_option(false, '_sc_currency'));
    }
}
