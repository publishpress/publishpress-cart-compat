<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use UnitTester;

class ThemeOverridesTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @test-id UT-270
     */
    public function test_UT_270_theme_override_dir_map_covers_leftover_studiocart(): void
    {
        $map = ncs_cart_get_legacy_theme_template_dir_map();

        $this->assertSame([ 'studiocart' ], $map['publishpress-cart']);
    }
}
