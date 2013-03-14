<?php
/**
 * A nice WordPress theme by <a href=http://www.lassestefanz.se>LS Produktions AB</a> built on Hobo Theme
 * @package lasse-stefanz
 * @subpackage Theme specific functions
 */


/**
 * Adds theme specific image sizes
 * @return void
 */
function ls_setup_images()
{
    if (function_exists('add_image_size')) {

        add_image_size(
            LS_ALBUM_IMAGE_SIZE,
            LS_ALBUM_IMAGE_SIZE_WIDTH,
            LS_ALBUM_IMAGE_SIZE_HEIGHT,
            LS_ALBUM_IMAGE_SIZE_CROP
        );

        add_image_size(
            LS_CAMPAIGN_IMAGE_SIZE,
            LS_CAMPAIGN_IMAGE_SIZE_WIDTH,
            LS_CAMPAIGN_IMAGE_SIZE_HEIGHT,
            LS_CAMPAIGN_IMAGE_SIZE_CROP
        );
    }

}
add_action('init', 'ls_setup_images');

add_filter('lc_album_image_size', function() { return LS_ALBUM_IMAGE_SIZE; });
add_filter('ls_campaign_image_size', function() { return LS_CAMPAIGN_IMAGE_SIZE; });

function ls_home_query($query)
{
    if ((is_home() || is_front_page()) && $query->is_main_query()) {
        $query->set( 'posts_per_page', '3' );
    }
}
add_action( 'pre_get_posts', 'ls_home_query' );

function ls_setup_hero()
{
    if (ls_has_hero()) {
        add_action('hobo_before_main', 'ls_insert_hero');
    }
}
add_action('wp', 'ls_setup_hero');

function ls_has_hero()
{
    return is_home() || is_front_page();
}

function ls_insert_hero()
{
    if (ls_has_hero()) {
        get_template_part( 'hero' );
    }
}

function ls_body_class($classes)
{
    if (ls_has_hero()) {
        $classes[] = 'has-hero';
    }

    return $classes;
}
add_filter('body_class', 'ls_body_class');


// START: MOVE TO PLUGIN
function ls_registered_post_type($post_type, $args = null)
{
    if ($post_type == 'slide') {

        global $wp_post_types;

        $type = $wp_post_types['slide'];
        $type->show_ui = false;

    }
}
add_action('registered_post_type', 'ls_registered_post_type', 10, 2);



function ls_wooslider_slider_types($types = array())
{
    if (class_exists('LSCampaign')) {
        $campaign = LSCampaign::instance();
        $types[$campaign->typeName() . 's'] = array(
            'name' => $campaign->pluralLabel(),
            'callback' => 'ls_wooslider_slideshow_type_campaign',
        );
    }

    return $types;
}
add_filter('wooslider_slider_types', 'ls_wooslider_slider_types', 100);


function ls_wooslider_slideshow_type_campaign($args = array(), $settings = array())
{
    global $post;
    $slides = array();

    $defaults = array(
        'limit' => '5',
        'slide_page' => '',
        'thumbnails' => ''
    );

    $args = wp_parse_args( $args, $defaults );

    $query_args = array( 'post_type' => LSCampaign::instance()->typeName(), 'numberposts' => intval( $args['limit'] ) );

    if ( $args['slide_page'] != '' ) {
        $cats_split = explode( ',', $args['slide_page'] );
        $query_args['tax_query'] = array();
        foreach ( $cats_split as $k => $v ) {
            $query_args['tax_query'][] = array(
                    'taxonomy' => 'slide-page',
                    'field' => 'slug',
                    'terms' => esc_attr( trim( rtrim( $v ) ) )
                );
        }
    }

    $posts = get_posts( $query_args );

    if ( ! is_wp_error( $posts ) && ( count( $posts ) > 0 ) ) {
        foreach ( $posts as $k => $post ) {
            setup_postdata( $post );
            $content = get_the_content();

            $data = array( 'content' => '<div class="slide-content">' . "\n" . apply_filters( 'wooslider_slide_content_slides', $content, $args ) . "\n" . '</div>' . "\n" );
            if ( 'true' == $args['thumbnails'] || 1 == $args['thumbnails'] ) {
                $thumb_url = wp_get_attachment_image_src( get_post_thumbnail_id( get_the_ID() ), 'medium' );
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

// END: MOVE TO PLUGIN


function ls_campaign_slideshow($args = null)
{
    return;
    var_dump(WooSlider_Utils::get_slider_types());

    $args = wp_parse_args( $args, array(
        'prev_text' => __('Previous', 'ls'),
        'next_text' => __('Next', 'ls'),
        'slider_type' => 'posts',
    ) );

    wooslider($args);
}
