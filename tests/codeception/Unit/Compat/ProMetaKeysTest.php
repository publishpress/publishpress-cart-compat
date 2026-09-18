<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class ProMetaKeysTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-311
     */
    public function test_UT_311_pro_meta_map_covers_leftover_keys_without_underscore(): void
    {
        $map = ncs_cart_get_pro_meta_key_map();

        $this->assertSame([ 'sc_custom_meta' ], $map['ppcart_custom_meta']);
        $this->assertSame([ 'sc_sq_cc_id' ], $map['ppcart_sq_cc_id']);
        $this->assertSame('ppcart_custom_meta', ncs_cart_legacy_to_canonical_pro_meta_key('sc_custom_meta'));
        $this->assertSame('ppcart_sq_cc_id', ncs_cart_legacy_to_canonical_pro_meta_key('sc_sq_cc_id'));
        $this->assertNull(ncs_cart_legacy_to_canonical_pro_meta_key('_sc_amount'));
        $this->assertSame([ 'sc_custom_meta', 'sc_sq_cc_id' ], ncs_cart_pro_meta_leftover_keys());
    }
}
