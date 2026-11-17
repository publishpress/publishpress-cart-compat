<?php

namespace unit\ShortcodeTagMigration;

use Codeception\Test\Unit;
use UnitTester;

class ElementorWidgetRewriteTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/shortcode-tag-migration/class-ppcartcomp-shortcode-tag-elementor.php';
    }

    /**
     * @test-id UT-319
     */
    public function test_UT_319_leftover_order_form_widget_becomes_cart_widget(): void
    {
        $raw = wp_json_encode(
            [
                [
                    'id'       => 'a1',
                    'elType'   => 'container',
                    'settings' => [],
                    'elements' => [
                        [
                            'id'         => 'c1',
                            'elType'     => 'widget',
                            'widgetType' => 'sc-orderform',
                            'settings'   => [
                                'cf_id'           => '5',
                                'sc_template'     => '',
                                'wrapper_bgcolor' => '#ffe600',
                            ],
                            'elements'   => [],
                        ],
                    ],
                ],
            ]
        );

        $this->assertTrue(\PPCartComp_Shortcode_Tag_Elementor::has_leftover_widget($raw));

        $next = \PPCartComp_Shortcode_Tag_Elementor::rewrite_leftover_widgets($raw);
        $data = json_decode($next, true);
        $widget = $data[0]['elements'][0];

        $this->assertSame('ppcart-orderform', $widget['widgetType']);
        $this->assertSame('5', $widget['settings']['cf_id']);
        $this->assertSame('#ffe600', $widget['settings']['wrapper_bgcolor']);
        $this->assertArrayHasKey('ppcart_template', $widget['settings']);
        $this->assertFalse(\PPCartComp_Shortcode_Tag_Elementor::has_leftover_widget($next));
    }

    /**
     * @test-id UT-319
     */
    public function test_UT_319_rows_without_leftover_widgets_are_untouched(): void
    {
        // Byte-for-byte: rows without StudioCart widgets must not be re-encoded.
        $raw = '[{"id":"h1","elType":"widget","widgetType":"heading","settings":{"title":"Café \/ shop"},"elements":[]}]';

        $this->assertFalse(\PPCartComp_Shortcode_Tag_Elementor::has_leftover_widget($raw));
        $this->assertSame($raw, \PPCartComp_Shortcode_Tag_Elementor::rewrite_leftover_widgets($raw));
    }

    /**
     * @test-id UT-319
     */
    public function test_UT_319_invalid_json_is_left_alone(): void
    {
        $raw = '{"widgetType":"sc-orderform" broken';

        $this->assertSame($raw, \PPCartComp_Shortcode_Tag_Elementor::rewrite_leftover_widgets($raw));
    }

    /**
     * @test-id UT-319
     */
    public function test_UT_319_every_leftover_widget_name_is_detected(): void
    {
        foreach ([ 'sc-countdown', 'sc-orderform', 'bump-heading', 'bump-checkbox', 'bump-text', 'bump-image' ] as $legacy) {
            $raw = '[{"elType":"widget","widgetType":"' . $legacy . '","settings":[],"elements":[]}]';
            $this->assertTrue(\PPCartComp_Shortcode_Tag_Elementor::has_leftover_widget($raw), $legacy);
        }
    }
}
