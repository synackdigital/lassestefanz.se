<?php


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
        add_action('registered_post_type', array(&$this, 'registered_post_type'), 10, 2);
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


    public function registered_post_type($post_type, $args = null)
    {
        if ($post_type == 'slide') {

            global $wp_post_types;

            $type = $wp_post_types['slide'];
            $type->show_ui = false;
        }
    }


    public function wooslider_slider_types($types = array())
    {
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
                $content = get_the_content();

                $data = array(
                    'content' => '<div class="slide-content">' . "\n" . apply_filters( 'wooslider_slide_content_slides', $content, $args ) . "\n" . '</div>' . "\n"
                );

                if ( 'true' == $args['thumbnails'] || 1 == $args['thumbnails'] || true == $args['thumbnails'] ) {
                    $thumb_url = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), class_exists('LSCampaign') ? LSCampaign::image_size() : 'medium' );
                    if ( ! is_bool( $thumb_url ) && isset( $thumb_url[0] ) ) {
                        $data['attributes'] = array( 'data-thumb' => esc_url( $thumb_url[0] ) );
                    } else {
                        $data['attributes'] = array( 'data-thumb' => esc_url( WooSlider_Utils::get_placeholder_image() ) );
                    }
                }

                $data['content'] = "CONTENT";

                $slides[] = $data;
            }
            wp_reset_postdata();
        }

        return $slides;
    }

    public function campaign_slideshow($args = array(), $extra_args = array(), $echo = true)
    {
        $args = wp_parse_args(
            $args,
            array(
                'prev_text' => __('Previous', 'ls'),
                'next_text' => __('Next', 'ls'),
                'slider_type' => class_exists('LSCampaign') ? LSCampaign::instance()->typeName() : null,
            )
        );

        $extra_args = wp_parse_args( $extra_args, array() );

        return wooslider($args, $extra_args, $echo);
    }
}

$lss = LSSlideshow::instance();
