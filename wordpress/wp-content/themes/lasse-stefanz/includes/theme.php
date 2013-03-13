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



function ls_insert_hero()
{
    if (is_home() || is_front_page()) {
        get_template_part( 'hero' );
    }
}
add_action('hobo_inside_main', 'ls_insert_hero');
