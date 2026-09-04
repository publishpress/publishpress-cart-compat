<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class ConstantsBridgeTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        $this->resetCompatibilityModeFlags();

        WordPressStubContext::set(
            'get_option',
            function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                if (array_key_exists($option, $options)) {
                    return $options[ $option ];
                }

                return $default;
            }
        );

        WordPressStubContext::set(
            'apply_filters',
            static function ($hook_name, $value) {
                return $value;
            }
        );

        WordPressStubContext::set(
            'add_filter',
            static function () {
                return true;
            }
        );

        WordPressStubContext::set(
            'add_action',
            static function () {
                return true;
            }
        );

        WordPressStubContext::setState('options', []);
    }

    protected function _after(): void
    {
        $this->resetCompatibilityModeFlags();
        WordPressStubContext::clear();
        parent::_after();
    }

    public function test_UT_190_constant_map_lists_canonical_to_legacy_pairs_and_is_not_a_class_map(): void
    {
        $map = ncs_cart_get_legacy_constant_map();

        $this->assertSame([ 'NCS_CART_VERSION' ], $map['PPCART_VERSION']);
        $this->assertSame([ 'NCS_CART_BASE_DIR' ], $map['PPCART_BASE_DIR']);
        $this->assertSame([ 'NCS_CART_BASE_URL' ], $map['PPCART_BASE_URL']);
        $this->assertSame([ 'NCS_CART_BASE_FILE' ], $map['PPCART_BASE_FILE']);
        $this->assertSame([ 'NCS_CART_FREE_LOADED' ], $map['PPCART_FREE_LOADED']);
        $this->assertSame([ 'NCS_STYLESHEETPATH' ], $map['PPCART_STYLESHEET_PATH']);
        $this->assertSame([ 'NCS_CART_LIB_VENDOR_PATH' ], $map['PPCART_LIB_VENDOR_PATH']);
        $this->assertSame([ 'NCS_CART_VENDOR_ASSETS_URL' ], $map['PPCART_VENDOR_ASSETS_URL']);
        $this->assertSame([ 'NCS_CART_ENCRYPT_SECRETS' ], $map['PPCART_ENCRYPT_SECRETS']);
        $this->assertSame([ 'NCS_CART_SECRETS_KEY' ], $map['PPCART_SECRETS_KEY']);
        $this->assertSame([ 'NCS_CART_DEBUG_LOG_DIR' ], $map['PPCART_DEBUG_LOG_DIR']);
        $this->assertSame([ 'NCS_CART_DEBUG_LOG_MAX_BYTES' ], $map['PPCART_DEBUG_LOG_MAX_BYTES']);
        $this->assertSame([ 'NCS_CART_STRIPE_WEBHOOK_LOG_DIR' ], $map['PPCART_STRIPE_WEBHOOK_LOG_DIR']);
        $this->assertSame([ 'NCS_CART_STRIPE_WEBHOOK_LOG_MAX_BYTES' ], $map['PPCART_STRIPE_WEBHOOK_LOG_MAX_BYTES']);
        $this->assertSame([ 'NCS_CART_PAYPAL_SSL_VERIFY' ], $map['PPCART_PAYPAL_SSL_VERIFY']);
        $this->assertSame([ 'SC_STRIPE_CONNECT_SERVER_URL' ], $map['PPCART_STRIPE_CONNECT_SERVER_URL']);
        $this->assertSame(
            [ 'SC_STRIPE_CONNECT_ALLOW_INSECURE_LOCAL_CREDENTIALS' ],
            $map['PPCART_STRIPE_CONNECT_ALLOW_INSECURE_LOCAL_CREDENTIALS']
        );
        $this->assertSame([ 'NCS_CART_LOADED_BY_PRO' ], $map['PPCART_LOADED_BY_PRO']);
        $this->assertSame([ 'NCS_CART_PRO_BASE_URL' ], $map['PPCART_PRO_BASE_URL']);
        $this->assertSame([ 'NCS_CART_PRO_LIB_VENDOR_DIR' ], $map['PPCART_PRO_LIB_VENDOR_DIR']);
        $this->assertSame([ 'NCS_CART_PRO_PLUGIN_NAME' ], $map['PPCART_PRO_PLUGIN_NAME']);

        $this->assertArrayNotHasKey('PPCART_MINIMUM_PHP_VERSION', $map);
        $this->assertArrayNotHasKey('PPCART_PLUGIN_ROOT', $map);
        $this->assertArrayNotHasKey('NCS_CART_LEGACY_COMPAT', $map);
        $this->assertArrayNotHasKey('PPCART_STUDIOCART_COMPATIBILITY_MODE', $map);

        $class_map = ncs_cart_get_legacy_class_alias_map();
        $this->assertArrayNotHasKey('PPCART_VERSION', $class_map);
        $this->assertArrayNotHasKey('NCS_CART_VERSION', $class_map);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_UT_190_inbound_copies_legacy_constant_when_compatibility_mode_is_on(): void
    {
        $this->enableCompatibilityMode();

        $this->assertFalse(defined('PPCART_PRO_BASE_URL'));
        define('NCS_CART_PRO_BASE_URL', 'https://example.test/pro/');

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_constants();

        $this->assertTrue(defined('PPCART_PRO_BASE_URL'));
        $this->assertSame('https://example.test/pro/', PPCART_PRO_BASE_URL);
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_constants_registered());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_UT_191_canonical_constant_wins_when_both_names_are_defined(): void
    {
        $this->enableCompatibilityMode();

        define('PPCART_PAYPAL_SSL_VERIFY', false);
        define('NCS_CART_PAYPAL_SSL_VERIFY', true);

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_constants();

        $this->assertFalse(PPCART_PAYPAL_SSL_VERIFY);
        $this->assertTrue(NCS_CART_PAYPAL_SSL_VERIFY);
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_UT_192_outbound_legacy_aliases_register_only_when_compatibility_mode_is_on(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_constants_registered());

        define('PPCART_STRIPE_CONNECT_ALLOW_INSECURE_LOCAL_CREDENTIALS', true);

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_constants();

        $this->assertFalse(defined('SC_STRIPE_CONNECT_ALLOW_INSECURE_LOCAL_CREDENTIALS'));
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_constants_registered());

        $this->enableCompatibilityMode();
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_constants();

        $this->assertTrue(defined('SC_STRIPE_CONNECT_ALLOW_INSECURE_LOCAL_CREDENTIALS'));
        $this->assertTrue(SC_STRIPE_CONNECT_ALLOW_INSECURE_LOCAL_CREDENTIALS);
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_constants_registered());
    }

    public function test_UT_193_developer_kill_switch_suppresses_outbound_legacy_constant_aliases(): void
    {
        WordPressStubContext::set(
            'apply_filters',
            static function ($hook_name, $value) {
                if ('ncs_cart_legacy_compat_enabled' === $hook_name) {
                    return false;
                }

                return $value;
            }
        );

        $this->enableCompatibilityMode();

        define('PPCART_STYLESHEET_PATH', '/tmp/ppcart-canonical-stylesheet');
        define('NCS_CART_STRIPE_WEBHOOK_LOG_DIR', '/tmp/ppcart-kill-switch-inbound');

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_constants();

        $this->assertFalse(defined('NCS_STYLESHEETPATH'));
        $this->assertTrue(defined('PPCART_STRIPE_WEBHOOK_LOG_DIR'));
        $this->assertSame('/tmp/ppcart-kill-switch-inbound', PPCART_STRIPE_WEBHOOK_LOG_DIR);
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_constants_registered());
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_UT_194_compatibility_mode_off_ignores_legacy_constants(): void
    {
        define('NCS_CART_LOADED_BY_PRO', true);
        define('PPCART_PRO_PLUGIN_NAME', 'Unit Test Pro');

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_constants();

        $this->assertFalse(defined('PPCART_LOADED_BY_PRO'));
        $this->assertFalse(defined('NCS_CART_PRO_PLUGIN_NAME'));
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_constants_registered());
    }

    /**
     * @return void
     */
    private function enableCompatibilityMode(): void
    {
        WordPressStubContext::setState(
            'options',
            [
                PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1',
            ]
        );
    }

    /**
     * @return void
     */
    private function resetCompatibilityModeFlags(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);
        $property = $reflection->getProperty('legacy_constants_registered');
        $property->setAccessible(true);
        $property->setValue(null, false);
    }
}
