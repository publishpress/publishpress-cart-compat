<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class AdminNoticeKeysTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-280
     */
    public function test_UT_280_request_field_map_covers_leftover_extension_notice(): void
    {
        $map = ncs_cart_get_legacy_request_field_map();

        $this->assertArrayNotHasKey('action', $map);
        $this->assertSame([ 'sc_extension_notice' ], $map['ppcart_extension_notice']);
    }
}
