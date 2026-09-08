<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_cpt_taxonomy_map')) {
    /**
     * @return array<string, array<int, string>>
     */
    function ncs_cart_get_legacy_cpt_taxonomy_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/cpt-taxonomy.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_get_legacy_cpt_taxonomy_names')) {
    /**
     * Leftover CPT/taxonomy/FSE names for one canonical key.
     *
     * @param string $canonical Canonical slug or template slug.
     * @return array<int, string>
     */
    function ncs_cart_get_legacy_cpt_taxonomy_names($canonical)
    {
        if (! is_string($canonical) || '' === $canonical) {
            return [];
        }

        $map = ncs_cart_get_legacy_cpt_taxonomy_map();

        if (! isset($map[ $canonical ]) || ! is_array($map[ $canonical ])) {
            return [];
        }

        return array_values(array_filter($map[ $canonical ], 'is_string'));
    }
}

if (! function_exists('ppcart_register_legacy_cpt_taxonomy_bridges')) {
    /**
     * Register leftover FSE template slugs and taxonomy rewrite aliases when Compatibility Mode is on.
     *
     * @return void
     */
    function ppcart_register_legacy_cpt_taxonomy_bridges()
    {
        if (function_exists('ppcart_cpt_leftover_fse_template_slugs')) {
            ppcart_cpt_leftover_fse_template_slugs(
                ncs_cart_get_legacy_cpt_taxonomy_names('single-ppcart_product'),
                true
            );
        }

        if (! has_action('init', 'ppcart_register_legacy_taxonomy_rewrite_aliases')) {
            add_action('init', 'ppcart_register_legacy_taxonomy_rewrite_aliases', 11);
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_cpt_taxonomy_bridges')) {
    /**
     * Clear leftover FSE template slugs. Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_cpt_taxonomy_bridges()
    {
        if (function_exists('ppcart_cpt_leftover_fse_template_slugs')) {
            ppcart_cpt_leftover_fse_template_slugs([], true);
        }

        remove_action('init', 'ppcart_register_legacy_taxonomy_rewrite_aliases', 11);
        remove_action('init', 'ppcart_register_legacy_fse_product_templates', 1001);
    }
}

if (! function_exists('ppcart_register_legacy_fse_product_templates')) {
    /**
     * Register leftover FSE product templates while Compatibility Mode is on.
     *
     * @return void
     */
    function ppcart_register_legacy_fse_product_templates()
    {
        if (
            ! class_exists('PPCart_Product_Template', false)
            || ! PPCart_Product_Template::is_supported_block_theme()
        ) {
            return;
        }

        if (get_option('_ppcart_disable_template')) {
            return;
        }

        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return;
        }

        if (! function_exists('ppcart_cpt_leftover_fse_template_slugs')) {
            return;
        }

        $plugin_slug = PPCart_Product_Template::PLUGIN_SLUG;
        $post_types  = PPCart_Product_Template::get_product_post_types();

        foreach (ppcart_cpt_leftover_fse_template_slugs() as $leftover_slug) {
            $leftover_name = $plugin_slug . '//' . $leftover_slug;
            $leftover_type = preg_replace('/^single-/', '', $leftover_slug);

            if (class_exists('WP_Block_Templates_Registry')) {
                $registry = WP_Block_Templates_Registry::get_instance();
                if ($registry->is_registered($leftover_name)) {
                    continue;
                }
            }

            register_block_template(
                $leftover_name,
                [
                    'title'       => PPCart_Product_Template::get_template_title($leftover_type),
                    'description' => esc_html__('Controls PublishPress Cart product pages. Edit this template to move product content and the checkout form.', 'publishpress-cart'),
                    'content'     => PPCart_Product_Template::get_template_content(),
                    'post_types'  => $leftover_type ? [ $leftover_type ] : $post_types,
                    'plugin'      => $plugin_slug,
                ]
            );
        }
    }
}

add_action('init', 'ppcart_register_legacy_fse_product_templates', 1001);

if (! function_exists('ppcart_register_legacy_taxonomy_rewrite_aliases')) {
    /**
     * Extra leftover taxonomy rewrite slugs after Data migration completed.
     *
     * @return void
     */
    function ppcart_register_legacy_taxonomy_rewrite_aliases()
    {
        if (
            ! class_exists('PPCartComp_Studiocart_Compatibility_Mode', false)
            || ! PPCartComp_Studiocart_Compatibility_Mode::is_enabled()
        ) {
            return;
        }

        if (! function_exists('ppcart_cpt_slug_migration_step1_complete') || ! ppcart_cpt_slug_migration_step1_complete()) {
            return;
        }

        if (! function_exists('ppcart_cpt_slug_migration_maps')) {
            return;
        }

        foreach (ppcart_cpt_slug_migration_maps()['taxonomies'] as $pair) {
            $legacy    = isset($pair['rewrite_legacy']) ? $pair['rewrite_legacy'] : $pair['legacy'];
            $canonical = $pair['canonical'];
            if ('' === $legacy || $legacy === $canonical) {
                continue;
            }

            add_rewrite_rule(
                '^' . preg_quote($legacy, '/') . '/([^/]+)/?$',
                'index.php?' . $canonical . '=$matches[1]',
                'top'
            );
        }
    }
}
