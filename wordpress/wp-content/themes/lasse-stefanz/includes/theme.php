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


function ls_campaign_slideshow($args = array(), $extra_args = array(), $echo = true)
{
    if (class_exists('LSSlideshow')) {
        return LSSlideshow::campaign_slideshow($args, $extra_args, $echo);
    }
}


function ls_instagram_feed($args = null) {
    $args = wp_parse_args( $args, array(
        'size' => null,
        'num' => null,
        'echo' => true,
    ) );
    extract($args);

    if (class_exists('LasseStefanz')) {

        $feed = LasseStefanz::instagram_feed(array_filter($args));

        if ($echo) {
            echo $feed;
        }

        return $feed;
    }

    return null;
}
