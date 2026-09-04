<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class FixturesTest extends WPTestCase
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

    public function test_IT_245_ppcart_fixtures_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Fixtures', 'ppcart-fixtures.php');
        $this->assertCanonicalWithoutLegacy('PPCart_Fixtures', 'NCS_Cart_Fixtures');
    }

    public function test_IT_246_ppcart_fixtures_admin_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Fixtures_Admin', 'ppcart-fixtures.php');
        $this->assertCanonicalWithoutLegacy('PPCart_Fixtures_Admin', 'NCS_Cart_Fixtures_Admin');
    }

    public function test_IT_247_ppcart_regression_fixtures_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Regression_Fixtures', 'ppcart-regression-fixtures.php');
        $this->assertCanonicalWithoutLegacy('PPCart_Regression_Fixtures', 'NCS_Cart_Regression_Fixtures');
    }

    public function test_IT_248_ppcart_smoke_fixtures_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Smoke_Fixtures', 'ppcart-smoke-fixtures.php');
        $this->assertCanonicalWithoutLegacy('PPCart_Smoke_Fixtures', 'NCS_Cart_Smoke_Fixtures');
    }

    public function test_IT_249_ppcart_admin_fixtures_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Admin_Fixtures', 'ppcart-admin-fixtures.php');
        $this->assertCanonicalWithoutLegacy('PPCart_Admin_Fixtures', 'NCS_Cart_Admin_Fixtures');
    }

    public function test_IT_250_ppcart_regression_paypal_mock_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Regression_PayPal_Mock', 'ppcart-regression-paypal-mock.php');
        $this->assertCanonicalWithoutLegacy('PPCart_Regression_PayPal_Mock', 'NCS_Cart_Regression_PayPal_Mock');
    }

    public function test_IT_252_ppcart_regression_stripe_mock_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Regression_Stripe_Mock', 'ppcart-regression-stripe-mock.php');
        $this->assertCanonicalWithoutLegacy('PPCart_Regression_Stripe_Mock', 'NCS_Cart_Regression_Stripe_Mock');
        $this->assertCanonicalWithoutLegacy('PPCart_Regression_Stripe_Http_Client', 'NCS_Cart_Regression_Stripe_Http_Client');
    }

    public function test_IT_251_ppcart_admin_workflow_state_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureAdminWorkflowStateLoaded();
        $this->assertCanonicalWithoutLegacy('PPCart_Admin_Workflow_State', 'NCS_Cart_Admin_Workflow_State');
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

    private function ensureFixtureClassLoaded(string $class, string $relativePath): void
    {
        if (class_exists($class, false)) {
            return;
        }

        $path = PPCART_PLUGIN_ROOT . 'tests/' . $relativePath;

        if (! is_readable($path)) {
            $this->markTestSkipped(sprintf('Fixture file is not available: %s', $relativePath));
        }

        require_once $path;
    }

    private function ensureAdminWorkflowStateLoaded(): void
    {
        if (class_exists('PPCart_Admin_Workflow_State', false)) {
            return;
        }

        $path = PPCART_PLUGIN_ROOT . 'tests/bin/lib/admin-workflow-state.php';

        if (! is_readable($path)) {
            $this->markTestSkipped('Admin workflow fixture state file is not available.');
        }

        require_once $path;
    }
}
