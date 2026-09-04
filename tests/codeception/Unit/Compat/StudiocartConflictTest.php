<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Conflict;
use Tests\Support\WordPressStubContext;
use UnitTester;

class StudiocartConflictTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        WordPressStubContext::clear();
        WordPressStubContext::set(
            'get_option',
            static function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                return array_key_exists($option, $options) ? $options[$option] : $default;
            }
        );

        if (! class_exists('PPCartComp_Studiocart_Conflict', false)) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/class-ppcartcomp-studiocart-conflict.php';
        }
    }

    protected function _after(): void
    {
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-244
     */
    public function test_UT_244_detects_studiocart_basenames_and_ignores_cart(): void
    {
        $this->assertTrue(
            PPCartComp_Studiocart_Conflict::is_studiocart_basename('Studiocart-main/studiocart.php')
        );
        $this->assertTrue(
            PPCartComp_Studiocart_Conflict::is_studiocart_basename('ncs-cart/ncs-cart.php')
        );
        $this->assertTrue(
            PPCartComp_Studiocart_Conflict::is_studiocart_basename('studiocart/plugin.php')
        );
        $this->assertFalse(
            PPCartComp_Studiocart_Conflict::is_studiocart_basename('publishpress-cart/publishpress-cart.php')
        );
        $this->assertFalse(
            PPCartComp_Studiocart_Conflict::is_studiocart_basename(
                'publishpress-cart-compat/publishpress-cart-compat.php'
            )
        );
        $this->assertFalse(
            PPCartComp_Studiocart_Conflict::is_studiocart_basename('akismet/akismet.php')
        );
    }

    /**
     * @test-id UT-245
     */
    public function test_UT_245_treats_active_studiocart_as_a_load_conflict(): void
    {
        $this->assertSame(
            'Studiocart-main/studiocart.php',
            PPCartComp_Studiocart_Conflict::find_active_basename(
                [
                    'classic-editor/classic-editor.php',
                    'Studiocart-main/studiocart.php',
                    'publishpress-cart/publishpress-cart.php',
                ]
            )
        );

        WordPressStubContext::setState(
            'options',
            [
                'active_plugins' => [
                    'publishpress-cart/publishpress-cart.php',
                    'publishpress-cart-compat/publishpress-cart-compat.php',
                ],
            ]
        );
        $this->assertFalse(PPCartComp_Studiocart_Conflict::is_active());

        WordPressStubContext::setState(
            'options',
            [
                'active_plugins' => [
                    'Studiocart-main/studiocart.php',
                    'publishpress-cart/publishpress-cart.php',
                ],
            ]
        );
        $this->assertTrue(PPCartComp_Studiocart_Conflict::is_active());
    }
}
