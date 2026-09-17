<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class RestNamespacesTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    private const CANONICAL_WEBHOOK = '/publishpress-cart/v1/webhook/zap-validate';
    private const LEFTOVER_WEBHOOK  = '/sc/v1/webhook/zap-validate';
    private const GUTENBERG_CANONICAL = '/publishpress-cart/v1/checkout-block/products';
    private const GUTENBERG_LEFTOVER  = '/sc/v1/checkout-block/products';

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_rest_namespaces')) {
            ppcart_unregister_legacy_rest_namespaces();
        }

        if (! did_action('init')) {
            do_action('init');
        }

        if (! did_action('rest_api_init')) {
            do_action('rest_api_init');
        }

        $this->ensureCanonicalWebhookRoute();
    }

    protected function tearDown(): void
    {
        if (function_exists('ppcart_unregister_legacy_rest_namespaces')) {
            ppcart_unregister_legacy_rest_namespaces();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        parent::tearDown();
    }

    /**
     * @test-id IT-362
     */
    public function test_IT_362_compat_off_does_not_register_leftover_sc_v1(): void
    {
        $routes = rest_get_server()->get_routes();

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertArrayHasKey(self::CANONICAL_WEBHOOK, $routes);
        $this->assertArrayNotHasKey(self::LEFTOVER_WEBHOOK, $routes);
        $this->assertArrayNotHasKey(self::GUTENBERG_LEFTOVER, $routes);
    }

    /**
     * @test-id IT-362
     */
    public function test_IT_362_compat_on_dual_registers_pro_rest_not_gutenberg(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_rest_namespaces();

        $routes = rest_get_server()->get_routes();

        $this->assertArrayHasKey(self::CANONICAL_WEBHOOK, $routes);
        $this->assertArrayHasKey(self::LEFTOVER_WEBHOOK, $routes);
        $this->assertSame(
            $routes[self::CANONICAL_WEBHOOK][0]['callback'] ?? null,
            $routes[self::LEFTOVER_WEBHOOK][0]['callback'] ?? null
        );
        $this->assertArrayHasKey(self::GUTENBERG_CANONICAL, $routes);
        $this->assertArrayNotHasKey(self::GUTENBERG_LEFTOVER, $routes);

        $request  = new \WP_REST_Request('POST', self::LEFTOVER_WEBHOOK);
        $response = rest_do_request($request);
        $this->assertNotSame(
            404,
            (int) $response->get_status(),
            'leftover sc/v1 webhook must dispatch, not 404'
        );
    }

    /**
     * @return void
     */
    private function ensureCanonicalWebhookRoute(): void
    {
        $routes = rest_get_server()->get_routes();

        if (isset($routes[self::CANONICAL_WEBHOOK])) {
            return;
        }

        register_rest_route(
            'publishpress-cart/v1',
            '/webhook/zap-validate',
            [
                [
                    'methods'             => 'POST',
                    'callback'            => static function () {
                        return [ 'ok' => true ];
                    },
                    'permission_callback' => '__return_true',
                ],
            ]
        );
    }
}
