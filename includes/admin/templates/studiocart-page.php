<?php

if (! defined('ABSPATH')) {
    exit;
}

$compat_class = 'PPCartComp_Studiocart_Compatibility_Mode';
$compat_ready = class_exists($compat_class, false);
$forced       = $compat_ready && $compat_class::is_wp_config_forced();
$enabled      = $compat_ready && $compat_class::is_user_enabled();
$option_name  = $compat_ready ? $compat_class::OPTION_NAME : '_ppcart_compatibility_mode';
?>
<div class="wrap ppcartcomp-studiocart-page">
    <h1><?php esc_html_e('Migrate from StudioCart', 'publishpress-cart-compat'); ?></h1>

    <div class="card ppcartcomp-migration-wizard-card">
        <?php if (class_exists('PPCartComp_Data_Migration_Wizard', false) && ! PPCartComp_Data_Migration_Wizard::assets_ready()) : ?>
            <div class="notice notice-error inline">
                <p><?php esc_html_e('Migration wizard assets are missing. Run npm run build:admin in the PublishPress Cart Migrations plugin.', 'publishpress-cart-compat'); ?></p>
            </div>
        <?php endif; ?>
        <div id="ppcartcomp-migration-wizard-root"></div>
    </div>

    <?php if ($compat_ready) : ?>
        <form method="post" action="options.php">
            <?php settings_fields(PPCartComp_Studiocart_Admin::OPTION_GROUP); ?>
            <div class="card">
                <h2><?php esc_html_e('Keep old StudioCart features working', 'publishpress-cart-compat'); ?></h2>
                <p>
                    <?php esc_html_e('Turn on Compatibility Mode if your site still uses things that were built for StudioCart. For example:', 'publishpress-cart-compat'); ?>
                </p>
                <ul class="ppcartcomp-compat-examples">
                    <li><?php esc_html_e('Pages with StudioCart shortcodes, blocks, or Elementor and Divi widgets.', 'publishpress-cart-compat'); ?></li>
                    <li><?php esc_html_e('Subscriptions that Stripe, Mollie, Razorpay, or Square set up while you used StudioCart.', 'publishpress-cart-compat'); ?></li>
                    <li><?php esc_html_e('Other plugins or custom code that call StudioCart functions or hooks.', 'publishpress-cart-compat'); ?></li>
                </ul>
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
                        <?php esc_html_e('Turn on Compatibility Mode', 'publishpress-cart-compat'); ?>
                    </label>
                </p>
                <p class="description">
                    <?php esc_html_e('You do not need this on a new store that never used StudioCart.', 'publishpress-cart-compat'); ?>
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
</div>
