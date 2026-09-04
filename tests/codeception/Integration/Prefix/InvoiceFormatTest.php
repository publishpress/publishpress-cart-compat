<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class InvoiceFormatTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var mixed
     */
    private $previousFormat;

    protected function setUp(): void
    {
        parent::setUp();

        $this->previousFormat = get_option('_ppcart_invoice_format', false);
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        update_option('_ppcart_invoice_format', 'sc_ns');
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (false === $this->previousFormat) {
            delete_option('_ppcart_invoice_format');
        } else {
            update_option('_ppcart_invoice_format', $this->previousFormat);
        }

        parent::tearDown();
    }

    /**
     * @test-id IT-340
     */
    public function test_IT_340_leftover_invoice_format_maps_only_when_compatibility_mode_is_on(): void
    {
        $this->assertSame('sc_ns', ppcart_get_invoice_format());
        $this->assertSame('sc_ns', get_option('_ppcart_invoice_format'));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $this->assertSame('ppcart_ns', ppcart_get_invoice_format());
        $this->assertSame('sc_ns', get_option('_ppcart_invoice_format'));
    }
}
