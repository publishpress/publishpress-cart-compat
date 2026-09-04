<?php

namespace unit\Prefix;

use Codeception\Test\Unit;
use Tests\Support\WordPressStubContext;
use UnitTester;

class CptSlugMigrationTest extends Unit
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

        if (! function_exists('ppcart_cpt_slug_migration_maps')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/cpt-slug-migration/maps.php';
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/cpt-slug-migration/state.php';
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/cpt-slug-migration/helpers.php';
        }
    }

    protected function _after(): void
    {
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-200
     */
    public function test_UT_200_live_post_type_is_leftover_until_step1_completes(): void
    {
        $this->assertSame('sc_product', ppcart_live_post_type('product'));
        $this->assertSame('sc_product_cat', ppcart_taxonomy_rewrite_slug('product_cat'));
        $this->assertSame('sc_cart_manager', ppcart_live_role('sc_cart_manager'));
        $this->assertSame('sc_cart_manager', ppcart_live_role('cart_manager'));

        WordPressStubContext::setState(
            'options',
            [
                'ppcart_cpt_slug_migration' => [
                    'ack'            => '',
                    'mode'           => 'canonical',
                    'step1'          => 'complete',
                    'step2'          => 'idle',
                    'stop_requested' => false,
                    'cursor'         => null,
                ],
            ]
        );

        $this->assertSame('ppcart_product', ppcart_live_post_type('product'));
        $this->assertSame('ppcart_product_cat', ppcart_taxonomy_rewrite_slug('product_cat'));
        $this->assertSame('ppcart_manager_option', ppcart_live_cap('sc_manager_option'));
        $this->assertSame('ppcart_manager_option', ppcart_live_cap('manager_option'));
        $this->assertSame('sc_cart_manager', ppcart_live_role('sc_cart_manager'));
        $this->assertSame('sc_cart_manager', ppcart_live_role('cart_manager'));
    }

    /**
     * @test-id UT-200
     */
    public function test_UT_200_live_cap_stays_leftover_while_step1_is_mixed(): void
    {
        WordPressStubContext::setState(
            'options',
            [
                'ppcart_cpt_slug_migration' => [
                    'ack'            => '',
                    'mode'           => 'legacy',
                    'step1'          => 'running',
                    'step2'          => 'idle',
                    'stop_requested' => false,
                    'cursor'         => null,
                ],
            ]
        );

        $this->assertSame('sc_manager_option', ppcart_live_cap('sc_manager_option'));
        $this->assertSame('sc_manager_option', ppcart_live_cap('manager_option'));
    }

    /**
     * @test-id UT-200
     */
    public function test_UT_200_query_post_types_include_both_slugs_while_step1_is_mixed(): void
    {
        WordPressStubContext::setState(
            'options',
            [
                'ppcart_cpt_slug_migration' => [
                    'ack'            => '',
                    'mode'           => 'legacy',
                    'step1'          => 'running',
                    'step2'          => 'idle',
                    'stop_requested' => false,
                    'cursor'         => null,
                ],
            ]
        );

        $this->assertSame(
            [ 'ppcart_product', 'sc_product' ],
            ppcart_query_post_types('product')
        );
        $this->assertTrue(ppcart_is_product_post_type('sc_product'));
        $this->assertTrue(ppcart_is_product_post_type('ppcart_product'));
    }

    /**
     * @test-id UT-200
     */
    public function test_UT_200_user_can_accepts_the_mapped_cap_when_live_cap_is_missing(): void
    {
        WordPressStubContext::set(
            'current_user_can',
            static function ($capability) {
                return 'edit_ppcart_products' === $capability;
            }
        );

        $this->assertTrue(ppcart_user_can('edit_sc_products'));
        $this->assertTrue(ppcart_user_can('edit_ppcart_products'));
    }

    /**
     * @test-id UT-200
     */
    public function test_UT_200_derived_wordpress_hooks_map_embedded_cpt_slugs(): void
    {
        $this->assertSame('save_post_ppcart_order', ncs_cart_legacy_to_canonical_hook_name('save_post_sc_order'));
        $this->assertContains(
            'save_post_sc_order',
            ncs_cart_canonical_to_legacy_hook_names('save_post_ppcart_order')
        );
        $this->assertSame(
            'manage_ppcart_product_posts_columns',
            ncs_cart_legacy_to_canonical_hook_name('manage_sc_product_posts_columns')
        );
    }
}
