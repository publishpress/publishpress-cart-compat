<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class GlobalsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var array<string, mixed>
     */
    private $previousCanonical = [];

    /**
     * @var array<string, bool>
     */
    private $hadCanonical = [];

    protected function setUp(): void
    {
        parent::setUp();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->snapshotCanonicalGlobals();
        $this->clearLegacyGlobals();
        $this->resetGlobalsFlag();
    }

    protected function tearDown(): void
    {
        $this->clearLegacyGlobals();
        $this->restoreCanonicalGlobals();
        $this->resetGlobalsFlag();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        parent::tearDown();
    }

    public function test_IT_263_legacy_runtime_globals_stay_unaliased_while_compatibility_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_globals_registered());

        foreach (ncs_cart_get_legacy_global_map() as $canonical => $legacy_names) {
            foreach ($legacy_names as $legacy) {
                $this->assertFalse(
                    array_key_exists($legacy, $GLOBALS),
                    sprintf('$%s must stay unaliased while Compatibility Mode is off.', $legacy)
                );
            }

            if (in_array($canonical, [ 'ppcart_debug_logger', 'ppcart_currency', 'ppcart_stripe', 'ppcart_public' ], true)) {
                $this->assertTrue(
                    array_key_exists($canonical, $GLOBALS),
                    sprintf('$%s should exist after bootstrap.', $canonical)
                );
            }
        }
    }

    /**
     * @depends test_IT_263_legacy_runtime_globals_stay_unaliased_while_compatibility_mode_is_off
     */
    public function test_IT_264_compatibility_mode_aliases_legacy_runtime_globals_bidirectionally(): void
    {
        $inbound_product = (object) [ 'ID' => 4242 ];
        $GLOBALS['scp'] = $inbound_product;
        unset($GLOBALS['ppcart_product']);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_globals();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_globals_registered());
        $this->assertSame($inbound_product, $GLOBALS['ppcart_product']);
        $this->assertSame($inbound_product, $GLOBALS['scp']);

        $GLOBALS['scp']->ID = 7;
        $this->assertSame(7, $GLOBALS['ppcart_product']->ID);

        $GLOBALS['ppcart_currency'] = 'EUR';
        $this->assertSame('EUR', $GLOBALS['sc_currency']);
        $GLOBALS['sc_currency'] = 'BRL';
        $this->assertSame('BRL', $GLOBALS['ppcart_currency']);

        $GLOBALS['ppcart_currency_symbol'] = 'R$';
        $this->assertSame('R$', $GLOBALS['sc_currency_symbol']);

        $GLOBALS['ppcart_stripe'] = [ 'mode' => 'test', 'pk' => 'pk_alias' ];
        $this->assertSame('pk_alias', $GLOBALS['sc_stripe']['pk']);
        $GLOBALS['sc_stripe']['sk'] = 'sk_alias';
        $this->assertSame('sk_alias', $GLOBALS['ppcart_stripe']['sk']);

        $probe = (object) [ 'probe' => true ];
        $GLOBALS['ppcart_debug_logger'] = $probe;
        $this->assertSame($probe, $GLOBALS['sc_debug_logger']);
        $GLOBALS['sc_debug_logger'] = $this->previousCanonical['ppcart_debug_logger'] ?? null;
        $this->assertSame($this->previousCanonical['ppcart_debug_logger'] ?? null, $GLOBALS['ppcart_debug_logger']);
    }

    /**
     * @test-id IT-314
     */
    public function test_IT_314_checkout_runtime_globals_are_canonical_while_compatibility_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->ensureCheckoutCoreFunctions();

        unset(
            $GLOBALS['ppcart_checkout_request_rendered'],
            $GLOBALS['sc_checkout_request_rendered'],
            $GLOBALS['ppcart_checkout_block_arrangement'],
            $GLOBALS['sc_checkout_block_arrangement']
        );

        $this->assertTrue(ppcart_checkout_claim_request_render([ 'product_id' => 99 ]));
        if (array_key_exists('ppcart_checkout_request_rendered', $GLOBALS)) {
            $this->assertSame([ 'product:99' => true ], $GLOBALS['ppcart_checkout_request_rendered']);
            $this->assertFalse(ppcart_checkout_claim_request_render([ 'product_id' => 99 ]));
        } else {
            $GLOBALS['ppcart_checkout_request_rendered'] = [ 'product:99' => true ];
        }

        $this->assertFalse(array_key_exists('sc_checkout_request_rendered', $GLOBALS));

        ppcart_checkout_reset_request_render_guard();
        $this->assertFalse(array_key_exists('ppcart_checkout_request_rendered', $GLOBALS));
        $this->assertFalse(array_key_exists('sc_checkout_request_rendered', $GLOBALS));

        $GLOBALS['ppcart_checkout_block_arrangement'] = [ 'template' => '2-step' ];
        $this->assertSame([ 'template' => '2-step' ], ppcart_checkout_get_arrangement_context());
        $this->assertFalse(array_key_exists('sc_checkout_block_arrangement', $GLOBALS));
    }

    /**
     * @test-id IT-314
     */
    public function test_IT_314_compatibility_mode_aliases_checkout_runtime_globals(): void
    {
        $this->ensureCheckoutCoreFunctions();

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_globals();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_globals_registered());

        $GLOBALS['ppcart_checkout_request_rendered'] = [ 'product:1' => true ];
        $this->assertSame([ 'product:1' => true ], $GLOBALS['sc_checkout_request_rendered']);
        $GLOBALS['sc_checkout_request_rendered']['product:2'] = true;
        $this->assertTrue($GLOBALS['ppcart_checkout_request_rendered']['product:2']);

        $GLOBALS['ppcart_checkout_block_arrangement'] = [ 'template' => 'opt-in' ];
        $this->assertSame('opt-in', $GLOBALS['sc_checkout_block_arrangement']['template']);
        $GLOBALS['sc_checkout_block_arrangement']['contentOrder'] = [ 'contact_info' ];
        $this->assertSame([ 'contact_info' ], $GLOBALS['ppcart_checkout_block_arrangement']['contentOrder']);
        $this->assertSame([ 'contact_info' ], ppcart_checkout_get_arrangement_context()['contentOrder']);
    }

    /**
     * @test-id IT-332
     */
    public function test_IT_332_studiocart_global_stays_unaliased_while_compatibility_mode_is_off(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertFalse(array_key_exists('studiocart', $GLOBALS));
        $this->assertTrue(array_key_exists('ppcart_public', $GLOBALS));
        $this->assertInstanceOf(\PPCart_Public::class, $GLOBALS['ppcart_public']);
    }

    /**
     * @test-id IT-332
     * @depends test_IT_332_studiocart_global_stays_unaliased_while_compatibility_mode_is_off
     */
    public function test_IT_332_compatibility_mode_aliases_studiocart_onto_ppcart_public(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_globals();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_globals_registered());
        $this->assertTrue(array_key_exists('studiocart', $GLOBALS));
        $this->assertSame($GLOBALS['ppcart_public'], $GLOBALS['studiocart']);
        $this->assertInstanceOf(\PPCart_Public::class, $GLOBALS['studiocart']);

        $probe = (object) [ 'slice' => 31 ];
        $GLOBALS['ppcart_public'] = $probe;
        $this->assertSame($probe, $GLOBALS['studiocart']);

        $restored = $this->previousCanonical['ppcart_public'] ?? $probe;
        $GLOBALS['studiocart'] = $restored;
        $this->assertSame($restored, $GLOBALS['ppcart_public']);
    }

    private function ensureCheckoutCoreFunctions(): void
    {
        if (! function_exists('ppcart_checkout_get_arrangement_context')) {
            require_once PPCART_PLUGIN_ROOT . 'public/templates/functions/checkout-core.php';
        }
    }

    private function snapshotCanonicalGlobals(): void
    {
        $this->previousCanonical = [];
        $this->hadCanonical = [];

        foreach (ncs_cart_get_legacy_global_map() as $canonical => $legacy_names) {
            unset($legacy_names);
            $this->hadCanonical[ $canonical ] = array_key_exists($canonical, $GLOBALS);
            $this->previousCanonical[ $canonical ] = $this->hadCanonical[ $canonical ]
                ? $GLOBALS[ $canonical ]
                : null;
        }
    }

    private function restoreCanonicalGlobals(): void
    {
        foreach ($this->hadCanonical as $canonical => $had) {
            if ($had) {
                $GLOBALS[ $canonical ] = $this->previousCanonical[ $canonical ];
                continue;
            }

            unset($GLOBALS[ $canonical ]);
        }
    }

    private function clearLegacyGlobals(): void
    {
        foreach (ncs_cart_get_legacy_global_map() as $legacy_names) {
            foreach ($legacy_names as $legacy) {
                unset($GLOBALS[ $legacy ]);
            }
        }
    }

    private function resetGlobalsFlag(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);
        $property = $reflection->getProperty('legacy_globals_registered');
        $property->setAccessible(true);
        $property->setValue(null, false);
    }
}
