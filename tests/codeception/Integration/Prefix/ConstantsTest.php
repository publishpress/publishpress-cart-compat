<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class ConstantsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        parent::tearDown();
    }

    public function test_IT_260_bootstrap_defines_canonical_constants_without_legacy_aliases(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());

        foreach ($this->bootstrapCanonicalConstants() as $canonical) {
            $this->assertTrue(defined($canonical), sprintf('%s should be defined at bootstrap.', $canonical));
        }

        $this->assertTrue(defined('PPCART_MINIMUM_PHP_VERSION'));
        $this->assertTrue(defined('PPCART_MINIMUM_WP_VERSION'));

        foreach (ncs_cart_get_legacy_constant_map() as $legacy_names) {
            foreach ($legacy_names as $legacy) {
                $this->assertFalse(
                    defined($legacy),
                    sprintf('%s must stay undefined while Compatibility Mode is off.', $legacy)
                );
            }
        }
    }

    /**
     * @return array<int, string>
     */
    private function bootstrapCanonicalConstants(): array
    {
        return [
            'PPCART_FREE_LOADED',
            'PPCART_VERSION',
            'PPCART_BASE_DIR',
            'PPCART_BASE_URL',
            'PPCART_BASE_FILE',
            'PPCART_STYLESHEET_PATH',
            'PPCART_LIB_VENDOR_PATH',
            'PPCART_VENDOR_ASSETS_URL',
            'PPCART_STRIPE_CONNECT_SERVER_URL',
        ];
    }
}
