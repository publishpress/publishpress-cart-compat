<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class AdminAjaxJsGlobalsTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var array<string, mixed>
     */
    private $originalPost = [];

    protected function _before(): void
    {
        $this->originalPost = $_POST;

        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }

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
        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }

        $_POST = $this->originalPost;
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-296
     */
    public function test_UT_296_request_field_map_covers_leftover_admin_ajax_keys(): void
    {
        $map = ncs_cart_get_legacy_request_field_map();

        $this->assertSame([ 'ncs_ajax_nonce' ], $map['ppcart_ajax_nonce']);
        $this->assertSame([ 'ncs_action' ], $map['ppcart_action']);
    }

    /**
     * @test-id UT-296
     */
    public function test_UT_296_leftover_admin_ajax_post_keys_copy_onto_canonical_keys_when_compat_is_on(): void
    {
        $_POST = [
            'ncs_ajax_nonce' => 'legacy-nonce',
            'ncs_action' => 'save_table_tax',
        ];

        ppcart_copy_legacy_request_fields();

        $this->assertArrayNotHasKey('ppcart_ajax_nonce', $_POST);
        $this->assertArrayNotHasKey('ppcart_action', $_POST);

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }

        $_POST = [
            'ncs_ajax_nonce' => 'legacy-nonce',
            'ncs_action' => 'save_table_tax',
        ];

        ppcart_copy_legacy_request_fields();

        $this->assertSame('legacy-nonce', $_POST['ppcart_ajax_nonce']);
        $this->assertSame('save_table_tax', $_POST['ppcart_action']);
        $this->assertSame('legacy-nonce', $_POST['ncs_ajax_nonce']);
        $this->assertSame('save_table_tax', $_POST['ncs_action']);
    }

    /**
     * @test-id UT-296
     */
    public function test_UT_296_js_globals_bridge_source_aliases_sc_tax_settings(): void
    {
        $source = (string) file_get_contents(
            dirname(__DIR__, 4) . '/includes/compat/studiocart-compatibility-mode/js/ppcart-compat-js-globals.js'
        );

        $this->assertStringContainsString('window.sc_tax_settings', $source);
        $this->assertStringContainsString('window.ppcart_tax_settings', $source);
    }
}
