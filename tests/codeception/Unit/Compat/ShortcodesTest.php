<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class ShortcodesTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var array<string, string>
     */
    private $expectedPairs = [
        'ppcart_form'                        => 'studiocart-form',
        'ppcart_receipt'                     => 'studiocart-receipt',
        'ppcart_store'                       => 'studiocart-store',
        'ppcart_order_downloads'             => 'studiocart-order-downloads',
        'ppcart_account'                     => 'studiocart_account',
        'ppcart_account_link'                => 'studiocart_account_link',
        'ppcart_account_order_detail'        => 'studiocart_order_detail',
        'ppcart_account_subscription_detail' => 'studiocart_subscription_detail',
        'ppcart_customer_bought_product'     => 'sc_customer_bought_product',
        'ppcart_customer_has_subscription'   => 'sc_customer_has_subscription',
        'ppcart_order_detail'                => 'sc_order_detail',
        'ppcart_plan'                        => 'sc_plan',
        'ppcart_product'                     => 'sc_product',
        'ppcart_affiliate_dashboard'         => 'sc_affiliate_dashboard',
        'ppcart_affiliate_signup'            => 'sc_affiliate_signup',
        'ppcart_order_summary_items_view'    => 'sc_order_summary_items_view',
    ];

    protected function _before(): void
    {
        $this->resetShortcodes();

        WordPressStubContext::setState('options', []);
        WordPressStubContext::setState('shortcodes', []);

        WordPressStubContext::set(
            'get_option',
            static function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                return array_key_exists($option, $options) ? $options[ $option ] : $default;
            }
        );

        WordPressStubContext::set(
            'add_shortcode',
            static function ($tag, $callback) {
                $GLOBALS['shortcode_tags'][ $tag ] = $callback;
                $shortcodes = WordPressStubContext::getState('shortcodes', []);
                $shortcodes[] = $tag;
                WordPressStubContext::setState('shortcodes', $shortcodes);

                return true;
            }
        );
    }

    protected function _after(): void
    {
        $this->resetShortcodes();
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-204
     */
    public function test_UT_204_shortcode_map_covers_all_sixteen_pairs(): void
    {
        $map = ncs_cart_get_legacy_shortcode_map();

        $this->assertCount(16, $map);
        foreach ($this->expectedPairs as $canonical => $legacy) {
            $this->assertArrayHasKey($canonical, $map);
            $this->assertSame($legacy, ncs_cart_legacy_shortcode_tag($map[ $canonical ]));
        }

        $this->assertSame(
            'ppcart_order_summary_shortcode',
            $map['ppcart_order_summary_items_view']['callback']
        );
        $this->assertNotSame(
            $this->expectedPairs['ppcart_account_order_detail'],
            $this->expectedPairs['ppcart_order_detail']
        );
    }

    /**
     * @test-id UT-204
     */
    public function test_UT_204_leftover_tags_register_only_when_compatibility_mode_is_on(): void
    {
        $GLOBALS['shortcode_tags'] = [
            'ppcart_form' => 'product_shortcode_stub',
        ];

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_shortcodes();

        $this->assertNotContains('studiocart-form', WordPressStubContext::getState('shortcodes', []));
        $this->assertArrayNotHasKey('studiocart-form', $GLOBALS['shortcode_tags']);

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );
        WordPressStubContext::setState('shortcodes', []);

        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_shortcodes();

        $this->assertContains('studiocart-form', WordPressStubContext::getState('shortcodes', []));
        $this->assertSame(
            'product_shortcode_stub',
            $GLOBALS['shortcode_tags']['studiocart-form']
        );

        WordPressStubContext::setState('options', []);
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_shortcodes();

        $this->assertArrayNotHasKey('studiocart-form', $GLOBALS['shortcode_tags']);
    }

    private function resetShortcodes(): void
    {
        if (function_exists('ppcart_unregister_legacy_shortcodes')) {
            ppcart_unregister_legacy_shortcodes();
        }

        $GLOBALS['shortcode_tags'] = [];
    }
}
