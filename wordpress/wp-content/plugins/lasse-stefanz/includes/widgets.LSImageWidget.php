<?php


// Load the widget on widgets_init
function ls_load_image_widget() {
    register_widget('LS_Image_Widget');
}
add_action('widgets_init', 'ls_load_image_widget');
remove_action('widgets_init', 'tribe_load_image_widget');


class LS_Image_Widget extends Tribe_Image_Widget {

    /**
     * Widget frontend output
     *
     * @param array $args
     * @param array $instance
     * @author Modern Tribe, Inc.
     */
    function widget( $args, $instance ) {

        return parent::widget($args, $instance);

        extract( $args );
        $instance = wp_parse_args( (array) $instance, self::get_defaults() );
        if ( !empty( $instance['imageurl'] ) || !empty( $instance['attachment_id'] ) ) {

            $instance['title'] = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
            $instance['description'] = apply_filters( 'widget_text', $instance['description'], $args, $instance );
            $instance['link'] = apply_filters( 'image_widget_image_link', esc_url( $instance['link'] ), $args, $instance );
            $instance['linktarget'] = apply_filters( 'image_widget_image_link_target', esc_attr( $instance['linktarget'] ), $args, $instance );
            $instance['width'] = apply_filters( 'image_widget_image_width', abs( $instance['width'] ), $args, $instance );
            $instance['height'] = apply_filters( 'image_widget_image_height', abs( $instance['height'] ), $args, $instance );
            $instance['align'] = apply_filters( 'image_widget_image_align', esc_attr( $instance['align'] ), $args, $instance );
            $instance['alt'] = apply_filters( 'image_widget_image_alt', esc_attr( $instance['alt'] ), $args, $instance );

            if ( !defined( 'IMAGE_WIDGET_COMPATIBILITY_TEST' ) ) {
                $instance['attachment_id'] = ( $instance['attachment_id'] > 0 ) ? $instance['attachment_id'] : $instance['image'];
                $instance['attachment_id'] = apply_filters( 'image_widget_image_attachment_id', abs( $instance['attachment_id'] ), $args, $instance );
                $instance['size'] = apply_filters( 'image_widget_image_size', esc_attr( $instance['size'] ), $args, $instance );
            }
            $instance['imageurl'] = apply_filters( 'image_widget_image_url', esc_url( $instance['imageurl'] ), $args, $instance );

            // No longer using extracted vars. This is here for backwards compatibility.
            extract( $instance );

            include( $this->getTemplateHierarchy( 'widget' ) );
        }
    }

    /**
     * Update widget options
     *
     * @param object $new_instance Widget Instance
     * @param object $old_instance Widget Instance
     * @return object
     * @author Modern Tribe, Inc.
     */
    function update( $new_instance, $old_instance ) {

        return parent::update($new_instance, $old_instance);

        $instance = $old_instance;
        $new_instance = wp_parse_args( (array) $new_instance, self::get_defaults() );
        $instance['title'] = strip_tags($new_instance['title']);
        if ( current_user_can('unfiltered_html') ) {
            $instance['description'] = $new_instance['description'];
        } else {
            $instance['description'] = wp_filter_post_kses($new_instance['description']);
        }
        $instance['link'] = $new_instance['link'];
        $instance['linktarget'] = $new_instance['linktarget'];
        $instance['width'] = abs( $new_instance['width'] );
        $instance['height'] =abs( $new_instance['height'] );
        if ( !defined( 'IMAGE_WIDGET_COMPATIBILITY_TEST' ) ) {
            $instance['size'] = $new_instance['size'];
        }
        $instance['align'] = $new_instance['align'];
        $instance['alt'] = $new_instance['alt'];

        // Reverse compatibility with $image, now called $attachement_id
        if ( !defined( 'IMAGE_WIDGET_COMPATIBILITY_TEST' ) && $new_instance['attachment_id'] > 0 ) {
            $instance['attachment_id'] = abs( $new_instance['attachment_id'] );
        } elseif ( $new_instance['image'] > 0 ) {
            $instance['attachment_id'] = $instance['image'] = abs( $new_instance['image'] );
            if ( class_exists('ImageWidgetDeprecated') ) {
                $instance['imageurl'] = ImageWidgetDeprecated::get_image_url( $instance['image'], $instance['width'], $instance['height'] );  // image resizing not working right now
            }
        }
        $instance['imageurl'] = $new_instance['imageurl']; // deprecated

        $instance['aspect_ratio'] = $this->get_image_aspect_ratio( $instance );

        return $instance;
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

    /**
     * Render the image html output.
     *
     * @param array $instance
     * @param bool $include_link will only render the link if this is set to true. Otherwise link is ignored.
     * @return string image html
     */
    private function get_image_html( $instance, $include_link = true ) {

        return get_image_html($instance, $include_link);

        // Backwards compatible image display.
        if ( $instance['attachment_id'] == 0 && $instance['image'] > 0 ) {
            $instance['attachment_id'] = $instance['image'];
        }

        $output = '';

        if ( $include_link && !empty( $instance['link'] ) ) {
            $attr = array(
                'href' => $instance['link'],
                'target' => $instance['linktarget'],
                'class' =>  $this->widget_options['classname'].'-image-link',
                'title' => ( !empty( $instance['alt'] ) ) ? $instance['alt'] : $instance['title'],
            );
            $attr = apply_filters('image_widget_link_attributes', $attr, $instance );
            $attr = array_map( 'esc_attr', $attr );
            $output = '<a';
            foreach ( $attr as $name => $value ) {
                $output .= sprintf( ' %s="%s"', $name, $value );
            }
            $output .= '>';
        }

        $size = $this->get_image_size( $instance );
        if ( is_array( $size ) ) {
            $instance['width'] = $size[0];
            $instance['height'] = $size[1];
        } elseif ( !empty( $instance['attachment_id'] ) ) {
            //$instance['width'] = $instance['height'] = 0;
            $image_details = wp_get_attachment_image_src( $instance['attachment_id'], $size );
            if ($image_details) {
                $instance['imageurl'] = $image_details[0];
                $instance['width'] = $image_details[1];
                $instance['height'] = $image_details[2];
            }
        }
        $instance['width'] = abs( $instance['width'] );
        $instance['height'] = abs( $instance['height'] );

        $attr = array();
        $attr['alt'] = $instance['title'];
        if (is_array($size)) {
            $attr['class'] = 'attachment-'.join('x',$size);
        } else {
            $attr['class'] = 'attachment-'.$size;
        }
        $attr['style'] = '';
        if (!empty($instance['width'])) {
            $attr['style'] .= "max-width: {$instance['width']}px;";
        }
        if (!empty($instance['height'])) {
            $attr['style'] .= "max-height: {$instance['height']}px;";
        }
        if (!empty($instance['align']) && $instance['align'] != 'none') {
            $attr['class'] .= " align{$instance['align']}";
        }
        $attr = apply_filters( 'image_widget_image_attributes', $attr, $instance );

        // If there is an imageurl, use it to render the image. Eventually we should kill this and simply rely on attachment_ids.
        if ( !empty( $instance['imageurl'] ) ) {
            // If all we have is an image src url we can still render an image.
            $attr['src'] = $instance['imageurl'];
            $attr = array_map( 'esc_attr', $attr );
            $hwstring = image_hwstring( $instance['width'], $instance['height'] );
            $output .= rtrim("<img $hwstring");
            foreach ( $attr as $name => $value ) {
                $output .= sprintf( ' %s="%s"', $name, $value );
            }
            $output .= ' />';
        } elseif( abs( $instance['attachment_id'] ) > 0 ) {
            $output .= wp_get_attachment_image($instance['attachment_id'], $size, false, $attr);
        }

        if ( $include_link && !empty( $instance['link'] ) ) {
            $output .= '</a>';
        }

        return $output;
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
