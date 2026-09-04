<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCart_Admin_Order_Controller;
use PPCart_Admin_Order_List_Controller;
use PPCart_Admin_Order_Refund_Controller;
use PPCart_Admin_Page_Notices;
use PPCart_Admin_Subscription_Controller;
use PPCart_Admin_Test_Mode_Notice_Controller;
use PPCart_Dashboard_Data;
use PPCart_Dashboard_Order_Data;
use PPCart_Dashboard_Plan_Data;
use PPCart_Dashboard_Renderer;
use PPCart_Dashboard_Subscription_Data;
use PPCart_Dashboard_Widget;

class AdminControllersTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->ensureAdminControllerClassesLoaded();
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        parent::tearDown();
    }

    public function test_IT_208_ppcart_admin_order_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Admin_Order_Controller::class,
            'NCS_Cart_Admin_Order_Controller'
        );
    }

    public function test_IT_209_ppcart_admin_order_list_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Admin_Order_List_Controller::class,
            'NCS_Cart_Admin_Order_List_Controller'
        );
    }

    public function test_IT_210_ppcart_admin_order_refund_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Admin_Order_Refund_Controller::class,
            'NCS_Cart_Admin_Order_Refund_Controller'
        );
    }

    public function test_IT_211_ppcart_admin_subscription_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Admin_Subscription_Controller::class,
            'NCS_Cart_Admin_Subscription_Controller'
        );
    }

    public function test_IT_212_ppcart_admin_test_mode_notice_controller_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Admin_Test_Mode_Notice_Controller::class,
            'NCS_Cart_Admin_Test_Mode_Notice_Controller'
        );
    }

    public function test_IT_213_ppcart_admin_page_notices_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Admin_Page_Notices::class,
            'NCS_Cart_Admin_Page_Notices'
        );
    }

    public function test_IT_214_ppcart_dashboard_widget_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Dashboard_Widget::class,
            'NCS_Cart_Dashboard_Widget'
        );
    }

    public function test_IT_215_ppcart_dashboard_data_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Dashboard_Data::class,
            'NCS_Cart_Dashboard_Data'
        );
    }

    public function test_IT_216_ppcart_dashboard_renderer_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Dashboard_Renderer::class,
            'NCS_Cart_Dashboard_Renderer'
        );
    }

    public function test_IT_217_ppcart_dashboard_order_data_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Dashboard_Order_Data::class,
            'NCS_Cart_Dashboard_Order_Data'
        );
    }

    public function test_IT_218_ppcart_dashboard_plan_data_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Dashboard_Plan_Data::class,
            'NCS_Cart_Dashboard_Plan_Data'
        );
    }

    public function test_ppcart_dashboard_subscription_data_is_canonical_while_legacy_name_stays_unregistered(): void
    {
        $this->assertCanonicalWithoutLegacy(
            PPCart_Dashboard_Subscription_Data::class,
            'NCS_Cart_Dashboard_Subscription_Data'
        );
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

    private function ensureAdminControllerClassesLoaded(): void
    {
        $root = PPCART_PLUGIN_ROOT;

        $files = [
            'admin/controllers/class-ppcart-admin-order-controller.php',
            'admin/controllers/class-ppcart-admin-order-list-controller.php',
            'admin/controllers/class-ppcart-admin-order-refund-controller.php',
            'admin/controllers/class-ppcart-admin-subscription-controller.php',
            'admin/controllers/class-ppcart-admin-test-mode-notice-controller.php',
            'admin/controllers/class-ppcart-admin-page-notices.php',
            'admin/dashboard/class-ppcart-dashboard-order-data.php',
            'admin/dashboard/class-ppcart-dashboard-subscription-data.php',
            'admin/dashboard/class-ppcart-dashboard-plan-data.php',
            'admin/dashboard/class-ppcart-dashboard-data.php',
            'admin/dashboard/class-ppcart-dashboard-renderer.php',
            'admin/dashboard/class-ppcart-dashboard-widget.php',
        ];

        foreach ($files as $file) {
            $path = $root . $file;
            if (is_readable($path)) {
                require_once $path;
            }
        }
    }
}
