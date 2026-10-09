<?php

if (! defined('ABSPATH')) {
    exit;
}

$step1      = isset($state['step1']) ? $state['step1'] : 'idle';
$step1_done = ppcart_shortcode_tag_migration_complete();
?>
<div class="ppcart-shortcode-tag-migration__block">
    <h3><?php esc_html_e('Shortcode tags in content and emails', 'publishpress-cart'); ?></h3>
    <p class="description">
        <?php esc_html_e('Rewrites leftover StudioCart shortcode tags in post content, excerpts, Elementor data, email template options, and product notification HTML to ppcart_* tags. Compatibility Mode still registers leftover tags for unmigrated content. This does not run until you start it.', 'publishpress-cart'); ?>
    </p>
    <p>
        <?php
        echo esc_html(
            sprintf(
                /* translators: 1: leftover posts, 2: leftover email options, 3: leftover notification meta, 4: Elementor data rows not scanned yet */
                __('Remaining: %1$d posts, %2$d email options, %3$d notification rows, %4$d Elementor rows to scan.', 'publishpress-cart'),
                isset($remaining['posts']) ? (int) $remaining['posts'] : 0,
                isset($remaining['options']) ? (int) $remaining['options'] : 0,
                isset($remaining['postmeta']) ? (int) $remaining['postmeta'] : 0,
                isset($remaining['elementor']) ? (int) $remaining['elementor'] : 0
            )
        );
        ?>
        <?php echo esc_html(sprintf(/* translators: %s: status */ __('Status: %s', 'publishpress-cart'), $step1)); ?>
    </p>
    <?php if (! $step1_done) : ?>
        <p>
            <button type="button" class="button button-primary ppcart-shortcode-tag-migration-run"><?php esc_html_e('Back up and migrate shortcode tags', 'publishpress-cart'); ?></button>
            <button type="button" class="button ppcart-shortcode-tag-migration-stop"><?php esc_html_e('Stop after this chunk', 'publishpress-cart'); ?></button>
        </p>
    <?php endif; ?>
    <p>
        <button type="button" class="button ppcart-shortcode-tag-migration-restore"><?php esc_html_e('Restore completed chunks', 'publishpress-cart'); ?></button>
        <button type="button" class="button ppcart-shortcode-tag-migration-delete-backup"><?php esc_html_e('Delete backup', 'publishpress-cart'); ?></button>
    </p>
    <p class="ppcart-shortcode-tag-migration-status"></p>
</div>
