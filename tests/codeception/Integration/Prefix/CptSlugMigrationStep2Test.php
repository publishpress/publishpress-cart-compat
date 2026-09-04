<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use PPCartComp_Cpt_Slug_Migrator;
use Tests\Support\Integration\CptSlugMigrationTestCase;

class CptSlugMigrationStep2Test extends CptSlugMigrationTestCase
{
    /**
     * @test-id IT-272
     */
    public function test_IT_272_step2_is_rejected_until_step1_is_complete(): void
    {
        $this->saveLegacyIdleState();

        $result = PPCartComp_Cpt_Slug_Migrator::run_step2();

        $this->assertFalse($result['ok']);
        $this->assertSame('step1_incomplete', $result['error']);
        $this->assertFalse(ppcart_cpt_slug_migration_step2_complete());
    }

    /**
     * @test-id IT-272
     */
    public function test_IT_272_step2_renames_leftover_role_and_reassigns_user(): void
    {
        $this->saveLegacyIdleState();
        $userId = $this->insertLeftoverCartManagerUser();

        ppcart_cpt_slug_migration_save_state(
            [
                'step1' => 'complete',
                'mode'  => 'legacy',
            ]
        );

        $result = PPCartComp_Cpt_Slug_Migrator::run_step2();
        $this->assertTrue($result['ok'], wp_json_encode($result));
        $this->assertTrue(! empty($result['done']));
        $this->assertTrue(ppcart_cpt_slug_migration_step2_complete());

        $this->assertNull(get_role('sc_cart_manager'));
        $this->assertNotNull(get_role('ppcart_cart_manager'));

        $user = get_user_by('id', $userId);
        $this->assertNotFalse($user);
        $this->assertTrue(in_array('ppcart_cart_manager', (array) $user->roles, true));
        $this->assertFalse(in_array('sc_cart_manager', (array) $user->roles, true));
        $this->assertGreaterThan(0, $this->backupRowCount());
    }

    /**
     * @test-id IT-272
     */
    public function test_IT_272_restore_step2_puts_leftover_role_and_user_back(): void
    {
        $this->saveLegacyIdleState();
        $userId = $this->insertLeftoverCartManagerUser();

        ppcart_cpt_slug_migration_save_state(
            [
                'step1' => 'complete',
                'mode'  => 'legacy',
            ]
        );

        $migrated = PPCartComp_Cpt_Slug_Migrator::run_step2();
        $this->assertTrue($migrated['ok']);

        $restore = PPCartComp_Cpt_Slug_Migrator::restore_step2();
        $this->assertTrue($restore['ok']);

        $this->assertNotNull(get_role('sc_cart_manager'));

        $user = get_user_by('id', $userId);
        $this->assertNotFalse($user);
        $this->assertTrue(in_array('sc_cart_manager', (array) $user->roles, true));
        $this->assertFalse(in_array('ppcart_cart_manager', (array) $user->roles, true));

        $state = ppcart_cpt_slug_migration_get_state();
        $this->assertSame('idle', $state['step2']);
        $this->assertSame(0, $this->backupRowCount());
    }
}
