<?php

declare(strict_types=1);

namespace Tests\Integration\CustomTables;

use PPCart_Activator;
use PPCart_Files;
use PPCart_Order_Items;
use PPCart_Price_Format;
use PPCart_Upgrade;
use Tests\NoTransactionWPTestCase;

class CustomTableMigrationTest extends NoTransactionWPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    public function set_up()
    {
        parent::set_up();

        if (! class_exists(PPCart_Activator::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/class-ppcart-activator.php';
        }
        if (! class_exists(PPCart_Upgrade::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/class-ppcart-upgrade.php';
        }
        if (! class_exists(PPCart_Price_Format::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/class-ppcart-price-format.php';
        }
        if (! class_exists(PPCart_Order_Items::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/order-items/class-ppcart-order-items.php';
        }
        if (! class_exists(PPCart_Files::class, false)) {
            require_once PPCART_PLUGIN_ROOT . 'includes/files/class-ppcart-files.php';
        }

        $this->flushLive();
    }

    public function tear_down()
    {
        $this->restoreCanonicalCustomTables();
        parent::tear_down();
    }

    public function test_IT_300_activate_and_upgrade_do_not_rename_leftover_custom_tables(): void
    {
        $this->installLeftoverCustomTables();
        $this->insertLeftoverMarkers();

        PPCart_Activator::setup_tax_table();
        PPCart_Upgrade::setup_tax_table();
        (new PPCart_Order_Items())->setup_items_table();
        (new PPCart_Files())->setup_download_table();
        $this->flushLive();

        foreach ($this->leftoverTables() as $table) {
            $this->assertTrue($this->tableExists($table), 'leftover table must survive activate/upgrade: ' . $table);
        }
        foreach ($this->canonicalTables() as $table) {
            $this->assertFalse($this->tableExists($table), 'activate/upgrade must not create canonical via RENAME: ' . $table);
        }

        $this->assertSame('slice16-tax', $this->getTaxTitle($this->leftoverTables()['tax_rate']));
        $this->assertSame('ncs_tax_rate', ppcart_live_table_suffix('tax_rate'));
        $this->assertSame('ncs_order_items', ppcart_live_table_suffix('order_items'));
        $this->assertSame('ncs_downloads', ppcart_live_table_suffix('downloads'));
    }

    public function test_IT_301_migrate_renames_leftover_custom_tables_to_canonical_names(): void
    {
        $this->installLeftoverCustomTables();

        $result = ppcart_migrate_custom_tables();
        $this->flushLive();

        foreach ($this->leftoverTables() as $table) {
            $this->assertFalse($this->tableExists($table), 'leftover table must be renamed away: ' . $table);
        }
        foreach ($this->canonicalTables() as $table) {
            $this->assertTrue($this->tableExists($table), 'canonical table must exist after migrate: ' . $table);
        }

        $this->assertSame($this->canonicalTables()['tax_rate'], ppcart_live_table('tax_rate'));
        $this->assertSame($this->canonicalTables()['order_items'], ppcart_live_table('order_items'));
        $this->assertSame($this->canonicalTables()['order_itemmeta'], ppcart_live_table('order_itemmeta'));
        $this->assertSame($this->canonicalTables()['downloads'], ppcart_live_table('downloads'));
        $this->assertArrayHasKey('tax_rate', $result['renamed']);
        $this->assertArrayHasKey('downloads', $result['renamed']);
    }

    public function test_IT_302_migrate_alters_order_item_meta_object_id_column(): void
    {
        $this->installLeftoverCustomTables();
        $this->assertTrue($this->columnExists($this->leftoverTables()['order_itemmeta'], 'ncs_order_item_id'));

        ppcart_migrate_custom_tables();
        $this->flushLive();

        $canonicalMeta = $this->canonicalTables()['order_itemmeta'];
        $this->assertTrue($this->columnExists($canonicalMeta, 'ppcart_order_item_id'));
        $this->assertFalse($this->columnExists($canonicalMeta, 'ncs_order_item_id'));
        $this->assertSame('ppcart_order_item', ppcart_live_metadata_type());
        $this->assertSame('ppcart_order_item_id', ppcart_live_order_item_id_column());
    }

    public function test_IT_303_migrate_preserves_leftover_rows(): void
    {
        global $wpdb;

        $this->installLeftoverCustomTables();
        $this->insertLeftoverMarkers();

        ppcart_migrate_custom_tables();
        $this->flushLive();

        $this->assertSame('slice16-tax', $this->getTaxTitle($this->canonicalTables()['tax_rate']));

        $item = $wpdb->get_row(
            $wpdb->prepare(
                'SELECT product_name, total_amount FROM %i WHERE order_item_id = %d',
                $this->canonicalTables()['order_items'],
                42
            )
        );
        $this->assertNotNull($item);
        $this->assertSame('Slice 16 Item', $item->product_name);
        $this->assertSame('1.234,56', $item->total_amount);

        $download = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT order_key FROM %i WHERE download_id = %d',
                $this->canonicalTables()['downloads'],
                7
            )
        );
        $this->assertSame('slice16-download-key', $download);
    }

    public function test_IT_304_order_item_meta_round_trips_after_migration(): void
    {
        global $wpdb;

        $this->installLeftoverCustomTables();
        $this->insertLeftoverMarkers();
        $this->flushLive();
        ppcart_register_live_meta_table();

        $this->assertSame('ncs_order_item', ppcart_live_metadata_type());
        add_metadata('ncs_order_item', 42, 'unit_price', '9.99');
        $this->assertSame('9.99', get_metadata('ncs_order_item', 42, 'unit_price', true));

        ppcart_migrate_custom_tables();
        $this->flushLive();
        ppcart_register_live_meta_table();

        $this->assertFalse(isset($wpdb->ncs_order_itemmeta));
        $this->assertSame($this->canonicalTables()['order_itemmeta'], $wpdb->ppcart_order_itemmeta);
        $this->assertSame('9.99', get_metadata('ppcart_order_item', 42, 'unit_price', true));

        update_metadata('ppcart_order_item', 42, 'unit_price', '10.50');
        $this->assertSame('10.50', get_metadata('ppcart_order_item', 42, 'unit_price', true));

        $column = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT ppcart_order_item_id FROM %i WHERE meta_key = %s',
                $this->canonicalTables()['order_itemmeta'],
                'unit_price'
            )
        );
        $this->assertSame('42', (string) $column);
    }

    public function test_IT_305_price_format_updates_the_live_order_items_table(): void
    {
        global $wpdb;

        $this->installLeftoverCustomTables();
        $this->insertLeftoverMarkers();
        $this->flushLive();

        update_option('_sc_thousand_separator', '.');
        update_option('_sc_decimal_separator', ',');

        $formatter = new PPCart_Price_Format();
        $formatter->update_order_items_amount();

        $leftoverAmount = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT total_amount FROM %i WHERE order_item_id = %d',
                $this->leftoverTables()['order_items'],
                42
            )
        );
        $this->assertNotSame('1.234,56', $leftoverAmount);
        $this->assertSame('1234.560000000', $leftoverAmount);
        $this->assertFalse($this->tableExists($this->canonicalTables()['order_items']));
    }

    /**
     * @return void
     */
    private function flushLive(): void
    {
        ppcart_flush_live_table_cache();
        ppcart_register_live_meta_table();
    }

    /**
     * @return string
     */
    private function tablePrefix(): string
    {
        global $wpdb;

        return $wpdb->prefix;
    }

    /**
     * @return array<string, string>
     */
    private function leftoverTables(): array
    {
        $prefix = $this->tablePrefix();

        return [
            'tax_rate'       => $prefix . 'ncs_tax_rate',
            'order_items'    => $prefix . 'ncs_order_items',
            'order_itemmeta' => $prefix . 'ncs_order_itemmeta',
            'downloads'      => $prefix . 'ncs_downloads',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function canonicalTables(): array
    {
        $prefix = $this->tablePrefix();

        return [
            'tax_rate'       => $prefix . 'ppcart_tax_rate',
            'order_items'    => $prefix . 'ppcart_order_items',
            'order_itemmeta' => $prefix . 'ppcart_order_itemmeta',
            'downloads'      => $prefix . 'ppcart_downloads',
        ];
    }

    /**
     * @return void
     */
    private function dropAllCustomTables(): void
    {
        global $wpdb;

        foreach (array_merge($this->leftoverTables(), $this->canonicalTables()) as $table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Test isolation.
            $wpdb->query('DROP TABLE IF EXISTS `' . str_replace('`', '', $table) . '`');
        }
        $this->flushLive();
    }

    /**
     * @return void
     */
    private function installLeftoverCustomTables(): void
    {
        global $wpdb;

        $this->dropAllCustomTables();
        $collate = $wpdb->get_charset_collate();
        $tables  = $this->leftoverTables();

        $sql = [
            "CREATE TABLE IF NOT EXISTS {$tables['tax_rate']} (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                tax_rate_country tinytext NOT NULL,
                tax_rate_state tinytext NOT NULL,
                tax_rate_postcode tinytext NOT NULL,
                tax_rate_city tinytext NOT NULL,
                tax_rate tinytext NOT NULL,
                tax_rate_title tinytext NULL,
                tax_rate_priority mediumint(9) NULL,
                tax_rate_meta longtext NULL,
                PRIMARY KEY (id)
            ) $collate",
            "CREATE TABLE IF NOT EXISTS {$tables['order_items']} (
                order_item_id bigint(20) NOT NULL AUTO_INCREMENT,
                order_id bigint(20) NOT NULL,
                product_id bigint(20) NOT NULL,
                price_id varchar(255) NOT NULL,
                item_type text,
                product_name text,
                price_name text,
                total_amount text,
                tax_amount text,
                PRIMARY KEY (order_item_id)
            ) $collate",
            "CREATE TABLE IF NOT EXISTS {$tables['order_itemmeta']} (
                meta_id bigint(20) NOT NULL AUTO_INCREMENT,
                ncs_order_item_id bigint(20) NOT NULL,
                meta_key varchar(255),
                meta_value longtext,
                PRIMARY KEY (meta_id)
            ) $collate",
            "CREATE TABLE IF NOT EXISTS {$tables['downloads']} (
                download_id bigint(20) NOT NULL AUTO_INCREMENT,
                file_id varchar(20) NOT NULL,
                order_id bigint(20) NOT NULL,
                order_key varchar(64) UNIQUE NOT NULL,
                product_id bigint(20) NOT NULL,
                access_granted TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                download_expires datetime,
                downloads_remaining varchar(20),
                downloads varchar(2000),
                PRIMARY KEY (download_id)
            ) $collate",
        ];

        foreach ($sql as $statement) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.DirectDatabaseQuery.SchemaChange -- Test fixture schema.
            $wpdb->query($statement);
        }

        foreach ($tables as $table) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Reset leftover fixture rows.
            $wpdb->query('TRUNCATE TABLE `' . str_replace('`', '', $table) . '`');
        }

        $this->flushLive();
    }

    /**
     * @return void
     */
    private function insertLeftoverMarkers(): void
    {
        global $wpdb;

        $tables = $this->leftoverTables();

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Test fixture.
        $wpdb->insert(
            $tables['tax_rate'],
            [
                'tax_rate_country'  => 'US',
                'tax_rate_state'    => 'CA',
                'tax_rate_postcode' => '94105',
                'tax_rate_city'     => 'San Francisco',
                'tax_rate'          => '8.5',
                'tax_rate_title'    => 'slice16-tax',
                'tax_rate_priority' => 1,
            ]
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Test fixture.
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO %i (order_item_id, order_id, product_id, price_id, item_type, product_name, price_name, total_amount, tax_amount) VALUES (%d, %d, %d, %s, %s, %s, %s, %s, %s)",
                $tables['order_items'],
                42,
                9,
                3,
                'main',
                'main',
                'Slice 16 Item',
                'Default',
                '1.234,56',
                '0'
            )
        );

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery -- Test fixture.
        $wpdb->query(
            $wpdb->prepare(
                "INSERT INTO %i (download_id, file_id, order_id, order_key, product_id, downloads_remaining) VALUES (%d, %s, %d, %s, %d, %s)",
                $tables['downloads'],
                7,
                'file1',
                9,
                'slice16-download-key',
                3,
                'unlimited'
            )
        );
    }

    /**
     * @return void
     */
    private function restoreCanonicalCustomTables(): void
    {
        $this->dropAllCustomTables();
        PPCart_Activator::setup_tax_table();
        (new PPCart_Order_Items())->setup_items_table();
        (new PPCart_Files())->setup_download_table();
        $this->flushLive();
        delete_option(ppcart_custom_table_migration_option());
        delete_option('_sc_thousand_separator');
        delete_option('_sc_decimal_separator');
        delete_option('sc_price_formatted');
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

    /**
     * @param string $table
     * @param string $column
     * @return bool
     */
    private function columnExists(string $table, string $column): bool
    {
        global $wpdb;

        if (! $this->tableExists($table)) {
            return false;
        }

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Test assertion.
        $row = $wpdb->get_row($wpdb->prepare('SHOW COLUMNS FROM %i LIKE %s', $table, $column));

        return ! empty($row);
    }

    /**
     * @param string $table
     * @return string
     */
    private function getTaxTitle(string $table): string
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Test assertion.
        $title = $wpdb->get_var(
            $wpdb->prepare(
                'SELECT tax_rate_title FROM %i WHERE tax_rate_title = %s',
                $table,
                'slice16-tax'
            )
        );

        return is_string($title) ? $title : '';
    }
}
