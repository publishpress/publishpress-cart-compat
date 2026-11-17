<?php

namespace unit\ShortcodeTagMigration;

use Codeception\Test\Unit;
use UnitTester;

class UpsellLinkRewriteTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/shortcode-tag-migration/maps.php';
        require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/shortcode-tag-migration/helpers.php';
    }

    /**
     * @test-id UT-321
     */
    public function test_UT_321_accept_and_decline_links_are_rewritten(): void
    {
        $html = '<a href="https://example.com/?sc-upsell-offer=yes">Yes</a>'
            . '<a href="https://example.com/offer/?step=2&sc-upsell-offer=no" class="button sc-decline-upsell-link">No</a>'
            . '<a href="#" class="sc-accept-upsell-link">Add it</a>';

        $this->assertTrue(ppcart_content_has_leftover_upsell_links($html));

        $next = ppcart_rewrite_leftover_upsell_links($html);

        $this->assertStringContainsString('?ppcart-upsell-offer=yes', $next);
        $this->assertStringContainsString('&ppcart-upsell-offer=no', $next);
        $this->assertStringContainsString('class="button ppcart-decline-upsell-link"', $next);
        $this->assertStringContainsString('class="ppcart-accept-upsell-link"', $next);
        $this->assertFalse(ppcart_content_has_leftover_upsell_links($next));
    }

    /**
     * @test-id UT-321
     */
    public function test_UT_321_new_names_and_lookalikes_are_untouched(): void
    {
        $html = '<a href="/?ppcart-upsell-offer=yes" class="ppcart-accept-upsell-link">Yes</a>'
            . '<span class="misc-upsell-offer=1 xsc-accept-upsell-link">x</span>';

        $this->assertFalse(ppcart_content_has_leftover_upsell_links($html));
        $this->assertSame($html, ppcart_rewrite_leftover_upsell_links($html));
    }

    /**
     * @test-id UT-321
     */
    public function test_UT_321_rewrite_works_inside_elementor_json(): void
    {
        $raw = '[{"widgetType":"button","settings":{"link":{"url":"https:\/\/example.com\/?sc-upsell-offer=yes"},"_css_classes":"sc-accept-upsell-link"}}]';

        $next = ppcart_rewrite_leftover_upsell_links($raw);

        $this->assertSame(
            '[{"widgetType":"button","settings":{"link":{"url":"https:\/\/example.com\/?ppcart-upsell-offer=yes"},"_css_classes":"ppcart-accept-upsell-link"}}]',
            $next
        );
        $this->assertNotNull(json_decode($next, true));
    }

    /**
     * @test-id UT-321
     */
    public function test_UT_321_page_content_rewrites_shortcodes_and_links(): void
    {
        $next = ppcart_rewrite_leftover_page_content('[studiocart-form id=5] <a href="/?sc-upsell-offer=yes">Yes</a>');

        $this->assertSame('[ppcart_form id=5] <a href="/?ppcart-upsell-offer=yes">Yes</a>', $next);
    }

    /**
     * @test-id UT-321
     */
    public function test_UT_321_reopened_step_waits_for_upsell_link_rerun(): void
    {
        require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/shortcode-tag-migration/state.php';

        $state = array_merge(
            ppcart_shortcode_tag_migration_default_state(),
            [
                'step1'                 => 'idle',
                'mode'                  => 'legacy',
                'upsell_links_reopened' => true,
            ]
        );
        $this->assertTrue(ppcartcomp_shortcode_step_awaits_upsell_links($state));

        $state['step1'] = 'complete';
        $state['mode']  = 'canonical';
        $this->assertFalse(ppcartcomp_shortcode_step_awaits_upsell_links($state));

        $never = array_merge(ppcart_shortcode_tag_migration_default_state(), [ 'step1' => 'idle' ]);
        $this->assertFalse(ppcartcomp_shortcode_step_awaits_upsell_links($never));
    }
}
