<?php

declare(strict_types=1);

namespace Tests\Integration\Compat;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class HookBridgesTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->resetCompatibilityModeFlags();

        if (function_exists('ncs_cart_reset_hook_bridge_lazy_attach_state')) {
            ncs_cart_reset_hook_bridge_lazy_attach_state();
        }

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_cron_hook_bridges();
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->resetCompatibilityModeFlags();

        if (function_exists('ncs_cart_reset_hook_bridge_lazy_attach_state')) {
            ncs_cart_reset_hook_bridge_lazy_attach_state();
        }

        parent::tearDown();
    }

    public function test_IT_259_canonical_hook_fires_without_legacy_listeners_when_compat_is_off(): void
    {
        $canonical_ran = false;
        $legacy_ran = false;

        add_action(
            'ppcart_after_order_paid',
            static function () use (&$canonical_ran) {
                $canonical_ran = true;
            },
            10,
            0
        );
        add_action(
            'sc_after_order_paid',
            static function () use (&$legacy_ran) {
                $legacy_ran = true;
            },
            10,
            0
        );

        do_action('ppcart_after_order_paid');

        $this->assertTrue($canonical_ran);
        $this->assertFalse($legacy_ran);
    }

    public function test_IT_259_compat_mode_bridges_canonical_fires_to_legacy_listeners(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        $legacy_ran = false;
        $extra_legacy_ran = false;

        add_action(
            'sc_after_order_paid',
            static function () use (&$legacy_ran) {
                $legacy_ran = true;
            },
            10,
            0
        );
        add_action(
            'studiocart_checkout_complete',
            static function () use (&$extra_legacy_ran) {
                $extra_legacy_ran = true;
            },
            10,
            0
        );

        do_action('ppcart_after_order_paid');

        $this->assertTrue($legacy_ran);
        $this->assertTrue($extra_legacy_ran);
    }

    public function test_IT_259_reverse_bridge_reaches_canonical_listeners_from_legacy_hook(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        $compat_callbacks = [
            [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_aliases' ],
            [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_functions' ],
            [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_option_storage_bridges' ],
            [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_options' ],
            [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_hooks' ],
            [ PPCartComp_Studiocart_Compatibility_Mode::class, 'maybe_register_constants' ],
        ];

        foreach ($compat_callbacks as $callback) {
            remove_action('ppcart_before_load', $callback, PHP_INT_MAX);
        }

        $canonical_ran = false;

        add_action(
            'ppcart_before_load',
            static function () use (&$canonical_ran) {
                $canonical_ran = true;
            },
            10,
            0
        );

        do_action('sc_before_load');

        $this->assertTrue($canonical_ran);

        foreach ($compat_callbacks as $callback) {
            add_action('ppcart_before_load', $callback, PHP_INT_MAX);
        }
    }

    public function test_IT_259_cron_drain_forwards_legacy_price_formatting_hook(): void
    {
        $canonical_ran = false;

        add_action(
            'ppcart_run_price_formatting',
            static function () use (&$canonical_ran) {
                $canonical_ran = true;
            },
            10,
            0
        );

        do_action('nsc_run_price_formatting');

        $this->assertTrue($canonical_ran);
    }

    public function test_IT_259_checkout_payment_template_does_not_fire_legacy_checkout_complete(): void
    {
        $path = PPCART_PLUGIN_ROOT . 'public/controllers/checkout/traits/templates/checkout-payment-ppcart-process-payment.php';
        $source = (string) file_get_contents($path);

        $this->assertStringNotContainsString('studiocart_checkout_complete', $source);
    }

    /**
     * @test-id IT-345
     */
    public function test_IT_345_leftover_before_sc_order_refund_bridge_only_when_compatibility_mode_is_on(): void
    {
        $canonical_ran = false;
        $legacy_ran = false;

        add_action(
            'ppcart_before_order_refund',
            static function () use (&$canonical_ran) {
                $canonical_ran = true;
            },
            10,
            1
        );
        add_action(
            'before_sc_order_refund',
            static function () use (&$legacy_ran) {
                $legacy_ran = true;
            },
            10,
            1
        );

        do_action('ppcart_before_order_refund', []);

        $this->assertTrue($canonical_ran);
        $this->assertFalse($legacy_ran);

        $canonical_ran = false;
        $legacy_ran = false;

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        do_action('ppcart_before_order_refund', []);

        $this->assertTrue($canonical_ran);
        $this->assertTrue($legacy_ran);

        $canonical_ran = false;
        $legacy_ran = false;

        do_action('before_sc_order_refund', []);

        $this->assertTrue($canonical_ran);
        $this->assertTrue($legacy_ran);
    }

    public function test_IT_265_plugin_identity_hooks_do_not_bridge_when_compat_is_off(): void
    {
        foreach ($this->pluginIdentityHookMap() as $canonical => $legacy) {
            add_filter(
                $legacy,
                static function ($value) {
                    return $value . '|legacy';
                }
            );

            $this->assertSame('value', apply_filters($canonical, 'value'), $canonical);
        }
    }

    public function test_IT_265_runtime_plugin_identity_is_canonical_ppcart(): void
    {
        $source = (string) file_get_contents(PPCART_PLUGIN_ROOT . 'includes/class-ppcart.php');

        $this->assertStringContainsString("\$this->plugin_name = 'ppcart';", $source);
        $this->assertStringNotContainsString("\$this->plugin_name = 'ncs-cart';", $source);
    }

    public function test_IT_265_compat_mode_bridges_canonical_plugin_identity_hooks_to_legacy_listeners(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        foreach ($this->pluginIdentityHookMap() as $canonical => $legacy) {
            add_filter(
                $legacy,
                static function ($value) {
                    return $value . '|legacy';
                }
            );
        }

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        foreach ($this->pluginIdentityHookMap() as $canonical => $legacy) {
            $value = apply_filters($canonical, 'value');
            $attached = ncs_cart_hook_bridge_attached_hooks();

            $this->assertArrayHasKey($canonical, $attached, $canonical);
            $this->assertNotFalse(has_filter($canonical), $canonical);
            $this->assertSame('value|legacy', $value, $canonical);
        }
    }

    public function test_IT_265_reverse_bridge_reaches_canonical_plugin_identity_hook_listeners(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        foreach ($this->pluginIdentityHookMap() as $canonical => $legacy) {
            add_filter(
                $canonical,
                static function ($value) {
                    return $value . '|canonical';
                }
            );
        }

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        foreach ($this->pluginIdentityHookMap() as $canonical => $legacy) {
            $this->assertSame('value|canonical', apply_filters($legacy, 'value'), $legacy);
        }
    }

    /**
     * @return array<string, string>
     */
    private function pluginIdentityHookMap(): array
    {
        return [
            'ppcart_currencies'                  => 'ncs-cart-currencies',
            'ppcart_currency_symbols'            => 'ncs-cart-currency-symbols',
            'ppcart_zero_decimal_currency'       => 'ncs-cart-zero-decimal-currency',
            'ppcart-repeater-more-link-label'    => 'ncs-cart-repeater-more-link-label',
        ];
    }

    private function resetCompatibilityModeFlags(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);

        foreach (
            [
                'legacy_hooks_registered',
                'cron_hook_bridges_registered',
                'legacy_options_registered',
                'option_storage_bridges_registered',
                'legacy_functions_registered',
                'legacy_globals_registered',
            ] as $property_name
        ) {
            if (! $reflection->hasProperty($property_name)) {
                continue;
            }

            $property = $reflection->getProperty($property_name);
            $property->setAccessible(true);
            $property->setValue(null, false);
        }
    }
}
