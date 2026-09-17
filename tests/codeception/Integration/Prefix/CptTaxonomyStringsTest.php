<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCart_Admin_Screens;
use PPCartComp_Studiocart_Compatibility_Mode;

class CptTaxonomyStringsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_cpt_taxonomy_bridges')) {
            ppcart_unregister_legacy_cpt_taxonomy_bridges();
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('ppcart_unregister_legacy_cpt_taxonomy_bridges')) {
            ppcart_unregister_legacy_cpt_taxonomy_bridges();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-328
     */
    public function test_IT_328_pro_collection_queries_include_leftover_from_maps(): void
    {
        $this->assertSame(
            [ 'ppcart_collection', 'sc_collection' ],
            ppcart_query_pro_post_types('collection')
        );
        $this->assertContains('sc_collection', ppcart_query_product_and_collection_post_types());
        $this->assertContains('ppcart_collection', PPCart_Admin_Screens::all_post_types());
        $this->assertContains('sc_collection', PPCart_Admin_Screens::all_post_types());
    }

    /**
     * @test-id IT-328
     */
    public function test_IT_328_leftover_fse_template_registers_only_when_compatibility_mode_is_on(): void
    {
        $this->assertSame([], ppcart_cpt_leftover_fse_template_slugs());

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_cpt_taxonomy();

        $this->assertContains('single-sc_product', ppcart_cpt_leftover_fse_template_slugs());
    }

    /**
     * @test-id IT-328
     */
    public function test_IT_328_leftover_pro_cpt_registers_only_when_compat_on_and_canonical_exists(): void
    {
        register_post_type(
            'ppcart_collection',
            [
                'public' => false,
                'label'  => 'Collections',
            ]
        );

        $this->assertFalse(post_type_exists('sc_collection'));

        ppcart_register_legacy_pro_post_types();
        $this->assertFalse(post_type_exists('sc_collection'));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        ppcart_register_legacy_pro_post_types();

        $this->assertTrue(post_type_exists('sc_collection'));
        $object = get_post_type_object('sc_collection');
        $this->assertNotNull($object);
        $this->assertFalse($object->show_ui);

        unregister_post_type('ppcart_collection');
    }
}
