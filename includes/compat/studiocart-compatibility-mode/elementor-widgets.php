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
            $suffix = substr($legacy, strlen('sc_countdown_'));
            if (substr($suffix, -8) === '_section' || 'section' === $suffix) {
                return '';
            }

            return 'ppcart_countdown_' . $suffix;
        }

        if ('sc_template' === $legacy) {
            return 'ppcart_template';
        }

        return '';
    }
}

if (! function_exists('ppcartcomp_legacy_elementor_setting_key')) {
    /**
     * Inverse of ppcartcomp_canonical_elementor_setting_key().
     *
     * @param string                    $canonical Canonical setting key.
     * @param array<string, mixed>|null $control   Canonical control definition when known.
     * @return string Leftover key, or empty when the key is not in this family.
     */
    function ppcartcomp_legacy_elementor_setting_key($canonical, $control = null)
    {
        if (! is_string($canonical) || '' === $canonical) {
            return '';
        }

        if (substr($canonical, -8) === '_section') {
            return '';
        }

        if (is_array($control)) {
            $type = isset($control['type']) ? (string) $control['type'] : '';
            if (in_array($type, [ 'section', 'tab', 'heading', 'divider', 'raw_html' ], true)) {
                return '';
            }
        }

        if (0 === strpos($canonical, 'ppcart_countdown_')) {
            return 'sc_countdown_' . substr($canonical, strlen('ppcart_countdown_'));
        }

        if ('ppcart_template' === $canonical) {
            return 'sc_template';
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

if (! function_exists('ppcartcomp_value_equals_default')) {
    /**
     * Whether a stored value is still the control default.
     *
     * Divi shortcode attrs are strings; Pro field defaults may be ints.
     * Arrays compare recursively; extra keys on the stored value are ignored
     * so Elementor filled slider/dimensions (unit, isLinked) still match a
     * shorter control['default'].
     *
     * @param mixed $value   Stored canonical value.
     * @param mixed $default Control/field default.
     * @return bool
     */
    function ppcartcomp_value_equals_default($value, $default)
    {
        if ($value === $default) {
            return true;
        }

        if (is_array($value) && is_array($default)) {
            if ([] === $default) {
                return [] === $value;
            }

            foreach ($default as $key => $default_item) {
                if (! array_key_exists($key, $value)) {
                    return false;
                }

                if (! ppcartcomp_value_equals_default($value[ $key ], $default_item)) {
                    return false;
                }
            }

            return true;
        }

        if (is_bool($value) || is_bool($default) || is_array($value) || is_array($default) || is_object($value) || is_object($default)) {
            return false;
        }

        if (is_scalar($value) && is_scalar($default)) {
            return (string) $value === (string) $default;
        }

        return false;
    }
}

if (! function_exists('ppcartcomp_elementor_normalize_control_default')) {
    /**
     * Fill slider/dimensions keys so live get_value() and the no-Elementor
     * fallback share one shape. unit/isLinked then participate in compare.
     *
     * @param array<string, mixed> $control Canonical control definition.
     * @param mixed                $explicit Raw default from get_value() or control['default'].
     * @return mixed
     */
    function ppcartcomp_elementor_normalize_control_default(array $control, $explicit)
    {
        $type = isset($control['type']) ? (string) $control['type'] : '';
        $unit = 'px';
        if (isset($control['default_unit']) && is_string($control['default_unit']) && '' !== $control['default_unit']) {
            $unit = $control['default_unit'];
        }

        if ('slider' === $type) {
            $base = is_array($explicit) ? $explicit : [];
            if (! array_key_exists('size', $base)) {
                $base['size'] = '';
            }
            if (! array_key_exists('unit', $base)) {
                $base['unit'] = $unit;
            }

            return $base;
        }

        if ('dimensions' === $type) {
            $base = is_array($explicit) ? $explicit : [];
            foreach ([ 'top', 'right', 'bottom', 'left' ] as $side) {
                if (! array_key_exists($side, $base)) {
                    $base[ $side ] = '';
                }
            }
            if (! array_key_exists('unit', $base)) {
                $base['unit'] = $unit;
            }
            if (! array_key_exists('isLinked', $base)) {
                $base['isLinked'] = true;
            }

            return $base;
        }

        return $explicit;
    }
}

if (! function_exists('ppcartcomp_elementor_control_effective_default')) {
    /**
     * Effective default Elementor would pre-fill for a control.
     *
     * Prefers the live control object's get_value(), then normalizes slider
     * and dimensions so unit/isLinked are comparable.
     *
     * @param array<string, mixed> $control Canonical control definition.
     * @return mixed Null when this control has no usable default.
     */
    function ppcartcomp_elementor_control_effective_default(array $control)
    {
        $type     = isset($control['type']) ? (string) $control['type'] : '';
        $explicit = array_key_exists('default', $control) ? $control['default'] : null;

        if (class_exists('\Elementor\Plugin', false)
            && isset(\Elementor\Plugin::$instance)
            && is_object(\Elementor\Plugin::$instance)
            && isset(\Elementor\Plugin::$instance->controls_manager)
        ) {
            $manager = \Elementor\Plugin::$instance->controls_manager;
            if (is_object($manager) && method_exists($manager, 'get_control')) {
                $obj = $manager->get_control($type);
                if (is_object($obj) && method_exists($obj, 'get_value')) {
                    $explicit = $obj->get_value($control, []);
                }
            }
        }

        return ppcartcomp_elementor_normalize_control_default($control, $explicit);
    }
}

if (! function_exists('ppcartcomp_elementor_control_defaults')) {
    /**
     * @param object $widget Elementor widget instance.
     * @return array<string, mixed>
     */
    function ppcartcomp_elementor_control_defaults($widget)
    {
        $defaults = [];
        if (! is_object($widget) || ! method_exists($widget, 'get_controls')) {
            return $defaults;
        }

        $controls = $widget->get_controls();
        if (! is_array($controls)) {
            return $defaults;
        }

        foreach ($controls as $id => $control) {
            if (! is_string($id) || ! is_array($control)) {
                continue;
            }

            $default = ppcartcomp_elementor_control_effective_default($control);
            if (null === $default) {
                continue;
            }

            $defaults[ $id ] = $default;
        }

        return $defaults;
    }
}

if (! function_exists('ppcartcomp_overlay_leftover_elementor_settings')) {
    /**
     * Copy leftover control values onto canonical keys when canonical is unset
     * or still equal to its control default. Merchant edits to canonical win.
     *
     * In-memory only; does not write `_elementor_data`.
     *
     * @param array<string, mixed> $settings           Elementor widget settings.
     * @param array<string, mixed> $canonical_defaults Canonical control defaults keyed by canonical id.
     * @return array<string, mixed>
     */
    function ppcartcomp_overlay_leftover_elementor_settings(array $settings, array $canonical_defaults = [])
    {
        foreach ($settings as $key => $value) {
            $canonical = ppcartcomp_canonical_elementor_setting_key((string) $key);
            if ('' === $canonical || ! ppcartcomp_elementor_setting_is_populated($value)) {
                continue;
            }

            $canonical_value = array_key_exists($canonical, $settings) ? $settings[ $canonical ] : null;
            $canonical_set   = array_key_exists($canonical, $settings)
                && ppcartcomp_elementor_setting_is_populated($canonical_value);

            $canonical_is_default = array_key_exists($canonical, $canonical_defaults)
                && ppcartcomp_value_equals_default($canonical_value, $canonical_defaults[ $canonical ]);

            if (! $canonical_set || $canonical_is_default) {
                $settings[ $canonical ] = $value;
            }
        }

        return $settings;
    }
}

if (! function_exists('ppcartcomp_hidden_leftover_elementor_control_args')) {
    /**
     * Placement args so leftover HIDDEN controls attach to the canonical section.
     *
     * @param array<string, mixed> $canonical_control Canonical control definition.
     * @return array<string, mixed>
     */
    function ppcartcomp_hidden_leftover_elementor_control_args(array $canonical_control)
    {
        $args = [];
        if (! empty($canonical_control['section']) && is_string($canonical_control['section'])) {
            $args['section'] = $canonical_control['section'];
        }

        if (isset($canonical_control['tab']) && (is_string($canonical_control['tab']) || is_int($canonical_control['tab']))) {
            $args['tab'] = $canonical_control['tab'];
        }

        return $args;
    }
}

if (! function_exists('ppcartcomp_planned_hidden_leftover_elementor_controls')) {
    /**
     * Leftover HIDDEN control ids to register, keyed by leftover id.
     *
     * @param array<string, array<string, mixed>> $controls Canonical control stack.
     * @return array<string, array<string, mixed>>
     */
    function ppcartcomp_planned_hidden_leftover_elementor_controls(array $controls)
    {
        $to_add = [];
        foreach ($controls as $id => $control) {
            if (! is_string($id) || ! is_array($control)) {
                continue;
            }

            $legacy = ppcartcomp_legacy_elementor_setting_key($id, $control);
            if ('' === $legacy || isset($controls[ $legacy ])) {
                continue;
            }

            $args = ppcartcomp_hidden_leftover_elementor_control_args($control);
            if (empty($args['section'])) {
                continue;
            }

            $to_add[ $legacy ] = $args;
        }

        return $to_add;
    }
}

if (! function_exists('ppcartcomp_elementor_control_stack_is_open')) {
    /**
     * @param object $widget Elementor widget instance.
     * @return bool
     */
    function ppcartcomp_elementor_control_stack_is_open($widget)
    {
        if (! is_object($widget) || ! method_exists($widget, 'get_current_section')) {
            return false;
        }

        $open = $widget->get_current_section();

        return is_array($open) && [] !== $open;
    }
}

if (! function_exists('ppcartcomp_hidden_leftover_elementor_add_control_args')) {
    /**
     * @param array<string, mixed> $args        Section/tab args from the canonical control.
     * @param string               $hidden_type Elementor HIDDEN type constant/value.
     * @param bool                 $stack_open  Whether a section is already open on the stack.
     * @return array<string, mixed>
     */
    function ppcartcomp_hidden_leftover_elementor_add_control_args(array $args, $hidden_type, $stack_open)
    {
        $args['type'] = $hidden_type;
        if ($stack_open) {
            unset($args['section'], $args['tab']);
        }

        return $args;
    }
}

if (! function_exists('ppcartcomp_add_hidden_leftover_elementor_controls')) {
    /**
     * Register leftover HIDDEN controls on a widget. $hidden_type is injected so
     * unit tests do not need to define \Elementor\Controls_Manager.
     *
     * @param object $widget      Elementor widget instance.
     * @param string $hidden_type Elementor HIDDEN type constant/value.
     * @return void
     */
    function ppcartcomp_add_hidden_leftover_elementor_controls($widget, $hidden_type)
    {
        if (! is_object($widget) || ! method_exists($widget, 'get_controls') || ! method_exists($widget, 'add_control')) {
            return;
        }

        $controls = $widget->get_controls();
        if (! is_array($controls)) {
            return;
        }

        $to_add     = ppcartcomp_planned_hidden_leftover_elementor_controls($controls);
        $stack_open = ppcartcomp_elementor_control_stack_is_open($widget);

        foreach ($to_add as $legacy => $args) {
            $widget->add_control(
                $legacy,
                ppcartcomp_hidden_leftover_elementor_add_control_args($args, $hidden_type, $stack_open)
            );
        }
    }
}

if (! function_exists('ppcartcomp_register_hidden_leftover_elementor_controls')) {
    /**
     * Register leftover control ids as HIDDEN so an editor save keeps stored keys.
     *
     * Selectors stay on the canonical controls. CSS uses
     * ppcartcomp_overlay_leftover_elementor_settings() instead of duplicating
     * leftover selectors.
     *
     * @param object $widget Elementor widget instance.
     * @return void
     */
    function ppcartcomp_register_hidden_leftover_elementor_controls($widget)
    {
        if (! class_exists('\Elementor\Controls_Manager', false)) {
            return;
        }

        ppcartcomp_add_hidden_leftover_elementor_controls($widget, \Elementor\Controls_Manager::HIDDEN);
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

        $overlaid = ppcartcomp_overlay_leftover_elementor_settings(
            $settings,
            ppcartcomp_elementor_control_defaults($widget)
        );

        foreach ($overlaid as $key => $value) {
            if (! array_key_exists($key, $settings) || $settings[ $key ] !== $value) {
                $widget->set_settings($key, $value);
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

        static $before_render_hooked = false;
        if (! $before_render_hooked && function_exists('add_action')) {
            add_action('elementor/frontend/widget/before_render', 'ppcartcomp_elementor_before_render_remap', 5);
            $before_render_hooked = true;
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
