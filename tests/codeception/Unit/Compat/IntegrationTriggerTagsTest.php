<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class IntegrationTriggerTagsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-256
     */
    public function test_UT_256_hook_helpers_map_leftover_integration_trigger_tags(): void
    {
        foreach ($this->integrationTriggerMap() as $canonical => $leftover) {
            $this->assertSame(
                $canonical,
                ncs_cart_legacy_to_canonical_hook_name($leftover),
                $leftover
            );
            $this->assertContains(
                $leftover,
                ncs_cart_canonical_to_legacy_hook_names($canonical),
                $canonical
            );
        }
    }

    /**
     * @return array<string, string>
     */
    private function integrationTriggerMap(): array
    {
        return [
            'ppcart_order_pending'            => 'sc_order_pending',
            'ppcart_order_complete'           => 'sc_order_complete',
            'ppcart_order_marked_complete'    => 'sc_order_marked_complete',
            'ppcart_order_refunded'           => 'sc_order_refunded',
            'ppcart_subscription_completed'   => 'sc_subscription_completed',
            'ppcart_subscription_trialing'    => 'sc_subscription_trialing',
            'ppcart_subscription_active'      => 'sc_subscription_active',
            'ppcart_subscription_canceled'    => 'sc_subscription_canceled',
            'ppcart_subscription_paused'      => 'sc_subscription_paused',
            'ppcart_subscription_past_due'    => 'sc_subscription_past_due',
            'ppcart_renewal_payment'          => 'sc_renewal_payment',
            'ppcart_renewal_failed'           => 'sc_renewal_failed',
            'ppcart_renewal_uncollectible'    => 'sc_renewal_uncollectible',
        ];
    }
}
