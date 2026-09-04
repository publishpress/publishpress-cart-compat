<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Admin;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCart;
use PPCart_Activator;
use PPCart_Admin;
use PPCart_Admin_Hook_Registrar;
use PPCart_Admin_Order_Controller;
use PPCart_Admin_Order_List_Controller;
use PPCart_Admin_Order_Refund_Controller;
use PPCart_Admin_Page_Notices;
use PPCart_Admin_Screens;
use PPCart_Admin_Subscription_Controller;
use PPCart_Admin_Test_Mode_Notice_Controller;
use PPCart_Currencies;
use PPCart_Dashboard_Data;
use PPCart_Dashboard_Order_Data;
use PPCart_Dashboard_Plan_Data;
use PPCart_Dashboard_Renderer;
use PPCart_Dashboard_Subscription_Data;
use PPCart_Dashboard_Widget;
use PPCart_Deactivator;
use PPCart_Debug_Log_Viewer;
use PPCart_Debug_Logger;
use PPCart_Dependency_Loader;
use PPCart_Files;
use PPCart_Helper;
use PPCart_I18n;
use PPCart_Loader;
use PPCart_Order;
use PPCart_Order_Helper;
use PPCart_Order_Item;
use PPCart_Order_Items;
use PPCart_Order_Refunds;
use PPCart_Paypal;
use PPCart_Post_Types;
use PPCart_Price_Format;
use PPCart_Product_Duplicator;
use PPCart_Product_Metaboxes;
use PPCart_Public;
use PPCart_Public_Hook_Registrar;
use PPCart_Public_Subscription_Checkout_Controller;
use PPCart_Sanitize;
use PPCart_Secrets;
use PPCart_Status_Labels;
use PPCart_Stripe;
use PPCart_Stripe_Save_Helper;
use PPCart_Stripe_Sync;
use PPCart_Stripe_Sync_Context;
use PPCart_Stripe_Webhook_Logger;
use PPCart_Subscription;
use PPCart_Upgrade;
use PPCart_Version_Notices;

class ZPrefixCompatibilityModeOnTest extends WPTestCase
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

    public function test_IT_163_enabling_compatibility_mode_registers_the_shipped_studiocart_class_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $registered = 0;

        foreach ($this->shippedAliasMap() as $canonical => $legacy_names) {
            if (! class_exists($canonical, false)) {
                foreach ($legacy_names as $legacy) {
                    $this->assertFalse(class_exists($legacy, false));
                }
                continue;
            }

            foreach ($legacy_names as $legacy) {
                $this->assertTrue(class_exists($legacy, false), sprintf('%s alias is missing.', $legacy));
                $this->assertTrue(is_a($legacy, $canonical, true));
            }
            $registered++;
        }

        $this->assertGreaterThan(0, $registered);
        $this->assertInstanceOf(PPCart_Order::class, new \ScrtOrder());
    }

    public function test_IT_261_enabling_compatibility_mode_defines_legacy_constant_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_constants();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_constants_registered());

        $aliased = 0;

        foreach (ncs_cart_get_legacy_constant_map() as $canonical => $legacy_names) {
            if (! defined($canonical)) {
                foreach ($legacy_names as $legacy) {
                    $this->assertFalse(
                        defined($legacy),
                        sprintf('%s must stay undefined when %s is not defined.', $legacy, $canonical)
                    );
                }
                continue;
            }

            foreach ($legacy_names as $legacy) {
                $this->assertTrue(defined($legacy), sprintf('%s alias is missing.', $legacy));
                $this->assertSame(
                    constant($canonical),
                    constant($legacy),
                    sprintf('%s must match %s.', $legacy, $canonical)
                );
                $aliased++;
            }
        }

        $this->assertGreaterThan(0, $aliased);
    }

    public function test_IT_166_PPCart_Order_compatibility_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('PPCart_Order', false));
        $this->assertTrue(class_exists('NCS_Cart_Order', false));
        $this->assertTrue(class_exists('ScrtOrder', false));
        $this->assertInstanceOf(PPCart_Order::class, new \NCS_Cart_Order());
        $this->assertInstanceOf(PPCart_Order::class, new \ScrtOrder());
    }

    public function test_IT_167_PPCart_Subscription_compatibility_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('PPCart_Subscription', false));
        $this->assertTrue(class_exists('NCS_Cart_Subscription', false));
        $this->assertTrue(class_exists('ScrtSubscription', false));
        $this->assertInstanceOf(PPCart_Subscription::class, new \NCS_Cart_Subscription());
        $this->assertInstanceOf(PPCart_Subscription::class, new \ScrtSubscription());
    }

    public function test_IT_168_PPCart_Order_Item_compatibility_aliases(): void
    {
        $this->ensureOrderItemClassesLoaded();

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('PPCart_Order_Item', false));
        $this->assertTrue(class_exists('NCS_Cart_Order_Item', false));
        $this->assertTrue(class_exists('ScrtOrderItem', false));
        $this->assertInstanceOf(PPCart_Order_Item::class, new \NCS_Cart_Order_Item());
        $this->assertInstanceOf(PPCart_Order_Item::class, new \ScrtOrderItem());
    }

    public function test_IT_169_PPCart_Order_Items_compatibility_aliases(): void
    {
        $this->ensureOrderItemClassesLoaded();

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('PPCart_Order_Items', false));
        $this->assertTrue(class_exists('NCS_Cart_Order_Items', false));
        $this->assertInstanceOf(PPCart_Order_Items::class, new \NCS_Cart_Order_Items());
    }

    public function test_PPCart_Collection_compatibility_aliases_when_pro_is_available(): void
    {
        if (! class_exists('PPCart_Collection', false)) {
            $this->markTestSkipped('PPCart_Collection is not loaded in this edition.');
        }

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('NCS_Cart_Collection', false));
        $this->assertTrue(class_exists('ScrtCollection', false));
        $this->assertInstanceOf('PPCart_Collection', new \NCS_Cart_Collection());
        $this->assertInstanceOf('PPCart_Collection', new \ScrtCollection());
    }

    public function test_IT_178_compatibility_mode_registers_gutenberg_class_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        foreach ($this->gutenbergClassMap() as $canonical => $legacy) {
            $this->assertTrue(class_exists($legacy, false), sprintf('%s alias is missing.', $legacy));
            $this->assertTrue(is_a($legacy, $canonical, true));
        }

        $gutenbergIndex = PPCART_PLUGIN_ROOT . 'includes/integrations/gutenberg/index.php';

        $this->assertInstanceOf('PPCart_Gutenberg_Bootstrap', new \NCS_Cart_Gutenberg_Bootstrap());
        $this->assertInstanceOf(
            'PPCart_Gutenberg_Assets',
            new \NCS_Cart_Gutenberg_Assets(
                $gutenbergIndex,
                new \PPCart_Checkout_Renderer(),
                new \PPCart_Account_Renderer()
            )
        );
        $this->assertInstanceOf('PPCart_Checkout_Renderer', new \NCS_Cart_Checkout_Renderer());
        $this->assertInstanceOf(
            'PPCart_Checkout_Preview_Controller',
            new \NCS_Cart_Checkout_Preview_Controller(new \PPCart_Checkout_Renderer())
        );
        $this->assertInstanceOf('PPCart_Account_Styles', new \NCS_Cart_Account_Styles());
        $this->assertInstanceOf('PPCart_Account_Renderer', new \NCS_Cart_Account_Renderer());
        $this->assertInstanceOf(
            'PPCart_Account_Detail_Controller',
            new \NCS_Cart_Account_Detail_Controller(new \PPCart_Account_Renderer())
        );
        $this->assertInstanceOf('PPCart_Account_Context', new \NCS_Cart_Account_Context());
        $this->assertInstanceOf('PPCart_Product_Template', new \NCS_Cart_Product_Template());
    }

    public function test_IT_188_compatibility_mode_registers_public_class_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        foreach ($this->publicClassMap() as $canonical => $legacy) {
            $this->assertTrue(class_exists($legacy, false), sprintf('%s alias is missing.', $legacy));
            $this->assertTrue(is_a($legacy, $canonical, true));
        }

        $this->assertInstanceOf(PPCart_Public::class, new \NCS_Cart_Public('', '', ''));
        $this->assertInstanceOf(PPCart_Paypal::class, new \NCS_Cart_Paypal('', ''));
        $this->assertSame(
            PPCart_Public_Subscription_Checkout_Controller::instance(),
            \NCS_Cart_Public_Subscription_Checkout_Controller::instance()
        );
    }

    public function test_IT_189_PPCart_Helper_compatibility_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('PPCart_Helper', false));
        $this->assertTrue(class_exists('NCS_Cart_Helper', false));
        $this->assertTrue(class_exists('NCS_Helper', false));
        $this->assertInstanceOf(PPCart_Helper::class, new \NCS_Cart_Helper());
        $this->assertInstanceOf(PPCart_Helper::class, new \NCS_Helper());
    }

    public function test_IT_190_PPCart_Order_Helper_compatibility_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('PPCart_Order_Helper', false));
        $this->assertTrue(class_exists('NCS_Cart_Order_Helper', false));
        $this->assertTrue(class_exists('NCS_Order_Helper', false));
        $this->assertInstanceOf(PPCart_Order_Helper::class, new \NCS_Cart_Order_Helper());
        $this->assertInstanceOf(PPCart_Order_Helper::class, new \NCS_Order_Helper());
    }

    public function test_IT_191_PPCart_Stripe_compatibility_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('PPCart_Stripe', false));
        $this->assertTrue(class_exists('NCS_Cart_Stripe', false));
        $this->assertTrue(class_exists('NCS_Stripe', false));
        $this->assertInstanceOf(PPCart_Stripe::class, new \NCS_Cart_Stripe());
        $this->assertInstanceOf(PPCart_Stripe::class, new \NCS_Stripe());
    }

    public function test_IT_192_PPCart_Price_Format_compatibility_aliases(): void
    {
        $this->ensurePriceFormatClassLoaded();

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('PPCart_Price_Format', false));
        $this->assertTrue(class_exists('NCS_Cart_Price_Format', false));
        $this->assertTrue(class_exists('NCS_Price_Format', false));
        $this->assertInstanceOf(PPCart_Price_Format::class, new \NCS_Cart_Price_Format());
        $this->assertInstanceOf(PPCart_Price_Format::class, new \NCS_Price_Format());
    }

    public function test_PPCart_Elementor_OrderForm_compatibility_aliases_when_pro_is_available(): void
    {
        if (! class_exists('PPCart_Elementor_OrderForm', false)) {
            $this->markTestSkipped('PPCart_Elementor_OrderForm is not loaded in this edition.');
        }

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(class_exists('NCS_Cart_Elementor_OrderForm', false));
        $this->assertTrue(class_exists('NCS_Elementor_OrderForm', false));
        $this->assertInstanceOf('PPCart_Elementor_OrderForm', new \NCS_Cart_Elementor_OrderForm());
        $this->assertInstanceOf('PPCart_Elementor_OrderForm', new \NCS_Elementor_OrderForm());
    }

    public function test_IT_207_compatibility_mode_registers_admin_class_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        foreach ($this->adminClassMap() as $canonical => $legacy) {
            $this->assertTrue(class_exists($legacy, false), sprintf('%s alias is missing.', $legacy));
            $this->assertTrue(is_a($legacy, $canonical, true));
        }

        $this->assertInstanceOf(PPCart_Admin::class, new \NCS_Cart_Admin('', '', ''));
        $this->assertInstanceOf(PPCart_Product_Metaboxes::class, new \NCS_Cart_Product_Metaboxes('', '', ''));
    }

    public function test_IT_219_compatibility_mode_registers_admin_controller_and_dashboard_aliases(): void
    {
        $this->ensureAdminControllerClassesLoaded();

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        foreach ($this->adminControllerClassMap() as $canonical => $legacy) {
            $this->assertTrue(class_exists($legacy, false), sprintf('%s alias is missing.', $legacy));
            $this->assertTrue(is_a($legacy, $canonical, true));
        }

        $this->assertInstanceOf(
            PPCart_Admin_Order_Controller::class,
            new \NCS_Cart_Admin_Order_Controller('', '', '')
        );
        $this->assertInstanceOf(
            PPCart_Dashboard_Data::class,
            new \NCS_Cart_Dashboard_Data()
        );
    }

    public function test_IT_227_compatibility_mode_registers_stripe_logging_and_files_aliases(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        foreach ($this->stripeLoggingClassMap() as $canonical => $legacy) {
            $this->assertTrue(class_exists($legacy, false), sprintf('%s alias is missing.', $legacy));
            $this->assertTrue(is_a($legacy, $canonical, true));
        }

        $this->assertInstanceOf(PPCart_Stripe_Sync::class, new \NCS_Cart_Stripe_Sync());
        $this->assertInstanceOf(PPCart_Stripe_Sync_Context::class, new \NCS_Cart_Stripe_Sync_Context());
        $this->assertInstanceOf(PPCart_Stripe_Save_Helper::class, new \NCS_Cart_Stripe_Save_Helper());
        $this->assertInstanceOf(PPCart_Stripe_Webhook_Logger::class, new \NCS_Cart_Stripe_Webhook_Logger());
        $this->assertInstanceOf(PPCart_Debug_Logger::class, new \NCS_Cart_Debug_Logger());
        $this->assertInstanceOf(PPCart_Debug_Log_Viewer::class, new \NCS_Cart_Debug_Log_Viewer());
        $this->assertInstanceOf(PPCart_Secrets::class, new \NCS_Cart_Secrets());
        $this->assertInstanceOf(PPCart_Files::class, new \NCS_Cart_Files());
    }

    public function test_compatibility_mode_registers_bootstrap_class_aliases(): void
    {
        $this->ensureActivatorLoaded();
        $this->ensureDeactivatorLoaded();
        $this->ensureUpgradeLoaded();
        $this->ensureHookRegistrarsLoaded();
        $this->ensureCurrenciesLoaded();

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        foreach ($this->bootstrapClassMap() as $canonical => $legacy) {
            $this->assertTrue(class_exists($legacy, false), sprintf('%s alias is missing.', $legacy));
            $this->assertTrue(is_a($legacy, $canonical, true));
        }

        $this->assertTrue(is_a('NCS_Cart', PPCart::class, true));
        $this->assertInstanceOf(PPCart_Loader::class, new \NCS_Cart_Loader());
        $this->assertInstanceOf(PPCart_Sanitize::class, new \NCS_Cart_Sanitize());
    }

    public function test_IT_252_compatibility_mode_registers_fixture_class_aliases(): void
    {
        $this->ensureAllFixtureClassesLoaded();

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        foreach ($this->fixtureClassMap() as $canonical => $legacy) {
            $this->assertTrue(class_exists($canonical, false), sprintf('%s must be loaded.', $canonical));
            $this->assertTrue(class_exists($legacy, false), sprintf('%s alias is missing.', $legacy));
            $this->assertTrue(is_a($legacy, $canonical, true));
        }
    }

    public function test_IT_253_ppcart_fixtures_compatibility_alias_resolves(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Fixtures', 'ppcart-fixtures.php');

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(is_a('NCS_Cart_Fixtures', 'PPCart_Fixtures', true));
    }

    public function test_IT_254_ppcart_regression_fixtures_compatibility_alias_resolves(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Regression_Fixtures', 'ppcart-regression-fixtures.php');

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(is_a('NCS_Cart_Regression_Fixtures', 'PPCart_Regression_Fixtures', true));
    }

    public function test_IT_255_ppcart_admin_workflow_state_compatibility_alias_resolves(): void
    {
        $this->ensureAdminWorkflowStateLoaded();

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_aliases();

        $this->assertTrue(is_a('NCS_Cart_Admin_Workflow_State', 'PPCart_Admin_Workflow_State', true));
    }

    /**
     * @return array<string, string>
     */
    private function adminClassMap(): array
    {
        return [
            PPCart_Admin::class                          => 'NCS_Cart_Admin',
            'PPCart_Admin_Ajax'                          => 'NCS_Cart_Admin_Ajax',
            'PPCart_Admin_Filters'                       => 'NCS_Cart_Admin_Filters',
            'PPCart_Admin_Settings'                      => 'NCS_Cart_Admin_Settings',
            'PPCart_Admin_Stripe_Connect_Settings'       => 'NCS_Cart_Admin_Stripe_Connect_Settings',
            'PPCart_Admin_Stripe_Webhook_Settings'       => 'NCS_Cart_Admin_Stripe_Webhook_Settings',
            'PPCart_Admin_Reports'                       => 'NCS_Cart_Admin_Reports',
            'PPCart_Customer_Reports'                    => 'NCS_Cart_Customer_Reports',
            'PPCart_Contacts_Page'                       => 'NCS_Cart_Contacts_Page',
            'PPCart_Extension_Page'                      => 'NCS_Cart_Extension_Page',
            'PPCart_Order_Admin'                         => 'NCS_Cart_Order_Admin',
            'PPCart_Product_Admin'                       => 'NCS_Cart_Product_Admin',
            PPCart_Product_Metaboxes::class              => 'NCS_Cart_Product_Metaboxes',
            'PPCart_Product_Metabox_Option_Sources'      => 'NCS_Cart_Product_Metabox_Option_Sources',
            'PPCart_Order_Metaboxes'                     => 'NCS_Cart_Order_Metaboxes',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function adminControllerClassMap(): array
    {
        return [
            PPCart_Admin_Order_Controller::class            => 'NCS_Cart_Admin_Order_Controller',
            PPCart_Admin_Order_List_Controller::class       => 'NCS_Cart_Admin_Order_List_Controller',
            PPCart_Admin_Order_Refund_Controller::class     => 'NCS_Cart_Admin_Order_Refund_Controller',
            PPCart_Admin_Subscription_Controller::class     => 'NCS_Cart_Admin_Subscription_Controller',
            PPCart_Admin_Test_Mode_Notice_Controller::class => 'NCS_Cart_Admin_Test_Mode_Notice_Controller',
            PPCart_Admin_Page_Notices::class                => 'NCS_Cart_Admin_Page_Notices',
            PPCart_Dashboard_Widget::class                  => 'NCS_Cart_Dashboard_Widget',
            PPCart_Dashboard_Data::class                    => 'NCS_Cart_Dashboard_Data',
            PPCart_Dashboard_Renderer::class                => 'NCS_Cart_Dashboard_Renderer',
            PPCart_Dashboard_Order_Data::class              => 'NCS_Cart_Dashboard_Order_Data',
            PPCart_Dashboard_Plan_Data::class               => 'NCS_Cart_Dashboard_Plan_Data',
            PPCart_Dashboard_Subscription_Data::class       => 'NCS_Cart_Dashboard_Subscription_Data',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function bootstrapClassMap(): array
    {
        return [
            PPCart::class                       => 'NCS_Cart',
            PPCart_Loader::class                => 'NCS_Cart_Loader',
            PPCart_Activator::class             => 'NCS_Cart_Activator',
            PPCart_Deactivator::class           => 'NCS_Cart_Deactivator',
            PPCart_I18n::class                  => 'NCS_Cart_I18n',
            PPCart_Upgrade::class               => 'NCS_Cart_Upgrade',
            PPCart_Admin_Screens::class         => 'NCS_Cart_Admin_Screens',
            PPCart_Version_Notices::class       => 'NCS_Cart_Version_Notices',
            PPCart_Post_Types::class            => 'NCS_Cart_Post_Types',
            PPCart_Dependency_Loader::class     => 'NCS_Cart_Dependency_Loader',
            PPCart_Admin_Hook_Registrar::class  => 'NCS_Cart_Admin_Hook_Registrar',
            PPCart_Public_Hook_Registrar::class => 'NCS_Cart_Public_Hook_Registrar',
            PPCart_Status_Labels::class         => 'NCS_Cart_Status_Labels',
            PPCart_Sanitize::class              => 'NCS_Cart_Sanitize',
            PPCart_Product_Duplicator::class    => 'NCS_Cart_Product_Duplicator',
            PPCart_Order_Refunds::class         => 'NCS_Cart_Order_Refunds',
            PPCart_Currencies::class            => 'NCS_Cart_Currencies',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function fixtureClassMap(): array
    {
        return [
            'PPCart_Fixtures'               => 'NCS_Cart_Fixtures',
            'PPCart_Fixtures_Admin'         => 'NCS_Cart_Fixtures_Admin',
            'PPCart_Regression_Fixtures'    => 'NCS_Cart_Regression_Fixtures',
            'PPCart_Smoke_Fixtures'         => 'NCS_Cart_Smoke_Fixtures',
            'PPCart_Admin_Fixtures'         => 'NCS_Cart_Admin_Fixtures',
            'PPCart_Regression_PayPal_Mock' => 'NCS_Cart_Regression_PayPal_Mock',
            'PPCart_Regression_Stripe_Mock' => 'NCS_Cart_Regression_Stripe_Mock',
            'PPCart_Regression_Stripe_Http_Client' => 'NCS_Cart_Regression_Stripe_Http_Client',
            'PPCart_Admin_Workflow_State'   => 'NCS_Cart_Admin_Workflow_State',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function gutenbergClassMap(): array
    {
        return [
            'PPCart_Gutenberg_Bootstrap'         => 'NCS_Cart_Gutenberg_Bootstrap',
            'PPCart_Gutenberg_Assets'            => 'NCS_Cart_Gutenberg_Assets',
            'PPCart_Checkout_Renderer'           => 'NCS_Cart_Checkout_Renderer',
            'PPCart_Checkout_Preview_Controller' => 'NCS_Cart_Checkout_Preview_Controller',
            'PPCart_Account_Styles'              => 'NCS_Cart_Account_Styles',
            'PPCart_Account_Renderer'            => 'NCS_Cart_Account_Renderer',
            'PPCart_Account_Detail_Controller'   => 'NCS_Cart_Account_Detail_Controller',
            'PPCart_Account_Context'             => 'NCS_Cart_Account_Context',
            'PPCart_Product_Template'            => 'NCS_Cart_Product_Template',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function publicClassMap(): array
    {
        return [
            PPCart_Public::class                                  => 'NCS_Cart_Public',
            PPCart_Paypal::class                                  => 'NCS_Cart_Paypal',
            'PPCart_Public_Account_Controller'                    => 'NCS_Cart_Public_Account_Controller',
            'PPCart_Public_Asset_Controller'                      => 'NCS_Cart_Public_Asset_Controller',
            'PPCart_Public_Checkout_Controller'                   => 'NCS_Cart_Public_Checkout_Controller',
            'PPCart_Public_Hosted_Checkout_Controller'            => 'NCS_Cart_Public_Hosted_Checkout_Controller',
            'PPCart_Public_Order_Controller'                      => 'NCS_Cart_Public_Order_Controller',
            'PPCart_Public_Page_Controller'                       => 'NCS_Cart_Public_Page_Controller',
            'PPCart_Public_Payment_Controller'                    => 'NCS_Cart_Public_Payment_Controller',
            PPCart_Public_Subscription_Checkout_Controller::class => 'NCS_Cart_Public_Subscription_Checkout_Controller',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function stripeLoggingClassMap(): array
    {
        return [
            PPCart_Stripe_Sync::class           => 'NCS_Cart_Stripe_Sync',
            PPCart_Stripe_Sync_Context::class   => 'NCS_Cart_Stripe_Sync_Context',
            PPCart_Stripe_Save_Helper::class    => 'NCS_Cart_Stripe_Save_Helper',
            PPCart_Stripe_Webhook_Logger::class => 'NCS_Cart_Stripe_Webhook_Logger',
            PPCart_Debug_Logger::class          => 'NCS_Cart_Debug_Logger',
            PPCart_Debug_Log_Viewer::class      => 'NCS_Cart_Debug_Log_Viewer',
            PPCart_Secrets::class               => 'NCS_Cart_Secrets',
            PPCart_Files::class                 => 'NCS_Cart_Files',
        ];
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

    private function ensureAllFixtureClassesLoaded(): void
    {
        $this->ensureFixtureClassLoaded('PPCart_Fixtures', 'ppcart-fixtures.php');
        $this->ensureFixtureClassLoaded('PPCart_Fixtures_Admin', 'ppcart-fixtures.php');
        $this->ensureFixtureClassLoaded('PPCart_Regression_Fixtures', 'ppcart-regression-fixtures.php');
        $this->ensureFixtureClassLoaded('PPCart_Smoke_Fixtures', 'ppcart-smoke-fixtures.php');
        $this->ensureFixtureClassLoaded('PPCart_Admin_Fixtures', 'ppcart-admin-fixtures.php');
        $this->ensureFixtureClassLoaded('PPCart_Regression_PayPal_Mock', 'ppcart-regression-paypal-mock.php');
        $this->ensureFixtureClassLoaded('PPCart_Regression_Stripe_Mock', 'ppcart-regression-stripe-mock.php');
        $this->ensureAdminWorkflowStateLoaded();
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

    private function ensureOrderItemClassesLoaded(): void
    {
        if (! class_exists('PPCart_Order_Items', false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/order-items/class-ppcart-order-items.php';
        }

        if (! class_exists('PPCart_Order_Item', false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/order-items/class-ppcart-order-item.php';
        }
    }

    private function ensurePriceFormatClassLoaded(): void
    {
        if (! class_exists('PPCart_Price_Format', false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/class-ppcart-price-format.php';
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    private function shippedAliasMap(): array
    {
        if (! function_exists('ncs_cart_get_legacy_class_alias_map')) {
            return [];
        }

        return ncs_cart_get_legacy_class_alias_map();
    }

    public function test_wp_config_constant_true_overrides_disabled_option_on_real_site(): void
    {
        if (defined('PPCART_STUDIOCART_COMPATIBILITY_MODE')) {
            $this->markTestSkipped('PPCART_STUDIOCART_COMPATIBILITY_MODE is already defined in this process.');
        }

        define('PPCART_STUDIOCART_COMPATIBILITY_MODE', true);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '0');

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(ppcart_cart_supports(PPCartComp_Studiocart_Compatibility_Mode::FEATURE_ID));
    }

    public function test_wp_config_constant_false_overrides_enabled_option_on_real_site(): void
    {
        if (defined('PPCART_STUDIOCART_COMPATIBILITY_MODE')) {
            $this->markTestSkipped('PPCART_STUDIOCART_COMPATIBILITY_MODE is already defined in this process.');
        }

        define('PPCART_STUDIOCART_COMPATIBILITY_MODE', false);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertFalse(ppcart_cart_supports(PPCartComp_Studiocart_Compatibility_Mode::FEATURE_ID));
    }

    public function test_settings_checkbox_is_disabled_when_wp_config_constant_is_forced(): void
    {
        if (defined('PPCART_STUDIOCART_COMPATIBILITY_MODE')) {
            $this->markTestSkipped('PPCART_STUDIOCART_COMPATIBILITY_MODE is already defined in this process.');
        }

        define('PPCART_STUDIOCART_COMPATIBILITY_MODE', true);
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '0');

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_wp_config_forced());
        $this->assertSame('0', PPCartComp_Studiocart_Admin::sanitize_compatibility_mode('1'));
    }
}
