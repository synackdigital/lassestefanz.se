<?php
/**
 * A nice WordPress theme by <a href=http://www.lassestefanz.se>LS Produktions AB</a> built on Hobo Theme
 * @package lasse-stefanz
 * @subpackage Theme specific functions
 */


/* Additional theme specific code goes here */
/*
// Add post types of "Talk" and "Event"
function nc_custom_post_types() {
    register_post_type( 'talk',
        array(
            'labels' => array(
                'name' => __( 'Talks' ),
                'singular_name' => __( 'Talk' )
            ),
        'public' => true,
        'has_archive' => true,
        'taxonomies' => array('talktype', 'category'),
        )
    );
    register_post_type( 'event',
        array(
            'labels' => array(
                'name' => __( 'Events' ),
                'singular_name' => __( 'Event' )
            ),
        'public' => true,
        'has_archive' => true,
        )
    );
}
add_action( 'init', 'nc_custom_post_types' );


function nc_custom_taxonomies() {
        // Add new "talktype" taxonomy to "talk" post type
        register_taxonomy('talktype', 'talk', array(
                'hierarchical' => true,
                // This array of options controls the labels displayed in the WordPress Admin UI
                'labels' => array(
                        'name' => _x( 'Talk Types', 'taxonomy general name' ),
                        'singular_name' => _x( 'Talk Type', 'taxonomy singular name' ),
                        'search_items' =>  __( 'Search Talk Types' ),
                        'all_items' => __( 'All Talk Types' ),
                        'parent_item' => __( 'Parent Talk Type' ),
                        'parent_item_colon' => __( 'Parent Talk Type:' ),
                        'edit_item' => __( 'Edit Talk Type' ),
                        'update_item' => __( 'Update Talk Type' ),
                        'add_new_item' => __( 'Add New Talk Type' ),
                        'new_item_name' => __( 'New Talk Type Name' ),
                        'menu_name' => __( 'Talk Types' ),
                ),
                'query_var' => true,
                // Control the slugs used for this taxonomy
                'rewrite' => array(
                        'slug' => 'talktype',
                        'with_front' => false, // Don't display the category base before "/locations/"
                        'hierarchical' => true // This will allow URL's like "/locations/boston/cambridge/"
                ),
        ));
}
add_action( 'after_setup_theme', 'nc_custom_taxonomies', 0 );

function sunday_query_args() {

    $nextSundayTalkArgs = array(
        'post_type' => 'talk',
        'posts_per_page' => 1,
        'tax_query' => array(
            array(
                'taxonomy' => 'talktype',
                'field' => 'slug',
                'terms' => 'sunday-talk'
            )
        )
    );

    return $nextSundayTalkArgs;
}
*/
