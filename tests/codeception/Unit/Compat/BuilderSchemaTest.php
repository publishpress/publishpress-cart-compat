<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class BuilderSchemaTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-310
     */
    public function test_UT_310_elementor_widget_map_covers_leftover_names(): void
    {
        $map = ncs_cart_get_legacy_elementor_widget_map();

        $this->assertSame('sc-countdown', $map['ppcart-countdown']);
        $this->assertSame('sc-orderform', $map['ppcart-orderform']);
        $this->assertSame('bump-heading', $map['ppcart-bump-heading']);
        $this->assertSame('bump-checkbox', $map['ppcart-bump-checkbox']);
        $this->assertSame('bump-text', $map['ppcart-bump-text']);
        $this->assertSame('bump-image', $map['ppcart-bump-image']);
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_elementor_rewrite_helper_maps_widget_and_controls(): void
    {
        $rewritten = ppcartcomp_rewrite_elementor_elements(
            [
                [
                    'elType'     => 'widget',
                    'widgetType' => 'sc-countdown',
                    'settings'   => [
                        'sc_countdown_due_date' => '2026-01-01 00:00:00',
                        'sc_template'           => 'yes',
                    ],
                    'elements'   => [],
                ],
            ]
        );

        $this->assertSame('ppcart-countdown', $rewritten[0]['widgetType']);
        $this->assertSame('2026-01-01 00:00:00', $rewritten[0]['settings']['ppcart_countdown_due_date']);
        $this->assertSame('yes', $rewritten[0]['settings']['ppcart_template']);
        $this->assertArrayHasKey('sc_countdown_due_date', $rewritten[0]['settings']);
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_divi_rewrite_helper_maps_slug_and_fields(): void
    {
        $map = ncs_cart_get_legacy_divi_module_map();
        $this->assertSame('stof_studiocart_order_form', $map['ppcart_order_form']['legacy']);
        $this->assertSame('sc_hide_labels', $map['ppcart_order_form']['fields']['ppcart_hide_labels']);
        $this->assertSame('stof_input', $map['ppcart_order_form']['field_types']['ppcart_input']);

        $rewritten = ppcartcomp_rewrite_divi_module_shortcodes(
            '[stof_studiocart_order_form sc_hide_labels="yes" sc_template="2-step" sc_coupon="SAVE"][/stof_studiocart_order_form]'
        );

        $this->assertStringContainsString('[ppcart_order_form', $rewritten);
        $this->assertStringContainsString('ppcart_hide_labels="yes"', $rewritten);
        $this->assertStringContainsString('ppcart_template="2-step"', $rewritten);
        $this->assertStringContainsString('ppcart_coupon="SAVE"', $rewritten);
        $this->assertStringNotContainsString('stof_studiocart_order_form', $rewritten);
        $this->assertStringNotContainsString('sc_hide_labels=', $rewritten);
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_divi_remap_leftover_slug_copies_stored_attrs(): void
    {
        $module        = new \stdClass();
        $module->slug  = 'stof_studiocart_order_form';
        $module->props = [
            'ppcart_hide_labels' => 'yes',
            'sc_hide_labels'     => 'no',
        ];

        ppcartcomp_remap_divi_module_props($module, [ 'ppcart_hide_labels' => 'yes' ]);

        $this->assertSame('no', $module->props['ppcart_hide_labels']);
        $this->assertSame('no', $module->props['sc_hide_labels']);
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_divi_remap_keeps_merchant_canonical_edit(): void
    {
        $module        = new \stdClass();
        $module->slug  = 'stof_studiocart_order_form';
        $module->props = [
            'ppcart_hide_labels' => 'no',
            'sc_hide_labels'     => 'yes',
        ];

        ppcartcomp_remap_divi_module_props($module, [ 'ppcart_hide_labels' => 'yes' ]);

        $this->assertSame('no', $module->props['ppcart_hide_labels']);
        $this->assertSame('yes', $module->props['sc_hide_labels']);
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_overlay_prefers_leftover_over_canonical_default(): void
    {
        $this->assertSame('sc_countdown_due_date', ppcartcomp_legacy_elementor_setting_key('ppcart_countdown_due_date'));
        $this->assertSame('sc_template', ppcartcomp_legacy_elementor_setting_key('ppcart_template'));

        $overlaid = ppcartcomp_overlay_leftover_elementor_settings(
            [
                'ppcart_countdown_due_date'   => '2027-01-01 00:00:00',
                'sc_countdown_due_date'       => '2026-01-01 00:00:00',
                'ppcart_template'             => '',
                'sc_template'                 => 'yes',
                'ppcart_countdown_expire_type' => 'evergreen',
                'sc_countdown_expire_type'    => 'due_date',
            ],
            [
                'ppcart_countdown_due_date'    => '2027-01-01 00:00:00',
                'ppcart_template'              => '',
                'ppcart_countdown_expire_type' => 'due_date',
            ]
        );

        $this->assertSame('2026-01-01 00:00:00', $overlaid['ppcart_countdown_due_date']);
        $this->assertSame('yes', $overlaid['ppcart_template']);
        $this->assertSame('evergreen', $overlaid['ppcart_countdown_expire_type']);
        $this->assertSame('2026-01-01 00:00:00', $overlaid['sc_countdown_due_date']);
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_hidden_leftover_controls_carry_section(): void
    {
        $args = ppcartcomp_hidden_leftover_elementor_control_args(
            [
                'type'    => 'date_time',
                'section' => 'ppcart_countdown_section',
                'tab'     => 'content',
            ]
        );

        $this->assertSame('ppcart_countdown_section', $args['section']);
        $this->assertSame('content', $args['tab']);
        $this->assertArrayNotHasKey('type', $args);

        $skipped = ppcartcomp_hidden_leftover_elementor_control_args([ 'type' => 'hidden' ]);
        $this->assertSame([], $skipped);
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_legacy_setting_key_skips_section_controls(): void
    {
        $this->assertSame('', ppcartcomp_legacy_elementor_setting_key('ppcart_countdown_section'));
        $this->assertSame(
            '',
            ppcartcomp_legacy_elementor_setting_key(
                'ppcart_countdown_due_date',
                [ 'type' => 'section' ]
            )
        );
        $this->assertSame(
            'sc_countdown_due_date',
            ppcartcomp_legacy_elementor_setting_key(
                'ppcart_countdown_due_date',
                [ 'type' => 'date_time', 'section' => 'ppcart_countdown_section' ]
            )
        );
        $this->assertSame('', ppcartcomp_canonical_elementor_setting_key('sc_countdown_section'));
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_planned_hidden_controls_skip_sections_and_strip_when_stack_open(): void
    {
        $planned = ppcartcomp_planned_hidden_leftover_elementor_controls(
            [
                'ppcart_countdown_section' => [
                    'type' => 'section',
                ],
                'ppcart_countdown_due_date' => [
                    'type'    => 'date_time',
                    'section' => 'ppcart_countdown_section',
                    'tab'     => 'content',
                ],
            ]
        );

        $this->assertArrayHasKey('sc_countdown_due_date', $planned);
        $this->assertArrayNotHasKey('sc_countdown_section', $planned);
        $this->assertSame('ppcart_countdown_section', $planned['sc_countdown_due_date']['section']);

        $closed = new class () {
            public function get_current_section()
            {
                return null;
            }
        };
        $open = new class () {
            public function get_current_section()
            {
                return [ 'section' => 'section_coupon_button_style' ];
            }
        };

        $this->assertFalse(ppcartcomp_elementor_control_stack_is_open($closed));
        $this->assertTrue(ppcartcomp_elementor_control_stack_is_open($open));
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_divi_remap_string_zero_product_is_still_default(): void
    {
        $defaults = ppcartcomp_divi_canonical_field_defaults(
            [
                'ppcart_product'     => [ 'default' => 0 ],
                'ppcart_hide_labels' => [ 'default' => 'yes' ],
                'ppcart_template'    => [ 'default' => 'false' ],
                'ppcart_coupon'      => [ 'default' => '' ],
            ]
        );

        $this->assertSame(0, $defaults['ppcart_product']);

        $module        = new \stdClass();
        $module->slug  = 'stof_studiocart_order_form';
        $module->props = [
            'ppcart_product' => '0',
            'sc_product'     => '4242',
        ];

        ppcartcomp_remap_divi_module_props($module, $defaults);

        $this->assertSame('4242', $module->props['ppcart_product']);

        $edited        = new \stdClass();
        $edited->slug  = 'stof_studiocart_order_form';
        $edited->props = [
            'ppcart_product' => '99',
            'sc_product'     => '4242',
        ];

        ppcartcomp_remap_divi_module_props($edited, $defaults);

        $this->assertSame('99', $edited->props['ppcart_product']);
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_register_hidden_controls_passes_section_to_add_control(): void
    {
        $widget = new class () {
            public $added = [];
            public $section = null;

            public function get_controls()
            {
                return [
                    'ppcart_countdown_due_date' => [
                        'type'    => 'date_time',
                        'section' => 'ppcart_countdown_section',
                        'tab'     => 'content',
                    ],
                    'ppcart_template' => [
                        'type' => 'select',
                    ],
                    'sc_template' => [
                        'type' => 'hidden',
                    ],
                ];
            }

            public function add_control($id, $args)
            {
                $this->added[ $id ] = $args;
            }

            public function get_current_section()
            {
                return $this->section;
            }
        };

        ppcartcomp_add_hidden_leftover_elementor_controls($widget, 'hidden');

        $this->assertArrayHasKey('sc_countdown_due_date', $widget->added);
        $this->assertSame('hidden', $widget->added['sc_countdown_due_date']['type']);
        $this->assertSame('ppcart_countdown_section', $widget->added['sc_countdown_due_date']['section']);
        $this->assertArrayNotHasKey('sc_template', $widget->added);

        $widget->added   = [];
        $widget->section = [ 'section' => 'ppcart_countdown_section' ];
        ppcartcomp_add_hidden_leftover_elementor_controls($widget, 'hidden');
        $this->assertSame('hidden', $widget->added['sc_countdown_due_date']['type']);
        $this->assertArrayNotHasKey('section', $widget->added['sc_countdown_due_date']);
    }

    /**
     * @test-id UT-310
     */
    public function test_UT_310_overlay_copies_leftover_dimensions_and_slider_defaults(): void
    {
        $widget = new class () {
            public function get_controls()
            {
                return [
                    'ppcart_countdown_box_padding' => [
                        'type'       => 'dimensions',
                        'size_units' => [ '%', 'px', 'em' ],
                        'section'    => 'ppcart_countdown_box_style_section',
                    ],
                    'ppcart_countdown_column_width' => [
                        'type'    => 'slider',
                        'default' => [ 'size' => 100 ],
                        'section' => 'ppcart_countdown_box_style_section',
                    ],
                ];
            }
        };

        $defaults = ppcartcomp_elementor_control_defaults($widget);

        $this->assertSame('', $defaults['ppcart_countdown_box_padding']['top']);
        $this->assertSame('px', $defaults['ppcart_countdown_box_padding']['unit']);
        $this->assertSame(100, $defaults['ppcart_countdown_column_width']['size']);
        $this->assertSame('px', $defaults['ppcart_countdown_column_width']['unit']);

        $overlaid = ppcartcomp_overlay_leftover_elementor_settings(
            [
                'ppcart_countdown_box_padding' => [
                    'top'      => '',
                    'right'    => '',
                    'bottom'   => '',
                    'left'     => '',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'sc_countdown_box_padding' => [
                    'top'      => '10',
                    'right'    => '10',
                    'bottom'   => '10',
                    'left'     => '10',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'ppcart_countdown_column_width' => [
                    'size' => 100,
                    'unit' => 'px',
                ],
                'sc_countdown_column_width' => [
                    'size' => 80,
                    'unit' => 'px',
                ],
            ],
            $defaults
        );

        $this->assertSame('10', $overlaid['ppcart_countdown_box_padding']['top']);
        $this->assertSame(80, $overlaid['ppcart_countdown_column_width']['size']);

        $unit_only = ppcartcomp_overlay_leftover_elementor_settings(
            [
                'ppcart_countdown_column_width' => [
                    'size' => 100,
                    'unit' => 'em',
                ],
                'sc_countdown_column_width' => [
                    'size' => 80,
                    'unit' => 'px',
                ],
            ],
            $defaults
        );

        $this->assertSame(100, $unit_only['ppcart_countdown_column_width']['size']);
        $this->assertSame('em', $unit_only['ppcart_countdown_column_width']['unit']);

        $edited = ppcartcomp_overlay_leftover_elementor_settings(
            [
                'ppcart_countdown_box_padding' => [
                    'top'      => '4',
                    'right'    => '4',
                    'bottom'   => '4',
                    'left'     => '4',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'sc_countdown_box_padding' => [
                    'top'      => '10',
                    'right'    => '10',
                    'bottom'   => '10',
                    'left'     => '10',
                    'unit'     => 'px',
                    'isLinked' => true,
                ],
                'ppcart_countdown_column_width' => [
                    'size' => 60,
                    'unit' => 'px',
                ],
                'sc_countdown_column_width' => [
                    'size' => 80,
                    'unit' => 'px',
                ],
            ],
            $defaults
        );

        $this->assertSame('4', $edited['ppcart_countdown_box_padding']['top']);
        $this->assertSame(60, $edited['ppcart_countdown_column_width']['size']);

        $from_get_value = ppcartcomp_elementor_normalize_control_default(
            [
                'type'    => 'slider',
                'default' => [ 'size' => 100 ],
            ],
            [ 'size' => 100 ]
        );
        $this->assertSame('px', $from_get_value['unit']);
        $this->assertSame(100, $from_get_value['size']);
        $this->assertFalse(ppcartcomp_value_equals_default([ 'size' => 100, 'unit' => 'em' ], $from_get_value));
        $this->assertFalse(ppcartcomp_value_equals_default([ 'a' => 1 ], []));
    }
}
