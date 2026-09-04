<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class LogRequestFieldsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-254
     */
    public function test_UT_254_request_field_map_covers_log_get_keys_and_nonce_fields(): void
    {
        $map = ncs_cart_get_legacy_request_field_map();

        $this->assertArrayNotHasKey('action', $map);
        $this->assertSame([ 'sc_view_log' ], $map['ppcart_view_log']);
        $this->assertSame([ 'sc_download_log' ], $map['ppcart_download_log']);
        $this->assertSame([ 'sc_reset_log' ], $map['ppcart_reset_log']);
        $this->assertSame([ 'sc_view_stripe_webhook_log' ], $map['ppcart_view_stripe_webhook_log']);
        $this->assertSame([ 'sc_download_stripe_webhook_log' ], $map['ppcart_download_stripe_webhook_log']);
        $this->assertSame([ 'sc_clear_stripe_webhook_log' ], $map['ppcart_clear_stripe_webhook_log']);
        $this->assertSame([ 'sc_view_debug_log_nonce' ], $map['ppcart_view_debug_log_nonce']);
        $this->assertSame([ 'sc_download_debug_log_nonce' ], $map['ppcart_download_debug_log_nonce']);
        $this->assertSame([ 'sc_reset_debug_log_nonce' ], $map['ppcart_reset_debug_log_nonce']);
        $this->assertSame([ 'sc_view_stripe_webhook_log_nonce' ], $map['ppcart_view_stripe_webhook_log_nonce']);
        $this->assertSame([ 'sc_download_stripe_webhook_log_nonce' ], $map['ppcart_download_stripe_webhook_log_nonce']);
        $this->assertSame([ 'sc_clear_stripe_webhook_log_nonce' ], $map['ppcart_clear_stripe_webhook_log_nonce']);
        $this->assertSame([ 'sc_stripe_webhook_nonce' ], $map['ppcart_stripe_webhook_nonce']);
        $this->assertSame([ 'sc_stripe_webhook_log_cleared' ], $map['ppcart_stripe_webhook_log_cleared']);
    }
}
