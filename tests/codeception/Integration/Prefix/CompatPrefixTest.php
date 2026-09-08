<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class CompatPrefixTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    /**
     * @var array<int, int>
     */
    private $postIds = [];

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->resetCompatibilityModeFlags();
        $this->removeMetadataBridges();
    }

    protected function tearDown(): void
    {
        foreach ($this->postIds as $postId) {
            wp_delete_post($postId, true);
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->removeMetadataBridges();
        $this->resetCompatibilityModeFlags();

        parent::tearDown();
    }

    /**
     * @test-id IT-355
     */
    public function test_IT_355_metadata_bridges_absent_when_compat_off(): void
    {
        $this->assertFalse(has_filter('get_post_metadata', 'ppcartcomp_get_post_metadata'));
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::legacy_metadata_registered());
    }

    /**
     * @test-id IT-355
     */
    public function test_IT_355_metadata_bridges_register_ppcartcomp_callbacks_when_compat_on(): void
    {
        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_metadata_bridges();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::legacy_metadata_registered());
        $this->assertNotFalse(has_filter('get_post_metadata', 'ppcartcomp_get_post_metadata'));
        $this->assertNotFalse(has_filter('update_post_metadata', 'ppcartcomp_update_post_metadata'));
        $this->assertNotFalse(has_filter('get_user_metadata', 'ppcartcomp_get_user_metadata'));
    }

    /**
     * @test-id IT-355
     */
    public function test_IT_355_compat_on_reads_leftover_meta_via_ppcartcomp_bridge(): void
    {
        $orderId = $this->insertOrderPost();
        update_post_meta($orderId, '_sc_amount', '4.50');

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_metadata_bridges();

        $this->assertSame('4.50', get_post_meta($orderId, '_sc_amount', true));
        $this->assertSame('', get_post_meta($orderId, '_ppcart_amount', true));
    }

    /**
     * @return int
     */
    private function insertOrderPost(): int
    {
        $orderId = (int) wp_insert_post(
            [
                'post_type'   => 'ppcart_order',
                'post_status' => 'publish',
                'post_title'  => 'Compat prefix order',
            ],
            true
        );
        $this->postIds[] = $orderId;

        return $orderId;
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
