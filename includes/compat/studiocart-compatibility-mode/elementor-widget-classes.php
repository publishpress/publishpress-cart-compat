<?php

if (! defined('ABSPATH')) {
    exit;
}

/**
 * Leftover Elementor widget names. Loaded only after Pro canonical widgets exist.
 * Hidden from the inserter; stored leftover widgetType still renders.
 */

if (class_exists('\Elementor\PPCart_Pro_Elementor_Countdown_Timer', false)
    && ! class_exists('PPCartComp_Legacy_Elementor_Countdown', false)
) {
    class PPCartComp_Legacy_Elementor_Countdown extends \Elementor\PPCart_Pro_Elementor_Countdown_Timer
    {
        public function get_name()
        {
            return 'sc-countdown';
        }

        public function show_in_panel()
        {
            return false;
        }

        protected function register_controls()
        {
            parent::register_controls();
            ppcartcomp_register_hidden_leftover_elementor_controls($this);
        }

        protected function get_init_settings()
        {
            $settings = parent::get_init_settings();

            return is_array($settings)
                ? ppcartcomp_overlay_leftover_elementor_settings(
                    $settings,
                    ppcartcomp_elementor_control_defaults($this)
                )
                : $settings;
        }
    }
}

if (class_exists('PPCart_Pro_Elementor_OrderForm', false)
    && ! class_exists('PPCartComp_Legacy_Elementor_OrderForm', false)
) {
    class PPCartComp_Legacy_Elementor_OrderForm extends PPCart_Pro_Elementor_OrderForm
    {
        public function get_name()
        {
            return 'sc-orderform';
        }

        public function show_in_panel()
        {
            return false;
        }

        protected function register_controls()
        {
            parent::register_controls();
            ppcartcomp_register_hidden_leftover_elementor_controls($this);
        }

        protected function get_init_settings()
        {
            $settings = parent::get_init_settings();

            return is_array($settings)
                ? ppcartcomp_overlay_leftover_elementor_settings(
                    $settings,
                    ppcartcomp_elementor_control_defaults($this)
                )
                : $settings;
        }
    }
}

if (class_exists('PPCart_Pro_Bump_Heading', false)
    && ! class_exists('PPCartComp_Legacy_Elementor_Bump_Heading', false)
) {
    class PPCartComp_Legacy_Elementor_Bump_Heading extends PPCart_Pro_Bump_Heading
    {
        public function get_name()
        {
            return 'bump-heading';
        }

        public function show_in_panel()
        {
            return false;
        }
    }
}

if (class_exists('\Elementor\PPCart_Pro_Bump_Checkbox', false)
    && ! class_exists('PPCartComp_Legacy_Elementor_Bump_Checkbox', false)
) {
    class PPCartComp_Legacy_Elementor_Bump_Checkbox extends \Elementor\PPCart_Pro_Bump_Checkbox
    {
        public function get_name()
        {
            return 'bump-checkbox';
        }

        public function show_in_panel()
        {
            return false;
        }
    }
}

if (class_exists('\Elementor\PPCart_Pro_Bump_Text', false)
    && ! class_exists('PPCartComp_Legacy_Elementor_Bump_Text', false)
) {
    class PPCartComp_Legacy_Elementor_Bump_Text extends \Elementor\PPCart_Pro_Bump_Text
    {
        public function get_name()
        {
            return 'bump-text';
        }

        public function show_in_panel()
        {
            return false;
        }
    }
}

if (class_exists('\Elementor\PPCart_Pro_Bump_Image', false)
    && ! class_exists('PPCartComp_Legacy_Elementor_Bump_Image', false)
) {
    class PPCartComp_Legacy_Elementor_Bump_Image extends \Elementor\PPCart_Pro_Bump_Image
    {
        public function get_name()
        {
            return 'bump-image';
        }

        public function show_in_panel()
        {
            return false;
        }
    }
}
