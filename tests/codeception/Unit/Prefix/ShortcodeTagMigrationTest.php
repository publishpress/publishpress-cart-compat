<?php

namespace unit\Prefix;

use Codeception\Test\Unit;
use UnitTester;

class ShortcodeTagMigrationTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        if (! function_exists('ppcart_rewrite_leftover_shortcode_tags')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/shortcode-tag-migration/maps.php';
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/shortcode-tag-migration/helpers.php';
        }
    }

    /**
     * @test-id UT-206
     */
    public function test_UT_206_rewrite_replaces_leftover_openings_with_canonical_tags(): void
    {
        $rewritten = ppcart_rewrite_leftover_shortcode_tags(
            '[studiocart-form id="9"] and [studiocart-order-downloads]'
        );

        $this->assertSame('[ppcart_form id="9"] and [ppcart_order_downloads]', $rewritten);
        $this->assertSame(
            '[ppcart_form id="1"]',
            ppcart_rewrite_leftover_shortcode_tags('[ppcart_form id="1"]')
        );
    }

    /**
     * @test-id UT-206
     */
    public function test_UT_206_detection_finds_leftover_tags_and_ignores_canonical(): void
    {
        $this->assertTrue(ppcart_content_has_leftover_shortcode_tags('[studiocart-form id="1"]'));
        $this->assertFalse(ppcart_content_has_leftover_shortcode_tags('[ppcart_form id="1"]'));
        $this->assertTrue(
            ppcart_value_has_leftover_shortcode_tags(
                [ 'message' => 'See [studiocart-order-downloads]' ]
            )
        );
        $this->assertSame(
            [ 'message' => 'See [ppcart_order_downloads]' ],
            ppcart_rewrite_leftover_shortcode_tags_in_value(
                [ 'message' => 'See [studiocart-order-downloads]' ]
            )
        );
    }
}
