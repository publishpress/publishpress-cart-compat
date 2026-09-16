<?php

namespace unit\StoreFixtures;

use Codeception\Test\Unit;
use Tests\StoreFixtures\Catalog;
use UnitTester;

class StoreSeederCatalogTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        if (! class_exists(Catalog::class, false)) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'tests/store-fixtures/catalog.php';
        }
    }

    /**
     * @test-id UT-243
     */
    public function test_UT_243_store_seed_catalog_uses_leftover_studiocart_shapes(): void
    {
        $this->assertSame('sc_product', Catalog::PRODUCT_POST_TYPE);
        $this->assertSame('sc_order', Catalog::ORDER_POST_TYPE);
        $this->assertSame('sc_subscription', Catalog::SUBSCRIPTION_POST_TYPE);
        $this->assertSame('studiocart-form', Catalog::CHECKOUT_SHORTCODE);
        $this->assertSame('[studiocart-form id="41"]', Catalog::checkout_shortcode(41));

        foreach (array_keys(Catalog::leftover_store_options()) as $option) {
            $this->assertTrue(
                0 === strpos($option, '_sc_') || '_my_account' === $option,
                $option . ' is not a leftover option name'
            );
        }

        foreach (Catalog::leftover_meta_key_list() as $key) {
            $this->assertStringStartsWith('_sc_', $key);
        }

        foreach (Catalog::leftover_user_meta_key_list() as $key) {
            $this->assertStringStartsWith('_sc_', $key);
            $this->assertContains($key, Catalog::leftover_meta_key_list());
        }

        $profile = Catalog::customer_profiles()[0];
        $this->assertArrayHasKey('phone', $profile);
        $this->assertArrayHasKey('address_1', $profile);
        $this->assertArrayHasKey('city', $profile);
        $this->assertArrayHasKey('state', $profile);
        $this->assertArrayHasKey('zip', $profile);
        $this->assertArrayHasKey('country', $profile);
        $this->assertSame('US', $profile['country']);
        $this->assertArrayHasKey('address_2', Catalog::customer_profiles()[0]);
        $this->assertArrayNotHasKey('address_2', Catalog::customer_profiles()[1]);

        $pay = Catalog::product_catalog()[0]['meta']['_sc_pay_options'][0];
        $this->assertArrayHasKey('option_id', $pay);
        $this->assertArrayHasKey('option_name', $pay);
        $this->assertArrayHasKey('price', $pay);
        $this->assertSame('', $pay['product_type']);

        $recurring = Catalog::recurring_plan('m', 'Monthly', '29');
        $this->assertSame('recurring', $recurring['product_type']);
        $this->assertSame('month', $recurring['interval']);

        $core    = Catalog::product_catalog();
        $extras  = Catalog::extra_product_catalog();
        $this->assertGreaterThanOrEqual(14, count($core));
        $overflow = Catalog::product_catalog_for_count(count($core) + 6);
        $this->assertCount(count($core) + 6, $overflow);
        for ($i = 0; $i < 6; $i++) {
            $this->assertSame($extras[ $i ]['title'], $overflow[ count($core) + $i ]['title']);
        }
        $wrapped = array_column(Catalog::product_catalog_for_count(100), 'title');
        $this->assertCount(100, $wrapped);
        $this->assertCount(100, array_unique($wrapped));
        $sale = Catalog::sale_plan('s', 'Seat', '99', '69');
        $this->assertSame('69', $sale['sale_price']);
        $yearly = Catalog::recurring_plan('y', 'Yearly', '199', '-1', 'year');
        $this->assertSame('year', $yearly['interval']);
        $this->assertSame(96, Catalog::DEFAULT_ORDERS);
        $this->assertSame(30, Catalog::MONTH_DAYS);
        $this->assertSame(1, preg_match('/^\d{4}-\d{2}-\d{2} /', Catalog::month_post_date(0, 96)));
    }
}
