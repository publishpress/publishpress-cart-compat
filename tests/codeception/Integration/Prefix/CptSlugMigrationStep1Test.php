<?php

declare(strict_types=1);

namespace Tests\Integration\Prefix;

use Tests\Support\Integration\CptSlugMigrationTestCase;

class CptSlugMigrationStep1Test extends CptSlugMigrationTestCase
{
    /**
     * @test-id IT-270
     */
    public function test_IT_270_step1_renames_leftover_orders_subscriptions_terms_template_and_caps(): void
    {
        $this->saveLegacyIdleState();

        $orderId        = $this->insertLeftoverPost('sc_order', 'Leftover order');
        $subscriptionId = $this->insertLeftoverPost('sc_subscription', 'Leftover subscription');
        $collectionId   = $this->insertLeftoverPost('sc_collection', 'Leftover collection');
        $catTtId        = $this->insertLeftoverTerm('sc_product_cat', 'Leftover category');
        $tagTtId        = $this->insertLeftoverTerm('sc_product_tag', 'Leftover tag');
        $templateId     = $this->insertLeftoverProductTemplate();
        $roleName       = $this->insertThrowawayRoleWithLeftoverCap();

        $result = $this->runStep1UntilDone();
        $this->assertTrue(! empty($result['done']), wp_json_encode($result));

        $this->cleanPostCaches([ $orderId, $subscriptionId, $collectionId, $templateId ]);

        $this->assertSame('ppcart_order', get_post_type($orderId));
        $this->assertSame('ppcart_subscription', get_post_type($subscriptionId));
        $this->assertSame('ppcart_collection', get_post_type($collectionId));
        $this->assertSame('ppcart_product_cat', $this->termTaxonomySlug($catTtId));
        $this->assertSame('ppcart_product_tag', $this->termTaxonomySlug($tagTtId));
        $this->assertSame('single-ppcart_product', get_post_field('post_name', $templateId));

        $role = get_role($roleName);
        $this->assertNotNull($role);
        $this->assertTrue($role->has_cap('ppcart_manager_option'));
        $this->assertFalse($role->has_cap('sc_manager_option'));

        $this->assertGreaterThan(0, $this->backupRowCount());
        $this->assertTrue(ppcart_cpt_slug_migration_step1_complete());
    }

    /**
     * @test-id IT-270
     */
    public function test_IT_270_step1_leaves_unmapped_post_type_unchanged(): void
    {
        $this->saveLegacyIdleState();

        $orderId    = $this->insertLeftoverPost('sc_order', 'Mapped leftover order');
        register_post_type('sc_unmapped_fixture', [ 'public' => false, 'label' => 'Unmapped fixture' ]);
        $unmappedId = $this->insertLeftoverPost('sc_unmapped_fixture', 'Unmapped fixture post');

        $result = $this->runStep1UntilDone();
        $this->assertTrue(! empty($result['done']), wp_json_encode($result));

        $this->cleanPostCaches([ $orderId, $unmappedId ]);

        $this->assertSame('ppcart_order', get_post_type($orderId));
        $this->assertSame('sc_unmapped_fixture', get_post_type($unmappedId));
        $this->assertGreaterThan(0, $this->backupRowCount());

        unregister_post_type('sc_unmapped_fixture');
    }
}
