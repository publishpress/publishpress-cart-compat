<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class OptionBridgesTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        $this->resetCompatibilityModeFlags();

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

        WordPressStubContext::set(
            'add_filter',
            static function () {
                return true;
            }
        );

        WordPressStubContext::setState('options', []);
    }

    protected function _after(): void
    {
        $this->resetCompatibilityModeFlags();
        WordPressStubContext::clear();
        parent::_after();
    }

    public function test_UT_188_option_name_helpers_map_legacy_and_canonical_keys(): void
    {
        $this->assertSame('_ppcart_stripe_enable', ncs_cart_legacy_to_canonical_option_name('_sc_stripe_enable'));
        $this->assertSame('ppcart_admin_email', ncs_cart_legacy_to_canonical_option_name('sc_admin_email'));
        $this->assertSame('ppcart_report_schedule', ncs_cart_legacy_to_canonical_option_name('ncs_report_schedule'));
        $this->assertSame('ppcart_current_schedule_val', ncs_cart_legacy_to_canonical_option_name('current_schedule_val'));
        $this->assertSame('_ppcart_myaccount_page_id', ncs_cart_legacy_to_canonical_option_name('_my_account'));

        $this->assertNull(
            ncs_cart_legacy_to_canonical_option_name(
                PPCartComp_Studiocart_Compatibility_Mode::OLDEST_LEGACY_OPTION_NAME
            )
        );
        $this->assertNull(
            ncs_cart_canonical_to_legacy_option_name(
                PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME
            )
        );
        $this->assertNull(ncs_cart_legacy_to_canonical_option_name('unrelated_option'));

        $this->assertSame('_sc_stripe_enable', ncs_cart_canonical_to_legacy_option_name('_ppcart_stripe_enable'));
        $this->assertSame('sc_admin_email', ncs_cart_canonical_to_legacy_option_name('ppcart_admin_email'));
        $this->assertSame('current_schedule_val', ncs_cart_canonical_to_legacy_option_name('ppcart_current_schedule_val'));
        $this->assertSame('_my_account', ncs_cart_canonical_to_legacy_option_name('_ppcart_myaccount_page_id'));
    }

    public function test_UT_188_storage_bridges_register_always_and_api_bridges_only_when_compat_is_on(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::option_storage_bridges_registered());
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_options_registered());

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_option_storage_bridges();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::option_storage_bridges_registered());
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_options_registered());

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_options();
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_options_registered());

        WordPressStubContext::setState(
            'options',
            [
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1',
            ]
        );

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_options();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_options_registered());
    }

    /**
     * @test-id UT-202
     */
    public function test_UT_202_leftover_option_names_are_copyable_except_the_compat_toggle(): void
    {
        $this->assertTrue(ppcart_legacy_option_name_is_copyable('_sc_stripe_enable'));
        $this->assertTrue(ppcart_legacy_option_name_is_copyable('sc_admin_email'));
        $this->assertTrue(ppcart_legacy_option_name_is_copyable('ncs_report_schedule'));
        $this->assertTrue(ppcart_legacy_option_name_is_copyable('current_schedule_val'));
        $this->assertTrue(ppcart_legacy_option_name_is_copyable('_my_account'));

        $this->assertFalse(
            ppcart_legacy_option_name_is_copyable(
                PPCartComp_Studiocart_Compatibility_Mode::OLDEST_LEGACY_OPTION_NAME
            )
        );
        $this->assertFalse(
            ppcart_legacy_option_name_is_copyable(
                PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME
            )
        );
        $this->assertFalse(ppcart_legacy_option_name_is_copyable('unrelated_option'));
        $this->assertFalse(ppcart_legacy_option_name_is_copyable('_ppcart_stripe_enable'));
        $this->assertFalse(ppcart_legacy_option_name_is_copyable('ppcart_admin_email'));
    }

    private function resetCompatibilityModeFlags(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);

        foreach ([ 'legacy_options_registered', 'option_storage_bridges_registered' ] as $property_name) {
            $property = $reflection->getProperty($property_name);
            $property->setAccessible(true);
            $property->setValue(null, false);
        }
    }
}
