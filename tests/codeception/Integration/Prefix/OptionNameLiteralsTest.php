<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCart_Secrets;
use PPCartComp_Studiocart_Compatibility_Mode;

class OptionNameLiteralsTest extends WPTestCase
{
    /**
     * @var \IntegrationTester
     */
    protected $tester;

    protected function setUp(): void
    {
        parent::setUp();

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->deleteOptionRow('_sc_mailchimp_api');
        $this->deleteOptionRow('_ppcart_mailchimp_api');
        $this->resetSensitiveOptionCache();
    }

    protected function tearDown(): void
    {
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        $this->deleteOptionRow('_sc_mailchimp_api');
        $this->deleteOptionRow('_ppcart_mailchimp_api');
        $this->resetSensitiveOptionCache();

        parent::tearDown();
    }

    /**
     * @test-id IT-348
     */
    public function test_IT_348_leftover_option_name_sensitivity_and_storage_bridge(): void
    {
        $this->seedLeftoverOption('_sc_mailchimp_api', 'mc-leftover-key');

        $this->assertFalse(ppcart_is_sensitive_option('_sc_mailchimp_api'));
        $this->assertTrue(ppcart_is_sensitive_option('_ppcart_mailchimp_api'));
        $this->assertSame('', PPCart_Secrets::get_raw_option_value('_ppcart_mailchimp_api'));
        $this->assertSame('mc-leftover-key', get_option('_ppcart_mailchimp_api'));
        $this->assertFalse($this->optionRowExists('_ppcart_mailchimp_api'));
        $this->assertTrue($this->optionRowExists('_sc_mailchimp_api'));

        update_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME, '1');
        $this->resetSensitiveOptionCache();

        $this->assertTrue(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertTrue(ppcart_is_sensitive_option('_sc_mailchimp_api'));
        $this->assertTrue(ppcart_is_sensitive_option('_sc_custom_api_key'));
        $this->assertFalse(ppcart_is_sensitive_option('_sc_currency'));
        $this->assertSame('', PPCart_Secrets::get_raw_option_value('_ppcart_mailchimp_api'));
        $this->assertSame('mc-leftover-key', get_option('_ppcart_mailchimp_api'));
        $this->assertFalse($this->optionRowExists('_ppcart_mailchimp_api'));
        $this->assertTrue($this->optionRowExists('_sc_mailchimp_api'));
    }

    private function seedLeftoverOption(string $option_name, string $value): void
    {
        global $wpdb;

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Test seeds leftover storage directly.
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

        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching -- Test cleanup for seeded option rows.
        $wpdb->delete($wpdb->options, [ 'option_name' => $option_name ], [ '%s' ]);
        wp_cache_delete($option_name, 'options');
        wp_cache_delete('alloptions', 'options');
        wp_cache_delete('notoptions', 'options');
    }

    private function optionRowExists(string $option_name): bool
    {
        return null !== ppcart_get_option_storage_row($option_name);
    }

    private function resetSensitiveOptionCache(): void
    {
        $reflection = new \ReflectionClass(PPCart_Secrets::class);
        $cache      = $reflection->getProperty('sensitive_option_names_cache');
        $cache->setAccessible(true);
        $cache->setValue(null, null);
    }
}
