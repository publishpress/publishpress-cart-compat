<?php

namespace unit\Prefix;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class MetaKeyMigrationTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        WordPressStubContext::clear();
        WordPressStubContext::setState('options', []);

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
            'update_option',
            function ($option, $value) {
                $options = WordPressStubContext::getState('options', []);
                $options[$option] = $value;
                WordPressStubContext::setState('options', $options);

                return true;
            }
        );

        WordPressStubContext::set(
            'apply_filters',
            static function ($hook, $value) {
                return $value;
            }
        );

        if (! function_exists('ppcart_meta_key')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/meta-key-migration/helpers.php';
        }
        if (! function_exists('ppcart_meta_key_migration_complete')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/meta-key-migration/state.php';
        }
    }

    protected function _after(): void
    {
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-203
     */
    public function test_UT_203_helper_maps_leftover_canonical_and_suffix(): void
    {
        $this->assertSame('_ppcart_pay_options', ppcart_meta_key('pay_options'));
        $this->assertSame('_ppcart_pay_options', ppcart_meta_key('_sc_pay_options'));
        $this->assertSame('_ppcart_pay_options', ppcart_meta_key('_ppcart_pay_options'));
        $this->assertSame('_sc_pay_options', ppcart_legacy_meta_key('pay_options'));
        $this->assertSame('_sc_pay_options', ppcart_legacy_meta_key('_ppcart_pay_options'));
        $this->assertTrue(ppcart_is_meta_field_id('_ppcart_hide_plans', 'hide_plans'));
        $this->assertTrue(ppcart_is_meta_field_id('_sc_hide_plans', 'hide_plans'));
        $this->assertTrue(ppcart_is_meta_field_id('hide_plans', 'hide_plans'));
        $this->assertSame('_ppcart_checkout_starts', ppcart_meta_key('checkout_starts'));
        $this->assertSame('_ppcart_checkout_starts', ppcart_meta_key('_sc_cart_open'));
        $this->assertSame('_ppcart_checkout_starts', ppcart_meta_key('cart_open'));
        $this->assertSame('_sc_cart_open', ppcart_legacy_meta_key('checkout_starts'));
        $this->assertSame('_sc_cart_open', ppcart_legacy_meta_key('_ppcart_checkout_starts'));
        $this->assertSame('_ppcart_checkout_ended_redirect', ppcart_meta_key('_sc_cart_redirect'));
        $this->assertSame('_sc_cart_redirect', ppcart_legacy_meta_key('checkout_ended_redirect'));
        $this->assertTrue(ppcart_is_meta_field_id('_sc_cart_open', 'checkout_starts'));
        $this->assertTrue(ppcart_is_meta_field_id('_ppcart_checkout_starts', 'checkout_starts'));
    }

    /**
     * @test-id UT-203
     */
    public function test_UT_203_query_helper_returns_canonical_only_when_compat_is_off(): void
    {
        if (PPCartComp_Studiocart_Compatibility_Mode::is_wp_config_forced() && PPCartComp_Studiocart_Compatibility_Mode::is_enabled()) {
            $this->markTestSkipped('Compatibility Mode is forced on via wp-config in this process.');
        }

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertSame([ '_ppcart_amount' ], ppcart_query_meta_keys('amount'));
        $this->assertSame([ '_ppcart_amount' ], ppcart_query_meta_keys('_sc_amount'));
    }

    /**
     * @test-id UT-203
     */
    public function test_UT_203_query_helper_returns_both_keys_when_compat_is_on(): void
    {
        if (PPCartComp_Studiocart_Compatibility_Mode::is_wp_config_forced() && ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()) {
            $this->markTestSkipped('Compatibility Mode is forced off via wp-config in this process.');
        }

        WordPressStubContext::setState(
            'options',
            [
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1',
            ]
        );

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertSame(
            [ '_ppcart_amount', '_sc_amount' ],
            ppcart_query_meta_keys('amount')
        );
    }

    /**
     * @test-id UT-203
     */
    public function test_UT_203_query_helper_returns_canonical_only_after_migration_completes(): void
    {
        WordPressStubContext::setState(
            'options',
            [
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1',
                'ppcart_meta_key_migration' => [
                    'ack'            => '',
                    'mode'           => 'canonical',
                    'step1'          => 'complete',
                    'stop_requested' => false,
                    'cursor'         => null,
                ],
            ]
        );

        $this->assertTrue(ppcart_meta_key_migration_complete());
        $this->assertSame([ '_ppcart_amount' ], ppcart_query_meta_keys('amount'));
    }

    /**
     * @test-id UT-203
     */
    public function test_UT_203_meta_query_expander_leaves_unrelated_keys_alone(): void
    {
        WordPressStubContext::set('add_action', static function () {
            return true;
        });
        WordPressStubContext::set('add_filter', static function () {
            return true;
        });
        WordPressStubContext::set('has_filter', static function () {
            return false;
        });

        if (! function_exists('ppcart_expand_meta_query_keys')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/meta-key-migration/index.php';
        }

        $query = [
            'relation' => 'AND',
            [
                'key'   => '_thumbnail_id',
                'value' => '12',
            ],
            [
                'key'   => '_sc_amount',
                'value' => '10',
            ],
        ];

        $expanded = ppcart_expand_meta_query_keys($query);

        $this->assertSame('_thumbnail_id', $expanded[0]['key']);
        if (isset($expanded[1]['key'])) {
            $this->assertSame('_ppcart_amount', $expanded[1]['key']);
        } else {
            $this->assertSame('OR', $expanded[1]['relation']);
            $this->assertSame('_ppcart_amount', $expanded[1][0]['key']);
            $this->assertSame('_sc_amount', $expanded[1][1]['key']);
        }
    }

    /**
     * @test-id UT-247
     */
    public function test_UT_247_product_duplicate_rewrites_leftover_meta_to_canonical(): void
    {
        if (! function_exists('ppcartcomp_product_duplicate_meta_key')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/meta-key-migration/product-duplicate.php';
        }

        $leftover_only = [ '_sc_pay_options' => [ 'serialized' ] ];
        $this->assertSame(
            '_ppcart_pay_options',
            ppcartcomp_product_duplicate_meta_key('_sc_pay_options', $leftover_only)
        );

        $with_canonical_twin = [
            '_sc_pay_options'     => [ 'leftover' ],
            '_ppcart_pay_options' => [ 'canonical' ],
        ];
        $this->assertNull(
            ppcartcomp_product_duplicate_meta_key('_sc_pay_options', $with_canonical_twin)
        );

        $this->assertSame(
            '_ppcart_pay_options',
            ppcartcomp_product_duplicate_meta_key('_ppcart_pay_options', $leftover_only)
        );
    }

    /**
     * @test-id UT-247
     */
    public function test_UT_247_product_duplicate_excludes_leftover_stripe_prod_id(): void
    {
        if (! function_exists('ppcartcomp_product_duplicate_excluded_meta_keys')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/meta-key-migration/product-duplicate.php';
        }

        $excluded = ppcartcomp_product_duplicate_excluded_meta_keys(
            [ ppcart_meta_key('stripe_prod_id') ]
        );

        $this->assertContains(ppcart_legacy_meta_key('stripe_prod_id'), $excluded);
        $this->assertContains(ppcart_meta_key('stripe_prod_id'), $excluded);
    }

    /**
     * @test-id UT-247
     */
    public function test_UT_247_setup_from_meta_hydrates_leftover_product_and_order_keys(): void
    {
        if (! function_exists('ppcartcomp_setup_product_from_leftover_meta')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/meta-key-migration/setup-from-meta.php';
        }

        $product = ppcartcomp_setup_product_from_leftover_meta(
            [ 'ID' => 11 ],
            [
                '_sc_pay_options' => [ serialize([ [ 'option_id' => 'plan_one' ] ]) ],
                '_ppcart_ignored' => [ 'canonical-not-read-here' ],
            ],
            11
        );

        $this->assertSame(
            [ [ 'option_id' => 'plan_one' ] ],
            $product['pay_options']
        );
        $this->assertArrayNotHasKey('ignored', $product);

        $order = ppcartcomp_setup_order_from_leftover_meta(
            [ 'ID' => 22, 'invoice_total' => 0 ],
            [
                '_sc_amount' => [ '12.50' ],
                '_sc_email'  => [ 'buyer@example.test' ],
            ],
            22
        );

        $this->assertSame('12.50', $order['amount']);
        $this->assertSame('buyer@example.test', $order['email']);
    }
}
