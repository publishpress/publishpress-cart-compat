<?php

namespace unit\Compat;

use Codeception\Test\Unit;
use PPCartComp_Cpt_Slug_Migration_Admin;
use PPCartComp_Data_Migration_Wizard;
use Tests\Support\WordPressStubContext;
use UnitTester;

class DataMigrationWizardTest extends Unit
{
    /**
     * @var UnitTester
     */
    protected $tester;

    protected function _before(): void
    {
        WordPressStubContext::clear();
        WordPressStubContext::setState('options', []);
        WordPressStubContext::set(
            'get_option',
            function ($option, $default = false) {
                $options = WordPressStubContext::getState('options', []);

                return array_key_exists($option, $options) ? $options[$option] : $default;
            }
        );
        WordPressStubContext::set(
            'update_option',
            function ($option, $value) {
                $options = WordPressStubContext::getState('options', []);
                $options[$option] = $value;
                WordPressStubContext::setState('options', $options);

                return true;
            }
        );
        WordPressStubContext::set(
            'apply_filters',
            static function ($hook, $value) {
                return $value;
            }
        );

        if (! function_exists('ppcart_cpt_slug_migration_get_state')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/cpt-slug-migration/state.php';
        }
        if (! function_exists('ppcart_meta_key_migration_get_state')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/meta-key-migration/state.php';
        }
        if (! function_exists('ppcart_shortcode_tag_migration_get_state')) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/shortcode-tag-migration/state.php';
        }
        if (! class_exists('PPCartComp_Meta_Key_Migration_Admin', false)) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/meta-key-migration/class-ppcartcomp-meta-key-migration-admin.php';
        }
        if (! class_exists('PPCartComp_Shortcode_Tag_Migration_Admin', false)) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/compat/shortcode-tag-migration/class-ppcartcomp-shortcode-tag-migration-admin.php';
        }
        if (! class_exists('PPCartComp_Data_Migration_Wizard', false)) {
            require_once PPCARTCOMP_PLUGIN_ROOT . 'includes/admin/class-ppcartcomp-data-migration-wizard.php';
        }

        unset($_GET[PPCartComp_Data_Migration_Wizard::VIEW_QUERY], $_GET[PPCartComp_Data_Migration_Wizard::STEP_QUERY]);
    }

    protected function _after(): void
    {
        unset($_GET[PPCartComp_Data_Migration_Wizard::VIEW_QUERY], $_GET[PPCartComp_Data_Migration_Wizard::STEP_QUERY]);
        WordPressStubContext::clear();
        parent::_after();
    }

    /**
     * @test-id UT-239
     */
    public function test_UT_239_progress_denominator_excludes_backup_and_unneeded_tables(): void
    {
        $idle = [
            'cpt'        => 'idle',
            'roles'      => 'idle',
            'meta'       => 'idle',
            'shortcodes' => 'idle',
            'tables'     => 'skipped',
            'backup'     => 'idle',
        ];
        $skipped_tables = PPCartComp_Data_Migration_Wizard::progress_from_status(
            $idle,
            [ 'needsMigration' => false, 'completedAt' => null ]
        );
        $this->assertSame(4, $skipped_tables['total']);
        $this->assertSame(0, $skipped_tables['complete']);
        $this->assertSame('cpt', $skipped_tables['firstIncomplete']);

        $needed = PPCartComp_Data_Migration_Wizard::progress_from_status(
            array_merge($idle, [ 'tables' => 'idle' ]),
            [ 'needsMigration' => true, 'completedAt' => null ]
        );
        $this->assertSame(5, $needed['total']);

        $ran = PPCartComp_Data_Migration_Wizard::progress_from_status(
            array_merge($idle, [ 'tables' => 'done' ]),
            [ 'needsMigration' => false, 'completedAt' => 1700000000 ]
        );
        $this->assertSame(5, $ran['total']);
        $this->assertSame(1, $ran['complete']);
    }

    /**
     * @test-id UT-240
     */
    public function test_UT_240_first_incomplete_skips_done_steps(): void
    {
        $progress = PPCartComp_Data_Migration_Wizard::progress_from_status(
            [
                'cpt'        => 'done',
                'roles'      => 'paused',
                'meta'       => 'idle',
                'shortcodes' => 'idle',
                'tables'     => 'skipped',
            ],
            [ 'needsMigration' => false, 'completedAt' => null ]
        );
        $this->assertSame('roles', $progress['firstIncomplete']);
        $this->assertSame(1, $progress['complete']);

        $all_done = PPCartComp_Data_Migration_Wizard::progress_from_status(
            [
                'cpt'        => 'done',
                'roles'      => 'done',
                'meta'       => 'done',
                'shortcodes' => 'done',
                'tables'     => 'skipped',
            ],
            [ 'needsMigration' => false, 'completedAt' => null ]
        );
        $this->assertNull($all_done['firstIncomplete']);
        $this->assertSame($all_done['total'], $all_done['complete']);
    }

    /**
     * @test-id UT-241
     */
    public function test_UT_241_backup_exists_is_three_named_groups(): void
    {
        $exists = PPCartComp_Data_Migration_Wizard::backup_exists_for_tables(
            [ 'wp_ppcart_cpt_migration_backup', 'wp_ppcart_shortcode_tag_migration_backup' ],
            [
                'cpt'       => 'wp_ppcart_cpt_migration_backup',
                'meta'      => 'wp_ppcart_meta_key_migration_backup',
                'shortcode' => 'wp_ppcart_shortcode_tag_migration_backup',
            ]
        );

        $this->assertSame(
            [ 'cpt' => true, 'meta' => false, 'shortcode' => true ],
            $exists
        );
    }

    /**
     * @test-id UT-242
     */
    public function test_UT_242_status_payload_matches_bootstrap_shape(): void
    {
        $payload = PPCartComp_Data_Migration_Wizard::build_bootstrap();

        foreach (
            [
                'ajaxUrl',
                'nonce',
                'actions',
                'completion',
                'statuses',
                'remaining',
                'backupExists',
                'tables',
                'progress',
                'urls',
                'initialView',
                'initialStep',
            ] as $key
        ) {
            $this->assertArrayHasKey($key, $payload);
        }

        $this->assertArrayHasKey('status', $payload['actions']);
        $this->assertArrayHasKey('cpt', $payload['actions']);
        $this->assertArrayHasKey('meta', $payload['actions']);
        $this->assertArrayHasKey('shortcode', $payload['actions']);
        $this->assertArrayHasKey('tables', $payload['actions']);
        $this->assertSame(PPCartComp_Data_Migration_Wizard::AJAX_STATUS, $payload['actions']['status']);

        $this->assertArrayHasKey('cpt', $payload['backupExists']);
        $this->assertArrayHasKey('meta', $payload['backupExists']);
        $this->assertArrayHasKey('shortcode', $payload['backupExists']);
        $this->assertIsBool($payload['backupExists']['cpt']);

        $this->assertArrayHasKey('needsMigration', $payload['tables']);
        $this->assertArrayHasKey('completedAt', $payload['tables']);

        $this->assertArrayHasKey('complete', $payload['progress']);
        $this->assertArrayHasKey('total', $payload['progress']);
        $this->assertArrayHasKey('firstIncomplete', $payload['progress']);
        $this->assertContains($payload['progress']['total'], [ 4, 5 ]);

        foreach ([ 'cpt', 'roles', 'meta', 'shortcodes', 'tables' ] as $slug) {
            $this->assertArrayHasKey($slug, $payload['statuses']);
            $this->assertArrayHasKey($slug, $payload['completion']);
        }

        $this->assertSame('landing', $payload['initialView']);
        $this->assertSame(
            'https://example.test/wp-admin/admin.php?page=ppcart-studiocart',
            PPCartComp_Cpt_Slug_Migration_Admin::settings_url()
        );
        $this->assertStringNotContainsString(
            'ppcartcomp_migration_view',
            PPCartComp_Cpt_Slug_Migration_Admin::settings_url()
        );
        $this->assertStringContainsString(
            'ppcartcomp_migration_view=wizard',
            PPCartComp_Data_Migration_Wizard::wizard_url('cpt')
        );
        $this->assertStringContainsString(
            'ppcartcomp_migration_step=cpt',
            PPCartComp_Data_Migration_Wizard::wizard_url('cpt')
        );
    }
}
