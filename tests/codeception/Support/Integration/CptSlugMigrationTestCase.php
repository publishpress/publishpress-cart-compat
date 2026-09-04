<?php

declare(strict_types=1);

namespace Tests\Support\Integration;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Cpt_Slug_Migrator;
use Exception;

class CptSlugMigrationRedirectException extends Exception
{
}

abstract class CptSlugMigrationTestCase extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var int
     */
    protected $adminUserId = 0;

    /**
     * @var array<int, int>
     */
    protected $createdPostIds = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    protected $createdTerms = [];

    /**
     * @var array<int, int>
     */
    protected $createdUserIds = [];

    /**
     * @var array<int, string>
     */
    protected $createdRoleSlugs = [];

    public const THROWAWAY_CAP_ROLE = 'ppcart_it_mig_caps';

    protected function setUp(): void
    {
        parent::setUp();

        $this->createdPostIds  = [];
        $this->createdTerms    = [];
        $this->createdUserIds  = [];
        $this->createdRoleSlugs = [];

        $this->adminUserId = (int) self::factory()->user->create([ 'role' => 'administrator' ]);
        wp_set_current_user($this->adminUserId);
    }

    protected function tearDown(): void
    {
        wp_set_current_user($this->adminUserId);

        foreach (array_reverse($this->createdPostIds) as $postId) {
            wp_delete_post($postId, true);
        }

        foreach ($this->createdTerms as $row) {
            wp_delete_term($row['term_id'], $row['taxonomy']);
            if (isset($row['legacy_taxonomy'])) {
                wp_delete_term($row['term_id'], $row['legacy_taxonomy']);
            }
            if (isset($row['canonical_taxonomy'])) {
                wp_delete_term($row['term_id'], $row['canonical_taxonomy']);
            }
        }

        foreach ($this->createdUserIds as $userId) {
            wp_delete_user($userId);
        }

        foreach ($this->createdRoleSlugs as $slug) {
            remove_role($slug);
        }

        remove_role(self::THROWAWAY_CAP_ROLE);
        remove_role('sc_cart_manager');

        delete_option(ppcart_cpt_slug_migration_option_name());
        delete_transient(PPCartComp_Cpt_Slug_Migrator::STEP1_LOCK);

        if (current_user_can('manage_options')) {
            PPCartComp_Cpt_Slug_Migrator::delete_backup();
        }

        $_GET = [];
        unset($GLOBALS['current_screen']);
        remove_filter('wp_doing_ajax', '__return_false', 999);
        remove_filter('wp_doing_rest', '__return_false', 999);
        if (function_exists('set_current_screen')) {
            set_current_screen('front');
        }

        ppcart_cpt_slug_migration_maybe_seed_state();

        parent::tearDown();
    }

    /**
     * @return void
     */
    protected function saveLegacyIdleState(): void
    {
        ppcart_cpt_slug_migration_save_state(
            [
                'ack'            => '',
                'mode'           => 'legacy',
                'step1'          => 'idle',
                'step2'          => 'idle',
                'stop_requested' => false,
                'cursor'         => null,
            ]
        );
    }

    /**
     * @param string $postType Post type.
     * @param string $title    Title.
     * @return int
     */
    protected function insertLeftoverPost(string $postType, string $title): int
    {
        $id = (int) wp_insert_post(
            [
                'post_title'  => $title,
                'post_type'   => $postType,
                'post_status' => 'publish',
            ]
        );

        $this->assertGreaterThan(0, $id);
        $this->createdPostIds[] = $id;

        return $id;
    }

    /**
     * @param string $taxonomy Leftover taxonomy slug.
     * @param string $name     Term name.
     * @return int Term taxonomy ID.
     */
    protected function insertLeftoverTerm(string $taxonomy, string $name): int
    {
        $this->ensureTaxonomy($taxonomy);

        $result = wp_insert_term($name, $taxonomy);
        $this->assertIsArray($result, wp_json_encode($result));

        $termId = (int) $result['term_id'];
        $ttId   = (int) $result['term_taxonomy_id'];
        $this->createdTerms[] = [
            'term_id'              => $termId,
            'taxonomy'             => $taxonomy,
            'legacy_taxonomy'      => $taxonomy,
            'canonical_taxonomy'   => 'sc_product_cat' === $taxonomy ? 'ppcart_product_cat' : ( 'sc_product_tag' === $taxonomy ? 'ppcart_product_tag' : $taxonomy ),
        ];

        return $ttId;
    }

    /**
     * @param string $taxonomy Taxonomy.
     * @return void
     */
    protected function ensureTaxonomy(string $taxonomy): void
    {
        if (taxonomy_exists($taxonomy)) {
            return;
        }

        register_taxonomy(
            $taxonomy,
            [ 'sc_product', 'ppcart_product' ],
            [
                'public' => false,
            ]
        );
    }

    /**
     * @return int
     */
    protected function insertLeftoverProductTemplate(): int
    {
        return $this->insertLeftoverPostWithName('wp_template', 'single-sc_product', 'Single leftover product');
    }

    /**
     * @param string $postType Post type.
     * @param string $name     Post name.
     * @param string $title    Title.
     * @return int
     */
    protected function insertLeftoverPostWithName(string $postType, string $name, string $title): int
    {
        $id = (int) wp_insert_post(
            [
                'post_title'  => $title,
                'post_name'   => $name,
                'post_type'   => $postType,
                'post_status' => 'publish',
            ]
        );

        $this->assertGreaterThan(0, $id);
        $this->createdPostIds[] = $id;

        return $id;
    }

    /**
     * Throwaway role with one leftover capability. Never touches administrator.
     *
     * @return string
     */
    protected function insertThrowawayRoleWithLeftoverCap(): string
    {
        remove_role(self::THROWAWAY_CAP_ROLE);
        add_role(self::THROWAWAY_CAP_ROLE, 'IT migration caps', [ 'read' => true ]);
        $this->createdRoleSlugs[] = self::THROWAWAY_CAP_ROLE;

        $role = get_role(self::THROWAWAY_CAP_ROLE);
        $this->assertNotNull($role);
        $role->add_cap('sc_manager_option');

        return self::THROWAWAY_CAP_ROLE;
    }

    /**
     * @return int User ID assigned only to leftover cart manager.
     */
    protected function insertLeftoverCartManagerUser(): int
    {
        add_role('sc_cart_manager', 'Cart Manager', [ 'read' => true ]);
        $this->createdRoleSlugs[] = 'sc_cart_manager';

        $userId = (int) self::factory()->user->create([ 'role' => 'sc_cart_manager' ]);
        $this->createdUserIds[] = $userId;

        return $userId;
    }

    /**
     * @return array<string, mixed>
     */
    protected function runStep1UntilDone(): array
    {
        $result = [ 'ok' => false ];

        for ($i = 0; $i < 50; $i++) {
            $result = PPCartComp_Cpt_Slug_Migrator::run_step1_chunk();
            $this->assertTrue(! empty($result['ok']), wp_json_encode($result));
            if (! empty($result['done']) || ! empty($result['paused'])) {
                return $result;
            }
        }

        $this->fail('step 1 did not finish within 50 chunks: ' . wp_json_encode($result));
    }

    /**
     * @param array<int, int> $postIds Post IDs.
     * @return void
     */
    protected function cleanPostCaches(array $postIds): void
    {
        foreach ($postIds as $postId) {
            clean_post_cache($postId);
        }
    }

    /**
     * @return int
     */
    protected function backupRowCount(): int
    {
        global $wpdb;

        $table = PPCartComp_Cpt_Slug_Migrator::table_name();
        $like  = $wpdb->esc_like($table);
        $found = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $like));
        if (! $found) {
            return 0;
        }

        return (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
    }

    /**
     * @param int $termTaxonomyId Term taxonomy ID.
     * @return string
     */
    protected function termTaxonomySlug(int $termTaxonomyId): string
    {
        global $wpdb;

        $slug = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT taxonomy FROM {$wpdb->term_taxonomy} WHERE term_taxonomy_id = %d",
                $termTaxonomyId
            )
        );

        return is_string($slug) ? $slug : '';
    }

    /**
     * WPLoader is a front-end bootstrap; stub a screen so is_admin() is true.
     *
     * @return void
     */
    protected function forceAdminContext(): void
    {
        $GLOBALS['current_screen'] = new class {
            /**
             * @param string|null $admin Admin scope.
             * @return bool
             */
            public function in_admin($admin = null)
            {
                return true;
            }
        };

        add_filter('wp_doing_ajax', '__return_false', 999);
        add_filter('wp_doing_rest', '__return_false', 999);
    }

    /**
     * Catch wp_safe_redirect() without hitting exit.
     *
     * @param callable $callback Callback that should redirect.
     * @return string Redirect location.
     */
    protected function captureRedirect(callable $callback): string
    {
        $location = '';
        $handler  = static function ($url) use (&$location) {
            $location = (string) $url;
            throw new CptSlugMigrationRedirectException('ppcart_migration_redirect');
        };

        add_filter('wp_redirect', $handler, 10, 1);

        try {
            $callback();
            $this->fail('expected wp_redirect');
        } catch (CptSlugMigrationRedirectException $e) {
            $this->assertSame('ppcart_migration_redirect', $e->getMessage());
        } finally {
            remove_filter('wp_redirect', $handler, 10);
        }

        $this->assertNotSame('', $location);

        return $location;
    }
}
