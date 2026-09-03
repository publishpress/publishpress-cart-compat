<?php

if (! defined('ABSPATH')) {
    exit;
}

$step1 = isset($state['step1']) ? $state['step1'] : 'idle';
$step2 = isset($state['step2']) ? $state['step2'] : 'idle';
$step1_done = ppcart_cpt_slug_migration_step1_complete();
?>
<div class="ppcart-cpt-slug-migration__body">
    <p class="ppcart-settings__card-desc">
        <?php esc_html_e('This tool renames stored StudioCart post types, taxonomies, capabilities, leftover shortcode tags in content and emails, leftover meta keys, and leftover custom tables to PublishPress Cart names. CPT/meta/shortcode steps write a reverse-map backup first so you can restore completed chunks. Custom table rename is opt-in and has no restore. It is not StudioCart Compatibility Mode, which only keeps old PHP APIs for third-party plugins.', 'publishpress-cart'); ?>
    </p>

    <div class="ppcart-cpt-slug-migration__block">
        <h3><?php esc_html_e('Step 1: Post types, taxonomies, and capabilities', 'publishpress-cart'); ?></h3>
        <p>
            <?php
            echo esc_html(
                sprintf(
                    /* translators: 1: leftover posts, 2: leftover terms */
                    __('Remaining: %1$d posts, %2$d terms.', 'publishpress-cart'),
                    isset($remaining['posts']) ? (int) $remaining['posts'] : 0,
                    isset($remaining['terms']) ? (int) $remaining['terms'] : 0
                )
            );
            ?>
            <?php echo esc_html(sprintf(/* translators: %s: status */ __('Status: %s', 'publishpress-cart'), $step1)); ?>
        </p>
        <?php if (! $step1_done) : ?>
            <p>
                <button type="button" class="button button-primary ppcart-cpt-migration-run" data-step="1"><?php esc_html_e('Back up and migrate', 'publishpress-cart'); ?></button>
                <button type="button" class="button ppcart-cpt-migration-stop"><?php esc_html_e('Stop after this chunk', 'publishpress-cart'); ?></button>
            </p>
        <?php endif; ?>
        <p>
            <button type="button" class="button ppcart-cpt-migration-restore" data-step="1"><?php esc_html_e('Restore completed chunks', 'publishpress-cart'); ?></button>
        </p>
        <p class="description"><?php esc_html_e('Restore only undoes rows already in the backup. Leftover StudioCart slugs stay until you run this step again.', 'publishpress-cart'); ?></p>
        <p class="ppcart-cpt-migration-status" data-step="1"></p>
    </div>

    <div class="ppcart-cpt-slug-migration__block">
        <h3><?php esc_html_e('Step 2: Role slugs', 'publishpress-cart'); ?></h3>
        <p><?php esc_html_e('Renames Cart Manager and Cart Administrator role keys. Run this only after step 1 is complete.', 'publishpress-cart'); ?></p>
        <p><?php echo esc_html(sprintf(/* translators: %s: status */ __('Status: %s', 'publishpress-cart'), $step2)); ?></p>
        <p>
            <button type="button" class="button button-primary ppcart-cpt-migration-run" data-step="2" <?php disabled(! $step1_done); ?>><?php esc_html_e('Migrate role slugs', 'publishpress-cart'); ?></button>
            <button type="button" class="button ppcart-cpt-migration-restore" data-step="2"><?php esc_html_e('Restore role slugs', 'publishpress-cart'); ?></button>
        </p>
        <p class="ppcart-cpt-migration-status" data-step="2"></p>
    </div>

    <?php
    if (class_exists('PPCartComp_Meta_Key_Migration_Admin', false)) {
        PPCartComp_Meta_Key_Migration_Admin::render_block();
    }

    if (class_exists('PPCartComp_Shortcode_Tag_Migration_Admin', false)) {
        PPCartComp_Shortcode_Tag_Migration_Admin::render_block();
    }

    if (function_exists('ppcart_custom_table_migration_render_block')) {
        ppcart_custom_table_migration_render_block();
    }
    ?>

    <div class="ppcart-cpt-slug-migration__block">
        <h3><?php esc_html_e('Backup', 'publishpress-cart'); ?></h3>
        <p><?php esc_html_e('Delete the reverse map when you are sure you will not restore. This cannot be undone from Cart.', 'publishpress-cart'); ?></p>
        <p>
            <button type="button" class="button ppcart-cpt-migration-delete-backup"><?php esc_html_e('Delete backup', 'publishpress-cart'); ?></button>
        </p>
        <p class="ppcart-cpt-migration-status" data-step="backup"></p>
    </div>
</div>
