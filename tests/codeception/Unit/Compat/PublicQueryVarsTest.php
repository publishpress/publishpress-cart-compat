<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCartComp_Studiocart_Public_Query_Compatibility;
use Tests\Support\WordPressStubContext;
use UnitTester;

class PublicQueryVarsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        WordPressStubContext::clear();
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
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-198
     */
    public function test_UT_198_exposes_the_complete_legacy_to_canonical_query_map(): void
    {
        $this->assertSame(
            [
                'sc-preview'     => 'ppcart-preview',
                'sc-order'       => 'ppcart-order',
                'sc-plan'        => 'ppcart-plan',
                'sc-manage'      => 'ppcart-manage',
                'sc-download'    => 'ppcart-download',
                'sc-csv-export'  => 'ppcart-csv-export',
                'sc-api'         => 'ppcart-api',
                'sc-invoice'     => 'ppcart-invoice',
                'coupon'         => 'ppcart-coupon',
                'plan'           => 'ppcart-pay-plan',
            ],
            PPCartComp_Studiocart_Public_Query_Compatibility::legacy_query_var_map()
        );
        $this->assertSame(302, PPCartComp_Studiocart_Public_Query_Compatibility::REDIRECT_STATUS);
    }

    /**
     * @test-id UT-198
     */
    public function test_UT_198_registers_legacy_query_vars_only_when_compatibility_mode_is_on(): void
    {
        $canonical = [ 'ppcart-order' ];

        $this->assertSame(
            $canonical,
            PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_query_vars($canonical)
        );

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        $registered = PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_query_vars($canonical);

        $this->assertSame(
            [
                'ppcart-order',
                'sc-preview',
                'sc-order',
                'sc-plan',
                'sc-manage',
                'sc-download',
                'sc-csv-export',
                'sc-api',
                'sc-invoice',
                'coupon',
                'plan',
            ],
            $registered
        );
    }

    /**
     * @test-id UT-198
     */
    public function test_UT_198_maps_multiple_scalar_values_and_ignores_array_values(): void
    {
        $mapped = PPCartComp_Studiocart_Public_Query_Compatibility::map_legacy_query_args(
            [
                'sc-preview' => 'email',
                'sc-order'   => '0',
                'sc-plan'    => [ 'invalid' ],
                'unrelated'  => 'keep',
            ],
            [ 'sc-download' => 'download-key' ]
        );

        $this->assertSame(
            [
                'ppcart-preview'  => 'email',
                'ppcart-order'    => '0',
                'ppcart-download' => 'download-key',
            ],
            $mapped['canonical_args']
        );
        $this->assertSame(
            [ 'sc-preview', 'sc-order', 'sc-plan' ],
            $mapped['legacy_args']
        );
    }

    /**
     * @test-id UT-198
     */
    public function test_UT_198_does_not_remap_a_stale_download_var_after_canonicalization(): void
    {
        $mapped = PPCartComp_Studiocart_Public_Query_Compatibility::map_legacy_query_args(
            [ 'ppcart-download' => 'download-key' ],
            [ 'sc-download' => 'download-key' ]
        );

        $this->assertSame([], $mapped['canonical_args']);
        $this->assertSame([], $mapped['legacy_args']);
    }

    /**
     * @test-id UT-198
     */
    public function test_UT_198_canonical_values_take_precedence_over_legacy_values(): void
    {
        $mapped = PPCartComp_Studiocart_Public_Query_Compatibility::map_legacy_query_args(
            [
                'ppcart-order' => 'canonical-order',
                'sc-order'     => 'legacy-order',
                'sc-plan'      => '0',
            ]
        );

        $this->assertSame(
            [ 'ppcart-plan' => '0' ],
            $mapped['canonical_args']
        );
        $this->assertSame(
            [ 'sc-order', 'sc-plan' ],
            $mapped['legacy_args']
        );
    }

    /**
     * @test-id UT-198
     */
    public function test_UT_198_allows_only_safe_public_get_and_head_requests(): void
    {
        $this->assertTrue(
            PPCartComp_Studiocart_Public_Query_Compatibility::is_safe_public_request('GET', false, false, false)
        );
        $this->assertTrue(
            PPCartComp_Studiocart_Public_Query_Compatibility::is_safe_public_request('HEAD', false, false, false)
        );
        $this->assertFalse(
            PPCartComp_Studiocart_Public_Query_Compatibility::is_safe_public_request('POST', false, false, false)
        );
        $this->assertFalse(
            PPCartComp_Studiocart_Public_Query_Compatibility::is_safe_public_request('GET', true, false, false)
        );
        $this->assertFalse(
            PPCartComp_Studiocart_Public_Query_Compatibility::is_safe_public_request('GET', false, true, false)
        );
        $this->assertFalse(
            PPCartComp_Studiocart_Public_Query_Compatibility::is_safe_public_request('GET', false, false, true)
        );
    }

    /**
     * @test-id UT-260
     */
    public function test_UT_260_query_var_map_covers_leftover_csv_export(): void
    {
        $map = PPCartComp_Studiocart_Public_Query_Compatibility::legacy_query_var_map();

        $this->assertSame('ppcart-csv-export', $map['sc-csv-export']);
        $this->assertSame(302, PPCartComp_Studiocart_Public_Query_Compatibility::REDIRECT_STATUS);
    }

    /**
     * @test-id UT-274
     */
    public function test_UT_274_query_var_map_covers_leftover_webhook_and_invoice(): void
    {
        $map = PPCartComp_Studiocart_Public_Query_Compatibility::legacy_query_var_map();

        $this->assertSame('ppcart-api', $map['sc-api']);
        $this->assertSame('ppcart-invoice', $map['sc-invoice']);
        $this->assertSame(
            [
                'regex' => 'sc-webhook/?([^/]*)',
                'query' => 'index.php?ppcart-api=$matches[1]',
            ],
            PPCartComp_Studiocart_Public_Query_Compatibility::leftover_webhook_rewrite()
        );
        $this->assertSame(302, PPCartComp_Studiocart_Public_Query_Compatibility::REDIRECT_STATUS);
    }

    /**
     * @test-id UT-274
     */
    public function test_UT_274_leftover_webhook_rewrite_registers_only_when_compatibility_mode_is_on(): void
    {
        $calls = [];
        WordPressStubContext::set(
            'add_rewrite_rule',
            static function ($regex, $query, $after = 'bottom') use (&$calls) {
                $calls[] = [ $regex, $query, $after ];
            }
        );

        PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_webhook_rewrite();
        $this->assertSame([], $calls);

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_webhook_rewrite();
        $this->assertSame(
            [
                [ 'sc-webhook/?([^/]*)', 'index.php?ppcart-api=$matches[1]', 'top' ],
            ],
            $calls
        );
    }

    /**
     * @test-id UT-274
     */
    public function test_UT_274_copies_leftover_webhook_gateway_on_post_not_get(): void
    {
        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );
        WordPressStubContext::set('is_admin', static fn () => false);
        WordPressStubContext::set('wp_doing_ajax', static fn () => false);
        WordPressStubContext::set('wp_is_serving_rest_request', static fn () => false);

        $this->assertSame(
            'stripe',
            PPCartComp_Studiocart_Public_Query_Compatibility::resolve_legacy_webhook_gateway(
                [ 'sc-api' => 'stripe' ]
            )
        );
        $this->assertSame(
            'paypal',
            PPCartComp_Studiocart_Public_Query_Compatibility::resolve_legacy_webhook_gateway(
                [],
                '/sc-webhook/paypal?foo=1'
            )
        );

        $_SERVER['REQUEST_METHOD'] = 'GET';
        $this->assertSame(
            [ 'sc-api' => 'stripe' ],
            PPCartComp_Studiocart_Public_Query_Compatibility::copy_legacy_webhook_request(
                [ 'sc-api' => 'stripe' ]
            )
        );

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $copied = PPCartComp_Studiocart_Public_Query_Compatibility::copy_legacy_webhook_request(
            [ 'sc-api' => 'stripe' ]
        );
        $this->assertSame('stripe', $copied['ppcart-api']);
        $this->assertSame('stripe', $copied['sc-api']);
    }

    /**
     * @test-id UT-316
     */
    public function test_UT_316_maps_leftover_checkout_coupon_and_plan_without_colliding_with_account_plan(): void
    {
        $mapped = PPCartComp_Studiocart_Public_Query_Compatibility::map_legacy_query_args(
            [
                'coupon'  => '20off',
                'plan'    => 'pro-annual',
                'sc-plan' => '42',
            ]
        );

        $this->assertSame(
            [
                'ppcart-plan'     => '42',
                'ppcart-coupon'   => '20off',
                'ppcart-pay-plan' => 'pro-annual',
            ],
            $mapped['canonical_args']
        );
        $this->assertSame(
            [ 'sc-plan', 'coupon', 'plan' ],
            $mapped['legacy_args']
        );
        $this->assertTrue($mapped['should_redirect']);
    }

    /**
     * @test-id UT-316
     */
    public function test_UT_316_canonical_checkout_query_keys_win_over_leftover_coupon_and_plan(): void
    {
        $mapped = PPCartComp_Studiocart_Public_Query_Compatibility::map_legacy_query_args(
            [
                'ppcart-coupon'   => 'canonical-coupon',
                'coupon'          => 'legacy-coupon',
                'ppcart-pay-plan' => 'canonical-plan',
                'plan'            => 'legacy-plan',
            ]
        );

        $this->assertSame([], $mapped['canonical_args']);
        $this->assertSame([ 'coupon', 'plan' ], $mapped['legacy_args']);
        $this->assertTrue($mapped['should_redirect']);
    }
}
