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
        $checkout_complete_ran = false;

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
            static function () use (&$checkout_complete_ran) {
                $checkout_complete_ran = true;
            },
            10,
            0
        );

        do_action('ppcart_after_order_paid');

        $this->assertTrue($legacy_ran);
        $this->assertFalse($checkout_complete_ran);
    }

    public function test_IT_259_checkout_complete_reaches_legacy_listeners_with_the_same_arguments(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        $received = null;

        add_action(
            'studiocart_checkout_complete',
            static function ($order_id, $product) use (&$received) {
                $received = [$order_id, $product];
            },
            10,
            2
        );

        $product = (object) ['ID' => 77];
        do_action('ppcart_checkout_complete', 42, $product);

        $this->assertSame([42, $product], $received);
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

    /**
     * @test-id IT-357
     */
    public function test_IT_357_leftover_cpt_taxonomy_option_filters_bridge_only_when_compatibility_mode_is_on(): void
    {
        add_filter(
            'sc-cart-cpt-options',
            static function ($opts, $cpt_name) {
                $opts['bridged'] = $cpt_name;

                return $opts;
            },
            10,
            2
        );
        add_filter(
            'sc-cart-taxonomy-options',
            static function ($opts) {
                $opts['bridged'] = true;

                return $opts;
            }
        );

        $cpt = apply_filters('ppcart_cpt_options', ['menu_icon' => 'dashicons-products'], 'ppcart_product');
        $taxonomy = apply_filters('ppcart_taxonomy_options', ['hierarchical' => true]);

        $this->assertArrayNotHasKey('bridged', $cpt);
        $this->assertArrayNotHasKey('bridged', $taxonomy);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        $cpt = apply_filters('ppcart_cpt_options', ['menu_icon' => 'dashicons-products'], 'ppcart_product');
        $taxonomy = apply_filters('ppcart_taxonomy_options', ['hierarchical' => true]);

        $this->assertSame('ppcart_product', $cpt['bridged'] ?? null);
        $this->assertTrue($taxonomy['bridged'] ?? false);

        $canonical_cpt = false;
        $canonical_taxonomy = false;

        add_filter(
            'ppcart_cpt_options',
            static function ($opts) use (&$canonical_cpt) {
                $canonical_cpt = true;

                return $opts;
            }
        );
        add_filter(
            'ppcart_taxonomy_options',
            static function ($opts) use (&$canonical_taxonomy) {
                $canonical_taxonomy = true;

                return $opts;
            }
        );

        apply_filters('sc-cart-cpt-options', ['menu_icon' => 'dashicons-products'], 'ppcart_product');
        apply_filters('sc-cart-taxonomy-options', ['hierarchical' => true]);

        $this->assertTrue($canonical_cpt);
        $this->assertTrue($canonical_taxonomy);
    }

    /**
     * @test-id IT-359
     */
    public function test_IT_359_leftover_activate_upgrade_product_hooks_bridge_only_when_compatibility_mode_is_on(): void
    {
        // Cart listens on these tags to CREATE custom tables. Strip those
        // side effects so this hook-name check does not pollute later schema tests.
        remove_all_actions('ppcart_activate');
        remove_all_actions('ppcart_upgrade');

        $legacy_activate = false;
        $legacy_upgrade = false;
        $mangled_activate = false;

        add_action(
            'sc_activate',
            static function () use (&$legacy_activate) {
                $legacy_activate = true;
            }
        );
        add_action(
            'ncs_upgrade',
            static function () use (&$legacy_upgrade) {
                $legacy_upgrade = true;
            }
        );
        add_action(
            'ppcart_ctivate',
            static function () use (&$mangled_activate) {
                $mangled_activate = true;
            }
        );
        add_filter(
            'sc_product',
            static function ($value) {
                $value['bridged'] = true;

                return $value;
            }
        );
        add_filter(
            'sc_product_price',
            static function ($price) {
                return $price + 1;
            }
        );

        do_action('ppcart_activate');
        do_action('ppcart_upgrade');
        $product = apply_filters('ppcart_product', ['ID' => 1]);
        $price = apply_filters('ppcart_product_price', 10);

        $this->assertFalse($legacy_activate);
        $this->assertFalse($legacy_upgrade);
        $this->assertFalse($mangled_activate);
        $this->assertArrayNotHasKey('bridged', $product);
        $this->assertSame(10, $price);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        $legacy_activate = false;
        $legacy_upgrade = false;
        $mangled_activate = false;

        do_action('ppcart_activate');
        do_action('ppcart_upgrade');
        $product = apply_filters('ppcart_product', ['ID' => 1]);
        $price = apply_filters('ppcart_product_price', 10);

        $this->assertTrue($legacy_activate);
        $this->assertTrue($legacy_upgrade);
        $this->assertFalse($mangled_activate);
        $this->assertTrue($product['bridged'] ?? false);
        $this->assertSame(11, $price);

        $canonical_activate = false;
        $canonical_product = false;

        add_action(
            'ppcart_activate',
            static function () use (&$canonical_activate) {
                $canonical_activate = true;
            }
        );
        add_filter(
            'ppcart_product',
            static function ($value) use (&$canonical_product) {
                $canonical_product = true;

                return $value;
            }
        );

        do_action('ncs_activate');
        apply_filters('sc_product', ['ID' => 2]);

        $this->assertTrue($canonical_activate);
        $this->assertTrue($canonical_product);
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
    public function test_IT_372_a_cron_hook_fired_with_no_arguments_does_not_fatal_in_compat_mode(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        $legacy_ran = false;
        add_action(
            'sc_after_order_paid',
            static function () use (&$legacy_ran) {
                $legacy_ran = true;
            },
            10,
            0
        );

        // WP-Cron runs events this way: no arguments at all.
        do_action_ref_array('ppcart_after_order_paid', []);
        do_action_ref_array('sc_after_order_paid', []);

        $this->assertTrue($legacy_ran);
    }

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
