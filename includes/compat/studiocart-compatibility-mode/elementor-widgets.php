<?php

if (! defined('ABSPATH')) {
    exit;
}

if (! function_exists('ncs_cart_get_legacy_elementor_widget_map')) {
    /**
     * Canonical Elementor widget name => leftover widget name.
     *
     * @return array<string, string>
     */
    function ncs_cart_get_legacy_elementor_widget_map()
    {
        static $map = null;

        if (null !== $map) {
            return $map;
        }

        $map_file = __DIR__ . '/maps/elementor-widgets.php';
        $map      = is_readable($map_file) ? include $map_file : [];

        return is_array($map) ? $map : [];
    }
}

if (! function_exists('ncs_cart_legacy_elementor_widgets_registered')) {
    /**
     * @return array<string, bool>
     */
    function &ncs_cart_legacy_elementor_widgets_registered()
    {
        static $registered = [];

        return $registered;
    }
}

if (! function_exists('ppcartcomp_canonical_elementor_setting_key')) {
    /**
     * Map a leftover Elementor control id onto the canonical control id.
     *
     * @param string $legacy Leftover setting key.
     * @return string Canonical key, or empty when the key is not leftover.
     */
    function ppcartcomp_canonical_elementor_setting_key($legacy)
    {
        if (! is_string($legacy) || '' === $legacy) {
            return '';
        }

        if (0 === strpos($legacy, 'sc_countdown_')) {
            return 'ppcart_countdown_' . substr($legacy, strlen('sc_countdown_'));
        }

        if ('sc_template' === $legacy) {
            return 'ppcart_template';
        }

        return '';
    }
}

if (! function_exists('ppcartcomp_canonical_elementor_widget_type')) {
    /**
     * @param string $widget_type Stored widgetType.
     * @return string
     */
    function ppcartcomp_canonical_elementor_widget_type($widget_type)
    {
        if (! is_string($widget_type) || '' === $widget_type) {
            return '';
        }

        foreach (ncs_cart_get_legacy_elementor_widget_map() as $canonical => $legacy) {
            if ($legacy === $widget_type) {
                return $canonical;
            }
        }

        return $widget_type;
    }
}

if (! function_exists('ppcartcomp_rewrite_elementor_settings')) {
    /**
     * Copy leftover control keys onto canonical keys. Does not write post meta.
     *
     * @param array<string, mixed> $settings Elementor widget settings.
     * @return array<string, mixed>
     */
    function ppcartcomp_rewrite_elementor_settings(array $settings)
    {
        foreach ($settings as $key => $value) {
            $canonical = ppcartcomp_canonical_elementor_setting_key((string) $key);
            if ('' === $canonical || array_key_exists($canonical, $settings)) {
                continue;
            }

            $settings[ $canonical ] = $value;
        }

        return $settings;
    }
}

if (! function_exists('ppcartcomp_rewrite_elementor_elements')) {
    /**
     * Walk Elementor document elements and rewrite leftover widgetType + settings.
     *
     * Merchant Data migration may persist the return value. Runtime dual-register
     * must not write this back to `_elementor_data`.
     *
     * @param mixed $elements Elementor elements tree.
     * @return mixed
     */
    function ppcartcomp_rewrite_elementor_elements($elements)
    {
        if (! is_array($elements)) {
            return $elements;
        }

        foreach ($elements as $index => $element) {
            if (! is_array($element)) {
                continue;
            }

            if (isset($element['widgetType']) && is_string($element['widgetType'])) {
                $element['widgetType'] = ppcartcomp_canonical_elementor_widget_type($element['widgetType']);
            }

            if (isset($element['settings']) && is_array($element['settings'])) {
                $element['settings'] = ppcartcomp_rewrite_elementor_settings($element['settings']);
            }

            if (isset($element['elements']) && is_array($element['elements'])) {
                $element['elements'] = ppcartcomp_rewrite_elementor_elements($element['elements']);
            }

            $elements[ $index ] = $element;
        }

        return $elements;
    }
}

if (! function_exists('ppcartcomp_elementor_setting_is_populated')) {
    /**
     * @param mixed $value Setting value.
     * @return bool
     */
    function ppcartcomp_elementor_setting_is_populated($value)
    {
        if (null === $value || '' === $value || [] === $value || false === $value) {
            return false;
        }

        return true;
    }
}

if (! function_exists('ppcartcomp_remap_elementor_widget_settings')) {
    /**
     * In-memory leftover control remap for a live Elementor widget. Not a DB write.
     *
     * @param object $widget Elementor widget instance.
     * @return void
     */
    function ppcartcomp_remap_elementor_widget_settings($widget)
    {
        if (! is_object($widget) || ! method_exists($widget, 'get_settings') || ! method_exists($widget, 'set_settings')) {
            return;
        }

        $settings = $widget->get_settings();
        if (! is_array($settings)) {
            return;
        }

        $name            = method_exists($widget, 'get_name') ? (string) $widget->get_name() : '';
        $leftover_names  = array_values(ncs_cart_get_legacy_elementor_widget_map());
        $canonical_names = array_keys(ncs_cart_get_legacy_elementor_widget_map());
        $is_family       = in_array($name, $leftover_names, true) || in_array($name, $canonical_names, true);

        if (! $is_family) {
            return;
        }

        $is_leftover = in_array($name, $leftover_names, true);

        foreach ($settings as $key => $value) {
            $canonical = ppcartcomp_canonical_elementor_setting_key((string) $key);
            if ('' === $canonical) {
                continue;
            }

            $canonical_value = isset($settings[ $canonical ]) ? $settings[ $canonical ] : null;
            $canonical_set   = array_key_exists($canonical, $settings)
                && ppcartcomp_elementor_setting_is_populated($canonical_value);

            if ($is_leftover || ! $canonical_set) {
                $widget->set_settings($canonical, $value);
            }
        }
    }
}

if (! function_exists('ppcartcomp_elementor_before_render_remap')) {
    /**
     * @param object $widget Elementor widget.
     * @return void
     */
    function ppcartcomp_elementor_before_render_remap($widget)
    {
        ppcartcomp_remap_elementor_widget_settings($widget);
    }
}

if (! function_exists('ppcart_register_legacy_elementor_widgets')) {
    /**
     * Dual-register leftover Elementor widget names while Compatibility Mode is on.
     *
     * @param object|null $widgets_manager Elementor widgets manager.
     * @return void
     */
    function ppcart_register_legacy_elementor_widgets($widgets_manager = null)
    {
        if (! is_object($widgets_manager)) {
            if (! class_exists('\Elementor\Plugin', false) || ! isset(\Elementor\Plugin::$instance)) {
                return;
            }

            $widgets_manager = \Elementor\Plugin::$instance->widgets_manager;
        }

        if (! is_object($widgets_manager) || ! method_exists($widgets_manager, 'register')) {
            return;
        }

        $classes = __DIR__ . '/elementor-widget-classes.php';
        if (! is_readable($classes)) {
            return;
        }

        require_once $classes;

        $registered = &ncs_cart_legacy_elementor_widgets_registered();
        $class_map  = [
            'sc-countdown'     => 'PPCartComp_Legacy_Elementor_Countdown',
            'sc-orderform'     => 'PPCartComp_Legacy_Elementor_OrderForm',
            'bump-heading'     => 'PPCartComp_Legacy_Elementor_Bump_Heading',
            'bump-checkbox'    => 'PPCartComp_Legacy_Elementor_Bump_Checkbox',
            'bump-text'        => 'PPCartComp_Legacy_Elementor_Bump_Text',
            'bump-image'       => 'PPCartComp_Legacy_Elementor_Bump_Image',
        ];

        foreach (ncs_cart_get_legacy_elementor_widget_map() as $canonical => $legacy) {
            unset($canonical);

            if (! is_string($legacy) || '' === $legacy || isset($registered[ $legacy ])) {
                continue;
            }

            $class = isset($class_map[ $legacy ]) ? $class_map[ $legacy ] : '';
            if ('' === $class || ! class_exists($class, false)) {
                continue;
            }

            $widgets_manager->register(new $class());
            $registered[ $legacy ] = true;
        }

        if (function_exists('add_action')) {
            add_action('elementor/frontend/widget/before_render', 'ppcartcomp_elementor_before_render_remap', 5);
        }
    }
}

if (! function_exists('ppcart_unregister_legacy_elementor_widgets')) {
    /**
     * Test helper. Production leftover widgets live for the request only.
     *
     * @param object|null $widgets_manager Elementor widgets manager.
     * @return void
     */
    function ppcart_unregister_legacy_elementor_widgets($widgets_manager = null)
    {
        $registered = &ncs_cart_legacy_elementor_widgets_registered();

        if (is_object($widgets_manager) && method_exists($widgets_manager, 'unregister')) {
            foreach (array_keys($registered) as $legacy) {
                $widgets_manager->unregister($legacy);
            }
        }

        $registered = [];
    }
}
