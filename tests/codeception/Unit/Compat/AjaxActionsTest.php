<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class AjaxActionsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        if (! function_exists('ppcart_verify_nonce')) {
            require_once PPCART_PLUGIN_ROOT . 'includes/functions/ajax-security.php';
        }

        $this->resetAjaxBridges();

        WordPressStubContext::setState('options', []);
        WordPressStubContext::setState('actions', []);
        WordPressStubContext::setState('nonces', []);

        WordPressStubContext::set(
            'get_option',
            static function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                return array_key_exists($option, $options) ? $options[ $option ] : $default;
            }
        );

        WordPressStubContext::set(
            'add_action',
            static function ($hook) {
                $actions = WordPressStubContext::getState('actions', []);
                $actions[] = $hook;
                WordPressStubContext::setState('actions', $actions);

                return true;
            }
        );

        WordPressStubContext::set(
            'remove_action',
            static function () {
                return true;
            }
        );

        WordPressStubContext::set(
            'do_action',
            static function () {
                return null;
            }
        );

        WordPressStubContext::set(
            'wp_verify_nonce',
            static function ($nonce, $action) {
                $nonces = WordPressStubContext::getState('nonces', []);

                return ( $nonces[ $action ] ?? null ) === $nonce ? 1 : false;
            }
        );
    }

    protected function _after(): void
    {
        $this->resetAjaxBridges();
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-199
     */
    public function test_UT_199_ajax_map_covers_legacy_actions_with_auth_parity(): void
    {
        $map = ncs_cart_get_legacy_ajax_action_map();

        $this->assertSame([ 'sc_save_order_to_db' ], $map['ppcart_save_order_to_db']['legacy']);
        $this->assertTrue($map['ppcart_save_order_to_db']['auth']);
        $this->assertTrue($map['ppcart_save_order_to_db']['nopriv']);

        $this->assertSame([ 'ncs_ajax_action' ], $map['ppcart_ajax_action']['legacy']);
        $this->assertTrue($map['ppcart_ajax_action']['auth']);
        $this->assertFalse($map['ppcart_ajax_action']['nopriv']);

        $this->assertSame([ 'sc_json_search_user' ], $map['ppcart_json_search_user']['legacy']);
        $this->assertTrue($map['ppcart_json_search_user']['auth']);
        $this->assertFalse($map['ppcart_json_search_user']['nopriv']);

        foreach ($map as $canonical => $contract) {
            $this->assertStringStartsWith('ppcart_', $canonical);
            $this->assertNotEmpty($contract['legacy']);
            $this->assertTrue($contract['auth']);
            $this->assertArrayHasKey('nopriv', $contract);
        }
    }

    /**
     * @test-id UT-238
     */
    public function test_UT_238_public_js_posts_canonical_pro_ajax_actions(): void
    {
        $js = file_get_contents(PPCART_PLUGIN_ROOT . 'public/js/ppcart-public.js');
        $this->assertIsString($js);

        $this->assertStringContainsString("'ppcart_check_username'", $js);
        $this->assertStringContainsString("'ppcart_capture_lead'", $js);
        $this->assertStringNotContainsString("'sc_check_username'", $js);
        $this->assertStringNotContainsString("'sc_capture_lead'", $js);
    }

    /**
     * @test-id UT-238
     */
    public function test_UT_238_ajax_map_covers_pro_owned_username_and_lead_actions(): void
    {
        $map = ncs_cart_get_legacy_ajax_action_map();

        $this->assertSame([ 'sc_check_username' ], $map['ppcart_check_username']['legacy']);
        $this->assertTrue($map['ppcart_check_username']['auth']);
        $this->assertTrue($map['ppcart_check_username']['nopriv']);

        $this->assertSame([ 'sc_capture_lead' ], $map['ppcart_capture_lead']['legacy']);
        $this->assertTrue($map['ppcart_capture_lead']['auth']);
        $this->assertTrue($map['ppcart_capture_lead']['nopriv']);

        $this->assertSame([ 'sc_validate_coupon' ], $map['ppcart_validate_coupon']['legacy']);
        $this->assertTrue($map['ppcart_validate_coupon']['auth']);
        $this->assertTrue($map['ppcart_validate_coupon']['nopriv']);

        $this->assertSame([ 'sc_process_upsell' ], $map['ppcart_process_upsell']['legacy']);
        $this->assertTrue($map['ppcart_process_upsell']['auth']);
        $this->assertTrue($map['ppcart_process_upsell']['nopriv']);
    }

    /**
     * @test-id UT-199
     */
    public function test_UT_199_nonce_map_covers_exact_and_prefix_legacy_actions(): void
    {
        $map = ncs_cart_get_legacy_nonce_action_map();

        $this->assertSame([ 'sc_purchase_nonce' ], $map['exact']['ppcart_purchase_nonce']);
        $this->assertSame([ 'sc-ajax-nonce' ], $map['exact']['ppcart_ajax_nonce']);
        $this->assertSame(
            [ 'ncs_cart_search_report_customers' ],
            $map['exact']['ppcart_search_report_customers']
        );
        $this->assertSame([ 'studiocart_upsell-' ], $map['prefix']['ppcart_upsell-']);
        $this->assertSame(
            [ 'studiocart_upsell-99' ],
            ncs_cart_get_legacy_nonce_actions('ppcart_upsell-99')
        );
        $this->assertSame(
            [ 'sc_duplicate_product_12' ],
            ncs_cart_get_legacy_nonce_actions('ppcart_duplicate_product_12')
        );
    }

    /**
     * @test-id UT-199
     */
    public function test_UT_199_registers_legacy_ajax_hooks_only_when_compatibility_mode_is_on(): void
    {
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_ajax();

        $this->assertNotContains('wp_ajax_sc_save_order_to_db', WordPressStubContext::getState('actions', []));
        $this->assertNotContains('wp_ajax_ncs_ajax_action', WordPressStubContext::getState('actions', []));

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );
        WordPressStubContext::setState('actions', []);

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_ajax();

        $actions = WordPressStubContext::getState('actions', []);

        $this->assertContains('wp_ajax_sc_save_order_to_db', $actions);
        $this->assertContains('wp_ajax_nopriv_sc_save_order_to_db', $actions);
        $this->assertContains('wp_ajax_ncs_ajax_action', $actions);
        $this->assertContains('wp_ajax_sc_json_search_user', $actions);
        $this->assertNotContains('wp_ajax_nopriv_ncs_ajax_action', $actions);
        $this->assertNotContains('wp_ajax_nopriv_sc_json_search_user', $actions);
    }

    /**
     * @test-id UT-199
     */
    public function test_UT_199_legacy_nonces_verify_only_when_compatibility_mode_is_on(): void
    {
        WordPressStubContext::setState(
            'nonces',
            [
                'ppcart_purchase_nonce' => 'canonical-token',
                'sc_purchase_nonce'     => 'legacy-token',
                'studiocart_upsell-7'   => 'upsell-token',
            ]
        );

        $this->assertSame(1, ppcart_verify_nonce('canonical-token', 'ppcart_purchase_nonce'));
        $this->assertFalse(ppcart_verify_nonce('legacy-token', 'ppcart_purchase_nonce'));
        $this->assertFalse(ppcart_verify_nonce('upsell-token', 'ppcart_upsell-7'));

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_ajax();

        $this->assertSame(1, ppcart_verify_nonce('canonical-token', 'ppcart_purchase_nonce'));
        $this->assertSame(1, ppcart_verify_nonce('legacy-token', 'ppcart_purchase_nonce'));
        $this->assertSame(1, ppcart_verify_nonce('upsell-token', 'ppcart_upsell-7'));
    }

    private function resetAjaxBridges(): void
    {
        if (function_exists('ppcart_unregister_legacy_ajax_bridges')) {
            ppcart_unregister_legacy_ajax_bridges();
        }
    }
}
