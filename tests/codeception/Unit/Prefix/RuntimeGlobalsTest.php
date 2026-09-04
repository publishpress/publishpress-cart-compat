<?php

namespace unit\Prefix;

use Codeception\Test\Unit;
use UnitTester;

class RuntimeGlobalsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-227
     */
    public function test_UT_227_compatibility_mode_maps_leftover_checkout_runtime_globals(): void
    {
        $source = (string) file_get_contents(
            PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/studiocart-compatibility-mode/globals.php'
        );
        $this->assertStringContainsString("'ppcart_checkout_request_rendered'", $source);
        $this->assertStringContainsString("'sc_checkout_request_rendered'", $source);
        $this->assertStringContainsString("'ppcart_checkout_block_arrangement'", $source);
        $this->assertStringContainsString("'sc_checkout_block_arrangement'", $source);
    }

    /**
     * @test-id UT-248
     */
    public function test_UT_248_compatibility_mode_maps_leftover_studiocart_runtime_global(): void
    {
        if (! function_exists('ncs_cart_get_legacy_global_map')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/studiocart-compatibility-mode/globals.php';
        }

        $this->assertTrue(function_exists('ncs_cart_get_legacy_global_map'));
        $map = ncs_cart_get_legacy_global_map();
        $this->assertArrayHasKey('ppcart_public', $map);
        $this->assertSame([ 'studiocart' ], $map['ppcart_public']);
    }

    /**
     * @test-id UT-250
     */
    public function test_UT_250_compatibility_mode_maps_leftover_files_product_fields_and_admin_screen_globals(): void
    {
        if (! function_exists('ncs_cart_get_legacy_global_map')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/studiocart-compatibility-mode/globals.php';
        }

        $this->assertTrue(function_exists('ncs_cart_get_legacy_global_map'));
        $map = ncs_cart_get_legacy_global_map();
        $this->assertArrayHasKey('ppcart_files', $map);
        $this->assertSame([ 'scFiles' ], $map['ppcart_files']);
        $this->assertArrayHasKey('ppcart_product_fields', $map);
        $this->assertSame([ 'sc_product_fields' ], $map['ppcart_product_fields']);
        $this->assertArrayHasKey('ppcart_is_admin_screen', $map);
        $this->assertSame([ 'sc_is_studiocart_admin_screen' ], $map['ppcart_is_admin_screen']);
    }
}
