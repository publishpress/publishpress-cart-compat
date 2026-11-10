<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Cpt_Slug_Migration_Admin;
use PPCartComp_Studiocart_Admin;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class StudiocartCompatibilityModeTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);

        foreach ([ 'legacy_functions_registered' ] as $property_name) {
            $property = $reflection->getProperty($property_name);
            $property->setAccessible(true);
            $property->setValue(null, false);
        }

        WordPressStubContext::set(
            'add_filter',
            static function () {
                return true;
            }
        );

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

        WordPressStubContext::set(
            'get_option',
            function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', array());

                if (array_key_exists($option, $options)) {
                    return $options[$option];
                }

                return $default;
            }
        );

        WordPressStubContext::set(
            'update_option',
            function ($option, $value) {
                $options = WordPressStubContext::getState('options', array());
                $options[$option] = $value;
                WordPressStubContext::setState('options', $options);

                return true;
            }
        );

        WordPressStubContext::set(
            'delete_option',
            function ($option) {
                $options = WordPressStubContext::getState('options', array());
                unset($options[$option]);
                WordPressStubContext::setState('options', $options);

                return true;
            }
        );

        WordPressStubContext::setState('compat_filters', array());
        WordPressStubContext::setState('options', array());
    }

    protected function _after(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);

        foreach ([ 'legacy_functions_registered' ] as $property_name) {
            $property = $reflection->getProperty($property_name);
            $property->setAccessible(true);
            $property->setValue(null, false);
        }

        WordPressStubContext::clear();
        parent::_after();
    }

    public function test_UT_179_compatibility_mode_is_off_when_the_setting_is_unset(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_user_enabled());
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
    }

    public function test_UT_180_compatibility_mode_is_on_when_the_setting_is_enabled(): void
    {
        WordPressStubContext::setState(
            'options',
            array(
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => 1,
            )
        );

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_user_enabled());
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
    }

    public function test_UT_201_oldest_toggle_option_still_enables_without_mutating_storage(): void
    {
        WordPressStubContext::setState(
            'options',
            array(
                PPCartComp_Studiocart_Compatibility_Mode::OLDEST_LEGACY_OPTION_NAME => '1',
            )
        );

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_user_enabled());
        $this->assertSame(
            array(
                PPCartComp_Studiocart_Compatibility_Mode::OLDEST_LEGACY_OPTION_NAME => '1',
            ),
            WordPressStubContext::getState('options', array())
        );
    }

    public function test_UT_181_merchant_mode_is_independent_of_the_developer_switch(): void
    {
        WordPressStubContext::setState(
            'options',
            array(
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => 1,
            )
        );
        WordPressStubContext::setState(
            'compat_filters',
            array(
                'ncs_cart_legacy_compat_enabled' => static function () {
                    return false;
                },
            )
        );

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_user_enabled());
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertFalse(ncs_cart_legacy_compat_enabled());
    }

    public function test_UT_182_feature_support_filter_only_gates_compatibility_mode(): void
    {
        WordPressStubContext::setState(
            'options',
            array(
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => 1,
            )
        );

        $this->assertTrue(
            PPCartComp_Studiocart_Compatibility_Mode::supports_feature(
                false,
                PPCartComp_Studiocart_Compatibility_Mode::FEATURE_ID
            )
        );
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::supports_feature(true, 'pro'));
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::supports_feature(false, 'pro'));

        WordPressStubContext::setState('options', array());

        $this->assertFalse(
            PPCartComp_Studiocart_Compatibility_Mode::supports_feature(
                true,
                PPCartComp_Studiocart_Compatibility_Mode::FEATURE_ID
            )
        );
    }

    public function test_UT_183_settings_registrar_adds_the_compatibility_mode_checkbox(): void
    {
        $this->assertFalse(method_exists(PPCartComp_Studiocart_Compatibility_Mode::class, 'register_settings'));
        $this->assertSame('ppcartcomp_studiocart', PPCartComp_Studiocart_Admin::OPTION_GROUP);
        $this->assertSame(
            PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME,
            '_ppcart_compatibility_mode'
        );
        $this->assertTrue(method_exists(PPCartComp_Studiocart_Admin::class, 'register_setting'));
    }

    /**
     * @test-id UT-291
     */
    public function test_UT_291_canonical_feature_id_uses_a_read_only_legacy_toggle_bridge(): void
    {
        WordPressStubContext::setState(
            'options',
            array(
                PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME => '1',
            )
        );

        $this->assertSame('ppcart-compatibility-mode', PPCartComp_Studiocart_Compatibility_Mode::FEATURE_ID);
        $this->assertSame('_ppcart_compatibility_mode', PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->assertTrue(
            PPCartComp_Studiocart_Compatibility_Mode::supports_feature(
                false,
                PPCartComp_Studiocart_Compatibility_Mode::FEATURE_ID
            )
        );
        $this->assertTrue(
            PPCartComp_Studiocart_Compatibility_Mode::supports_feature(
                false,
                PPCartComp_Studiocart_Compatibility_Mode::LEGACY_FEATURE_ID
            )
        );
        $this->assertSame(
            array(
                PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME => '1',
            ),
            WordPressStubContext::getState('options', array())
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_UT_184_wp_config_constant_true_overrides_disabled_option(): void
    {
        if (defined('PPCART_STUDIOCART_COMPATIBILITY_MODE')) {
            $this->markTestSkipped('PPCART_STUDIOCART_COMPATIBILITY_MODE is already defined in this process.');
        }

        define('PPCART_STUDIOCART_COMPATIBILITY_MODE', true);

        WordPressStubContext::set(
            'get_option',
            static function () {
                return '0';
            }
        );

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_wp_config_forced());
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_user_enabled());
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_UT_185_wp_config_constant_false_overrides_enabled_option(): void
    {
        if (defined('PPCART_STUDIOCART_COMPATIBILITY_MODE')) {
            $this->markTestSkipped('PPCART_STUDIOCART_COMPATIBILITY_MODE is already defined in this process.');
        }

        define('PPCART_STUDIOCART_COMPATIBILITY_MODE', false);

        WordPressStubContext::set(
            'get_option',
            static function () {
                return '1';
            }
        );

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_wp_config_forced());
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_user_enabled());
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_UT_186_settings_field_is_disabled_when_wp_config_constant_is_forced(): void
    {
        if (defined('PPCART_STUDIOCART_COMPATIBILITY_MODE')) {
            $this->markTestSkipped('PPCART_STUDIOCART_COMPATIBILITY_MODE is already defined in this process.');
        }

        define('PPCART_STUDIOCART_COMPATIBILITY_MODE', true);

        WordPressStubContext::set(
            'get_option',
            static function () {
                return '0';
            }
        );

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_wp_config_forced());
        $this->assertSame('0', PPCartComp_Studiocart_Admin::sanitize_compatibility_mode('1'));
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_UT_187_legacy_function_wrappers_load_only_when_compatibility_mode_is_on(): void
    {
        if (defined('PPCART_STUDIOCART_COMPATIBILITY_MODE')) {
            $this->markTestSkipped('PPCART_STUDIOCART_COMPATIBILITY_MODE is already defined in this process.');
        }

        WordPressStubContext::setState(
            'options',
            array(
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => 1,
            )
        );

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_functions_registered());
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_functions();
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_functions_registered());
    }

    public function test_UT_195_settings_page_slug_is_canonical_ppcart_without_legacy_bridge(): void
    {
        $this->assertSame('ppcart-studiocart', PPCartComp_Studiocart_Admin::PAGE_SLUG);
        $this->assertFalse(
            defined(PPCartComp_Studiocart_Compatibility_Mode::class . '::SETTINGS_PAGE')
        );
        $this->assertFalse(
            defined(PPCartComp_Studiocart_Compatibility_Mode::class . '::SETTINGS_TAB')
        );
    }

    public function test_UT_196_register_settings_section_targets_only_the_canonical_page(): void
    {
        $this->assertFalse(method_exists(PPCartComp_Studiocart_Compatibility_Mode::class, 'register_settings_section'));
        $this->assertFalse(method_exists(PPCartComp_Cpt_Slug_Migration_Admin::class, 'register_settings_section'));
        $this->assertSame('ppcart-studiocart', PPCartComp_Studiocart_Admin::PAGE_SLUG);
        $this->assertSame(
            'https://example.test/wp-admin/admin.php?page=ppcart-studiocart',
            PPCartComp_Cpt_Slug_Migration_Admin::settings_url()
        );
    }

    /**
     * @test-id UT-246
     */
    public function test_UT_246_saving_the_studiocart_page_leaves_drop_backups_option_alone(): void
    {
        $this->assertSame(
            'ppcartcomp_drop_migration_backups_on_uninstall',
            PPCartComp_Studiocart_Admin::DROP_BACKUPS_OPTION
        );

        WordPressStubContext::setState('registered_settings', array());
        WordPressStubContext::set(
            'register_setting',
            static function ($group, $name, $args = array()) {
                $registered   = WordPressStubContext::getState('registered_settings', array());
                $registered[] = array(
                    'group' => $group,
                    'name'  => $name,
                );
                WordPressStubContext::setState('registered_settings', $registered);
            }
        );

        PPCartComp_Studiocart_Admin::register_setting();

        $names = array_column(WordPressStubContext::getState('registered_settings', array()), 'name');

        $this->assertContains(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, $names);
        $this->assertNotContains(PPCartComp_Studiocart_Admin::DROP_BACKUPS_OPTION, $names);
    }
}
