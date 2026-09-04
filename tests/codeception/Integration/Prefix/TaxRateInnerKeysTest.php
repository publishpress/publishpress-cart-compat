<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class TaxRateInnerKeysTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var mixed
     */
    private $previousRates;

    protected function setUp(): void
    {
        parent::setUp();

        // pre_update_option__ppcart_tax_rates still points at missing
        // PPCart_Admin_Settings::validate_custom_tax. Seed the leftover row
        // without running that callback.
        remove_all_filters('pre_update_option__ppcart_tax_rates');

        $this->previousRates = get_option('_ppcart_tax_rates', false);
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        update_option(
            '_ppcart_tax_rates',
            [
                '_sc_tax_rate_title' => [ 'VAT' ],
                '_sc_tax_rate_slug' => [ 'vat' ],
                '_sc_tax_rate' => [ '20' ],
            ]
        );
    }

    protected function tearDown(): void
    {
        remove_all_filters('pre_update_option__ppcart_tax_rates');
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (false === $this->previousRates) {
            delete_option('_ppcart_tax_rates');
        } else {
            update_option('_ppcart_tax_rates', $this->previousRates);
        }

        parent::tearDown();
    }

    /**
     * @test-id IT-341
     */
    public function test_IT_341_leftover_tax_rate_inner_keys_copy_only_when_compatibility_mode_is_on(): void
    {
        $off = ppcart_get_tax_data('vat');
        $this->assertTrue(empty($off));
        $this->assertSame(
            [ 'VAT' ],
            get_option('_ppcart_tax_rates')['_sc_tax_rate_title']
        );
        $this->assertArrayNotHasKey('_ppcart_tax_rate_title', get_option('_ppcart_tax_rates'));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $on = ppcart_get_tax_data('vat');
        $this->assertSame('VAT', $on['tax_rate_title']);
        $this->assertSame('vat', $on['tax_rate_slug']);
        $this->assertSame('20', $on['tax_rate']);

        $stored = get_option('_ppcart_tax_rates');
        $this->assertSame([ 'VAT' ], $stored['_sc_tax_rate_title']);
        $this->assertArrayNotHasKey('_ppcart_tax_rate_title', $stored);
    }
}
