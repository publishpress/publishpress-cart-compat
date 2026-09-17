<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_divi_module_map')) {
    /**
     * Canonical Divi module slug => leftover slug and stored field names.
     *
     * @return array<string, array{legacy: string, fields: array<string, string>, field_types: array<string, string>}>
     */
    function ncs_cart_get_legacy_divi_module_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/divi-modules.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_legacy_divi_modules_registered')) {
    /**
     * @return array<string, bool>
     */
    function &ncs_cart_legacy_divi_modules_registered()
    {
        static $registered = [];

        return $registered;
    }
}

if (! function_exists('ppcartcomp_remap_divi_module_props')) {
    /**
     * Copy leftover Divi props onto canonical keys. In-memory only.
     *
     * @param object $module Divi module instance.
     * @return void
     */
    function ppcartcomp_remap_divi_module_props($module)
    {
        if (! is_object($module) || ! isset($module->props) || ! is_array($module->props)) {
            return;
        }

        foreach (ncs_cart_get_legacy_divi_module_map() as $entry) {
            if (! is_array($entry) || empty($entry['fields']) || ! is_array($entry['fields'])) {
                continue;
            }

            foreach ($entry['fields'] as $canonical => $legacy) {
                if (! is_string($canonical) || '' === $canonical || ! is_string($legacy) || '' === $legacy) {
                    continue;
                }

                if (isset($module->props[ $legacy ])) {
                    $module->props[ $canonical ] = $module->props[ $legacy ];
                }
            }
        }
    }
}

if (! function_exists('ppcartcomp_rewrite_divi_module_shortcodes')) {
    /**
     * Rewrite leftover Divi module slugs and attrs in post content.
     *
     * Merchant Data migration may persist the return value. Runtime dual-register
     * must not write this back to post_content.
     *
     * @param string $content Post content.
     * @return string
     */
    function ppcartcomp_rewrite_divi_module_shortcodes($content)
    {
        if (! is_string($content) || '' === $content) {
            return $content;
        }

        foreach (ncs_cart_get_legacy_divi_module_map() as $canonical_slug => $entry) {
            if (! is_string($canonical_slug) || '' === $canonical_slug || ! is_array($entry)) {
                continue;
            }

            $legacy_slug = isset($entry['legacy']) && is_string($entry['legacy']) ? $entry['legacy'] : '';
            if ('' === $legacy_slug) {
                continue;
            }

            $fields = (isset($entry['fields']) && is_array($entry['fields'])) ? $entry['fields'] : [];

            $content = preg_replace_callback(
                '/\[' . preg_quote($legacy_slug, '/') . '\b([^\]]*)\]/',
                static function ($matches) use ($canonical_slug, $fields) {
                    $attrs = $matches[1];
                    foreach ($fields as $canonical_field => $legacy_field) {
                        if (! is_string($canonical_field) || '' === $canonical_field || ! is_string($legacy_field) || '' === $legacy_field) {
                            continue;
                        }

                        $attrs = preg_replace(
                            '/(\s)' . preg_quote($legacy_field, '/') . '=/',
                            '$1' . $canonical_field . '=',
                            $attrs
                        );
                    }

                    return '[' . $canonical_slug . $attrs . ']';
                },
                $content
            );

            $content = str_replace('[/' . $legacy_slug . ']', '[/' . $canonical_slug . ']', $content);
        }

        return is_string($content) ? $content : '';
    }
}

if (! function_exists('ppcart_register_legacy_divi_modules')) {
    /**
     * Dual-register leftover Divi module slug while Compatibility Mode is on.
     *
     * @return void
     */
    function ppcart_register_legacy_divi_modules()
    {
        if (! class_exists('ET_Builder_Module', false) || ! class_exists('PPCart_Pro_Divi_Order_Form_Module', false)) {
            return;
        }

        $registered = &ncs_cart_legacy_divi_modules_registered();
        $legacy     = 'stof_studiocart_order_form';

        if (isset($registered[ $legacy ])) {
            return;
        }

        if (! class_exists('PPCartComp_Legacy_Divi_Order_Form', false)) {
            /**
             * Leftover Divi order-form slug.
             */
            class PPCartComp_Legacy_Divi_Order_Form extends PPCart_Pro_Divi_Order_Form_Module
            {
                public $slug = 'stof_studiocart_order_form';

                public function get_fields()
                {
                    $fields = parent::get_fields();
                    $map    = ncs_cart_get_legacy_divi_module_map();
                    $entry  = isset($map['ppcart_order_form']['fields']) && is_array($map['ppcart_order_form']['fields'])
                        ? $map['ppcart_order_form']['fields']
                        : [];

                    foreach ($entry as $canonical => $legacy_field) {
                        if (isset($fields[ $canonical ]) && is_string($legacy_field) && ! isset($fields[ $legacy_field ])) {
                            $fields[ $legacy_field ] = $fields[ $canonical ];
                        }
                    }

                    return $fields;
                }

                public function render($attrs, $content = null, $render_slug = null)
                {
                    ppcartcomp_remap_divi_module_props($this);

                    return parent::render($attrs, $content, $render_slug);
                }
            }
        }

        new PPCartComp_Legacy_Divi_Order_Form();
        $registered[ $legacy ] = true;
    }
}

if (! function_exists('ppcart_unregister_legacy_divi_modules')) {
    /**
     * Test helper. Divi cannot un-register a module slug in-process.
     *
     * @return void
     */
    function ppcart_unregister_legacy_divi_modules()
    {
        $registered = &ncs_cart_legacy_divi_modules_registered();
        $registered = [];
    }
}
