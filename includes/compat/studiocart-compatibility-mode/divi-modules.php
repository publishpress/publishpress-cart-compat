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

if (! function_exists('ppcartcomp_divi_canonical_field_defaults')) {
    /**
     * Canonical Divi field defaults from a get_fields() array. Not a map copy.
     *
     * @param array<string, mixed> $fields Module get_fields() result.
     * @return array<string, mixed>
     */
    function ppcartcomp_divi_canonical_field_defaults(array $fields)
    {
        $defaults = [];

        foreach (ncs_cart_get_legacy_divi_module_map() as $entry) {
            if (! is_array($entry) || empty($entry['fields']) || ! is_array($entry['fields'])) {
                continue;
            }

            foreach ($entry['fields'] as $canonical => $legacy) {
                unset($legacy);
                if (! is_string($canonical) || '' === $canonical || ! isset($fields[ $canonical ]) || ! is_array($fields[ $canonical ])) {
                    continue;
                }

                if (array_key_exists('default', $fields[ $canonical ])) {
                    $defaults[ $canonical ] = $fields[ $canonical ]['default'];
                }
            }
        }

        return $defaults;
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
     * @param object               $module              Divi module instance.
     * @param array<string, mixed> $canonical_defaults  Canonical field defaults keyed by canonical field id.
     * @return void
     */
    function ppcartcomp_remap_divi_module_props($module, array $canonical_defaults = [])
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

                if (! isset($module->props[ $legacy ])) {
                    continue;
                }

                $canonical_value = array_key_exists($canonical, $module->props) ? $module->props[ $canonical ] : null;
                $canonical_set   = array_key_exists($canonical, $module->props)
                    && '' !== $canonical_value
                    && null !== $canonical_value;

                $canonical_is_default = array_key_exists($canonical, $canonical_defaults)
                    && ppcartcomp_value_equals_default($canonical_value, $canonical_defaults[ $canonical ]);

                if (! $canonical_set || $canonical_is_default) {
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

                /**
                 * @var array<string, mixed>|null
                 */
                public $ppcartcomp_canonical_defaults = null;

                public function init()
                {
                    parent::init();
                    $this->name .= ' (' . esc_html__('legacy', 'publishpress-cart-compat') . ')';
                }

                public function get_fields()
                {
                    $fields = parent::get_fields();
                    $map    = ncs_cart_get_legacy_divi_module_map();
                    $entry  = isset($map['ppcart_order_form']['fields']) && is_array($map['ppcart_order_form']['fields'])
                        ? $map['ppcart_order_form']['fields']
                        : [];

                    foreach ($entry as $canonical => $legacy_field) {
                        if (isset($fields[ $canonical ]) && is_string($legacy_field) && ! isset($fields[ $legacy_field ])) {
                            $copy          = $fields[ $canonical ];
                            $copy['label'] = (isset($copy['label']) ? $copy['label'] : $legacy_field)
                                . ' (' . esc_html__('legacy', 'publishpress-cart-compat') . ')';
                            $fields[ $legacy_field ] = $copy;
                        }
                    }

                    return $fields;
                }

                public function render($attrs, $content = null, $render_slug = null)
                {
                    if (null === $this->ppcartcomp_canonical_defaults) {
                        $fields = (isset($this->fields_unprocessed) && is_array($this->fields_unprocessed) && [] !== $this->fields_unprocessed)
                            ? $this->fields_unprocessed
                            : [];
                        if ([] !== $fields) {
                            $this->ppcartcomp_canonical_defaults = ppcartcomp_divi_canonical_field_defaults($fields);
                        }
                    }

                    if (is_array($this->ppcartcomp_canonical_defaults)) {
                        ppcartcomp_remap_divi_module_props($this, $this->ppcartcomp_canonical_defaults);
                    }

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
