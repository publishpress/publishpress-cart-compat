<?php

namespace unit\Prefix;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class GatewayMetadataTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        $this->loadProHelper();
        $this->unregisterLookups();

        WordPressStubContext::setState('options', []);
        WordPressStubContext::set(
            'get_option',
            static function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                return array_key_exists($option, $options) ? $options[ $option ] : $default;
            }
        );
    }

    protected function _after(): void
    {
        $this->unregisterLookups();
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-314
     */
    public function test_UT_314_maps_cover_leftover_processor_keys(): void
    {
        $this->assertSame(
            [ 'sc_subscription_id' ],
            ncs_cart_get_legacy_mollie_metadata_names('ppcart_subscription_id')
        );
        $this->assertSame(
            [ 'sc_order_id' ],
            ncs_cart_get_legacy_mollie_metadata_names('ppcart_order_id')
        );
        $this->assertSame(
            [ 'sc_subscription_id' ],
            ncs_cart_get_legacy_razorpay_notes_names('ppcart_subscription_id')
        );
        $this->assertSame(
            [ 'sc_order_id', 'order_id' ],
            ncs_cart_get_legacy_razorpay_notes_names('ppcart_order_id')
        );
        $this->assertSame(
            [ '#sc_plan_' ],
            ncs_cart_get_legacy_square_catalog_names('ppcart-plan-')
        );
        $this->assertSame(
            [ 'sc_tax_id' ],
            ncs_cart_get_legacy_stripe_metadata_names('ppcart_tax_id')
        );
    }

    /**
     * @test-id UT-314
     */
    public function test_UT_314_mollie_helper_reads_leftover_only_when_lookup_is_registered(): void
    {
        $leftoverOnly = (object) [
            'metadata' => (object) [
                'sc_subscription_id' => 41,
            ],
        ];
        $canonicalWins = (object) [
            'metadata' => (object) [
                'ppcart_subscription_id' => 5,
                'sc_subscription_id' => 9,
            ],
        ];
        $emptyCanonical = (object) [
            'metadata' => (object) [
                'ppcart_subscription_id' => 0,
                'sc_subscription_id' => 12,
            ],
        ];

        $this->assertSame(null, ppcart_pro_mollie_metadata($leftoverOnly, 'ppcart_subscription_id'));
        $this->assertSame(5, ppcart_pro_mollie_metadata($canonicalWins, 'ppcart_subscription_id'));

        ppcart_pro_gateway_metadata_compatibility_lookup(
            'mollie',
            static function ($canonical_key) {
                return 'ppcart_subscription_id' === $canonical_key ? [ 'sc_subscription_id' ] : [];
            },
            true
        );

        $this->assertSame(41, ppcart_pro_mollie_metadata($leftoverOnly, 'ppcart_subscription_id'));
        $this->assertSame(5, ppcart_pro_mollie_metadata($canonicalWins, 'ppcart_subscription_id'));
        $this->assertSame(12, ppcart_pro_mollie_metadata($emptyCanonical, 'ppcart_subscription_id'));
    }

    /**
     * @test-id UT-314
     */
    public function test_UT_314_razorpay_helper_reads_leftover_only_when_lookup_is_registered(): void
    {
        $leftoverOnly = (object) [
            'sc_subscription_id' => 41,
            'order_id' => 7,
        ];
        $canonicalWins = (object) [
            'ppcart_subscription_id' => 5,
            'sc_subscription_id' => 9,
            'ppcart_order_id' => 3,
            'order_id' => 8,
        ];

        $this->assertSame(null, ppcart_pro_razorpay_notes($leftoverOnly, 'ppcart_subscription_id'));
        $this->assertSame(null, ppcart_pro_razorpay_notes($leftoverOnly, 'ppcart_order_id'));
        $this->assertSame(5, ppcart_pro_razorpay_notes($canonicalWins, 'ppcart_subscription_id'));

        ppcart_pro_gateway_metadata_compatibility_lookup(
            'razorpay',
            static function ($canonical_key) {
                if ('ppcart_subscription_id' === $canonical_key) {
                    return [ 'sc_subscription_id' ];
                }
                if ('ppcart_order_id' === $canonical_key) {
                    return [ 'sc_order_id', 'order_id' ];
                }

                return [];
            },
            true
        );

        $this->assertSame(41, ppcart_pro_razorpay_notes($leftoverOnly, 'ppcart_subscription_id'));
        $this->assertSame(7, ppcart_pro_razorpay_notes($leftoverOnly, 'ppcart_order_id'));
        $this->assertSame(5, ppcart_pro_razorpay_notes($canonicalWins, 'ppcart_subscription_id'));
        $this->assertSame(3, ppcart_pro_razorpay_notes($canonicalWins, 'ppcart_order_id'));
    }

    /**
     * @test-id UT-314
     */
    public function test_UT_314_square_candidates_include_leftover_prefix_when_lookup_is_registered(): void
    {
        $this->assertSame(
            [ '#ppcart-plan-plan1' . 99 ],
            ppcart_pro_square_plan_object_id_candidates('plan1', 99)
        );

        ppcart_pro_gateway_metadata_compatibility_lookup(
            'square',
            static function ($canonical_key) {
                return 'ppcart-plan-' === $canonical_key ? [ '#sc_plan_' ] : [];
            },
            true
        );

        $this->assertSame(
            [ '#ppcart-plan-plan1' . 99, '#sc_plan_plan1' . 99 ],
            ppcart_pro_square_plan_object_id_candidates('plan1', 99)
        );
        $this->assertSame('#ppcart-plan-plan1' . 99, ppcart_pro_square_plan_object_id('plan1', 99));
    }

    /**
     * @test-id UT-314
     */
    public function test_UT_314_stripe_tax_helper_reads_leftover_only_when_lookup_is_registered(): void
    {
        $leftoverOnly = (object) [
            'metadata' => (object) [
                'sc_tax_id' => 41,
            ],
        ];
        $canonicalWins = (object) [
            'metadata' => (object) [
                'ppcart_tax_id' => 5,
                'sc_tax_id' => 9,
            ],
        ];

        $this->assertSame(null, ppcart_stripe_metadata($leftoverOnly, 'ppcart_tax_id'));
        $this->assertSame(5, ppcart_stripe_metadata($canonicalWins, 'ppcart_tax_id'));

        ppcart_stripe_metadata_compatibility_lookup(
            static function ($canonical_key) {
                return 'ppcart_tax_id' === $canonical_key ? [ 'sc_tax_id' ] : [];
            },
            true
        );

        $this->assertSame(41, ppcart_stripe_metadata($leftoverOnly, 'ppcart_tax_id'));
        $this->assertSame(5, ppcart_stripe_metadata($canonicalWins, 'ppcart_tax_id'));
    }

    /**
     * @test-id UT-314
     */
    public function test_UT_314_compat_register_reads_leftover_when_mode_is_on(): void
    {
        $payment = (object) [
            'metadata' => (object) [
                'sc_subscription_id' => 41,
                'sc_order_id' => 88,
            ],
        ];
        $notes = (object) [
            'sc_subscription_id' => 22,
            'order_id' => 7,
        ];

        ppcart_register_legacy_mollie_metadata_bridges();
        ppcart_register_legacy_razorpay_notes_bridges();
        ppcart_register_legacy_square_catalog_bridges();

        $this->assertSame(null, ppcart_pro_mollie_metadata($payment, 'ppcart_subscription_id'));
        $this->assertSame(null, ppcart_pro_mollie_metadata($payment, 'ppcart_order_id'));
        $this->assertSame(null, ppcart_pro_razorpay_notes($notes, 'ppcart_subscription_id'));
        $this->assertSame(
            [ '#ppcart-plan-a1' ],
            ppcart_pro_square_plan_object_id_candidates('a', 1)
        );

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        $this->assertSame(41, ppcart_pro_mollie_metadata($payment, 'ppcart_subscription_id'));
        $this->assertSame(88, ppcart_pro_mollie_metadata($payment, 'ppcart_order_id'));
        $this->assertSame(22, ppcart_pro_razorpay_notes($notes, 'ppcart_subscription_id'));
        $this->assertSame(7, ppcart_pro_razorpay_notes($notes, 'ppcart_order_id'));
        $this->assertSame(
            [ '#ppcart-plan-a1', '#sc_plan_a1' ],
            ppcart_pro_square_plan_object_id_candidates('a', 1)
        );
    }

    /**
     * @return void
     */
    private function loadProHelper(): void
    {
        if (function_exists('ppcart_pro_mollie_metadata')) {
            return;
        }

        $helper = dirname(PPCARTCOMP_PLUGIN_ROOT) . '/publishpress-cart-pro/includes/helpers/ppcart-pro-gateway-metadata.php';
        $this->assertFileExists($helper);
        require_once $helper;
    }

    /**
     * @return void
     */
    private function unregisterLookups(): void
    {
        if (function_exists('ppcart_unregister_legacy_mollie_metadata_bridges')) {
            ppcart_unregister_legacy_mollie_metadata_bridges();
        }
        if (function_exists('ppcart_unregister_legacy_razorpay_notes_bridges')) {
            ppcart_unregister_legacy_razorpay_notes_bridges();
        }
        if (function_exists('ppcart_unregister_legacy_square_catalog_bridges')) {
            ppcart_unregister_legacy_square_catalog_bridges();
        }
        if (function_exists('ppcart_unregister_legacy_stripe_metadata_bridges')) {
            ppcart_unregister_legacy_stripe_metadata_bridges();
        }
    }
}
