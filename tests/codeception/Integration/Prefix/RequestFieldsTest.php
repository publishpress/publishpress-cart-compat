<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class RequestFieldsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var array<string, mixed>
     */
    private $originalPost = [];

    /**
     * @var array<string, mixed>
     */
    private $originalGet = [];

    /**
     * @var array<string, mixed>
     */
    private $originalRequest = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalPost = $_POST;
        $this->originalGet = $_GET;
        $this->originalRequest = $_REQUEST;

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }
    }

    protected function tearDown(): void
    {
        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);

        $_POST = $this->originalPost;
        $_GET = $this->originalGet;
        $_REQUEST = $this->originalRequest;

        parent::tearDown();
    }

    /**
     * @test-id IT-317
     */
    public function test_IT_317_leftover_post_and_get_copy_onto_canonical_keys_only_when_compatibility_mode_is_on(): void
    {
        $_POST = [
            'sc-nonce' => 'legacy-nonce',
            'action' => 'sc_save_order_to_db',
        ];
        $_GET = [ 'sc_product_duplicated' => '41' ];
        $_REQUEST = array_merge($_GET, $_POST);

        ppcart_copy_legacy_request_fields();

        $this->assertArrayNotHasKey('ppcart-nonce', $_POST);
        $this->assertArrayNotHasKey('ppcart_product_duplicated', $_GET);
        $this->assertSame('sc_save_order_to_db', $_POST['action']);

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        ppcart_unregister_legacy_request_field_bridges();

        $_POST = [
            'sc-nonce' => 'legacy-nonce',
            'action' => 'sc_save_order_to_db',
        ];
        $_GET = [ 'sc_product_duplicated' => '41' ];
        $_REQUEST = array_merge($_GET, $_POST);

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_request_fields();

        $this->assertSame('legacy-nonce', $_POST['ppcart-nonce']);
        $this->assertSame('legacy-nonce', $_POST['sc-nonce']);
        $this->assertSame('41', $_GET['ppcart_product_duplicated']);
        $this->assertSame('41', $_GET['sc_product_duplicated']);
        $this->assertSame('sc_save_order_to_db', $_POST['action']);
        $this->assertArrayNotHasKey('ppcart_save_order_to_db', $_POST);
    }

    /**
     * @test-id IT-318
     */
    public function test_IT_318_leftover_duplicate_admin_action_registers_only_when_compatibility_mode_is_on(): void
    {
        $this->assertNotFalse(has_action('admin_action_ppcart_duplicate_product'));
        $this->assertFalse(has_action('admin_action_sc_duplicate_product'));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_request_fields();

        $this->assertNotFalse(has_action('admin_action_ppcart_duplicate_product'));
        $this->assertNotFalse(has_action('admin_action_sc_duplicate_product'));
    }
}
