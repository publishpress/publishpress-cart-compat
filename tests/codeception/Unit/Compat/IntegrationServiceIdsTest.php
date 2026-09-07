<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class IntegrationServiceIdsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-283
     */
    public function test_UT_283_integration_service_id_map_covers_leftover_services_values(): void
    {
        $map = ncs_cart_get_legacy_integration_service_id_map();

        $this->assertSame([ 'sc_subscription' ], $map['ppcart_subscription']);
        $this->assertSame([ 'sc_refund_order' ], $map['ppcart_refund_order']);
        $this->assertSame([ 'sc_wpdomainchecker' ], $map['ppcart_wpdomainchecker']);

        $input = [
            [
                'services' => 'sc_subscription',
                'sc_sub_prod_id' => '10',
            ],
            [
                'services' => 'sc_refund_order',
            ],
            [
                'services' => 'sc_wpdomainchecker',
            ],
            [
                'services' => 'ppcart_subscription',
            ],
            [
                'services' => 'mailchimp',
            ],
        ];

        $copied = ppcart_copy_legacy_integration_repeater_rows($input);

        $this->assertSame('ppcart_subscription', $copied[0]['services']);
        $this->assertSame('10', $copied[0]['sc_sub_prod_id']);
        $this->assertSame('ppcart_refund_order', $copied[1]['services']);
        $this->assertSame('ppcart_wpdomainchecker', $copied[2]['services']);
        $this->assertSame('ppcart_subscription', $copied[3]['services']);
        $this->assertSame('mailchimp', $copied[4]['services']);
        $this->assertSame('sc_subscription', $input[0]['services']);
        $this->assertSame('sc_refund_order', $input[1]['services']);

        $this->assertSame(
            'ppcart_ppcart_subscription_integrations',
            ncs_cart_legacy_to_canonical_hook_name('ppcart_sc_subscription_integrations')
        );
        $this->assertContains(
            'ppcart_sc_subscription_integrations',
            ncs_cart_canonical_to_legacy_hook_names('ppcart_ppcart_subscription_integrations')
        );
        $this->assertSame(
            'ppcart_ppcart_refund_order_integrations',
            ncs_cart_legacy_to_canonical_hook_name('ppcart_sc_refund_order_integrations')
        );
        $this->assertSame(
            'ppcart_ppcart_wpdomainchecker_integrations',
            ncs_cart_legacy_to_canonical_hook_name('ppcart_sc_wpdomainchecker_integrations')
        );
    }
}
