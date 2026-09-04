<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use Tests\Support\Integration\CptSlugMigrationTestCase;

class CptSlugMigrationSeedTest extends CptSlugMigrationTestCase
{
    /**
     * @test-id IT-273
     */
    public function test_IT_273_seed_marks_canonical_when_nothing_is_leftover(): void
    {
        delete_option(ppcart_cpt_slug_migration_option_name());
        $this->assertFalse(ppcart_cpt_slug_migration_has_leftover_rows());

        ppcart_cpt_slug_migration_maybe_seed_state();

        $state = ppcart_cpt_slug_migration_get_state();
        $this->assertSame('canonical', $state['mode']);
        $this->assertSame('complete', $state['step1']);
        $this->assertSame('complete', $state['step2']);
        $this->assertTrue(ppcart_cpt_slug_migration_step1_complete());
    }

    /**
     * @test-id IT-273
     */
    public function test_IT_273_seed_stays_legacy_when_a_leftover_capability_exists(): void
    {
        $this->insertThrowawayRoleWithLeftoverCap();

        delete_option(ppcart_cpt_slug_migration_option_name());
        ppcart_cpt_slug_migration_maybe_seed_state();

        $this->assertSame('legacy', ppcart_cpt_slug_migration_get_state()['mode']);
        $this->assertFalse(ppcart_cpt_slug_migration_step1_complete());
    }

    /**
     * @test-id IT-273
     */
    public function test_IT_273_seed_stays_legacy_when_a_leftover_role_exists(): void
    {
        add_role('sc_cart_manager', 'Cart Manager', [ 'read' => true ]);
        $this->createdRoleSlugs[] = 'sc_cart_manager';

        delete_option(ppcart_cpt_slug_migration_option_name());
        ppcart_cpt_slug_migration_maybe_seed_state();

        $this->assertSame('legacy', ppcart_cpt_slug_migration_get_state()['mode']);
        $this->assertFalse(ppcart_cpt_slug_migration_step1_complete());
    }
}
