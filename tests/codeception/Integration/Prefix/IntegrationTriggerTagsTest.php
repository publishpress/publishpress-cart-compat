<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class IntegrationTriggerTagsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->resetCompatibilityModeFlags();

        if (function_exists('ncs_cart_reset_hook_bridge_lazy_attach_state')) {
            ncs_cart_reset_hook_bridge_lazy_attach_state();
        }
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->resetCompatibilityModeFlags();

        if (function_exists('ncs_cart_reset_hook_bridge_lazy_attach_state')) {
            ncs_cart_reset_hook_bridge_lazy_attach_state();
        }

        parent::tearDown();
    }

    /**
     * @test-id IT-336
     */
    public function test_IT_336_leftover_integration_trigger_listeners_run_only_when_compatibility_mode_is_on(): void
    {
        foreach ($this->integrationTriggerMap() as $canonical => $leftover) {
            $legacy_ran = false;

            add_action(
                $leftover,
                static function () use (&$legacy_ran) {
                    $legacy_ran = true;
                },
                10,
                0
            );

            $this->fireCanonicalTrigger($canonical);

            $this->assertFalse($legacy_ran, $leftover . ' must not run with Compatibility Mode off');
        }

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_hooks();

        foreach ($this->integrationTriggerMap() as $canonical => $leftover) {
            $legacy_ran = false;

            add_action(
                $leftover,
                static function () use (&$legacy_ran) {
                    $legacy_ran = true;
                },
                10,
                0
            );

            $this->fireCanonicalTrigger($canonical);

            $this->assertTrue($legacy_ran, $leftover . ' must run with Compatibility Mode on');
        }
    }

    /**
     * Representative tags with no first-party required-arg listeners, plus
     * ppcart_order_complete with PPCart_Public silenced.
     */
    private function fireCanonicalTrigger(string $canonical): void
    {
        global $ppcart_public;

        $silenced = false;

        if (
            'ppcart_order_complete' === $canonical
            && is_object($ppcart_public)
        ) {
            $silenced = remove_action(
                $canonical,
                [ $ppcart_public, 'do_order_complete_functions' ],
                10
            );
        }

        try {
            do_action($canonical);
        } finally {
            if ($silenced && is_object($ppcart_public)) {
                add_action(
                    $canonical,
                    [ $ppcart_public, 'do_order_complete_functions' ],
                    10,
                    3
                );
            }
        }
    }

    /**
     * @return array<string, string>
     */
    private function integrationTriggerMap(): array
    {
        return [
            'ppcart_order_complete'        => 'sc_order_complete',
            'ppcart_order_marked_complete' => 'sc_order_marked_complete',
            'ppcart_subscription_trialing' => 'sc_subscription_trialing',
        ];
    }

    private function resetCompatibilityModeFlags(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);

        foreach (
            [
                'legacy_hooks_registered',
                'cron_hook_bridges_registered',
            ] as $property_name
        ) {
            $property = $reflection->getProperty($property_name);
            $property->setAccessible(true);
            $property->setValue(null, false);
        }
    }
}
