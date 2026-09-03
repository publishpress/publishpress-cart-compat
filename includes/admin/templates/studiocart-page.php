<?php

if (! defined('ABSPATH')) {
    exit;
}

$compat_class = 'PPCartComp_Studiocart_Compatibility_Mode';
$compat_ready = class_exists($compat_class, false);
$forced       = $compat_ready && $compat_class::is_wp_config_forced();
$enabled      = $compat_ready && $compat_class::is_user_enabled();
$option_name  = $compat_ready ? $compat_class::OPTION_NAME : '_ppcart_studiocart_compatibility_mode';
?>
<div class="wrap ppcartcomp-studiocart-page">
    <h1><?php esc_html_e('StudioCart', 'publishpress-cart-compat'); ?></h1>

    <?php if ($compat_ready) : ?>
        <form method="post" action="options.php">
            <?php settings_fields(PPCartComp_Studiocart_Admin::OPTION_GROUP); ?>
            <div class="card">
                <h2><?php esc_html_e('Compatibility', 'publishpress-cart-compat'); ?></h2>
                <p>
                    <?php esc_html_e('Keep StudioCart legacy PHP APIs and public links available for third-party plugins and apps.', 'publishpress-cart-compat'); ?>
                </p>
                <p>
                    <?php esc_html_e('Renaming stored post types and capabilities is a separate Data migration on this screen. Compatibility Mode only keeps StudioCart PHP APIs.', 'publishpress-cart-compat'); ?>
                </p>
                <p>
                    <label>
                        <input type="hidden" name="<?php echo esc_attr($option_name); ?>" value="0" />
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr($option_name); ?>"
                            value="1"
                            <?php checked($enabled); ?>
                            <?php disabled($forced); ?>
                        />
                        <?php esc_html_e('StudioCart Compatibility Mode', 'publishpress-cart-compat'); ?>
                    </label>
                </p>
                <p class="description">
                    <?php esc_html_e('Enable legacy StudioCart PHP APIs and public URL redirects so existing third-party plugins, apps, and links keep working. Leave this off on new stores.', 'publishpress-cart-compat'); ?>
                </p>
                <?php if ($forced) : ?>
                    <p class="description">
                        <?php esc_html_e('This setting is controlled by PPCART_STUDIOCART_COMPATIBILITY_MODE in wp-config.php.', 'publishpress-cart-compat'); ?>
                    </p>
                <?php endif; ?>
                <?php submit_button(); ?>
            </div>
        </form>
    <?php endif; ?>

    <?php if (class_exists('PPCartComp_Cpt_Slug_Migration_Admin', false)) : ?>
        <div class="card ppcart-cpt-slug-migration">
            <h2><?php esc_html_e('Data migration', 'publishpress-cart-compat'); ?></h2>
            <?php PPCartComp_Cpt_Slug_Migration_Admin::render_page(); ?>
        </div>
    <?php endif; ?>
</div>
