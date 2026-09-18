<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;

class ThirdPartyStoredKeysTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var bool
     */
    private $createdMeprTable = false;

    /**
     * @var bool
     */
    private $createdAffiliateTable = false;

    public function tear_down()
    {
        global $wpdb;

        $mepr = $wpdb->prefix . 'mepr_transactions';
        if ($this->createdMeprTable && $this->tableExists($mepr)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Test fixture teardown.
            $wpdb->query('DROP TABLE IF EXISTS `' . str_replace('`', '', $mepr) . '`');
        } elseif ($this->tableExists($mepr)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Test fixture teardown.
            $wpdb->query($wpdb->prepare('DELETE FROM %i WHERE trans_num LIKE %s OR trans_num LIKE %s', $mepr, 'sc-txn-367%', 'ppcart-txn-367%'));
        }

        $affiliate = $this->affiliateTable();
        if ('' !== $affiliate && $this->tableExists($affiliate)) {
            $objectCol = $this->affiliateObjectColumn($affiliate);
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Test fixture teardown.
            $wpdb->query(
                $wpdb->prepare(
                    'DELETE FROM %i WHERE %i = %d AND meta_key IN (%s, %s)',
                    $affiliate,
                    $objectCol,
                    367001,
                    'sc_submission_id',
                    'ppcart_submission_id'
                )
            );
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Test fixture teardown.
            $wpdb->query(
                $wpdb->prepare(
                    'DELETE FROM %i WHERE %i = %d AND meta_key IN (%s, %s)',
                    $affiliate,
                    $objectCol,
                    367002,
                    'sc_submission_id',
                    'ppcart_submission_id'
                )
            );
        }

        if ($this->createdAffiliateTable && '' !== $affiliate && $this->tableExists($affiliate)) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Test fixture teardown.
            $wpdb->query('DROP TABLE IF EXISTS `' . str_replace('`', '', $affiliate) . '`');
        }

        delete_option(ppcart_third_party_key_migration_option());
        if (function_exists('ppcart_flush_live_table_cache')) {
            ppcart_flush_live_table_cache();
        }

        parent::tear_down();
    }

    /**
     * @test-id IT-367
     */
    public function test_IT_367_migrate_rewrites_leftover_memberpress_trans_num_and_affiliate_meta(): void
    {
        global $wpdb;

        $this->installMeprTable();
        $this->installAffiliateTable();

        $mepr = $wpdb->prefix . 'mepr_transactions';
        $affiliate = $this->affiliateTable();
        $objectCol = $this->affiliateObjectColumn($affiliate);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Test fixture.
        $wpdb->insert($mepr, [ 'trans_num' => 'sc-txn-3671' ]);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Test fixture.
        $wpdb->insert($mepr, [ 'trans_num' => 'sc-txn-3672' ]);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Test fixture.
        $wpdb->insert($mepr, [ 'trans_num' => 'ppcart-txn-3672' ]);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Test fixture.
        $wpdb->insert(
            $affiliate,
            [
                $objectCol => 367001,
                'meta_key' => 'sc_submission_id',
                'meta_value' => '41',
            ]
        );
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Test fixture.
        $wpdb->insert(
            $affiliate,
            [
                $objectCol => 367001,
                'meta_key' => 'ppcart_submission_id',
                'meta_value' => '99',
            ]
        );

        $result = ppcart_migrate_third_party_stored_keys();

        $this->assertSame('ok', $result['memberpress']['status']);
        $this->assertSame(1, (int) $result['memberpress']['moved']);
        $this->assertSame(1, (int) $result['memberpress']['skipped']);
        $this->assertSame('ok', $result['affiliate']['status']);
        $this->assertSame(0, (int) $result['affiliate']['moved']);
        $this->assertGreaterThanOrEqual(1, (int) $result['affiliate']['skipped']);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Assert migrated trans_num.
        $moved = $wpdb->get_var($wpdb->prepare('SELECT trans_num FROM %i WHERE trans_num = %s', $mepr, 'ppcart-txn-3671'));
        $this->assertSame('ppcart-txn-3671', $moved);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Conflict leftover stays.
        $leftoverConflict = $wpdb->get_var($wpdb->prepare('SELECT trans_num FROM %i WHERE trans_num = %s', $mepr, 'sc-txn-3672'));
        $this->assertSame('sc-txn-3672', $leftoverConflict);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Canonical affiliate key survived.
        $canonicalAffiliate = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT meta_value FROM %i WHERE %i = %d AND meta_key = %s',
                $affiliate,
                $objectCol,
                367001,
                'ppcart_submission_id'
            )
        );
        $this->assertSame('99', $canonicalAffiliate);

        $this->installAffiliateOnlyRow();
        $second = ppcart_migrate_affiliate_submission_ids();
        $this->assertSame(1, (int) $second['moved']);

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Leftover-only affiliate key renamed.
        $renamed = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT meta_key FROM %i WHERE %i = %d AND meta_value = %s',
                $affiliate,
                $objectCol,
                367002,
                '77'
            )
        );
        $this->assertSame('ppcart_submission_id', $renamed);
    }

    /**
     * @return void
     */
    private function installMeprTable(): void
    {
        global $wpdb;

        $table = $wpdb->prefix . 'mepr_transactions';
        if ($this->tableExists($table)) {
            return;
        }

        $collate = $wpdb->get_charset_collate();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Test fixture schema.
        $wpdb->query(
            "CREATE TABLE {$table} (
                id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                trans_num varchar(100) NOT NULL,
                PRIMARY KEY (id),
                UNIQUE KEY trans_num (trans_num)
            ) {$collate}"
        );
        $this->createdMeprTable = true;
        if (function_exists('ppcart_flush_live_table_cache')) {
            ppcart_flush_live_table_cache();
        }
    }

    /**
     * @return void
     */
    private function installAffiliateTable(): void
    {
        global $wpdb;

        $table = $this->affiliateTable();
        if ('' !== $table && $this->tableExists($table)) {
            return;
        }

        $table = $wpdb->prefix . 'ppcart_affiliatemeta';
        $collate = $wpdb->get_charset_collate();
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Test fixture schema.
        $wpdb->query(
            "CREATE TABLE {$table} (
                meta_id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                ppcart_affiliate_id bigint(20) unsigned NOT NULL,
                meta_key varchar(255) DEFAULT NULL,
                meta_value longtext DEFAULT NULL,
                PRIMARY KEY (meta_id)
            ) {$collate}"
        );
        $this->createdAffiliateTable = true;
        if (function_exists('ppcart_flush_live_table_cache')) {
            ppcart_flush_live_table_cache();
        }
    }

    /**
     * @return void
     */
    private function installAffiliateOnlyRow(): void
    {
        global $wpdb;

        $affiliate = $this->affiliateTable();
        $objectCol = $this->affiliateObjectColumn($affiliate);
        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Test fixture.
        $wpdb->insert(
            $affiliate,
            [
                $objectCol => 367002,
                'meta_key' => 'sc_submission_id',
                'meta_value' => '77',
            ]
        );
    }

    /**
     * @return string
     */
    private function affiliateTable(): string
    {
        if (function_exists('ppcart_third_party_affiliatemeta_table')) {
            $table = ppcart_third_party_affiliatemeta_table();
            if (is_string($table) && '' !== $table) {
                return $table;
            }
        }

        global $wpdb;

        return $wpdb->prefix . 'ppcart_affiliatemeta';
    }

    /**
     * @param string $table Table name.
     * @return string
     */
    private function affiliateObjectColumn($table): string
    {
        if (function_exists('ppcart_custom_table_column_exists')) {
            if (ppcart_custom_table_column_exists($table, 'ppcart_affiliate_id')) {
                return 'ppcart_affiliate_id';
            }
            if (ppcart_custom_table_column_exists($table, 'ncs_affiliate_id')) {
                return 'ncs_affiliate_id';
            }
        }

        return 'ppcart_affiliate_id';
    }

    /**
     * @param string $table Table name.
     * @return bool
     */
    private function tableExists($table): bool
    {
        if (function_exists('ppcart_custom_table_exists')) {
            return ppcart_custom_table_exists($table);
        }

        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Schema probe.
        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $table));

        return $found === $table;
    }
}
