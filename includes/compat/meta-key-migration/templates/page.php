<?php

if (! defined('ABSPATH')) {
    exit;
}

$step1      = isset($state['step1']) ? $state['step1'] : 'idle';
$step1_done = ppcart_meta_key_migration_complete();
?>
<div class="ppcart-meta-key-migration__block">
    <h3><?php esc_html_e('Post-meta and user-meta keys', 'publishpress-cart'); ?></h3>
    <p class="description">
        <?php esc_html_e('Renames leftover _sc_* product, order, subscription, and profile meta keys to _ppcart_*. New writes already use canonical keys. This does not run until you start it.', 'publishpress-cart'); ?>
    </p>
    <p>
        <?php
        echo esc_html(
            sprintf(
                /* translators: 1: leftover post-meta rows, 2: leftover user-meta rows */
                __('Remaining: %1$d post-meta rows, %2$d user-meta rows.', 'publishpress-cart'),
                isset($remaining['postmeta']) ? (int) $remaining['postmeta'] : 0,
                isset($remaining['usermeta']) ? (int) $remaining['usermeta'] : 0
            )
        );
        ?>
        <?php echo esc_html(sprintf(/* translators: %s: status */ __('Status: %s', 'publishpress-cart'), $step1)); ?>
    </p>
    <?php if (! $step1_done) : ?>
        <p>
            <button type="button" class="button button-primary ppcart-meta-key-migration-run"><?php esc_html_e('Back up and migrate meta keys', 'publishpress-cart'); ?></button>
            <button type="button" class="button ppcart-meta-key-migration-stop"><?php esc_html_e('Stop after this chunk', 'publishpress-cart'); ?></button>
        </p>
    <?php endif; ?>
    <p>
        <button type="button" class="button ppcart-meta-key-migration-restore"><?php esc_html_e('Restore completed chunks', 'publishpress-cart'); ?></button>
        <button type="button" class="button ppcart-meta-key-migration-delete-backup"><?php esc_html_e('Delete backup', 'publishpress-cart'); ?></button>
    </p>
    <p class="ppcart-meta-key-migration-status"></p>
</div>
