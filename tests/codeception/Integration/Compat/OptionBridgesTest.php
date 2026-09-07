<?php

declare(strict_types=1);

namespace Tests\Integration\Compat;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCart_Secrets;
use PPCartComp_Studiocart_Compatibility_Mode;

class OptionBridgesTest extends WPTestCase
{
  /**
   * @var \IntegrationTester
   */
    protected $tester;

    private const LEGACY_OPTION = 'sc_admin_email';
    private const CANONICAL_OPTION = 'ppcart_admin_email';

    protected function setUp(): void
    {
        parent::setUp();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->deleteOptionRow(self::LEGACY_OPTION);
        $this->deleteOptionRow(self::CANONICAL_OPTION);
        $this->deleteOptionRow(PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME);
        $this->deleteOptionRow('_sc_stripe_test_sk');
        $this->deleteOptionRow('_ppcart_stripe_test_sk');
        $this->deleteOptionRow(ppcart_option_key_migration_option());
        $this->resetCompatibilityModeFlags();
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_option_storage_bridges();
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->deleteOptionRow(self::LEGACY_OPTION);
        $this->deleteOptionRow(self::CANONICAL_OPTION);
        $this->deleteOptionRow(PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME);
        $this->deleteOptionRow('_sc_stripe_test_sk');
        $this->deleteOptionRow('_ppcart_stripe_test_sk');
        $this->deleteOptionRow(ppcart_option_key_migration_option());
        parent::tearDown();
    }

    /**
     * @test-id IT-258
     */
    public function test_IT_258_canonical_reads_and_writes_use_canonical_storage_rows(): void
    {
        update_option(self::CANONICAL_OPTION, 'canonical-admin@example.test');

        $this->assertTrue($this->optionRowExists(self::CANONICAL_OPTION));
        $this->assertFalse($this->optionRowExists(self::LEGACY_OPTION));
        $this->assertSame('canonical-admin@example.test', get_option(self::CANONICAL_OPTION));

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertFalse(get_option(self::LEGACY_OPTION));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_options();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertSame('canonical-admin@example.test', get_option(self::LEGACY_OPTION));

        update_option(self::CANONICAL_OPTION, 'updated-admin@example.test');

        $this->assertSame('updated-admin@example.test', ppcart_read_option_from_db(self::CANONICAL_OPTION));
        $this->assertFalse($this->optionRowExists(self::LEGACY_OPTION));
        $this->assertSame('updated-admin@example.test', get_option(self::LEGACY_OPTION));
    }

    /**
     * @test-id IT-280
     */
    public function test_IT_280_leftover_only_option_row_reads_without_mutating(): void
    {
        $this->seedLegacyOption(self::LEGACY_OPTION, 'leftover-admin@example.test');

        $this->assertSame('leftover-admin@example.test', get_option(self::CANONICAL_OPTION));
        $this->assertFalse($this->optionRowExists(self::CANONICAL_OPTION));
        $this->assertTrue($this->optionRowExists(self::LEGACY_OPTION));
    }

    /**
     * @test-id IT-280
     */
    public function test_IT_280_boot_does_not_copy_leftover_option_rows(): void
    {
        $this->seedLegacyOption(self::LEGACY_OPTION, 'leftover-admin@example.test');

        $this->resetCompatibilityModeFlags();
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_option_storage_bridges();

        $this->assertFalse($this->optionRowExists(self::CANONICAL_OPTION));
        $this->assertTrue($this->optionRowExists(self::LEGACY_OPTION));
        $this->assertSame('leftover-admin@example.test', get_option(self::CANONICAL_OPTION));
        $this->assertTrue($this->optionRowExists(self::LEGACY_OPTION));
    }

    /**
     * @test-id IT-280
     */
    public function test_IT_280_canonical_row_wins_when_both_option_rows_exist(): void
    {
        $this->seedLegacyOption(self::CANONICAL_OPTION, 'canonical-admin@example.test');
        $this->seedLegacyOption(self::LEGACY_OPTION, 'leftover-admin@example.test');

        $this->assertSame('canonical-admin@example.test', get_option(self::CANONICAL_OPTION));
        $this->assertTrue($this->optionRowExists(self::LEGACY_OPTION));

        $copied = ppcart_maybe_copy_leftover_option_rows(true);

        $this->assertGreaterThan(0, $copied);
        $this->assertSame('canonical-admin@example.test', ppcart_read_option_from_db(self::CANONICAL_OPTION));
        $this->assertFalse($this->optionRowExists(self::LEGACY_OPTION));
        $this->assertSame('canonical-admin@example.test', get_option(self::CANONICAL_OPTION));
    }

    /**
     * @test-id IT-280
     */
    public function test_IT_280_compat_mode_reads_canonical_after_explicit_option_migration(): void
    {
        $this->seedLegacyOption(self::LEGACY_OPTION, 'leftover-admin@example.test');
        $copied = ppcart_maybe_copy_leftover_option_rows(true);

        $this->assertGreaterThan(0, $copied);
        $this->assertTrue($this->optionRowExists(self::CANONICAL_OPTION));
        $this->assertFalse($this->optionRowExists(self::LEGACY_OPTION));
        $this->assertSame('leftover-admin@example.test', get_option(self::CANONICAL_OPTION));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_options();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertSame('leftover-admin@example.test', get_option(self::LEGACY_OPTION));
    }

    /**
     * @test-id IT-280
     */
    public function test_IT_280_leftover_compat_toggle_is_excluded_from_option_copier(): void
    {
        $this->seedLegacyOption(PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME, '1');

        ppcart_maybe_copy_leftover_option_rows(true);

        $this->assertTrue($this->optionRowExists(PPCartComp_Studiocart_Compatibility_Mode::LEGACY_OPTION_NAME));
        $this->assertFalse($this->optionRowExists(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME));
    }

    /**
     * @test-id IT-280
     */
    public function test_IT_280_secret_ciphertext_reads_then_migrates_without_reencrypt(): void
    {
        $blob = 'PPCARTENC:unit-test-ciphertext-blob';
        $this->seedLegacyOption('_sc_stripe_test_sk', $blob);

        $this->assertSame('', PPCart_Secrets::get_raw_option_value('_ppcart_stripe_test_sk'));
        $this->assertFalse($this->optionRowExists('_ppcart_stripe_test_sk'));
        $this->assertTrue($this->optionRowExists('_sc_stripe_test_sk'));
        $this->assertSame($blob, get_option('_ppcart_stripe_test_sk'));

        ppcart_maybe_copy_leftover_option_rows(true);

        $this->assertTrue($this->optionRowExists('_ppcart_stripe_test_sk'));
        $this->assertFalse($this->optionRowExists('_sc_stripe_test_sk'));
        $this->assertSame($blob, PPCart_Secrets::get_raw_option_value('_ppcart_stripe_test_sk'));
    }

    /**
     * @test-id IT-280
     */
    public function test_IT_280_explicit_option_key_migration_persists_completion_state(): void
    {
        $this->seedLegacyOption(self::LEGACY_OPTION, 'leftover-admin@example.test');

        $result = ppcart_migrate_option_keys();

        $this->assertGreaterThan(0, $result['copied']);
        $this->assertTrue($this->optionRowExists(self::CANONICAL_OPTION));
        $this->assertFalse($this->optionRowExists(self::LEGACY_OPTION));
        $this->assertFalse(ppcart_option_key_needs_migration());

        $state = ppcart_option_key_migration_state();
        $this->assertArrayHasKey('completed_at', $state);
        $this->assertGreaterThan(0, (int) $state['completed_at']);
    }

    private function seedLegacyOption(string $option_name, string $value): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Test seeds leftover storage directly.
        $wpdb->insert(
            $wpdb->options,
            [
                'option_name'  => $option_name,
                'option_value' => $value,
                'autoload'     => 'no',
            ],
            [ '%s', '%s', '%s' ]
        );

        wp_cache_delete($option_name, 'options');
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
    }

    private function deleteOptionRow(string $option_name): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Test cleanup for seeded option rows.
        $wpdb->delete($wpdb->options, [ 'option_name' => $option_name ], [ '%s' ]);
        wp_cache_delete($option_name, 'options');
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
    }

    private function optionRowExists(string $option_name): bool
    {
        return null !== ppcart_get_option_storage_row($option_name);
    }

    private function resetCompatibilityModeFlags(): void
    {
        $reflection = new \ReflectionClass(PPCartComp_Studiocart_Compatibility_Mode::class);

        foreach ([ 'legacy_options_registered', 'option_storage_bridges_registered' ] as $property_name) {
            $property = $reflection->getProperty($property_name);
            $property->setAccessible(true);
            $property->setValue(null, false);
        }
    }
}
