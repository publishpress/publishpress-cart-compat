<?php

namespace unit\Prefix;

use Codeception\Test\Unit;
use UnitTester;

class StripeMetadataTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        if (function_exists('ppcart_unregister_legacy_stripe_metadata_bridges')) {
            ppcart_unregister_legacy_stripe_metadata_bridges();
        }
    }

    protected function _after(): void
    {
        if (function_exists('ppcart_unregister_legacy_stripe_metadata_bridges')) {
            ppcart_unregister_legacy_stripe_metadata_bridges();
        }

        parent::_after();
    }

    /**
     * @test-id UT-235
     */
    public function test_UT_235_helper_reads_leftover_only_when_lookup_is_registered(): void
    {
        $leftoverOnly = (object) [
            'metadata' => (object) [
                'sc_order_id' => 41,
            ],
        ];
        $canonicalWins = (object) [
            'metadata' => (object) [
                'ppcart_order_id' => 5,
                'sc_order_id' => 9,
            ],
        ];
        $emptyCanonical = (object) [
            'metadata' => (object) [
                'ppcart_order_id' => 0,
                'sc_order_id' => 12,
            ],
        ];

        $this->assertSame(null, ppcart_stripe_metadata($leftoverOnly, 'ppcart_order_id'));
        $this->assertSame(5, ppcart_stripe_metadata($canonicalWins, 'ppcart_order_id'));

        ppcart_stripe_metadata_compatibility_lookup(
            static function ($canonical_key) {
                return 'ppcart_order_id' === $canonical_key ? [ 'sc_order_id' ] : [];
            },
            true
        );

        $this->assertSame(41, ppcart_stripe_metadata($leftoverOnly, 'ppcart_order_id'));
        $this->assertSame(5, ppcart_stripe_metadata($canonicalWins, 'ppcart_order_id'));
        $this->assertSame(12, ppcart_stripe_metadata($emptyCanonical, 'ppcart_order_id'));
    }
}
