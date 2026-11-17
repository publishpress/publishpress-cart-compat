<?php

namespace unit\ShortcodeTagMigration;

use Codeception\Test\Unit;
use UnitTester;

class ElementorWidgetReopenNoticeTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/shortcode-tag-migration/state.php';
    }

    /**
     * @test-id UT-320
     */
    public function test_UT_320_reopened_step_waits_for_the_merchant(): void
    {
        $state = array_merge(
            ppcart_shortcode_tag_migration_default_state(),
            [
                'step1'                      => 'idle',
                'mode'                       => 'legacy',
                'elementor_widgets_reopened' => true,
            ]
        );

        $this->assertTrue(ppcartcomp_shortcode_step_awaits_elementor_widgets($state));

        $state['step1'] = 'running';
        $this->assertTrue(ppcartcomp_shortcode_step_awaits_elementor_widgets($state));
    }

    /**
     * @test-id UT-320
     */
    public function test_UT_320_finished_rerun_stops_waiting(): void
    {
        $state = array_merge(
            ppcart_shortcode_tag_migration_default_state(),
            [
                'step1'                      => 'complete',
                'mode'                       => 'canonical',
                'elementor_widgets_reopened' => true,
            ]
        );

        $this->assertFalse(ppcartcomp_shortcode_step_awaits_elementor_widgets($state));
    }

    /**
     * @test-id UT-320
     */
    public function test_UT_320_step_that_was_never_reopened_does_not_wait(): void
    {
        $this->assertFalse(
            ppcartcomp_shortcode_step_awaits_elementor_widgets(ppcart_shortcode_tag_migration_default_state())
        );
        $this->assertFalse(ppcartcomp_shortcode_step_awaits_elementor_widgets([]));
    }
}
