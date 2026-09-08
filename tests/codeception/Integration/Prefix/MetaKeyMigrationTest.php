<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Meta_Key_Migrator;
use PPCart_Order;
use PPCartComp_Studiocart_Compatibility_Mode;

class MetaKeyMigrationTest extends WPTestCase
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
     * @var array<int, int>
     */
    private $postIds = [];

    /**
     * @var array<int, int>
     */
    private $userIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->userId = (int) self::factory()->user->create([ 'role' => 'administrator' ]);
        wp_set_current_user($this->userId);
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->resetCompatibilityModeFlags();
        $this->saveLegacyIdleMetaState();
    }

    protected function tearDown(): void
    {
        foreach ($this->postIds as $postId) {
            wp_delete_post($postId, true);
        }
        foreach ($this->userIds as $userId) {
            if ($userId !== $this->userId) {
                wp_delete_user($userId);
            }
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->removeMetadataBridges();
        $this->resetCompatibilityModeFlags();

        if (current_user_can('manage_options')) {
            PPCartComp_Meta_Key_Migrator::restore();
            PPCartComp_Meta_Key_Migrator::delete_backup();
        }
        delete_option(ppcart_meta_key_migration_option_name());
        delete_transient(PPCartComp_Meta_Key_Migrator::STEP1_LOCK);
        ppcart_meta_key_migration_maybe_seed_state();

        parent::tearDown();
    }

    /**
     * @test-id IT-281
     */
    public function test_IT_281_leftover_only_amount_hydrates_an_order(): void
    {
        $orderId = $this->insertOrderPost();
        update_post_meta($orderId, '_sc_amount', '12.50');

        $this->assertSame('12.50', ppcart_get_post_meta($orderId, 'amount', true));
        $this->assertSame('', get_post_meta($orderId, '_ppcart_amount', true));

        $order = ppcart_setup_order($orderId);
        $this->assertIsObject($order);
        $this->assertSame(12.5, (float) $order->amount);
    }

    /**
     * @test-id IT-281
     */
    public function test_IT_281_order_update_writes_canonical_meta_only(): void
    {
        $orderId = $this->insertOrderPost();
        update_post_meta($orderId, '_sc_amount', '9.00');

        $order = new PPCart_Order($orderId);
        $order->amount = '15.00';
        $order->id     = $orderId;
        PPCart_Order::update($order);

        $this->assertSame('15.00', get_post_meta($orderId, '_ppcart_amount', true));
        $this->assertSame('', get_post_meta($orderId, '_sc_amount', true));
        $this->assertSame('15.00', ppcart_get_post_meta($orderId, 'amount', true));
    }

    /**
     * @test-id IT-281
     */
    public function test_IT_281_canonical_meta_wins_when_both_keys_exist(): void
    {
        $orderId = $this->insertOrderPost();
        update_post_meta($orderId, '_sc_amount', '1.00');
        update_post_meta($orderId, '_ppcart_amount', '2.00');

        $this->assertSame('2.00', ppcart_get_post_meta($orderId, 'amount', true));
    }

    /**
     * @test-id IT-281
     */
    public function test_IT_281_compat_mode_reads_canonical_after_leftover_meta_is_gone(): void
    {
        $orderId = $this->insertOrderPost();
        update_post_meta($orderId, '_ppcart_amount', '7.25');
        delete_post_meta($orderId, '_sc_amount');

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_metadata_bridges();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_metadata_registered());
        $this->assertSame('7.25', get_post_meta($orderId, '_sc_amount', true));
    }

    /**
     * @test-id IT-281
     */
    public function test_IT_281_user_meta_phone_follows_the_same_rules(): void
    {
        $profileId        = (int) self::factory()->user->create([ 'role' => 'subscriber' ]);
        $this->userIds[]  = $profileId;

        update_user_meta($profileId, '_sc_phone', '555-0100');
        $this->assertSame('555-0100', ppcart_get_user_meta($profileId, 'phone', true));

        ppcart_update_user_meta($profileId, 'phone', '555-0199');
        $this->assertSame('555-0199', get_user_meta($profileId, '_ppcart_phone', true));
        $this->assertSame('', get_user_meta($profileId, '_sc_phone', true));

        update_user_meta($profileId, '_sc_phone', 'old');
        $this->assertSame('555-0199', ppcart_get_user_meta($profileId, 'phone', true));
    }

    /**
     * @test-id IT-281
     */
    public function test_IT_281_migrator_copies_leftover_only_and_does_not_overwrite_canonical(): void
    {
        $leftoverOnly = $this->insertOrderPost();
        $bothKeys     = $this->insertOrderPost();
        update_post_meta($leftoverOnly, '_sc_amount', '3.00');
        update_post_meta($bothKeys, '_sc_amount', 'old');
        update_post_meta($bothKeys, '_ppcart_amount', 'keep');

        $this->saveLegacyIdleMetaState();

        $result = [ 'ok' => false ];
        for ($i = 0; $i < 50; $i++) {
            $result = PPCartComp_Meta_Key_Migrator::run_chunk();
            $this->assertTrue(! empty($result['ok']), wp_json_encode($result));
            if (! empty($result['done']) || ! empty($result['paused'])) {
                break;
            }
        }

        wp_cache_delete($leftoverOnly, 'post_meta');
        wp_cache_delete($bothKeys, 'post_meta');

        $this->assertSame('3.00', get_post_meta($leftoverOnly, '_ppcart_amount', true));
        $this->assertSame('', get_post_meta($leftoverOnly, '_sc_amount', true));
        $this->assertSame('keep', get_post_meta($bothKeys, '_ppcart_amount', true));
        $this->assertSame('', get_post_meta($bothKeys, '_sc_amount', true));
    }

    /**
     * @test-id IT-281
     */
    public function test_IT_281_get_prefix_is_ppcart(): void
    {
        global $ppcart_public;

        $this->assertIsObject($ppcart_public);
        $this->assertSame('ppcart_', $ppcart_public->prefix);
    }

    /**
     * @test-id IT-281
     */
    public function test_IT_281_compat_mode_update_does_not_wipe_canonical(): void
    {
        $orderId = $this->insertOrderPost();
        update_post_meta($orderId, '_sc_amount', '9.00');

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_metadata_bridges();

        ppcart_update_post_meta($orderId, 'amount', '12.00');

        $this->assertSame('12.00', get_post_meta($orderId, '_ppcart_amount', true));

        $this->removeMetadataBridges();
        wp_cache_delete($orderId, 'post_meta');

        $this->assertSame('12.00', get_post_meta($orderId, '_ppcart_amount', true));
        $this->assertSame('', get_post_meta($orderId, '_sc_amount', true));
    }

    /**
     * @test-id IT-281
     */
    public function test_IT_281_migrator_renames_product_and_profile_leftover_keys(): void
    {
        $productId = $this->insertProductPost();
        $userId    = (int) self::factory()->user->create();
        $this->userIds[] = $userId;

        update_post_meta($productId, '_sc_pay_options', [ [ 'option_id' => 'plan_one' ] ]);
        update_post_meta($productId, '_sc_shipping_single', '9.99');
        update_user_meta($userId, '_sc_address_1', '123 Main');
        update_user_meta($userId, '_sc_phone', '555-0100');

        $this->saveLegacyIdleMetaState();

        $result = [ 'ok' => false ];
        for ($i = 0; $i < 50; $i++) {
            $result = PPCartComp_Meta_Key_Migrator::run_chunk();
            $this->assertTrue(! empty($result['ok']), wp_json_encode($result));
            if (! empty($result['done']) || ! empty($result['paused'])) {
                break;
            }
        }

        wp_cache_delete($productId, 'post_meta');
        wp_cache_delete($userId, 'user_meta');

        $this->assertSame(
            [ [ 'option_id' => 'plan_one' ] ],
            get_post_meta($productId, '_ppcart_pay_options', true)
        );
        $this->assertSame('9.99', get_post_meta($productId, '_ppcart_shipping_single', true));
        $this->assertSame('', get_post_meta($productId, '_sc_pay_options', true));
        $this->assertSame('', get_post_meta($productId, '_sc_shipping_single', true));

        $this->assertSame('123 Main', get_user_meta($userId, '_ppcart_address_1', true));
        $this->assertSame('555-0100', get_user_meta($userId, '_ppcart_phone', true));
        $this->assertSame('', get_user_meta($userId, '_sc_address_1', true));
        $this->assertSame('', get_user_meta($userId, '_sc_phone', true));
    }

    /**
     * @return int
     */
    private function insertOrderPost(): int
    {
        $id = (int) wp_insert_post(
            [
                'post_title'  => 'Meta key order',
                'post_type'   => ppcart_live_post_type('order'),
                'post_status' => 'paid',
            ]
        );

        $this->assertGreaterThan(0, $id);
        $this->postIds[] = $id;

        return $id;
    }

    /**
     * @return int
     */
    private function insertProductPost(): int
    {
        $id = (int) wp_insert_post(
            [
                'post_title'  => 'Meta key product',
                'post_type'   => ppcart_live_post_type('product'),
                'post_status' => 'publish',
            ]
        );

        $this->assertGreaterThan(0, $id);
        $this->postIds[] = $id;

        return $id;
    }

    /**
     * @return void
     */
    private function saveLegacyIdleMetaState(): void
    {
        ppcart_meta_key_migration_save_state(
            [
                'ack'            => '',
                'mode'           => 'legacy',
                'step1'          => 'idle',
                'stop_requested' => false,
                'cursor'         => null,
            ]
        );
    }

    /**
     * @return void
     */
    private function resetCompatibilityModeFlags(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);
        foreach ([ 'legacy_options_registered', 'option_storage_bridges_registered', 'legacy_metadata_registered' ] as $property_name) {
            if (! $reflection->hasProperty($property_name)) {
                continue;
            }
            $property = $reflection->getProperty($property_name);
            $property->setAccessible(true);
            $property->setValue(null, false);
        }
    }

    /**
     * @return void
     */
    private function removeMetadataBridges(): void
    {
        $filters = [
            'get_post_metadata'    => 'ppcartcomp_get_post_metadata',
            'update_post_metadata' => 'ppcartcomp_update_post_metadata',
            'delete_post_metadata' => 'ppcartcomp_delete_post_metadata',
            'add_post_metadata'    => 'ppcartcomp_add_post_metadata',
            'get_user_metadata'    => 'ppcartcomp_get_user_metadata',
            'update_user_metadata' => 'ppcartcomp_update_user_metadata',
            'delete_user_metadata' => 'ppcartcomp_delete_user_metadata',
            'add_user_metadata'    => 'ppcartcomp_add_user_metadata',
        ];

        foreach ($filters as $hook => $callback) {
            remove_filter($hook, $callback, 10);
        }
    }
}
