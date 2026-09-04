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
}
