<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use lucatume\WPBrowser\TestCase\WPTestCase;
use PPCartComp_Shortcode_Tag_Migrator;
use PPCartComp_Studiocart_Compatibility_Mode;

class ShortcodeTagMigrationTest extends WPTestCase
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

    protected function setUp(): void
    {
        parent::setUp();

        $this->userId = (int) self::factory()->user->create([ 'role' => 'administrator' ]);
        wp_set_current_user($this->userId);
        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        delete_option(ppcart_shortcode_tag_migration_option_name());
        delete_option('_ppcart_email_confirmation_body');

        if (function_exists('ppcart_unregister_legacy_shortcodes')) {
            ppcart_unregister_legacy_shortcodes();
        }
    }

    protected function tearDown(): void
    {
        foreach ($this->postIds as $postId) {
            wp_delete_post($postId, true);
        }

        if (current_user_can('manage_options')) {
            PPCartComp_Shortcode_Tag_Migrator::restore();
            PPCartComp_Shortcode_Tag_Migrator::delete_backup();
        }

        if (function_exists('ppcart_unregister_legacy_shortcodes')) {
            ppcart_unregister_legacy_shortcodes();
        }

        delete_option(PPCartComp_Studiocart_Compatibility_Mode::OPTION_NAME);
        delete_option(ppcart_shortcode_tag_migration_option_name());
        delete_option('_ppcart_email_confirmation_body');
        delete_transient(PPCartComp_Shortcode_Tag_Migrator::STEP1_LOCK);

        parent::tearDown();
    }

    /**
     * @test-id IT-284
     */
    public function test_IT_284_migrates_leftover_form_tag_in_page_content(): void
    {
        $id = $this->insertPage('[studiocart-form id="9"] checkout');
        $this->runMigrationUntilDone();

        $this->assertSame(
            '[ppcart_form id="9"] checkout',
            get_post($id)->post_content
        );
    }

    /**
     * @test-id IT-284
     */
    public function test_IT_284_migrates_leftover_download_tag_in_email_option(): void
    {
        update_option('_ppcart_email_confirmation_body', 'Files [studiocart-order-downloads] here', false);
        $this->runMigrationUntilDone();

        $this->assertSame(
            'Files [ppcart_order_downloads] here',
            get_option('_ppcart_email_confirmation_body')
        );
    }

    /**
     * @test-id IT-349
     */
    public function test_IT_349_migrates_leftover_email_merge_tag(): void
    {
        update_option('_ppcart_email_confirmation_body', 'Powered by {studiocart}', false);
        $this->runMigrationUntilDone();

        $this->assertSame(
            'Powered by {publishpress_cart}',
            get_option('_ppcart_email_confirmation_body')
        );
    }

    /**
     * @test-id IT-284
     */
    public function test_IT_284_compat_off_does_not_register_leftover_shortcodes(): void
    {
        $this->assertFalse(PPCartComp_Studiocart_Compatibility_Mode::is_enabled());
        $this->assertFalse(shortcode_exists('studiocart-form'));
        $this->assertFalse(shortcode_exists('studiocart-order-downloads'));
        $this->assertTrue(shortcode_exists('ppcart_form'));
    }

    /**
     * @return void
     */
    private function runMigrationUntilDone()
    {
        ppcart_shortcode_tag_migration_save_state(
            [
                'mode'  => 'legacy',
                'step1' => 'idle',
            ]
        );

        for ($i = 0; $i < 10; $i++) {
            $result = PPCartComp_Shortcode_Tag_Migrator::run_chunk();
            $this->assertTrue(! empty($result['ok']));
            if (! empty($result['done'])) {
                return;
            }
        }

        $this->fail('Shortcode tag migration did not finish.');
    }

    /**
     * @param string $content Post content.
     * @return int
     */
    private function insertPage($content)
    {
        $id = (int) wp_insert_post(
            [
                'post_title'   => 'Shortcode migrate',
                'post_status'  => 'publish',
                'post_type'    => 'page',
                'post_content' => $content,
            ]
        );
        $this->postIds[] = $id;

        return $id;
    }
}
