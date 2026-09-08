<?php

declare(strict_types=1);

namespace Tests\Unit\Compat;

use Codeception\Test\Unit;

class CompatPrefixTest extends Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-299
     */
    public function test_UT_299_metadata_bridges_use_ppcartcomp_prefix(): void
    {
        $source = $this->pluginSource('includes/compat/meta-key-migration/metadata-bridges.php');

        $this->assertStringNotContainsString('ppcart_compat_', $source);
        $this->assertStringContainsString("has_filter('get_post_metadata', 'ppcartcomp_get_post_metadata')", $source);
        $this->assertStringContainsString("add_filter('get_post_metadata', 'ppcartcomp_get_post_metadata', 10, 4)", $source);
        $this->assertStringContainsString('function ppcartcomp_get_post_metadata(', $source);
        $this->assertStringContainsString('function ppcartcomp_write_metadata(', $source);
    }

    private function pluginSource(string $relative): string
    {
        return (string) file_get_contents(PPCARTCOMP_PLUGIN_ROOT . $relative);
    }
}
