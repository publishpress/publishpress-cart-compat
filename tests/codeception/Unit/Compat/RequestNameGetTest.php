<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class RequestNameGetTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var array<string, mixed>
     */
    private $originalGet = [];

    protected function _before(): void
    {
        $this->originalGet = $_GET;

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

        $_GET = $this->originalGet;
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-297
     */
    public function test_UT_297_request_field_map_covers_leftover_slm_order(): void
    {
        $map = ncs_cart_get_legacy_request_field_map();

        $this->assertArrayNotHasKey('action', $map);
        $this->assertSame([ 'sc-slm-order' ], $map['ppcart-slm-order']);
    }

    /**
     * @test-id UT-297
     */
    public function test_UT_297_leftover_slm_order_copies_onto_canonical_key_when_compat_is_on(): void
    {
        $_GET = [ 'sc-slm-order' => '42' ];

        ppcart_copy_legacy_request_fields();

        $this->assertArrayNotHasKey('ppcart-slm-order', $_GET);

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }

        $_GET = [ 'sc-slm-order' => '42' ];

        ppcart_copy_legacy_request_fields();

        $this->assertSame('42', $_GET['ppcart-slm-order']);
        $this->assertSame('42', $_GET['sc-slm-order']);
    }
}
