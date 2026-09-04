<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class AjaxActionsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_ajax_bridges')) {
            ppcart_unregister_legacy_ajax_bridges();
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('ppcart_unregister_legacy_ajax_bridges')) {
            ppcart_unregister_legacy_ajax_bridges();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-268
     */
    public function test_IT_268_core_registers_only_canonical_ajax_actions(): void
    {
        $this->assertNotFalse(has_action('wp_ajax_ppcart_save_order_to_db'));
        $this->assertNotFalse(has_action('wp_ajax_nopriv_ppcart_save_order_to_db'));
        $this->assertNotFalse(has_action('wp_ajax_ppcart_json_search_user'));
        $this->assertFalse(has_action('wp_ajax_nopriv_ppcart_json_search_user'));

        $this->assertFalse(has_action('wp_ajax_sc_save_order_to_db'));
        $this->assertFalse(has_action('wp_ajax_nopriv_sc_save_order_to_db'));
        $this->assertFalse(has_action('wp_ajax_ncs_ajax_action'));
        $this->assertFalse(has_action('wp_ajax_sc_json_search_user'));
    }

    /**
     * @test-id IT-268
     */
    public function test_IT_268_compatibility_mode_registers_legacy_ajax_actions_with_auth_parity(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_ajax();

        $this->assertNotFalse(has_action('wp_ajax_sc_save_order_to_db'));
        $this->assertNotFalse(has_action('wp_ajax_nopriv_sc_save_order_to_db'));
        $this->assertNotFalse(has_action('wp_ajax_ncs_ajax_action'));
        $this->assertNotFalse(has_action('wp_ajax_sc_json_search_user'));
        $this->assertFalse(has_action('wp_ajax_nopriv_ncs_ajax_action'));
        $this->assertFalse(has_action('wp_ajax_nopriv_sc_json_search_user'));
    }

    /**
     * @test-id IT-330
     */
    public function test_IT_330_pro_owned_legacy_ajax_hooks_absent_when_compat_off(): void
    {
        $this->assertFalse(has_action('wp_ajax_sc_check_username'));
        $this->assertFalse(has_action('wp_ajax_nopriv_sc_check_username'));
        $this->assertFalse(has_action('wp_ajax_sc_capture_lead'));
        $this->assertFalse(has_action('wp_ajax_nopriv_sc_capture_lead'));

        $this->assertFalse(has_action('wp_ajax_ppcart_check_username'));
        $this->assertFalse(has_action('wp_ajax_nopriv_ppcart_check_username'));
        $this->assertFalse(has_action('wp_ajax_ppcart_capture_lead'));
        $this->assertFalse(has_action('wp_ajax_nopriv_ppcart_capture_lead'));
    }

    /**
     * @test-id IT-330
     */
    public function test_IT_330_pro_owned_legacy_ajax_hooks_register_when_compat_on(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_ajax();

        $this->assertNotFalse(has_action('wp_ajax_sc_check_username'));
        $this->assertNotFalse(has_action('wp_ajax_nopriv_sc_check_username'));
        $this->assertNotFalse(has_action('wp_ajax_sc_capture_lead'));
        $this->assertNotFalse(has_action('wp_ajax_nopriv_sc_capture_lead'));

        $this->assertFalse(has_action('wp_ajax_ppcart_check_username'));
        $this->assertFalse(has_action('wp_ajax_nopriv_ppcart_check_username'));
        $this->assertFalse(has_action('wp_ajax_ppcart_capture_lead'));
        $this->assertFalse(has_action('wp_ajax_nopriv_ppcart_capture_lead'));
    }

    /**
     * @test-id IT-268
     */
    public function test_IT_268_canonical_nonces_verify_without_compatibility_mode(): void
    {
        $nonce = wp_create_nonce('ppcart_purchase_nonce');

        $this->assertNotFalse(ppcart_verify_nonce($nonce, 'ppcart_purchase_nonce'));
        $this->assertFalse(ppcart_verify_nonce($nonce, 'sc_purchase_nonce'));
    }

    /**
     * @test-id IT-268
     */
    public function test_IT_268_legacy_nonces_verify_only_when_compatibility_mode_is_on(): void
    {
        $legacy_purchase = wp_create_nonce('sc_purchase_nonce');
        $legacy_duplicate = wp_create_nonce('sc_duplicate_product_41');

        $this->assertFalse(ppcart_verify_nonce($legacy_purchase, 'ppcart_purchase_nonce'));
        $this->assertFalse(ppcart_verify_nonce($legacy_duplicate, 'ppcart_duplicate_product_41'));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_ajax();

        $this->assertNotFalse(ppcart_verify_nonce($legacy_purchase, 'ppcart_purchase_nonce'));
        $this->assertNotFalse(ppcart_verify_nonce($legacy_duplicate, 'ppcart_duplicate_product_41'));
        $this->assertNotFalse(
            ppcart_verify_nonce(wp_create_nonce('ppcart_purchase_nonce'), 'ppcart_purchase_nonce')
        );
    }
}
