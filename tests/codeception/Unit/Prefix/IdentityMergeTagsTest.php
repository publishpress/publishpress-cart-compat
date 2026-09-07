<?php

namespace unit\Prefix;

use Codeception\Test\Unit;
use UnitTester;

class IdentityMergeTagsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-287
     */
    public function test_UT_287_merge_tag_bridge_and_data_migration_are_compat_only(): void
    {
        $email = $this->pluginSource('includes/compat/studiocart-compatibility-mode/functions/sc-email.php');
        $maps = $this->pluginSource('includes/compat/shortcode-tag-migration/maps.php');
        $helpers = $this->pluginSource('includes/compat/shortcode-tag-migration/helpers.php');
        $migrator = $this->pluginSource('includes/compat/shortcode-tag-migration/class-ppcartcomp-shortcode-tag-migrator.php');

        $this->assertStringContainsString("\$replacements['studiocart']", $email);
        $this->assertStringContainsString("'studiocart' => 'publishpress_cart'", $maps);
        $this->assertStringContainsString('ppcart_rewrite_leftover_email_tags', $helpers);
        $this->assertStringContainsString('ppcart_value_has_leftover_email_tags', $migrator);
        $this->assertStringContainsString('ppcart_email_tag_migration_like_patterns', $migrator);
        $this->assertStringContainsString("add_filter('ppcart_personalize_replacements'", $email);
    }

    private function pluginSource(string $relative): string
    {
        return (string) file_get_contents(PPCARTCOMP_PLUGIN_ROOT . $relative);
    }
}
