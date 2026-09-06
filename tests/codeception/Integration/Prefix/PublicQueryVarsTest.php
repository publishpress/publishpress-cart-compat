<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCart_Files;
use PPCart_Public;
use PPCart_Public_Page_Controller;
use PPCartComp_Studiocart_Compatibility_Mode;
use PPCartComp_Studiocart_Public_Query_Compatibility;

class PublicQueryVarsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var \WP|null
     */
    private $originalWp;

    /**
     * @var \WP_Rewrite|null
     */
    private $originalWpRewrite;

    protected function setUp(): void
    {
        parent::setUp();

        global $wp, $wp_rewrite;

        $this->originalWp        = $wp;
        $this->originalWpRewrite = $wp_rewrite;

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
    }

    protected function tearDown(): void
    {
        global $wp, $wp_rewrite;

        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restores the isolated test global.
        $wp         = $this->originalWp;
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Restores the isolated test global.
        $wp_rewrite = $this->originalWpRewrite;

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-267
     */
    public function test_IT_267_core_registers_only_canonical_query_vars_and_download_rewrite(): void
    {
        $controller = (new \ReflectionClass(PPCart_Public_Page_Controller::class))
            ->newInstanceWithoutConstructor();
        $query_vars = $controller->query_vars([]);

        $this->assertSame(
            [
                'coupon',
                'plan',
                'ppcart-preview',
                'ppcart-order',
                'ppcart-plan',
                'ppcart-manage',
                'ppcart-download',
            ],
            $query_vars
        );

        foreach (array_keys(PPCartComp_Studiocart_Public_Query_Compatibility::legacy_query_var_map()) as $legacy) {
            $this->assertNotContains($legacy, $query_vars);
        }

        $this->useFreshRewriteEnvironment();

        $files = (new \ReflectionClass(PPCart_Files::class))->newInstanceWithoutConstructor();
        $files->download_page_rewrites();

        global $wp_rewrite;

        $regex = PPCart_Files::get_download_rewrite_regex();

        $this->assertSame(
            'index.php?ppcart-download=$matches[1]',
            $wp_rewrite->extra_rules_top[ $regex ]
        );
        $this->assertContains(PPCart_Files::DOWNLOAD_REWRITE_TAG, $wp_rewrite->rewritecode);
        $this->assertNotContains('%sc-download%', $wp_rewrite->rewritecode);
    }

    /**
     * @test-id IT-267
     */
    public function test_IT_267_compatibility_mode_gates_legacy_vars_without_changing_the_rewrite(): void
    {
        $canonical_query_vars = [ 'ppcart-order', 'ppcart-download' ];

        $this->assertSame(
            $canonical_query_vars,
            PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_query_vars($canonical_query_vars)
        );

        $this->useFreshRewriteEnvironment();

        $files = (new \ReflectionClass(PPCart_Files::class))->newInstanceWithoutConstructor();
        $files->download_page_rewrites();

        global $wp_rewrite;

        $regex = PPCart_Files::get_download_rewrite_regex();

        $this->assertSame(
            'index.php?ppcart-download=$matches[1]',
            $wp_rewrite->extra_rules_top[ $regex ]
        );
        $this->assertNotContains('%sc-download%', $wp_rewrite->rewritecode);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $registered = PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_query_vars(
            $canonical_query_vars
        );
        $files->download_page_rewrites();
        $legacy_query_vars = array_keys(
            PPCartComp_Studiocart_Public_Query_Compatibility::legacy_query_var_map()
        );

        foreach ($legacy_query_vars as $legacy_query_var) {
            $this->assertContains($legacy_query_var, $registered);
        }

        $this->assertSame(
            'index.php?ppcart-download=$matches[1]',
            $wp_rewrite->extra_rules_top[ $regex ]
        );
        $this->assertContains(PPCart_Files::DOWNLOAD_REWRITE_TAG, $wp_rewrite->rewritecode);
        $this->assertNotContains('%sc-download%', $wp_rewrite->rewritecode);
    }

    /**
     * @test-id IT-267
     */
    public function test_IT_267_redirect_maps_multiple_keys_and_preserves_unrelated_arguments(): void
    {
        $request_url = home_url(
            '/account/?sc-order=0&keep=value&sc-plan=42&sc-manage=stripe'
        );
        $redirect_url = PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
            $request_url,
            [
                'sc-order'  => '0',
                'keep'      => 'value',
                'sc-plan'   => '42',
                'sc-manage' => 'stripe',
            ]
        );

        $this->assertIsString($redirect_url);

        $query_args = [];
        wp_parse_str((string) wp_parse_url($redirect_url, PHP_URL_QUERY), $query_args);

        $this->assertSame('0', $query_args['ppcart-order']);
        $this->assertSame('42', $query_args['ppcart-plan']);
        $this->assertSame('stripe', $query_args['ppcart-manage']);
        $this->assertSame('value', $query_args['keep']);
        $this->assertArrayNotHasKey('sc-order', $query_args);
        $this->assertArrayNotHasKey('sc-plan', $query_args);
        $this->assertArrayNotHasKey('sc-manage', $query_args);
    }

    /**
     * @test-id IT-267
     */
    public function test_IT_267_canonical_values_win_when_both_query_keys_are_present(): void
    {
        $redirect_url = PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
            home_url('/account/?ppcart-order=canonical&sc-order=legacy&sc-plan=0&keep=value'),
            [
                'ppcart-order' => 'canonical',
                'sc-order'     => 'legacy',
                'sc-plan'      => '0',
                'keep'         => 'value',
            ]
        );
        $query_args = [];
        wp_parse_str((string) wp_parse_url($redirect_url, PHP_URL_QUERY), $query_args);

        $this->assertSame('canonical', $query_args['ppcart-order']);
        $this->assertSame('0', $query_args['ppcart-plan']);
        $this->assertSame('value', $query_args['keep']);
        $this->assertArrayNotHasKey('sc-order', $query_args);
        $this->assertArrayNotHasKey('sc-plan', $query_args);
    }

    /**
     * @test-id IT-267
     */
    public function test_IT_267_redirect_ignores_arrays_and_does_not_loop_on_canonical_urls(): void
    {
        $this->assertNull(
            PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
                home_url('/account/?sc-order%5B%5D=1'),
                [ 'sc-order' => [ '1' ] ]
            )
        );
        $this->assertNull(
            PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
                home_url('/account/?ppcart-order=1'),
                [ 'ppcart-order' => '1' ]
            )
        );

        $redirect_url = PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
            home_url('/account/?sc-order=1&sc-plan%5B%5D=2&keep=value'),
            [
                'sc-order' => '1',
                'sc-plan'  => [ '2' ],
                'keep'     => 'value',
            ]
        );
        $query_args = [];
        wp_parse_str((string) wp_parse_url($redirect_url, PHP_URL_QUERY), $query_args);

        $this->assertSame('1', $query_args['ppcart-order']);
        $this->assertSame('value', $query_args['keep']);
        $this->assertArrayNotHasKey('sc-plan', $query_args);
    }

    /**
     * @test-id IT-267
     */
    public function test_IT_267_canonical_pretty_download_stays_direct_and_stale_var_redirects(): void
    {
        $request_url = home_url('/file/download-key/');

        $this->assertNull(
            PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
                $request_url,
                [],
                [ 'ppcart-download' => 'download-key' ]
            )
        );

        $redirect_url = PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
            $request_url,
            [],
            [ 'sc-download' => 'download-key' ]
        );

        $this->assertSame(
            add_query_arg('ppcart-download', 'download-key', $request_url),
            $redirect_url
        );
        $this->assertNull(
            PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
                $redirect_url,
                [ 'ppcart-download' => 'download-key' ],
                [ 'sc-download' => 'download-key' ]
            )
        );

        $raw_redirect_url = PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
            home_url('/?sc-download=raw-key'),
            [ 'sc-download' => 'raw-key' ]
        );
        $this->assertSame(
            add_query_arg('ppcart-download', 'raw-key', home_url('/')),
            $raw_redirect_url
        );
    }

    /**
     * @test-id IT-267
     */
    public function test_IT_267_download_slug_option_hooks_use_the_live_canonical_option_name(): void
    {
        global $ppcart_files;

        $this->assertInstanceOf(PPCart_Files::class, $ppcart_files);
        $this->assertSame(
            10,
            has_action(
                'add_option_ppcart_download_slug',
                [ $ppcart_files, 'flush_permalinks' ]
            )
        );
        $this->assertSame(
            10,
            has_action(
                'update_option_ppcart_download_slug',
                [ $ppcart_files, 'flush_permalinks' ]
            )
        );
        $this->assertFalse(
            has_action(
                'add_option_sc_download_slug',
                [ $ppcart_files, 'flush_permalinks' ]
            )
        );
        $this->assertFalse(
            has_action(
                'update_option_sc_download_slug',
                [ $ppcart_files, 'flush_permalinks' ]
            )
        );
    }

    /**
     * @test-id IT-338
     */
    public function test_IT_338_leftover_csv_export_redirects_only_when_compatibility_mode_is_on(): void
    {
        $public = (new \ReflectionClass(PPCart_Public::class))->newInstanceWithoutConstructor();
        $query_vars = $public->api_query_vars([]);

        $this->assertContains('ppcart-csv-export', $query_vars);
        $this->assertNotContains('sc-csv-export', $query_vars);

        $this->assertSame(
            [ 'ppcart-csv-export' ],
            PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_query_vars(
                [ 'ppcart-csv-export' ]
            )
        );

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $registered = PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_query_vars(
            [ 'ppcart-csv-export' ]
        );
        $this->assertContains('sc-csv-export', $registered);
        $this->assertContains('ppcart-csv-export', $registered);

        $request_url = home_url('/?sc-csv-export=contacts&type=contact&_wpnonce=abc');
        $redirect_url = PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
            $request_url,
            [
                'sc-csv-export' => 'contacts',
                'type'          => 'contact',
                '_wpnonce'      => 'abc',
            ]
        );

        $this->assertIsString($redirect_url);

        $query_args = [];
        wp_parse_str((string) wp_parse_url($redirect_url, PHP_URL_QUERY), $query_args);

        $this->assertSame('contacts', $query_args['ppcart-csv-export']);
        $this->assertSame('contact', $query_args['type']);
        $this->assertSame('abc', $query_args['_wpnonce']);
        $this->assertArrayNotHasKey('sc-csv-export', $query_args);

        $this->assertNull(
            PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
                home_url('/?ppcart-csv-export=contacts'),
                [ 'ppcart-csv-export' => 'contacts' ]
            )
        );
    }

    /**
     * @test-id IT-344
     */
    public function test_IT_344_leftover_webhook_and_invoice_bridge_only_when_compatibility_mode_is_on(): void
    {
        $public = (new \ReflectionClass(PPCart_Public::class))->newInstanceWithoutConstructor();
        $query_vars = $public->api_query_vars([]);

        $this->assertContains('ppcart-api', $query_vars);
        $this->assertContains('ppcart-invoice', $query_vars);
        $this->assertNotContains('sc-api', $query_vars);
        $this->assertNotContains('sc-invoice', $query_vars);

        $this->assertSame(
            [ 'ppcart-api', 'ppcart-invoice' ],
            PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_query_vars(
                [ 'ppcart-api', 'ppcart-invoice' ]
            )
        );

        $this->useFreshRewriteEnvironment();
        PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_webhook_rewrite();

        global $wp_rewrite;
        $leftover = PPCartComp_Studiocart_Public_Query_Compatibility::leftover_webhook_rewrite();
        $this->assertArrayNotHasKey($leftover['regex'], $wp_rewrite->extra_rules_top);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');

        $registered = PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_query_vars(
            [ 'ppcart-api', 'ppcart-invoice' ]
        );
        $this->assertContains('sc-api', $registered);
        $this->assertContains('sc-invoice', $registered);

        $this->useFreshRewriteEnvironment();
        PPCartComp_Studiocart_Public_Query_Compatibility::register_legacy_webhook_rewrite();
        $this->assertSame(
            $leftover['query'],
            $wp_rewrite->extra_rules_top[ $leftover['regex'] ]
        );

        $request_url = home_url('/?sc-invoice=12&token=abc&dl=1');
        $redirect_url = PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
            $request_url,
            [
                'sc-invoice' => '12',
                'token'      => 'abc',
                'dl'         => '1',
            ]
        );
        $this->assertIsString($redirect_url);

        $query_args = [];
        wp_parse_str((string) wp_parse_url($redirect_url, PHP_URL_QUERY), $query_args);
        $this->assertSame('12', $query_args['ppcart-invoice']);
        $this->assertSame('abc', $query_args['token']);
        $this->assertSame('1', $query_args['dl']);
        $this->assertArrayNotHasKey('sc-invoice', $query_args);

        $this->assertFalse(
            PPCartComp_Studiocart_Public_Query_Compatibility::is_safe_public_request(
                'POST',
                false,
                false,
                false
            )
        );

        $_SERVER['REQUEST_METHOD'] = 'POST';
        $copied = PPCartComp_Studiocart_Public_Query_Compatibility::copy_legacy_webhook_request(
            [ 'sc-api' => 'stripe' ]
        );
        $this->assertSame('stripe', $copied['ppcart-api']);
        $this->assertNull(
            PPCartComp_Studiocart_Public_Query_Compatibility::get_legacy_redirect_url(
                home_url('/?ppcart-api=stripe'),
                [ 'ppcart-api' => 'stripe' ]
            )
        );
    }

    /**
     * Replace rewrite globals so rule assertions cannot leak across tests.
     *
     * @return void
     */
    private function useFreshRewriteEnvironment(): void
    {
        global $wp, $wp_rewrite;

        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Creates an isolated rewrite environment.
        $wp         = new \WP();
        // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited -- Creates an isolated rewrite environment.
        $wp_rewrite = new \WP_Rewrite();
    }
}
