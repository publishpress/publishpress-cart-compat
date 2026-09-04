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
        WordPressStubContext::set(
            'wp_die',
            static function () {
                WordPressStubContext::setState('wp_die_called', true);
            }
        );
        WordPressStubContext::set(
            'current_user_can',
            static function ($capability) {
                $caps = WordPressStubContext::getState('caps', []);

                return in_array($capability, $caps, true);
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

    /**
     * @test-id UT-249
     */
    public function test_UT_249_conflicted_activation_does_not_die(): void
    {
        WordPressStubContext::setState(
            'options',
            [
                'active_plugins' => [ 'Studiocart-main/studiocart.php' ],
            ]
        );
        WordPressStubContext::setState('wp_die_called', false);

        PPCartComp_Studiocart_Conflict::on_activate('/tmp/publishpress-cart-compat.php');

        $this->assertFalse(WordPressStubContext::getState('wp_die_called', false));
    }

    /**
     * @test-id UT-249
     */
    public function test_UT_249_conflict_notice_is_admin_only(): void
    {
        WordPressStubContext::setState('caps', []);
        ob_start();
        PPCartComp_Studiocart_Conflict::render_notice();
        $this->assertSame('', ob_get_clean());

        WordPressStubContext::setState('caps', [ 'activate_plugins' ]);
        ob_start();
        PPCartComp_Studiocart_Conflict::render_notice();
        $output = ob_get_clean();
        $this->assertStringContainsString('notice notice-error', $output);
        $this->assertStringContainsString('cannot run while Studiocart is active', $output);
        $this->assertStringContainsString('Deactivate Studiocart to start those plugins', $output);
    }
}
