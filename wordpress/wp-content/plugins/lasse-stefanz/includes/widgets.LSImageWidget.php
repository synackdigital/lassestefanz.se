<?php


if (!defined('I_HAVE_SUPPORTED_THE_IMAGE_WIDGET')) {
    define( 'I_HAVE_SUPPORTED_THE_IMAGE_WIDGET', true );
}

// Load the widget on widgets_init
function ls_load_image_widget() {
    register_widget('LS_Image_Widget');
}
add_action('widgets_init', 'ls_load_image_widget');
remove_action('widgets_init', 'tribe_load_image_widget');


class LS_Image_Widget extends Tribe_Image_Widget {

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


    /**
     * Widget frontend output
     *
     * @param array $args
     * @param array $instance
     * @author Modern Tribe, Inc.
     */
    function widget( $args, $instance ) {

        $class = 'size-' . $instance['size'] . ' align' . $instance['align'];

        $args['before_widget'] = preg_replace('/(.*class=["\'])([^"\']*)(["\'].*)/i', "$1$2 $class$3", $args['before_widget']);

        return parent::widget($args, $instance);
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


    public function image_widget_image_attributes($args, $intance = null)
    {
        if (array_key_exists('style', $args)) {
            unset($args['style']);
        }

        if (array_key_exists('align', $args)) {
            unset($args['align']);
        }

        return $args;
    }
}

add_filter('image_widget_image_attributes', array('LS_Image_Widget', 'image_widget_image_attributes'), 100, 2);
