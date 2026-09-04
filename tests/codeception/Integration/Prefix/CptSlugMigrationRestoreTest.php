<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use PPCartComp_Cpt_Slug_Migrator;
use Tests\Support\Integration\CptSlugMigrationTestCase;

class CptSlugMigrationRestoreTest extends CptSlugMigrationTestCase
{
    /**
     * @test-id IT-271
     */
    public function test_IT_271_restore_step1_puts_leftover_kinds_back_without_ack(): void
    {
        $this->saveLegacyIdleState();

        $orderId        = $this->insertLeftoverPost('sc_order', 'Leftover order');
        $subscriptionId = $this->insertLeftoverPost('sc_subscription', 'Leftover subscription');
        $catTtId        = $this->insertLeftoverTerm('sc_product_cat', 'Leftover category');
        $tagTtId        = $this->insertLeftoverTerm('sc_product_tag', 'Leftover tag');
        $templateId     = $this->insertLeftoverProductTemplate();
        $roleName       = $this->insertThrowawayRoleWithLeftoverCap();

        $migrated = $this->runStep1UntilDone();
        $this->assertTrue(! empty($migrated['done']), wp_json_encode($migrated));
        $this->assertGreaterThan(0, $this->backupRowCount());

        $restore = PPCartComp_Cpt_Slug_Migrator::restore_step1();
        $this->assertTrue($restore['ok']);

        $this->cleanPostCaches([ $orderId, $subscriptionId, $templateId ]);

        $this->assertSame('sc_order', get_post_type($orderId));
        $this->assertSame('sc_subscription', get_post_type($subscriptionId));
        $this->assertSame('sc_product_cat', $this->termTaxonomySlug($catTtId));
        $this->assertSame('sc_product_tag', $this->termTaxonomySlug($tagTtId));
        $this->assertSame('single-sc_product', get_post_field('post_name', $templateId));

        $role = get_role($roleName);
        $this->assertNotNull($role);
        $this->assertTrue($role->has_cap('sc_manager_option'));
        $this->assertFalse($role->has_cap('ppcart_manager_option'));

        $state = ppcart_cpt_slug_migration_get_state();
        $this->assertNotSame('later', $state['ack']);
        $this->assertSame('idle', $state['step1']);
        $this->assertSame('legacy', $state['mode']);
        $this->assertSame(0, $this->backupRowCount());
    }
}
