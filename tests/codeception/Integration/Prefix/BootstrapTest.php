<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCart;
use PPCart_Activator;
use PPCart_Admin_Hook_Registrar;
use PPCart_Admin_Screens;
use PPCart_Currencies;
use PPCart_Deactivator;
use PPCart_Dependency_Loader;
use PPCart_I18n;
use PPCart_Loader;
use PPCart_Order_Refunds;
use PPCart_Post_Types;
use PPCart_Product_Duplicator;
use PPCart_Public_Hook_Registrar;
use PPCart_Sanitize;
use PPCart_Status_Labels;
use PPCart_Upgrade;
use PPCart_Version_Notices;

class BootstrapTest extends WPTestCase
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

    public function test_IT_228_ppcart_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart::class, 'NCS_Cart');
    }

    public function test_IT_229_ppcart_loader_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Loader::class, 'NCS_Cart_Loader');
    }

    public function test_IT_230_ppcart_activator_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureActivatorLoaded();
        $this->assertCanonicalWithoutLegacy(PPCart_Activator::class, 'NCS_Cart_Activator');
    }

    public function test_IT_231_ppcart_deactivator_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureDeactivatorLoaded();
        $this->assertCanonicalWithoutLegacy(PPCart_Deactivator::class, 'NCS_Cart_Deactivator');
    }

    public function test_IT_232_ppcart_i18n_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_I18n::class, 'NCS_Cart_I18n');
    }

    public function test_IT_233_ppcart_upgrade_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureUpgradeLoaded();
        $this->assertCanonicalWithoutLegacy(PPCart_Upgrade::class, 'NCS_Cart_Upgrade');
    }

    public function test_IT_234_ppcart_admin_screens_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Admin_Screens::class, 'NCS_Cart_Admin_Screens');
    }

    public function test_IT_235_ppcart_version_notices_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Version_Notices::class, 'NCS_Cart_Version_Notices');
    }

    public function test_IT_236_ppcart_post_types_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Post_Types::class, 'NCS_Cart_Post_Types');
    }

    public function test_IT_237_ppcart_dependency_loader_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Dependency_Loader::class, 'NCS_Cart_Dependency_Loader');
    }

    public function test_IT_238_ppcart_admin_hook_registrar_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureHookRegistrarsLoaded();
        $this->assertCanonicalWithoutLegacy(PPCart_Admin_Hook_Registrar::class, 'NCS_Cart_Admin_Hook_Registrar');
    }

    public function test_IT_239_ppcart_public_hook_registrar_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureHookRegistrarsLoaded();
        $this->assertCanonicalWithoutLegacy(PPCart_Public_Hook_Registrar::class, 'NCS_Cart_Public_Hook_Registrar');
    }

    public function test_IT_240_ppcart_status_labels_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Status_Labels::class, 'NCS_Cart_Status_Labels');
    }

    public function test_IT_241_ppcart_sanitize_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Sanitize::class, 'NCS_Cart_Sanitize');
    }

    public function test_IT_242_ppcart_product_duplicator_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Product_Duplicator::class, 'NCS_Cart_Product_Duplicator');
    }

    public function test_IT_243_ppcart_order_refunds_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(PPCart_Order_Refunds::class, 'NCS_Cart_Order_Refunds');
    }

    public function test_IT_244_ppcart_currencies_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->ensureCurrenciesLoaded();
        $this->assertCanonicalWithoutLegacy(PPCart_Currencies::class, 'NCS_Cart_Currencies');
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

    private function ensureActivatorLoaded(): void
    {
        if (! class_exists(PPCart_Activator::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/class-ppcart-activator.php';
        }
    }

    private function ensureDeactivatorLoaded(): void
    {
        if (! class_exists(PPCart_Deactivator::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/class-ppcart-deactivator.php';
        }
    }

    private function ensureUpgradeLoaded(): void
    {
        if (! class_exists(PPCart_Upgrade::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/class-ppcart-upgrade.php';
        }
    }

    private function ensureHookRegistrarsLoaded(): void
    {
        if (! class_exists(PPCart_Admin_Hook_Registrar::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/bootstrap/class-ppcart-admin-hook-registrar.php';
        }

        if (! class_exists(PPCart_Public_Hook_Registrar::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/bootstrap/class-ppcart-public-hook-registrar.php';
        }
    }

    private function ensureCurrenciesLoaded(): void
    {
        if (! class_exists(PPCart_Currencies::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/functions/class-ppcart-currencies.php';
        }
    }
}
