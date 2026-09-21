<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Cpt_Slug_Migrator;

class CptSlugMigrationTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var int
     */
    private $userId = 0;

    /**
     * @var int
     */
    private $postId = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userId = self::factory()->user->create([ 'role' => 'administrator' ]);
        wp_set_current_user($this->userId);
    }

    protected function tearDown(): void
    {
        if ($this->postId) {
            wp_delete_post($this->postId, true);
        }

        delete_option(ppcart_cpt_slug_migration_option_name());
        delete_transient(PPCartComp_Cpt_Slug_Migrator::STEP1_LOCK);
        if (current_user_can('manage_options')) {
            PPCartComp_Cpt_Slug_Migrator::delete_backup();
        }
        ppcart_cpt_slug_migration_maybe_seed_state();

        parent::tearDown();
    }

    /**
     * @test-id IT-269
     */
    public function test_IT_269_seed_does_not_rename_leftover_posts(): void
    {
        $this->postId = wp_insert_post(
            [
                'post_title'  => 'Leftover product',
                'post_type'   => 'sc_product',
                'post_status' => 'publish',
            ]
        );

        $this->assertNotEmpty($this->postId);
        $this->assertSame('sc_product', get_post_type($this->postId));

        delete_option(ppcart_cpt_slug_migration_option_name());
        ppcart_cpt_slug_migration_maybe_seed_state();

        $this->assertSame('sc_product', get_post_type($this->postId));
        $this->assertSame('legacy', ppcart_cpt_slug_migration_get_state()['mode']);
        $this->assertFalse(ppcart_cpt_slug_migration_step1_complete());
    }

    /**
     * @test-id IT-269
     */
    public function test_IT_269_step1_chunk_renames_a_leftover_product_post(): void
    {
        $this->postId = wp_insert_post(
            [
                'post_title'  => 'Leftover product',
                'post_type'   => 'sc_product',
                'post_status' => 'publish',
            ]
        );

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

        $result = PPCartComp_Cpt_Slug_Migrator::run_step1_chunk();

        $this->assertTrue($result['ok']);
        $this->assertSame('ppcart_product', get_post_type($this->postId));
    }

    /**
     * @test-id IT-269
     */
    public function test_IT_269_restore_step1_renames_product_back_and_does_not_ack(): void
    {
        $this->postId = wp_insert_post(
            [
                'post_title'  => 'Leftover product',
                'post_type'   => 'sc_product',
                'post_status' => 'publish',
            ]
        );

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

        $result = PPCartComp_Cpt_Slug_Migrator::run_step1_chunk();
        $this->assertTrue($result['ok']);
        $this->assertSame('ppcart_product', get_post_type($this->postId));

        $restore = PPCartComp_Cpt_Slug_Migrator::restore_step1();
        $this->assertTrue($restore['ok']);
        $this->assertSame('sc_product', get_post_type($this->postId));

        $state = ppcart_cpt_slug_migration_get_state();
        $this->assertNotSame('later', $state['ack']);
        $this->assertSame('idle', $state['step1']);
        $this->assertSame('legacy', $state['mode']);
    }

    /**
     * @test-id IT-269
     */
    public function test_IT_269_seed_keeps_legacy_when_leftover_role_exists(): void
    {
        add_role('sc_cart_manager', 'Cart Manager', [ 'read' => true ]);

        delete_option(ppcart_cpt_slug_migration_option_name());
        ppcart_cpt_slug_migration_maybe_seed_state();

        $this->assertSame('legacy', ppcart_cpt_slug_migration_get_state()['mode']);
        $this->assertFalse(ppcart_cpt_slug_migration_step1_complete());

        remove_role('sc_cart_manager');
    }

    /**
     * @test-id IT-369
     */
    public function test_IT_369_step1_prefixes_leftover_order_post_status(): void
    {
        $this->postId = wp_insert_post(
            [
                'post_title'  => 'Leftover paid order',
                'post_type'   => 'sc_order',
                'post_status' => 'paid',
            ]
        );

        $this->assertNotEmpty($this->postId);
        $this->assertSame('sc_order', get_post_type($this->postId));
        $this->assertSame('paid', get_post_status($this->postId));

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

        for ($i = 0; $i < 20; $i++) {
            $result = PPCartComp_Cpt_Slug_Migrator::run_step1_chunk();
            $this->assertTrue($result['ok']);
            if ('ppcart_order' === get_post_type($this->postId)) {
                break;
            }
            if (! empty($result['done'])) {
                break;
            }
        }

        $this->assertSame('ppcart_order', get_post_type($this->postId));
        $this->assertSame('ppcart_paid', get_post_status($this->postId));

        $restore = PPCartComp_Cpt_Slug_Migrator::restore_step1();
        $this->assertTrue($restore['ok']);
        $this->assertSame('sc_order', get_post_type($this->postId));
        $this->assertSame('paid', get_post_status($this->postId));
    }
}
