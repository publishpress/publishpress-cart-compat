<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Admin_Page_Compatibility;
use UnitTester;

class AdminPageSlugsTest extends Unit
{
    /** @var UnitTester */
    protected $tester;

    public function test_legacy_admin_page_map_includes_sc_admin_to_ppcart_settings(): void
    {
        $map = PPCartComp_Studiocart_Admin_Page_Compatibility::legacy_admin_page_map();

        $this->assertSame('ppcart-settings', $map['sc-admin']);
        $this->assertSame('ppcart-affiliates', $map['sc-affiliates']);
    }

    public function test_get_legacy_redirect_url_preserves_query_args(): void
    {
        $url = PPCartComp_Studiocart_Admin_Page_Compatibility::get_legacy_redirect_url([
            'page' => 'sc-admin',
            'tab'  => 'affiliate',
        ]);

        $this->assertIsString($url);
        $this->assertStringContainsString('page=ppcart-settings', $url);
        $this->assertStringContainsString('tab=affiliate', $url);
        $this->assertStringNotContainsString('sc-admin', $url);
    }

    public function test_get_legacy_redirect_url_returns_null_for_canonical_slug(): void
    {
        $url = PPCartComp_Studiocart_Admin_Page_Compatibility::get_legacy_redirect_url([
            'page' => 'ppcart-settings',
        ]);

        $this->assertNull($url);
    }
}
