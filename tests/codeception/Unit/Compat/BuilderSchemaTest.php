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
}
