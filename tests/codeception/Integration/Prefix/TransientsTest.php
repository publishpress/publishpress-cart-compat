<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Studiocart_Compatibility_Mode;

class TransientsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->resetCompatibilityModeFlags();
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_option_storage_bridges();
        $this->clearSliceTransients();
    }

    protected function tearDown(): void
    {
        $this->clearSliceTransients();
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->resetCompatibilityModeFlags();
        parent::tearDown();
    }

    /**
     * @test-id IT-316
     */
    public function test_IT_316_leftover_transient_rows_read_without_mutating_then_explicit_copy(): void
    {
        $this->seedLeftoverTransient('sc_stripe_evt_evt_copy_slice24', '1');

        $this->assertSame('1', get_transient('ppcart_stripe_evt_evt_copy_slice24'));
        $this->assertNotNull(ppcart_get_option_storage_row('_transient_sc_stripe_evt_evt_copy_slice24'));
        $this->assertNull(ppcart_get_option_storage_row('_transient_ppcart_stripe_evt_evt_copy_slice24'));

        $copied = ppcart_maybe_copy_leftover_option_rows(true);

        $this->assertGreaterThan(0, $copied);
        $this->assertSame('1', get_transient('ppcart_stripe_evt_evt_copy_slice24'));
        $this->assertNull(ppcart_get_option_storage_row('_transient_sc_stripe_evt_evt_copy_slice24'));
        $this->assertNull(ppcart_get_option_storage_row('_transient_timeout_sc_stripe_evt_evt_copy_slice24'));
        $this->assertNotNull(ppcart_get_option_storage_row('_transient_ppcart_stripe_evt_evt_copy_slice24'));
        $this->assertNotNull(ppcart_get_option_storage_row('_transient_timeout_ppcart_stripe_evt_evt_copy_slice24'));
    }

    /**
     * @test-id IT-316
     */
    public function test_IT_316_compatibility_mode_reads_leftover_transient_names_from_canonical_rows(): void
    {
        set_transient('ppcart_stripe_evt_evt_compat_slice24', '1', HOUR_IN_SECONDS);

        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertFalse(get_transient('sc_stripe_evt_evt_compat_slice24'));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        PPCartComp_Studiocart_Compatibility_Mode::maybe_register_options();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertSame('1', get_transient('sc_stripe_evt_evt_compat_slice24'));
        $this->assertSame('1', get_transient('ppcart_stripe_evt_evt_compat_slice24'));
        $this->assertNull(ppcart_get_option_storage_row('_transient_sc_stripe_evt_evt_compat_slice24'));
    }

    private function seedLeftoverTransient(string $key, string $value): void
    {
        global $wpdb;

        $timeout = (string) (time() + HOUR_IN_SECONDS);

        foreach (
            [
                '_transient_' . $key => $value,
                '_transient_timeout_' . $key => $timeout,
            ] as $option_name => $option_value
        ) {
            // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Test seeds leftover transient storage directly.
            $wpdb->insert(
                $wpdb->options,
                [
                    'option_name'  => $option_name,
                    'option_value' => $option_value,
                    'autoload'     => 'no',
                ],
                [ '%s', '%s', '%s' ]
            );
            wp_cache_delete($option_name, 'options');
        }

        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
    }

    private function clearSliceTransients(): void
    {
        $keys = [
            'ppcart_stripe_evt_evt_prefix_slice24',
            'sc_stripe_evt_evt_prefix_slice24',
            'ppcart_hosted_session_cs_prefix_slice24',
            'ppcart_stripe_evt_evt_copy_slice24',
            'sc_stripe_evt_evt_copy_slice24',
            'ppcart_stripe_evt_evt_compat_slice24',
            'sc_stripe_evt_evt_compat_slice24',
        ];

        foreach ($keys as $key) {
            delete_transient($key);
            foreach ([ '_transient_' . $key, '_transient_timeout_' . $key ] as $option_name) {
                if (function_exists('ppcart_get_option_storage_row') && null !== ppcart_get_option_storage_row($option_name)) {
                    global $wpdb;
                    // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching -- Test cleanup for leftover transient option rows.
                    $wpdb->delete($wpdb->options, [ 'option_name' => $option_name ], [ '%s' ]);
                    wp_cache_delete($option_name, 'options');
                }
            }
        }
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
