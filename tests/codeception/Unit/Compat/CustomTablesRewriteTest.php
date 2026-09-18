<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\Support\WordPressStubContext;
use UnitTester;

class CustomTablesRewriteTest extends Unit
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
        WordPressStubContext::set(
            'get_option',
            static function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                return array_key_exists($option, $options) ? $options[ $option ] : $default;
            }
        );
        WordPressStubContext::set(
            'add_filter',
            static function () {
                return true;
            }
        );
        WordPressStubContext::set(
            'remove_filter',
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

        global $wpdb;
        $this->previousWpdb = isset($wpdb) ? $wpdb : null;

        if (! function_exists('ppcart_live_table')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/custom-table-migration/maps.php';
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/custom-table-migration/state.php';
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/custom-table-migration/helpers.php';
        }
        if (! function_exists('ncs_cart_get_legacy_custom_table_map')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/studiocart-compatibility-mode/custom-tables.php';
        }

        $this->setTables([]);
    }

    protected function _after(): void
    {
        if (function_exists('ppcart_unregister_legacy_custom_table_bridges')) {
            ppcart_unregister_legacy_custom_table_bridges();
        }
        if (function_exists('ppcart_flush_live_table_cache')) {
            ppcart_flush_live_table_cache();
        }

        global $wpdb;
        $wpdb = $this->previousWpdb;

        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-313
     */
    public function test_UT_313_custom_table_map_covers_leftover_pro_and_tax_suffixes(): void
    {
        $map = ncs_cart_get_legacy_custom_table_map();

        $this->assertSame([ 'ncs_tax_rate' ], $map['ppcart_tax_rate']);
        $this->assertSame([ 'ncs_affiliates' ], $map['ppcart_affiliates']);
        $this->assertSame([ 'ncs_affiliatemeta' ], $map['ppcart_affiliatemeta']);
        $this->assertSame([ 'ncs_affiliate_commissions' ], $map['ppcart_affiliate_commissions']);
        $this->assertSame([ 'ncs_affiliate_payouts' ], $map['ppcart_affiliate_payouts']);
        $this->assertSame([ 'ncs_affiliate_visits' ], $map['ppcart_affiliate_visits']);
        $this->assertSame([ 'ncs_affiliate_product_rel' ], $map['ppcart_affiliate_product_rel']);
    }

    /**
     * @test-id UT-313
     */
    public function test_UT_313_query_rewrite_is_gated_and_rewrites_leftover_to_live(): void
    {
        $sql = 'SELECT id FROM wp_ncs_affiliates WHERE email = %s';

        $this->assertSame($sql, ppcart_rewrite_legacy_custom_table_sql($sql));

        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        $rewritten = ppcart_rewrite_legacy_custom_table_sql($sql);
        $this->assertSame('SELECT id FROM wp_ppcart_affiliates WHERE email = %s', $rewritten);
    }

    /**
     * @test-id UT-313
     */
    public function test_UT_313_query_rewrite_does_not_recurse_when_live_table_lists_tables(): void
    {
        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        global $wpdb;

        $wpdb = new class {
            /**
             * @var string
             */
            public $prefix = 'wp_';

            /**
             * @var int
             */
            public $depth = 0;

            /**
             * @param string $query
             * @return string[]
             */
            public function get_col($query)
            {
                ++$this->depth;
                if ($this->depth > 8) {
                    throw new \RuntimeException('custom table query rewrite recursed into SHOW TABLES');
                }

                ppcart_rewrite_legacy_custom_table_sql((string) $query);
                --$this->depth;

                return [ 'wp_ppcart_affiliates' ];
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

        if (function_exists('ppcart_flush_live_table_cache')) {
            ppcart_flush_live_table_cache();
        }

        $sql = 'SELECT id FROM wp_ncs_affiliates WHERE email = %s';
        $this->assertSame(
            'SELECT id FROM wp_ppcart_affiliates WHERE email = %s',
            ppcart_rewrite_legacy_custom_table_sql($sql)
        );
        $this->assertLessThanOrEqual(1, $wpdb->depth);
    }

    /**
     * @test-id UT-313
     */
    public function test_UT_313_query_rewrite_skips_schema_probes(): void
    {
        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );

        $show = "SHOW COLUMNS FROM wp_ppcart_affiliatemeta LIKE 'ncs_affiliate_id'";
        $this->assertSame($show, ppcart_rewrite_legacy_custom_table_sql($show));

        $alter = 'ALTER TABLE `wp_ppcart_affiliatemeta` CHANGE `ncs_affiliate_id` `ppcart_affiliate_id` bigint(20) unsigned NOT NULL';
        $this->assertSame($alter, ppcart_rewrite_legacy_custom_table_sql($alter));
    }

    /**
     * @test-id UT-313
     */
    public function test_UT_313_leftover_affiliate_meta_wpdb_alias_survives_live_register(): void
    {
        WordPressStubContext::setState(
            'options',
            [ PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME => '1' ]
        );
        $this->setTables([ 'wp_ppcart_affiliatemeta' ]);

        ppcart_register_live_affiliate_meta_table();

        global $wpdb;
        $this->assertSame('wp_ppcart_affiliatemeta', $wpdb->ppcart_affiliatemeta);
        $this->assertSame('wp_ppcart_affiliatemeta', $wpdb->ncs_affiliatemeta);
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

        if (function_exists('ppcart_flush_live_table_cache')) {
            ppcart_flush_live_table_cache();
        }
    }
}
