<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use PPCartComp_Cpt_Slug_Migrator;
use PPCartComp_Meta_Key_Migrator;
use PPCartComp_Shortcode_Tag_Migrator;
use PPCartComp_Studiocart_Admin;
use PPCartComp_Studiocart_Compatibility_Mode;
use Tests\NoTransactionWPTestCase;

class UninstallMigrationBackupsTest extends NoTransactionWPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var int
     */
    private $leftoverPostId = 0;

    /**
     * @var mixed
     */
    private $originalCompat = false;

    /**
     * @var mixed
     */
    private $originalMigrationState = false;

    /**
     * @var bool
     */
    private $createdMigrationState = false;

    /**
     * @var bool
     */
    private $createdTaxRateStub = false;

    public function set_up()
    {
        parent::set_up();

        require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/ppcartcomp-drop-migration-backups.php';

        $this->originalCompat         = get_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, false);
        $this->originalMigrationState = get_option('ppcart_cpt_slug_migration', false);
        $this->createdMigrationState  = false;
        $this->createdTaxRateStub     = false;
    }

    public function tear_down()
    {
        global $wpdb;

        if ($this->leftoverPostId > 0) {
            wp_delete_post($this->leftoverPostId, true);
            $this->leftoverPostId = 0;
        }

        delete_option(PPCartComp_Studiocart_Admin::DROP_BACKUPS_OPTION);
        delete_option('_sc_it331_marker');

        if (false === $this->originalCompat) {
            delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        } else {
            update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, $this->originalCompat);
        }

        if ($this->createdMigrationState) {
            if (false === $this->originalMigrationState) {
                delete_option('ppcart_cpt_slug_migration');
            } else {
                update_option('ppcart_cpt_slug_migration', $this->originalMigrationState);
            }
        }

        if ($this->createdTaxRateStub) {
            $taxTable = $wpdb->prefix . 'ppcart_tax_rate';
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Test isolation for stub store table.
            $wpdb->query('DROP TABLE IF EXISTS `' . str_replace('`', '', $taxTable) . '`');
            $this->createdTaxRateStub = false;
        }

        parent::tear_down();
    }

    /**
     * @test-id IT-331
     */
    public function test_IT_331_uninstall_cleanup_drops_backup_tables_only_when_opted_in(): void
    {
        global $wpdb;

        $this->assertTrue(function_exists('ppcartcomp_maybe_drop_migration_backups_for_blog'));

        PPCartComp_Cpt_Slug_Migrator::maybe_create_table();
        PPCartComp_Meta_Key_Migrator::maybe_create_table();
        PPCartComp_Shortcode_Tag_Migrator::maybe_create_table();

        $backupTables = [
            $wpdb->prefix . 'ppcart_cpt_migration_backup',
            $wpdb->prefix . 'ppcart_meta_key_migration_backup',
            $wpdb->prefix . 'ppcart_shortcode_tag_migration_backup',
        ];
        foreach ($backupTables as $table) {
            $this->assertTrue($this->tableExists($table), 'backup table must exist before cleanup: ' . $table);
        }

        $taxTable = $wpdb->prefix . 'ppcart_tax_rate';
        if (! $this->tableExists($taxTable)) {
            $charset   = $wpdb->get_charset_collate();
            $safeTable = str_replace('`', '', $taxTable);
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Stub Cart store table when Cart has not created it yet.
            $wpdb->query(
                "CREATE TABLE `{$safeTable}` (
					id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
					PRIMARY KEY  (id)
				) {$charset}"
            );
            $this->createdTaxRateStub = true;
        }
        $this->assertTrue($this->tableExists($taxTable), 'Cart store table ppcart_tax_rate must exist');

        update_option('_sc_it331_marker', 'keep');
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        $migrationState = get_option('ppcart_cpt_slug_migration', false);
        if (false === $migrationState) {
            update_option('ppcart_cpt_slug_migration', [ 'step1' => 'complete' ]);
            $this->createdMigrationState = true;
            $migrationState              = get_option('ppcart_cpt_slug_migration');
        }

        $this->leftoverPostId = (int) wp_insert_post(
            [
                'post_title'  => 'IT-331 leftover',
                'post_status' => 'publish',
                'post_type'   => 'post',
            ]
        );
        $this->assertGreaterThan(0, $this->leftoverPostId);
        update_post_meta($this->leftoverPostId, '_sc_it331_marker', 'keep');

        delete_option(PPCartComp_Studiocart_Admin::DROP_BACKUPS_OPTION);
        ppcartcomp_maybe_drop_migration_backups_for_blog();
        foreach ($backupTables as $table) {
            $this->assertTrue($this->tableExists($table), 'unset flag must leave backup tables: ' . $table);
        }

        update_option(PPCartComp_Studiocart_Admin::DROP_BACKUPS_OPTION, '0');
        ppcartcomp_maybe_drop_migration_backups_for_blog();
        foreach ($backupTables as $table) {
            $this->assertTrue($this->tableExists($table), 'flag 0 must leave backup tables: ' . $table);
        }

        update_option(PPCartComp_Studiocart_Admin::DROP_BACKUPS_OPTION, '1');
        ppcartcomp_maybe_drop_migration_backups_for_blog();

        foreach ($backupTables as $table) {
            $this->assertFalse($this->tableExists($table), 'flag 1 must drop backup table: ' . $table);
        }
        $this->assertTrue($this->tableExists($taxTable));
        $this->assertSame('keep', get_option('_sc_it331_marker'));
        $this->assertSame('1', (string) get_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME));
        $this->assertEquals($migrationState, get_option('ppcart_cpt_slug_migration'));
        $this->assertSame('IT-331 leftover', get_post($this->leftoverPostId)->post_title);
        $this->assertSame('keep', get_post_meta($this->leftoverPostId, '_sc_it331_marker', true));
        $this->assertNull(
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Assert the flag option row is gone.
            $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT option_id FROM {$wpdb->options} WHERE option_name = %s",
                    PPCartComp_Studiocart_Admin::DROP_BACKUPS_OPTION
                )
            )
        );

        update_option(PPCartComp_Studiocart_Admin::DROP_BACKUPS_OPTION, '1');
        ppcartcomp_maybe_drop_migration_backups_for_blog();
        foreach ($backupTables as $table) {
            $this->assertFalse($this->tableExists($table), 'IF EXISTS must be fine when tables are already gone: ' . $table);
        }
        $this->assertTrue($this->tableExists($taxTable));
    }

    /**
     * @param string $table
     * @return bool
     */
    private function tableExists(string $table): bool
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Test assertion.
        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));

        return $found === $table;
    }
}
