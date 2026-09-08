<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class RevokeGetKeysTest extends Unit
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
     * @test-id UT-306
     */
    public function test_UT_306_request_field_map_covers_leftover_revoke_get_keys(): void
    {
        $map = ncs_cart_get_legacy_request_field_map();

        $this->assertArrayNotHasKey('action', $map);
        $this->assertSame([ 'sc-revoke' ], $map['ppcart-revoke']);
        $this->assertSame([ 'sc-revoked' ], $map['ppcart-revoked']);
    }

    /**
     * @test-id UT-306
     */
    public function test_UT_306_leftover_revoke_get_keys_copy_onto_canonical_when_compat_is_on(): void
    {
        $_GET = [
            'sc-revoke' => '12',
            'sc-revoked' => 'license.pdf',
        ];

        ppcart_copy_legacy_request_fields();

        $this->assertArrayNotHasKey('ppcart-revoke', $_GET);
        $this->assertArrayNotHasKey('ppcart-revoked', $_GET);

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        if (function_exists('ppcart_unregister_legacy_request_field_bridges')) {
            ppcart_unregister_legacy_request_field_bridges();
        }

        $_GET = [
            'sc-revoke' => '12',
            'sc-revoked' => 'license.pdf',
        ];

        ppcart_copy_legacy_request_fields();

        $this->assertSame('12', $_GET['ppcart-revoke']);
        $this->assertSame('license.pdf', $_GET['ppcart-revoked']);
        $this->assertSame('12', $_GET['sc-revoke']);
        $this->assertSame('license.pdf', $_GET['sc-revoked']);
    }
}
