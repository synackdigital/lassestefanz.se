<?php
/*
 * THIS FILE IS DEPRECATED!
 */

class LSSlideshow {

    protected static $instance;


    /**
     * Constructor. Don't call directly, @see instance() instead.
     *
     * @see instance()
     * @return void
     * @author Simon Fransson
     **/
    public function __construct()
    {
        add_action('registered_post_type', array(&$this, 'admin_hide_slideshow'), 10, 2);
        add_filter('wooslider_slider_types', array(&$this, 'wooslider_slider_types'), 100);
    }

    /**
     * Singleton accessor, returns the instance
     *
     * @return void
     * @author Simon Fransson
     **/
    public static function instance() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }


    public function admin_hide_slideshow($post_type, $args = null)
    {
        if ($post_type == 'slide') {

            global $wp_post_types;

            $type = $wp_post_types['slide'];
            $type->show_ui = false;
        }
    }


    public function wooslider_slider_types($types = array())
    {
        global $wooslider;
        remove_action( 'wp_footer', array( &$wooslider->frontend, 'load_slider_javascript' ) );
        add_action( 'wp_footer', array( &$wooslider->frontend, 'load_slider_javascript' ), 100 );


        if (class_exists('LSCampaign')) {
            $campaign = LSCampaign::instance();
            $types[$campaign->typeName()] = array(
                'name' => $campaign->pluralLabel(),
                'callback' => array(&$this, 'wooslider_slideshow_type_campaign'),
            );
        }

        return $types;
    }


    public function wooslider_slideshow_type_campaign($args = array(), $settings = array())
    {
        global $post;
        $slides = array();

        $defaults = array(
            'limit' => 3,
            'thumbnails' => true,
        );

        $args = wp_parse_args( $args, $defaults );

        $query_args = array(
            'post_type' => class_exists('LSCampaign') ? LSCampaign::instance()->typeName() : null,
            'numberposts' => intval( $args['limit'] )
        );

        $posts = get_posts( $query_args );

        if ( ! is_wp_error( $posts ) && ( count( $posts ) > 0 ) ) {
            foreach ( $posts as $k => $post ) {
                setup_postdata( $post );
                $content = get_the_title();
                if (!empty($content)) {
                    $content = sprintf('<h2 class="slide-title">%s</h2>', $content);
                }
                // $content = apply_filters( 'wooslider_slide_content_slides', $content, $args );

                $data = array(
                    'content' => '<div class="slide-content">' . "\n" . $content . "\n" . '</div>' . "\n"
                );

                if ( 'true' == $args['thumbnails'] || 1 == $args['thumbnails'] || true == $args['thumbnails'] ) {
                    $thumb_url = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), class_exists('LSCampaign') ? LSCampaign::image_size() : 'medium' );
                    if ( ! is_bool( $thumb_url ) && isset( $thumb_url[0] ) ) {
                        $data['attributes'] = array( 'data-thumb' => esc_url( $thumb_url[0] ) );
                    } else {
                        $data['attributes'] = array( 'data-thumb' => esc_url( WooSlider_Utils::get_placeholder_image() ) );
                    }
                }

                $slides[] = $data;
            }
            wp_reset_postdata();
        }

        return $slides;
    }

    public function campaign_slideshow($args = array(), $extra_args = array(), $echo = true)
    {
        if (function_exists('wooslider')) {
            $args = wp_parse_args(
                $args,
                array(
                    'prev_text' => __('Previous', 'ls'),
                    'next_text' => __('Next', 'ls'),
                    'control_nav' => null,
                    'slider_type' => class_exists('LSCampaign') ? LSCampaign::instance()->typeName() : null,
                )
            );

            $extra_args = wp_parse_args( $extra_args, array() );

            return wooslider($args, $extra_args, $echo);
        }

        return null;
    }
}

$lss = LSSlideshow::instance();
