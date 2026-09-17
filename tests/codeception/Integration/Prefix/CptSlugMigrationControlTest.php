<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use PPCart_Admin_Screens;
use PPCartComp_Cpt_Slug_Migration_Admin;
use PPCartComp_Cpt_Slug_Migrator;
use PPCartComp_Studiocart_Admin;
use Tests\Support\Integration\CptSlugMigrationTestCase;

class CptSlugMigrationControlTest extends CptSlugMigrationTestCase
{
    /**
     * @test-id IT-274
     */
    public function test_IT_274_step1_is_forbidden_without_manage_options(): void
    {
        $this->saveLegacyIdleState();
        $this->insertLeftoverPost('sc_order', 'Leftover order');

        $subscriberId = (int) self::factory()->user->create([ 'role' => 'subscriber' ]);
        $this->createdUserIds[] = $subscriberId;
        wp_set_current_user($subscriberId);

        $result = PPCartComp_Cpt_Slug_Migrator::run_step1_chunk();

        $this->assertFalse($result['ok']);
        $this->assertSame('forbidden', $result['error']);
    }

    /**
     * @test-id IT-274
     */
    public function test_IT_274_stop_request_pauses_a_running_step1(): void
    {
        $this->saveLegacyIdleState();
        ppcart_cpt_slug_migration_save_state(
            [
                'step1'          => 'running',
                'stop_requested' => true,
            ]
        );

        $result = PPCartComp_Cpt_Slug_Migrator::run_step1_chunk();

        $this->assertTrue($result['ok']);
        $this->assertTrue(! empty($result['paused']));
        $this->assertSame('paused', ppcart_cpt_slug_migration_get_state()['step1']);
        $this->assertFalse((bool) ppcart_cpt_slug_migration_get_state()['stop_requested']);
    }

    /**
     * @test-id IT-274
     */
    public function test_IT_274_lock_returns_busy(): void
    {
        $this->saveLegacyIdleState();
        $this->insertLeftoverPost('sc_order', 'Leftover order');
        set_transient(PPCartComp_Cpt_Slug_Migrator::STEP1_LOCK, 1, 60);

        $result = PPCartComp_Cpt_Slug_Migrator::run_step1_chunk();

        $this->assertFalse($result['ok']);
        $this->assertSame('busy', $result['error']);
    }

    /**
     * @test-id IT-274
     */
    public function test_IT_274_ack_later_hides_leftover_notice(): void
    {
        $this->saveLegacyIdleState();
        $this->insertLeftoverPost('sc_order', 'Leftover order');

        $this->assertTrue(PPCartComp_Cpt_Slug_Migration_Admin::should_show_notice());

        ppcart_cpt_slug_migration_save_state([ 'ack' => 'later' ]);

        $this->assertFalse(PPCartComp_Cpt_Slug_Migration_Admin::should_show_notice());
    }

    /**
     * @test-id IT-274
     */
    public function test_IT_274_notice_is_hidden_on_the_data_migration_page(): void
    {
        $this->saveLegacyIdleState();
        $this->insertLeftoverPost('sc_order', 'Leftover order');

        $this->assertTrue(PPCartComp_Cpt_Slug_Migration_Admin::should_show_notice());

        $_GET['page'] = PPCartComp_Cpt_Slug_Migration_Admin::PAGE_SLUG;
        $this->assertFalse(PPCartComp_Cpt_Slug_Migration_Admin::should_show_notice());

        $_GET['page'] = PPCartComp_Studiocart_Admin::PAGE_SLUG;
        $this->assertFalse(PPCartComp_Cpt_Slug_Migration_Admin::should_show_notice());

        $_GET['page'] = PPCart_Admin_Screens::PAGE_SETTINGS;
        $this->assertTrue(PPCartComp_Cpt_Slug_Migration_Admin::should_show_notice());
    }

    /**
     * @test-id IT-274
     */
    public function test_IT_274_legacy_migration_page_redirects_to_studiocart_screen(): void
    {
        $this->forceAdminContext();
        $_GET['page'] = PPCartComp_Cpt_Slug_Migration_Admin::PAGE_SLUG;

        $location = $this->captureRedirect(
            static function () {
                PPCartComp_Cpt_Slug_Migration_Admin::maybe_redirect_legacy_migration_page();
            }
        );

        $this->assertStringContainsString('page=ppcart-studiocart', $location);
        $this->assertStringNotContainsString('#studiocart', $location);
    }

    /**
     * @test-id IT-274
     */
    public function test_IT_274_data_migration_deep_links_the_studiocart_submenu(): void
    {
        $this->assertFalse(method_exists(PPCartComp_Cpt_Slug_Migration_Admin::class, 'register_settings_section'));
        $this->assertSame('ppcart-studiocart', PPCartComp_Studiocart_Admin::PAGE_SLUG);
        $this->assertStringContainsString(
            'page=ppcart-studiocart',
            PPCartComp_Cpt_Slug_Migration_Admin::settings_url()
        );
        $this->assertStringNotContainsString('#studiocart', PPCartComp_Cpt_Slug_Migration_Admin::settings_url());
    }

    /**
     * @test-id IT-274
     */
    public function test_IT_274_legacy_admin_query_redirects_after_step1(): void
    {
        $this->saveLegacyIdleState();
        ppcart_cpt_slug_migration_save_state(
            [
                'step1' => 'complete',
                'mode'  => 'canonical',
            ]
        );
        $this->assertTrue(ppcart_cpt_slug_migration_step1_complete());
        $this->assertNotFalse(
            has_action(
                'admin_init',
                [
                    PPCartComp_Cpt_Slug_Migration_Admin::class,
                    'maybe_redirect_legacy_admin_query',
                ]
            )
        );

        $this->assertSame(
            [ 'post_type' => 'ppcart_product' ],
            PPCartComp_Cpt_Slug_Migration_Admin::legacy_admin_query_args([ 'post_type' => 'sc_product' ])
        );
        $this->assertSame(
            [ 'post_type' => 'ppcart_collection' ],
            PPCartComp_Cpt_Slug_Migration_Admin::legacy_admin_query_args([ 'post_type' => 'sc_collection' ])
        );
        $this->assertSame(
            [ 'taxonomy' => 'ppcart_product_cat' ],
            PPCartComp_Cpt_Slug_Migration_Admin::legacy_admin_query_args([ 'taxonomy' => 'sc_product_cat' ])
        );

        // CheckoutBlock preview tests define REST_REQUEST for the rest of the process.
        if (! defined('REST_REQUEST')) {
            define('REST_REQUEST', true);
        }

        $this->forceAdminContext();

        $_GET['post_type'] = 'sc_product';
        $postTypeLocation  = $this->captureRedirect(
            static function () {
                PPCartComp_Cpt_Slug_Migration_Admin::maybe_redirect_legacy_admin_query();
            }
        );
        $this->assertStringContainsString('post_type=ppcart_product', $postTypeLocation);

        unset($_GET['post_type']);
        $_GET['taxonomy'] = 'sc_product_cat';
        $taxonomyLocation = $this->captureRedirect(
            static function () {
                PPCartComp_Cpt_Slug_Migration_Admin::maybe_redirect_legacy_admin_query();
            }
        );
        $this->assertStringContainsString('taxonomy=ppcart_product_cat', $taxonomyLocation);
    }

    /**
     * @test-id IT-274
     */
    public function test_IT_274_delete_backup_drops_the_reverse_map_table(): void
    {
        global $wpdb;

        PPCartComp_Cpt_Slug_Migrator::maybe_create_table();

        $table = PPCartComp_Cpt_Slug_Migrator::table_name();
        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
        $this->assertSame($table, $found);

        $result = PPCartComp_Cpt_Slug_Migrator::delete_backup();

        $this->assertTrue($result['ok']);
        $this->assertEmpty(
            $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)))
        );
    }
}
