<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCart_Order;
use Tests\Support\WordPressStubContext;
use UnitTester;

class LegacyCompatTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        WordPressStubContext::set(
            'apply_filters',
            function ($hook_name, $value) {
                $filters = WordPressStubContext::getState('compat_filters', array());

                if (! isset($filters[$hook_name])) {
                    return $value;
                }

                return $filters[$hook_name]($value);
            }
        );

        WordPressStubContext::setState('compat_filters', array());
    }

    protected function _after(): void
    {
        WordPressStubContext::clear();
        parent::_after();
    }

    public function test_UT_022_legacy_compatibility_is_enabled_by_default(): void
    {
        $this->assertTrue(ncs_cart_legacy_compat_enabled());
    }

    public function test_UT_023_legacy_compatibility_can_be_disabled_via_filter(): void
    {
        WordPressStubContext::setState(
            'compat_filters',
            array(
                'ncs_cart_legacy_compat_enabled' => function () {
                    return false;
                },
            )
        );

        $this->assertFalse(ncs_cart_legacy_compat_enabled());
    }

    public function test_UT_024_class_alias_is_created_when_compatibility_is_enabled(): void
    {
        $this->declareGlobalCompatStubClass('NCS_Cart_CompatAliasCanonical');

        ncs_cart_register_class_alias('NCS_Cart_CompatAliasCanonical', 'CompatAliasLegacy');

        $this->assertTrue(class_exists('CompatAliasLegacy', false));
        $this->assertTrue(is_a('CompatAliasLegacy', 'NCS_Cart_CompatAliasCanonical', true));
    }

    public function test_UT_025_class_alias_is_skipped_when_compatibility_is_disabled(): void
    {
        WordPressStubContext::setState(
            'compat_filters',
            array(
                'ncs_cart_legacy_compat_enabled' => function () {
                    return false;
                },
            )
        );

        $this->declareGlobalCompatStubClass('NCS_Cart_CompatAliasDisabled');

        ncs_cart_register_class_alias('NCS_Cart_CompatAliasDisabled', 'CompatAliasLegacyDisabled');

        $this->assertFalse(class_exists('CompatAliasLegacyDisabled', false));
    }

    public function test_UT_026_class_alias_is_skipped_when_the_legacy_name_already_exists(): void
    {
        $this->declareGlobalCompatStubClass('CompatAliasLegacyExisting');
        $this->declareGlobalCompatStubClass('NCS_Cart_CompatAliasExisting');

        ncs_cart_register_class_alias('NCS_Cart_CompatAliasExisting', 'CompatAliasLegacyExisting');

        $this->assertFalse(is_a('CompatAliasLegacyExisting', 'NCS_Cart_CompatAliasExisting', true));
        $this->assertSame('CompatAliasLegacyExisting', \CompatAliasLegacyExisting::class);
    }

    public function test_UT_027_class_alias_is_skipped_when_the_canonical_class_is_missing(): void
    {
        ncs_cart_register_class_alias('NCS_Cart_CompatAliasMissing', 'CompatAliasLegacyMissing');

        $this->assertFalse(class_exists('CompatAliasLegacyMissing', false));
    }

    public function test_UT_028_bulk_registrar_creates_the_shipped_model_alias(): void
    {
        if (! class_exists(PPCart_Order::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'models/class-ppcart-order.php';
        }

        ncs_cart_register_legacy_class_aliases();

        $this->assertTrue(class_exists('ScrtOrder', false));
        $this->assertTrue(is_a('ScrtOrder', PPCart_Order::class, true));
    }

    public function test_UT_029_bulk_registrar_does_not_create_pp_cart_class_aliases(): void
    {
        if (! class_exists(PPCart_Order::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'models/class-ppcart-order.php';
        }

        ncs_cart_register_legacy_class_aliases();

        $this->assertFalse(class_exists('PP_Cart_Status_Labels', false));
        $this->assertFalse(class_exists('PP_Cart_Secrets', false));
    }

    public function test_UT_030_legacy_compatibility_registers_no_pp_cart_function_aliases(): void
    {
        $this->assertFalse(function_exists('ppcart_register_legacy_function_aliases'));
        $this->assertFalse(function_exists('pp_cart_is_stripe_owned_field'));
        $this->assertFalse(function_exists('pp_cart_email_template_option_defaults'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_UT_031_legacy_compatibility_can_be_disabled_via_constant(): void
    {
        if (! defined('NCS_CART_LEGACY_COMPAT')) {
            define('NCS_CART_LEGACY_COMPAT', false);
        }

        $this->assertFalse(ncs_cart_legacy_compat_enabled());
    }

    /**
     * @param string $class_name Global class name.
     * @return void
     */
    private function declareGlobalCompatStubClass(string $class_name): void
    {
        if (class_exists($class_name, false)) {
            return;
        }

        eval(
            sprintf(
                'namespace { class %s {} }',
                $class_name
            )
        );
    }
}
