<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_gutenberg_block_map')) {
    /**
     * Canonical Gutenberg block name => leftover block name.
     *
     * @return array<string, string>
     */
    function ncs_cart_get_legacy_gutenberg_block_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/gutenberg-blocks.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_legacy_gutenberg_blocks_registered')) {
    /**
     * @return array<string, bool>
     */
    function &ncs_cart_legacy_gutenberg_blocks_registered()
    {
        static $registered = [];

        return $registered;
    }
}

if (! function_exists('ppcart_register_legacy_gutenberg_blocks')) {
    /**
     * Dual-register leftover Gutenberg block names onto the canonical types.
     *
     * @return void
     */
    function ppcart_register_legacy_gutenberg_blocks()
    {
        if (! function_exists('register_block_type') || ! class_exists('WP_Block_Type_Registry', false)) {
            return;
        }

        $registry   = WP_Block_Type_Registry::get_instance();
        $registered = &ncs_cart_legacy_gutenberg_blocks_registered();

        foreach (ncs_cart_get_legacy_gutenberg_block_map() as $canonical => $legacy) {
            if (! is_string($canonical) || '' === $canonical || ! is_string($legacy) || '' === $legacy) {
                continue;
            }

            if (isset($registered[ $legacy ])) {
                continue;
            }

            if ($registry->is_registered($legacy)) {
                $registered[ $legacy ] = true;
                continue;
            }

            if (! $registry->is_registered($canonical)) {
                continue;
            }

            $block = $registry->get_registered($canonical);

            if (! $block instanceof WP_Block_Type) {
                continue;
            }

            $supports = is_array($block->supports) ? $block->supports : [];
            $supports['html']     = false;
            $supports['inserter'] = false;

            $args = [
                'api_version'     => ! empty($block->api_version) ? $block->api_version : 3,
                'attributes'      => is_array($block->attributes) ? $block->attributes : [],
                'render_callback' => $block->render_callback,
                'supports'        => $supports,
            ];

            if (! empty($block->editor_script_handles[0])) {
                $args['editor_script'] = $block->editor_script_handles[0];
            }

            if (! empty($block->editor_style_handles[0])) {
                $args['editor_style'] = $block->editor_style_handles[0];
            }

            if (! empty($block->style_handles[0])) {
                $args['style'] = $block->style_handles[0];
            }

            register_block_type($legacy, $args);
            $registered[ $legacy ] = true;
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_gutenberg_blocks')) {
    /**
     * Remove leftover Gutenberg block types. Intended for isolated tests.
     *
     * @return void
     */
    function ppcart_unregister_legacy_gutenberg_blocks()
    {
        if (! function_exists('unregister_block_type')) {
            $registered = &ncs_cart_legacy_gutenberg_blocks_registered();
            $registered = [];

            return;
        }

        $registered = &ncs_cart_legacy_gutenberg_blocks_registered();

        foreach (array_keys($registered) as $legacy) {
            unregister_block_type($legacy);
            unset($registered[ $legacy ]);
        }
    }
}
