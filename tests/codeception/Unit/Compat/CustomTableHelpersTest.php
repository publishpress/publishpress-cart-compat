<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use Tests\Support\WordPressStubContext;
use UnitTester;

class CustomTableHelpersTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    /**
     * @var object|null
     */
    private $previousWpdb;

    protected function _before(): void
    {
        WordPressStubContext::clear();
        WordPressStubContext::setState('options', []);
        WordPressStubContext::set('apply_filters', static function ($hook, $value) {
            return $value;
        });
        WordPressStubContext::set('add_action', static function () {
            return true;
        });
        WordPressStubContext::set('add_filter', static function () {
            return true;
        });
        WordPressStubContext::set(
            'get_option',
            static function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                return array_key_exists($option, $options) ? $options[ $option ] : $default;
            }
        );

        global $wpdb;
        $this->previousWpdb = isset($wpdb) ? $wpdb : null;

        if (! function_exists('ppcart_live_table')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/custom-table-migration/maps.php';
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/custom-table-migration/state.php';
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/custom-table-migration/helpers.php';
        }

        $this->setTables([]);
    }

    protected function _after(): void
    {
        ppcart_flush_live_table_cache();

        global $wpdb;
        $wpdb = $this->previousWpdb;

        WordPressStubContext::clear();
        parent::_after();
    }

    public function test_UT_220_live_table_uses_leftover_when_only_leftover_exists(): void
    {
        $this->setTables([
            'wp_ncs_tax_rate',
            'wp_ncs_order_items',
            'wp_ncs_order_itemmeta',
            'wp_ncs_downloads',
        ]);

        $this->assertSame('wp_ncs_tax_rate', ppcart_live_table('tax_rate'));
        $this->assertSame('wp_ncs_order_items', ppcart_live_table('order_items'));
        $this->assertSame('wp_ncs_order_itemmeta', ppcart_live_table('order_itemmeta'));
        $this->assertSame('wp_ncs_downloads', ppcart_live_table('downloads'));
        $this->assertSame('ncs_order_item', ppcart_live_metadata_type());
        $this->assertSame('ncs_order_item_id', ppcart_live_order_item_id_column());
        $this->assertSame('ncs_tax_rate', ppcart_live_table_suffix('tax_rate'));
    }

    /**
     * @test-id UT-313
     */
    public function test_UT_313_live_table_uses_leftover_pro_families_when_only_leftover_exists(): void
    {
        $this->setTables([
            'wp_ncs_affiliates',
            'wp_ncs_affiliatemeta',
            'wp_ncs_affiliate_commissions',
            'wp_ncs_affiliate_payouts',
            'wp_ncs_affiliate_visits',
            'wp_ncs_affiliate_product_rel',
        ]);

        $this->assertSame('wp_ncs_affiliates', ppcart_live_table('affiliates'));
        $this->assertSame('wp_ncs_affiliatemeta', ppcart_live_table('affiliatemeta'));
        $this->assertSame('wp_ncs_affiliate_commissions', ppcart_live_table('affiliate_commissions'));
        $this->assertSame('wp_ncs_affiliate_payouts', ppcart_live_table('affiliate_payouts'));
        $this->assertSame('wp_ncs_affiliate_visits', ppcart_live_table('affiliate_visits'));
        $this->assertSame('wp_ncs_affiliate_product_rel', ppcart_live_table('affiliate_product_rel'));
        $this->assertSame('ncs_affiliate', ppcart_live_affiliate_metadata_type());
        $this->assertSame('ncs_affiliate_id', ppcart_live_affiliate_id_column());
    }

    /**
     * @test-id UT-313
     */
    public function test_UT_313_live_table_uses_canonical_pro_families_when_canonical_exists(): void
    {
        $this->setTables([
            'wp_ncs_affiliates',
            'wp_ppcart_affiliates',
            'wp_ppcart_affiliatemeta',
            'wp_ppcart_affiliate_commissions',
            'wp_ppcart_affiliate_payouts',
            'wp_ppcart_affiliate_visits',
            'wp_ppcart_affiliate_product_rel',
        ]);

        $this->assertSame('wp_ppcart_affiliates', ppcart_live_table('affiliates'));
        $this->assertSame('wp_ppcart_affiliatemeta', ppcart_live_table('affiliatemeta'));
        $this->assertSame('ppcart_affiliate', ppcart_live_affiliate_metadata_type());
        $this->assertSame('ppcart_affiliate_id', ppcart_live_affiliate_id_column());
    }

    /**
     * @test-id UT-313
     */
    public function test_UT_313_live_table_uses_canonical_pro_families_when_no_tables_exist(): void
    {
        $this->setTables([]);

        $this->assertSame('wp_ppcart_affiliates', ppcart_live_table('affiliates'));
        $this->assertSame('wp_ppcart_affiliatemeta', ppcart_live_table('affiliatemeta'));
        $this->assertSame('wp_ppcart_affiliate_commissions', ppcart_live_table('affiliate_commissions'));
        $this->assertSame('ppcart_affiliate', ppcart_live_affiliate_metadata_type());
        $this->assertSame('ppcart_affiliate_id', ppcart_live_affiliate_id_column());
    }

    public function test_UT_220_live_table_uses_canonical_when_canonical_exists(): void
    {
        $this->setTables([
            'wp_ncs_tax_rate',
            'wp_ppcart_tax_rate',
            'wp_ppcart_order_items',
            'wp_ppcart_order_itemmeta',
            'wp_ppcart_downloads',
        ]);

        $this->assertSame('wp_ppcart_tax_rate', ppcart_live_table('tax_rate'));
        $this->assertSame('wp_ppcart_order_items', ppcart_live_table('order_items'));
        $this->assertSame('wp_ppcart_order_itemmeta', ppcart_live_table('order_itemmeta'));
        $this->assertSame('wp_ppcart_downloads', ppcart_live_table('downloads'));
        $this->assertSame('ppcart_order_item', ppcart_live_metadata_type());
        $this->assertSame('ppcart_order_item_id', ppcart_live_order_item_id_column());
    }

    public function test_UT_220_live_table_uses_canonical_when_no_tables_exist(): void
    {
        $this->setTables([]);

        $this->assertSame('wp_ppcart_tax_rate', ppcart_live_table('tax_rate'));
        $this->assertSame('wp_ppcart_order_items', ppcart_live_table('order_items'));
        $this->assertSame('wp_ppcart_order_itemmeta', ppcart_live_table('order_itemmeta'));
        $this->assertSame('wp_ppcart_downloads', ppcart_live_table('downloads'));
        $this->assertSame('ppcart_order_item', ppcart_live_metadata_type());
        $this->assertSame('ppcart_order_item_id', ppcart_live_order_item_id_column());
        $this->assertSame('', ppcart_live_table('not_a_family'));
    }

    public function test_UT_220_metadata_type_and_wpdb_property_follow_live_meta_table(): void
    {
        global $wpdb;

        $this->setTables([ 'wp_ncs_order_itemmeta' ]);
        ppcart_register_live_meta_table();
        $this->assertSame('wp_ncs_order_itemmeta', $wpdb->ncs_order_itemmeta);
        $this->assertFalse(isset($wpdb->ppcart_order_itemmeta));

        $this->setTables([ 'wp_ppcart_order_itemmeta' ]);
        ppcart_register_live_meta_table();
        $this->assertSame('wp_ppcart_order_itemmeta', $wpdb->ppcart_order_itemmeta);
        $this->assertFalse(isset($wpdb->ncs_order_itemmeta));
    }

    /**
     * @param string[] $tables
     * @return void
     */
    private function setTables(array $tables): void
    {
        global $wpdb;

        $wpdb = new class($tables) {
            /**
             * @var string
             */
            public $prefix = 'wp_';

            /**
             * @var string[]
             */
            public $tables;

            /**
             * @param string[] $tables
             */
            public function __construct(array $tables)
            {
                $this->tables = $tables;
            }

            /**
             * @param string $query
             * @return string[]
             */
            public function get_col($query)
            {
                return $this->tables;
            }

            /**
             * @param string $query
             * @return null
             */
            public function get_row($query)
            {
                return null;
            }

            /**
             * @param string $query
             * @param mixed  ...$args
             * @return string
             */
            public function prepare($query, ...$args)
            {
                return $query;
            }
        };

        ppcart_flush_live_table_cache();
    }
}
