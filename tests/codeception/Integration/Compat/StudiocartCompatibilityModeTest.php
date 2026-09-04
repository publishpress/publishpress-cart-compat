<?php

declare(strict_types=1);

namespace Tests\Integration\Compat;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Admin;
use PPCartComp_Studiocart_Compatibility_Mode;

class StudiocartCompatibilityModeTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME);
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME);
        parent::tearDown();
    }

    /**
     * @test-id IT-161
     */
    public function test_IT_161_legacy_compat_helpers_load_from_studiocart_compatibility_mode(): void
    {
        $this->assertFileDoesNotExist(PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/legacy.php');
        $this->assertFileExists(
            PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/studiocart-compatibility-mode/index.php'
        );
        $this->assertTrue(class_exists(PPCartComp_Studiocart_Compatibility_Mode::class, false));
        $this->assertTrue(function_exists('ncs_cart_legacy_compat_enabled'));
        $this->assertTrue(function_exists('ncs_cart_register_class_alias'));
        $this->assertTrue(function_exists('ncs_cart_register_legacy_class_aliases'));
        $this->assertTrue(function_exists('ncs_cart_get_legacy_constant_map'));
        $this->assertTrue(function_exists('ncs_cart_resolve_canonical_constants'));
        $this->assertTrue(function_exists('ncs_cart_register_legacy_constants'));
        $this->assertTrue(function_exists('ncs_cart_get_legacy_global_map'));
        $this->assertTrue(function_exists('ncs_cart_register_legacy_globals'));
        $this->assertNotFalse(
            has_action(
                'ppcart_before_load',
                [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_aliases' ]
            )
        );
        $this->assertNotFalse(
            has_action(
                'ppcart_before_load',
                [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_functions' ]
            )
        );
        $this->assertNotFalse(
            has_action(
                'ppcart_before_load',
                [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_constants' ]
            )
        );
        $this->assertNotFalse(
            has_action(
                'ppcart_before_load',
                [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_globals' ]
            )
        );
        $this->assertNotFalse(
            has_filter(
                'ppcart_cart_supports_feature',
                [ PPCartComp_Studiocart_Compatibility_Mode::class, 'supports_feature' ]
            )
        );
    }

    /**
     * @depends test_IT_161_legacy_compat_helpers_load_from_studiocart_compatibility_mode
     */
    public function test_IT_162_shipped_class_aliases_stay_unregistered_while_compatibility_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertFalse(ppcart_cart_supports(PPCartComp_Studiocart_Compatibility_Mode::FEATURE_ID));

        foreach ($this->shippedAliasMap() as $canonical => $legacy_names) {
            if (! class_exists($canonical, false)) {
                continue;
            }

            foreach ($legacy_names as $legacy) {
                $this->assertFalse(
                    class_exists($legacy, false),
                    sprintf('%s must not be aliased while Compatibility Mode is off.', $legacy)
                );
            }
        }
    }

    public function test_IT_262_compat_mode_class_is_canonical_and_unreleased_name_stays_undefined(): void
    {
        $this->assertTrue(class_exists(PPCartComp_Studiocart_Compatibility_Mode::class, false));
        $this->assertFalse(class_exists('NCS_Cart_Studiocart_Compatibility_Mode', false));

        $map = ncs_cart_get_legacy_class_alias_map();
        $this->assertArrayNotHasKey('PPCartComp_Studiocart_Compatibility_Mode', $map);

        foreach ($map as $legacy_names) {
            $this->assertNotContains('NCS_Cart_Studiocart_Compatibility_Mode', $legacy_names);
        }
    }

    public function test_IT_164_ppcart_cart_supports_follows_the_compatibility_mode_option(): void
    {
        $pro_before = ppcart_cart_supports('pro');

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        $this->assertTrue(ppcart_cart_supports(PPCartComp_Studiocart_Compatibility_Mode::FEATURE_ID));
        $this->assertSame($pro_before, ppcart_cart_supports('pro'));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '0');
        $this->assertFalse(ppcart_cart_supports(PPCartComp_Studiocart_Compatibility_Mode::FEATURE_ID));
        $this->assertSame($pro_before, ppcart_cart_supports('pro'));
    }

    /**
     * @test-id IT-165
     */
    public function test_IT_165_studiocart_submenu_owns_the_compatibility_mode_option(): void
    {
        $this->assertFalse(has_filter('_ppcart_option_list', [ PPCartComp_Studiocart_Compatibility_Mode::class, 'register_settings' ]));
        $this->assertSame('ppcart-studiocart', PPCartComp_Studiocart_Admin::PAGE_SLUG);
        $this->assertSame(
            PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME,
            '_ppcart_studiocart_compatibility_mode'
        );

        global $wp_settings_sections, $wp_settings_fields;
        $this->assertFalse(
            isset($wp_settings_sections['ppcart']['ppcart-compatibility'])
        );
    }

    public function test_IT_275_leftover_option_row_still_enables_and_copies_to_canonical(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        update_option(PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME, '1');

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_user_enabled());
        $this->assertSame(
            '1',
            get_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, false)
        );
        $this->assertFalse(
            get_option(PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME, false)
        );
    }

    public function test_IT_256_legacy_function_wrappers_stay_unloaded_while_compatibility_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_functions_registered());

        $plugin_basename = plugin_basename(PPCART_BASE_FILE);

        foreach ($this->bootstrapFunctionMap() as $canonical => $legacy) {
            $this->assertTrue(function_exists($canonical), sprintf('%s must be defined.', $canonical));
            $this->assertFalse(
                function_exists($legacy),
                sprintf('%s must not be defined while Compatibility Mode is off.', $legacy)
            );
        }

        $this->assertNotFalse(has_action('activate_' . $plugin_basename, 'ppcart_activate'));
        $this->assertNotFalse(has_action('deactivate_' . $plugin_basename, 'ppcart_deactivate'));
        $this->assertNotFalse(has_action('upgrader_process_complete', 'ppcart_upgrade'));
        $this->assertFalse(has_action('activate_' . $plugin_basename, 'activate_ncs_cart'));
        $this->assertFalse(has_action('deactivate_' . $plugin_basename, 'deactivate_ncs_cart'));
        $this->assertFalse(has_action('upgrader_process_complete', 'upgrade_ncs_cart'));
    }

    /**
     * @depends test_IT_256_legacy_function_wrappers_stay_unloaded_while_compatibility_mode_is_off
     */
    public function test_IT_257_enabling_compatibility_mode_loads_legacy_function_wrappers(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_functions();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_functions_registered());

        foreach ($this->bootstrapFunctionMap() as $canonical => $legacy) {
            $this->assertTrue(function_exists($canonical), sprintf('%s must stay defined.', $canonical));
            $this->assertTrue(
                function_exists($legacy),
                sprintf('%s wrapper is missing while Compatibility Mode is on.', $legacy)
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function bootstrapFunctionMap(): array
    {
        return [
            'ppcart_activate'   => 'activate_ncs_cart',
            'ppcart_deactivate' => 'deactivate_ncs_cart',
            'ppcart_upgrade'    => 'upgrade_ncs_cart',
            'ppcart_run'        => 'run_ncs_cart',
        ];
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
}
