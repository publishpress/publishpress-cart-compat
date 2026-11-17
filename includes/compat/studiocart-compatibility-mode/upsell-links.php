<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Leftover StudioCart upsell links while Compatibility Mode is on.
 *
 * StudioCart accept/decline links used `?sc-upsell-offer=yes|no` and the
 * `sc-accept-upsell-link` / `sc-decline-upsell-link` classes. Cart Pro's
 * upsell script only handles the `ppcart-` names, so before it runs, copy the
 * leftover names onto the new ones. The Data migration rewrites stored
 * content; this keeps pages working until it has run.
 */

if (! function_exists('ppcartcomp_legacy_upsell_links_script')) {
    /**
     * @return string Inline JS.
     */
    function ppcartcomp_legacy_upsell_links_script()
    {
        return <<<'JS'
(function ($) {
    if (!$) {
        return;
    }
    $(function () {
        $('a[href*="sc-upsell-offer="]').each(function () {
            var href = this.getAttribute('href') || '';
            this.setAttribute('href', href.replace(/([?&])sc-upsell-offer=/g, '$1ppcart-upsell-offer='));
        });
        $('.sc-accept-upsell-link').addClass('ppcart-accept-upsell-link');
        $('.sc-decline-upsell-link').addClass('ppcart-decline-upsell-link');
    });
})(window.jQuery);
JS;
    }
}

if (! function_exists('ppcartcomp_maybe_add_legacy_upsell_links_script')) {
    /**
     * Attach the shim in front of Cart Pro's public script, wherever it prints.
     *
     * Runs on wp_print_scripts and wp_print_footer_scripts (priority 1) so it
     * also covers a script enqueued late, while a shortcode renders.
     *
     * @return void
     */
    function ppcartcomp_maybe_add_legacy_upsell_links_script()
    {
        static $added = false;

        if ($added || ! function_exists('wp_script_is') || ! wp_script_is('ppcart-public-pro', 'registered')) {
            return;
        }

        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return;
        }

        wp_add_inline_script('ppcart-public-pro', ppcartcomp_legacy_upsell_links_script(), 'before');
        $added = true;
    }
}

if (! function_exists('ppcartcomp_register_legacy_upsell_links')) {
    /**
     * @return void
     */
    function ppcartcomp_register_legacy_upsell_links()
    {
        static $registered = false;

        if ($registered || ! function_exists('add_action')) {
            return;
        }

        add_action('wp_print_scripts', 'ppcartcomp_maybe_add_legacy_upsell_links_script', 1);
        add_action('wp_print_footer_scripts', 'ppcartcomp_maybe_add_legacy_upsell_links_script', 1);
        $registered = true;
    }
}
