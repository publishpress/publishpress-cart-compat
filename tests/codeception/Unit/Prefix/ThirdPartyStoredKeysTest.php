<?php

namespace unit\Prefix;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class ThirdPartyStoredKeysTest extends Unit
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
     * @test-id UT-315
     */
    public function test_UT_315_maps_cover_leftover_third_party_keys(): void
    {
        $this->assertSame(
            [ 'sc-txn-' ],
            ncs_cart_get_legacy_memberpress_trans_num_names('ppcart-txn-')
        );
        $this->assertSame(
            [ 'sc_submission_id' ],
            ncs_cart_canonical_to_legacy_affiliate_meta_keys('ppcart_submission_id')
        );
        $this->assertSame(
            'ppcart_submission_id',
            ncs_cart_legacy_to_canonical_affiliate_meta_key('sc_submission_id')
        );
    }

    /**
     * @test-id UT-315
     */
    public function test_UT_315_memberpress_candidates_include_leftover_only_when_lookup_is_registered(): void
    {
        $this->assertSame('ppcart-txn-41', ppcart_pro_memberpress_trans_num(41));
        $this->assertSame(
            [ 'ppcart-txn-41' ],
            ppcart_pro_memberpress_trans_num_candidates(41)
        );
        $this->assertTrue(ppcart_pro_memberpress_trans_num_is_synthetic('ppcart-txn-41'));
        $this->assertFalse(ppcart_pro_memberpress_trans_num_is_synthetic('sc-txn-41'));

        ppcart_pro_third_party_key_compatibility_lookup(
            'memberpress_trans_num',
            static function ($canonical_key) {
                return 'ppcart-txn-' === $canonical_key ? [ 'sc-txn-' ] : [];
            },
            true
        );

        $this->assertSame(
            [ 'ppcart-txn-41', 'sc-txn-41' ],
            ppcart_pro_memberpress_trans_num_candidates(41)
        );
        $this->assertTrue(ppcart_pro_memberpress_trans_num_is_synthetic('sc-txn-41'));
    }

    /**
     * @test-id UT-315
     */
    public function test_UT_315_compat_register_reads_leftover_when_mode_is_on(): void
    {
        $this->assertSame(
            [ 'ppcart-txn-7' ],
            ppcart_pro_memberpress_trans_num_candidates(7)
        );

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_third_party_keys();

        $this->assertSame(
            [ 'ppcart-txn-7', 'sc-txn-7' ],
            ppcart_pro_memberpress_trans_num_candidates(7)
        );
    }

    /**
     * @return void
     */
    private function loadProHelper(): void
    {
        if (function_exists('ppcart_pro_memberpress_trans_num')) {
            return;
        }

        $helper = dirname(PPCARTCOMP_PLUGIN_ROOT) . '/publishpress-cart-pro/includes/helpers/ppcart-pro-third-party-keys.php';
        $this->assertFileExists($helper);
        require_once $helper;
    }

    /**
     * @return void
     */
    private function unregisterLookups(): void
    {
        if (function_exists('ppcart_unregister_legacy_memberpress_trans_num_bridges')) {
            ppcart_unregister_legacy_memberpress_trans_num_bridges();
        }
    }
}
