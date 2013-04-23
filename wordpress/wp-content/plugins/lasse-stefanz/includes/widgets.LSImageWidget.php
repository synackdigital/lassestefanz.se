<?php


// Load the widget on widgets_init
function ls_load_image_widget() {
    register_widget('LS_Image_Widget');
}
add_action('widgets_init', 'ls_load_image_widget');
remove_action('widgets_init', 'tribe_load_image_widget');


class LS_Image_Widget extends Tribe_Image_Widget {

    function _set($number) {

        parent::_set($number);

        $all_settings = $this->get_settings();

        if (is_array($all_settings) && array_key_exists($number, $all_settings)) {
            $settings = $all_settings[$number];

            if (is_array($settings) && array_key_exists('size', $settings)) {
                $this->widget_options['classname'] .= ' size-' . $settings['size'];
            }
        }
    }

    /**
     * Render an array of default values.
     *
     * @return array default values
     */
    private static function get_defaults() {

        $defaults = parent::get_defaults();

        $defaults['size'] = array_shift(self::image_sizes());

        return $defaults;
    }

    protected static function image_sizes()
    {
        $possible_sizes = apply_filters( 'image_size_names_choose', array(
            'full'      => __('Full Size', 'image_widget'),
            'thumbnail' => __('Thumbnail', 'image_widget'),
            'medium'    => __('Medium', 'image_widget'),
            'large'     => __('Large', 'image_widget'),
        ) );

        return $possible_sizes;
    }

    function getTemplateHierarchy($template) {

        $orig_template = $template;

        // whether or not .php was added
        $template_slug = rtrim($template, '.php');
        $template = $template_slug . '.php';

        $file = dirname(__FILE__) . '/../views/' . $template;

        if (!file_exists($file)) {
            $file = parent::getTemplateHierarchy($template);
        }

        return apply_filters( 'ls_template_image-widget_'.$template, $file);
    }

}
