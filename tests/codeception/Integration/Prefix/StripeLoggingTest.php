<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCart_Debug_Log_Viewer;
use PPCart_Debug_Logger;
use PPCart_Files;
use PPCart_Secrets;
use PPCart_Stripe_Save_Helper;
use PPCart_Stripe_Sync;
use PPCart_Stripe_Sync_Context;
use PPCart_Stripe_Webhook_Logger;

class StripeLoggingTest extends WPTestCase
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

    public function test_IT_220_ppcart_stripe_sync_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Stripe_Sync::class, 'NCS_Cart_Stripe_Sync');
    }

    public function test_IT_221_ppcart_stripe_sync_context_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Stripe_Sync_Context::class,
            'NCS_Cart_Stripe_Sync_Context'
        );
    }

    public function test_IT_222_ppcart_stripe_save_helper_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Stripe_Save_Helper::class,
            'NCS_Cart_Stripe_Save_Helper'
        );
    }

    public function test_IT_223_ppcart_stripe_webhook_logger_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Stripe_Webhook_Logger::class,
            'NCS_Cart_Stripe_Webhook_Logger'
        );
    }

    public function test_IT_224_ppcart_debug_logger_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Debug_Logger::class, 'NCS_Cart_Debug_Logger');
    }

    public function test_IT_225_ppcart_debug_log_viewer_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Debug_Log_Viewer::class,
            'NCS_Cart_Debug_Log_Viewer'
        );
    }

    public function test_IT_226_ppcart_secrets_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Secrets::class, 'NCS_Cart_Secrets');
    }

    public function test_ppcart_files_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Files::class, 'NCS_Cart_Files');
    }

    private function assertCanonicalWithoutLegacy(string $canonical, string $legacy): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(class_exists($canonical, false), sprintf('%s must be loaded.', $canonical));
        $this->assertFalse(
            class_exists($legacy, false),
            sprintf('%s must not be aliased while Compatibility Mode is off.', $legacy)
        );
    }
}
