<?php

if (! defined('ABSPATH')) {
    exit;
}

$compat_class        = 'PPCartComp_Studiocart_Compatibility_Mode';
$compat_ready        = class_exists($compat_class, false);
$forced              = $compat_ready && $compat_class::is_wp_config_forced();
$enabled             = $compat_ready && $compat_class::is_user_enabled();
$option_name         = $compat_ready ? $compat_class::OPTION_NAME : '_ppcart_studiocart_compatibility_mode';
$drop_backups_option = PPCartComp_Studiocart_Admin::DROP_BACKUPS_OPTION;
$drop_backups        = '1' === (string) get_option($drop_backups_option, '0');
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
            </div>
            <div class="card">
                <h2><?php esc_html_e('Migration backups', 'publishpress-cart-compat'); ?></h2>
                <p>
                    <label>
                        <input type="hidden" name="<?php echo esc_attr($drop_backups_option); ?>" value="0" />
                        <input
                            type="checkbox"
                            name="<?php echo esc_attr($drop_backups_option); ?>"
                            value="1"
                            <?php checked($drop_backups); ?>
                        />
                        <?php esc_html_e('Drop CPT, meta, and shortcode reverse-map backup tables when this plugin is deleted.', 'publishpress-cart-compat'); ?>
                    </label>
                </p>
                <p class="description">
                    <?php esc_html_e('Cart store tables and leftover StudioCart rows stay. Restore from those reverse-map backups will no longer be available. This setting is off by default.', 'publishpress-cart-compat'); ?>
                </p>
            </div>
            <?php submit_button(); ?>
        </form>
    <?php endif; ?>

    <div class="card ppcartcomp-migration-wizard-card">
        <?php if (class_exists('PPCartComp_Data_Migration_Wizard', false) && ! PPCartComp_Data_Migration_Wizard::assets_ready()) : ?>
            <div class="notice notice-error inline">
                <p><?php esc_html_e('Migration wizard assets are missing. Run npm run build:admin in the PublishPress Cart Compatibility plugin.', 'publishpress-cart-compat'); ?></p>
            </div>
        <?php endif; ?>
        <div id="ppcartcomp-migration-wizard-root"></div>
    </div>
</div>
