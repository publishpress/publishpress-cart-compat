<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class HookBridgesTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        $this->resetCompatibilityModeFlags();

        if (function_exists('ncs_cart_reset_hook_bridge_lazy_attach_state')) {
            ncs_cart_reset_hook_bridge_lazy_attach_state();
        }

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

        WordPressStubContext::set('add_action', static function () {
            return true;
        });

        WordPressStubContext::set('remove_action', static function () {
            return true;
        });

        WordPressStubContext::set('add_filter', static function () {
            return true;
        });

        WordPressStubContext::setState('options', []);
    }

    protected function _after(): void
    {
        $this->resetCompatibilityModeFlags();
        WordPressStubContext::clear();
        parent::_after();
    }

    public function test_UT_189_hook_name_helpers_map_legacy_and_canonical_hooks(): void
    {
        $this->assertSame('ppcart_after_order_paid', ncs_cart_legacy_to_canonical_hook_name('sc_after_order_paid'));
        $this->assertSame('_ppcart_option_list', ncs_cart_legacy_to_canonical_hook_name('_sc_option_list'));
        $this->assertSame('ppcart_cart_loaded', ncs_cart_legacy_to_canonical_hook_name('ncs_cart_loaded'));
        $this->assertSame('ppcart_daily_events', ncs_cart_legacy_to_canonical_hook_name('studiocart_daily_events'));
        $this->assertSame('ppcart_after_order_paid', ncs_cart_legacy_to_canonical_hook_name('studiocart_checkout_complete'));
        $this->assertSame('ppcart_page_metabox', ncs_cart_legacy_to_canonical_hook_name('studiocart_page_metabox'));
        $this->assertSame('ppcart_currencies', ncs_cart_legacy_to_canonical_hook_name('ncs-cart-currencies'));
        $this->assertSame(
            'ppcart_currency_symbols',
            ncs_cart_legacy_to_canonical_hook_name('ncs-cart-currency-symbols')
        );
        $this->assertSame(
            'ppcart_zero_decimal_currency',
            ncs_cart_legacy_to_canonical_hook_name('ncs-cart-zero-decimal-currency')
        );
        $this->assertSame(
            'ppcart-repeater-more-link-label',
            ncs_cart_legacy_to_canonical_hook_name('ncs-cart-repeater-more-link-label')
        );

        $this->assertNull(ncs_cart_legacy_to_canonical_hook_name('wp_ajax_sc_create_payment_intent'));
        $this->assertSame('save_post_ppcart_order', ncs_cart_legacy_to_canonical_hook_name('save_post_sc_order'));
        $this->assertContains('save_post_sc_order', ncs_cart_canonical_to_legacy_hook_names('save_post_ppcart_order'));
        $this->assertNull(ncs_cart_legacy_to_canonical_hook_name('pre_option__sc_studiocart_compatibility_mode'));

        $legacy_names = ncs_cart_canonical_to_legacy_hook_names('ppcart_after_order_paid');
        $this->assertContains('sc_after_order_paid', $legacy_names);
        $this->assertContains('studiocart_checkout_complete', $legacy_names);
        $this->assertContains(
            'ncs-cart-currency-symbols',
            ncs_cart_canonical_to_legacy_hook_names('ppcart_currency_symbols')
        );
        $this->assertContains(
            'ncs-cart-repeater-more-link-label',
            ncs_cart_canonical_to_legacy_hook_names('ppcart-repeater-more-link-label')
        );
        $this->assertTrue(ncs_cart_hook_is_canonical_name('ppcart-repeater-more-link-label'));
        $this->assertTrue(ncs_cart_hook_bridge_is_candidate('ncs-cart-repeater-more-link-label'));
    }

    /**
     * @test-id UT-276
     */
    public function test_UT_276_hook_map_covers_leftover_before_sc_order_refund(): void
    {
        $this->assertSame(
            'ppcart_before_order_refund',
            ncs_cart_legacy_to_canonical_hook_name('before_sc_order_refund')
        );
        $this->assertContains(
            'before_sc_order_refund',
            ncs_cart_canonical_to_legacy_hook_names('ppcart_before_order_refund')
        );
        $this->assertSame(
            'ppcart_before_create_main_order',
            ncs_cart_legacy_to_canonical_hook_name('sc_before_create_main_order')
        );
        $this->assertTrue(ncs_cart_hook_bridge_is_candidate('before_sc_order_refund'));
        $this->assertTrue(ncs_cart_hook_bridge_is_candidate('ppcart_before_order_refund'));
    }

    /**
     * @test-id UT-301
     */
    public function test_UT_301_hook_map_covers_leftover_sc_cart_cpt_taxonomy_options(): void
    {
        $this->assertSame(
            'ppcart_cpt_options',
            ncs_cart_legacy_to_canonical_hook_name('sc-cart-cpt-options')
        );
        $this->assertContains(
            'sc-cart-cpt-options',
            ncs_cart_canonical_to_legacy_hook_names('ppcart_cpt_options')
        );
        $this->assertSame(
            'ppcart_taxonomy_options',
            ncs_cart_legacy_to_canonical_hook_name('sc-cart-taxonomy-options')
        );
        $this->assertContains(
            'sc-cart-taxonomy-options',
            ncs_cart_canonical_to_legacy_hook_names('ppcart_taxonomy_options')
        );
        $this->assertTrue(ncs_cart_hook_bridge_is_candidate('sc-cart-cpt-options'));
        $this->assertTrue(ncs_cart_hook_bridge_is_candidate('sc-cart-taxonomy-options'));
        $this->assertTrue(ncs_cart_hook_bridge_is_candidate('ppcart_cpt_options'));
        $this->assertTrue(ncs_cart_hook_bridge_is_candidate('ppcart_taxonomy_options'));
    }

    /**
     * @test-id UT-305
     */
    public function test_UT_305_hook_map_covers_shipped_leftover_activate_upgrade_product_hooks(): void
    {
        $pairs = [
            'sc_activate'       => 'ppcart_activate',
            'ncs_activate'      => 'ppcart_activate',
            'sc_upgrade'        => 'ppcart_upgrade',
            'ncs_upgrade'       => 'ppcart_upgrade',
            'sc_product'        => 'ppcart_product',
            'sc_product_price'  => 'ppcart_product_price',
        ];

        foreach ($pairs as $leftover => $canonical) {
            $this->assertSame(
                $canonical,
                ncs_cart_legacy_to_canonical_hook_name($leftover),
                $leftover
            );
            $this->assertContains(
                $leftover,
                ncs_cart_canonical_to_legacy_hook_names($canonical),
                $leftover
            );
            $this->assertTrue(ncs_cart_hook_bridge_is_candidate($leftover), $leftover);
            $this->assertTrue(ncs_cart_hook_bridge_is_candidate($canonical), $canonical);
        }

        foreach (['ppcart_ctivate', 'ppcart_pgrade', 'ppcart_roduct', 'ppcart_roduct_price'] as $mangled) {
            $this->assertNull(
                ncs_cart_legacy_to_canonical_hook_name($mangled),
                $mangled . ' must not be aliased'
            );
        }

        $this->assertNotContains(
            'ppcart_ctivate',
            ncs_cart_canonical_to_legacy_hook_names('ppcart_activate')
        );
        $this->assertNotContains(
            'ppcart_pgrade',
            ncs_cart_canonical_to_legacy_hook_names('ppcart_upgrade')
        );
        $this->assertNotContains(
            'ppcart_roduct',
            ncs_cart_canonical_to_legacy_hook_names('ppcart_product')
        );
        $this->assertNotContains(
            'ppcart_roduct_price',
            ncs_cart_canonical_to_legacy_hook_names('ppcart_product_price')
        );
    }

    public function test_UT_189_cron_bridges_register_always_and_legacy_hook_bridges_only_when_compat_is_on(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::cron_hook_bridges_registered());
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_hooks_registered());

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_cron_hook_bridges();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::cron_hook_bridges_registered());
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_hooks_registered());

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_hooks_registered());

        WordPressStubContext::setState(
            'options',
            [
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1',
            ]
        );

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_hooks_registered());
    }

    private function resetCompatibilityModeFlags(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);

        foreach ([ 'legacy_hooks_registered', 'cron_hook_bridges_registered' ] as $property_name) {
            $property = $reflection->getProperty($property_name);
            $property->setAccessible(true);
            $property->setValue(null, false);
        }
    }
}
