<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCart_Product_Duplicator;
use PPCartComp_Studiocart_Compatibility_Mode;

class LeftoverRecognitionTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        delete_option('_ppcart_myaccount_page_id');
        delete_option('_my_account');

        if (function_exists('ppcart_unregister_legacy_cpt_taxonomy_bridges')) {
            ppcart_unregister_legacy_cpt_taxonomy_bridges();
        }
    }

    protected function tearDown(): void
    {
        delete_option('_ppcart_myaccount_page_id');
        delete_option('_my_account');
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_cpt_taxonomy_bridges')) {
            ppcart_unregister_legacy_cpt_taxonomy_bridges();
        }

        parent::tearDown();
    }

    /**
     * @test-id IT-354
     */
    public function test_IT_354_leftover_my_account_row_is_not_deleted_on_canonical_read(): void
    {
        update_option('_my_account', 42, false);
        delete_option('_ppcart_myaccount_page_id');

        $this->assertSame('42', (string) get_option('_ppcart_myaccount_page_id'));
        $this->assertTrue((bool) get_option('_my_account'));
    }

    /**
     * @test-id IT-354
     */
    public function test_IT_354_compat_on_reads_leftover_my_account_without_deleting_row(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        update_option('_my_account', 42, false);

        $this->assertSame('42', (string) get_option('_ppcart_myaccount_page_id'));
        $this->assertTrue((bool) get_option('_my_account'));
    }

    /**
     * @test-id IT-354
     */
    public function test_IT_354_compat_on_registers_leftover_fse_template_slugs(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_cpt_taxonomy();

        $this->assertContains('single-sc_product', ppcart_cpt_leftover_fse_template_slugs());
    }

    /**
     * @test-id IT-354
     */
    public function test_IT_354_compat_on_rewrites_leftover_product_meta_on_duplicate(): void
    {
        if (! function_exists('ppcartcomp_product_duplicate_meta_key')) {
            $this->markTestSkipped('Compat product-duplicate bridge unavailable.');
        }

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $rewritten = ppcartcomp_product_duplicate_meta_key(
            '_sc_pay_options',
            [ '_sc_pay_options' => [ 'plan' ] ]
        );

        $this->assertSame('_ppcart_pay_options', $rewritten);
    }

    /**
     * @test-id IT-354
     */
    public function test_IT_354_compat_on_stripe_owned_field_accepts_leftover_meta_prefix(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $this->assertTrue(ppcart_is_stripe_owned_field('_sc_status'));
        $this->assertFalse(ppcart_is_stripe_owned_field('_sc_email', new \PPCart_Order()));
    }
}
