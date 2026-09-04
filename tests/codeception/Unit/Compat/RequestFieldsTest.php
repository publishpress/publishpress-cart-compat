<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class RequestFieldsTest extends Unit
{
    /**
     * @var UnitTester
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

    protected function _before(): void
    {
        $this->originalPost = $_POST;
        $this->originalGet = $_GET;
        $this->originalRequest = $_REQUEST;

        $this->resetRequestFieldBridges();

        WordPressStubContext::setState('options', []);
        WordPressStubContext::setState('actions', []);

        WordPressStubContext::set(
            'get_option',
            static function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                return array_key_exists($option, $options) ? $options[ $option ] : $default;
            }
        );

        WordPressStubContext::set(
            'add_action',
            static function ($hook) {
                $actions = WordPressStubContext::getState('actions', []);
                $actions[] = $hook;
                WordPressStubContext::setState('actions', $actions);

                return true;
            }
        );

        WordPressStubContext::set(
            'remove_action',
            static function () {
                return true;
            }
        );
    }

    protected function _after(): void
    {
        $this->resetRequestFieldBridges();
        $_POST = $this->originalPost;
        $_GET = $this->originalGet;
        $_REQUEST = $this->originalRequest;
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-231
     */
    public function test_UT_231_request_field_map_covers_leftover_keys_and_skips_action(): void
    {
        $map = ncs_cart_get_legacy_request_field_map();

        $this->assertArrayNotHasKey('action', $map);
        $this->assertSame([ 'sc-nonce' ], $map['ppcart-nonce']);
        $this->assertSame([ 'sc_product_id' ], $map['ppcart_product_id']);
        $this->assertSame([ 'sc-orderbump' ], $map['ppcart-orderbump']);
        $this->assertSame([ 'sc_custom_fields' ], $map['ppcart_custom_fields']);
        $this->assertSame([ 'sc_product_duplicated' ], $map['ppcart_product_duplicated']);
        $this->assertSame([ 'sc_temp_order_id' ], $map['ppcart_temp_order_id']);

        foreach ($map as $canonical => $leftovers) {
            $this->assertNotSame('action', $canonical);
            $this->assertNotEmpty($leftovers);
            $this->assertNotContains('action', $leftovers);
        }
    }

    /**
     * @test-id UT-231
     */
    public function test_UT_231_copy_is_gated_canonical_wins_and_leaves_leftover_keys(): void
    {
        $_POST = [
            'sc-nonce' => 'legacy-nonce',
            'sc-orderbump' => [ 'main' => '1' ],
            'action' => 'sc_save_order_to_db',
        ];
        $_GET = [ 'sc_product_duplicated' => '41' ];
        $_REQUEST = $_POST + $_GET;

        ppcart_copy_legacy_request_fields();

        $this->assertArrayNotHasKey('ppcart-nonce', $_POST);
        $this->assertArrayNotHasKey('ppcart-orderbump', $_POST);
        $this->assertArrayNotHasKey('ppcart_product_duplicated', $_GET);
        $this->assertSame('sc_save_order_to_db', $_POST['action']);

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );
        $this->resetRequestFieldBridges();

        $_POST = [
            'sc-nonce' => 'legacy-nonce',
            'ppcart-nonce' => 'canonical-nonce',
            'sc-orderbump' => [ 'main' => '1' ],
            'action' => 'sc_save_order_to_db',
        ];
        $_GET = [ 'sc_product_duplicated' => '41' ];
        $_REQUEST = $_POST + $_GET;

        ppcart_copy_legacy_request_fields();

        $this->assertSame('canonical-nonce', $_POST['ppcart-nonce']);
        $this->assertSame('legacy-nonce', $_POST['sc-nonce']);
        $this->assertSame([ 'main' => '1' ], $_POST['ppcart-orderbump']);
        $this->assertSame([ 'main' => '1' ], $_POST['sc-orderbump']);
        $this->assertSame('41', $_GET['ppcart_product_duplicated']);
        $this->assertSame('41', $_GET['sc_product_duplicated']);
        $this->assertSame('sc_save_order_to_db', $_POST['action']);
        $this->assertArrayNotHasKey('ppcart_save_order_to_db', $_POST);
    }

    /**
     * @test-id UT-231
     */
    public function test_UT_231_filter_input_lookup_returns_leftovers_only_when_compatibility_mode_is_on(): void
    {
        $this->assertSame([], ppcart_legacy_request_field_lookup('ppcart-nonce'));

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        $this->assertSame([ 'sc-nonce' ], ppcart_legacy_request_field_lookup('ppcart-nonce'));
        $this->assertSame([ 'sc_custom_fields' ], ppcart_legacy_request_field_lookup('ppcart_custom_fields'));
        $this->assertSame([], ppcart_legacy_request_field_lookup('action'));
    }

    /**
     * @test-id UT-232
     */
    public function test_UT_232_registers_leftover_duplicate_admin_action_only_when_compatibility_mode_is_on(): void
    {
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_request_fields();

        $this->assertNotContains(
            'admin_action_sc_duplicate_product',
            WordPressStubContext::getState('actions', [])
        );

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );
        WordPressStubContext::setState('actions', []);

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_request_fields();

        $this->assertContains(
            'admin_action_sc_duplicate_product',
            WordPressStubContext::getState('actions', [])
        );
    }

    private function resetRequestFieldBridges(): void
    {
        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }
    }
}
