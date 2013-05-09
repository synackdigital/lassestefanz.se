<?php
/**
 * A nice WordPress theme by <a href=http://www.lassestefanz.se>LS Produktions AB</a> built on Hobo Theme
 * @package lasse-stefanz
 */

include_once(dirname(__FILE__) . '/includes/defines.php');

/**
 * Callback function for 'init' action. Performs basic theme setup. Enqueues the theme styles.
 *
 * @since 1.0
 *
 * @return void
 * @author LS Produktions AB
 **/
function ls_setup() {

    add_filter('hobo_class_for_element_id', 'ls_class_for_element_id', 10, 2);
    add_filter('hobo_inner_classes_for_parent', 'ls_inner_classes_for_parent', 10, 2);

    if ( hobo_should_enqueue() ) {

        // $ws_token = 'wooslider';
        // global $wooslider;
        // if ($wooslider) {
        //     $ws_token = $wooslider->token;
        // }

        wp_enqueue_script( 'ls.lasse-stefanz', get_stylesheet_directory_uri() . '/js/lasse-stefanz.min.js', array('jquery'), hobo_version(), hobo_scripts_in_footer() );

        wp_localize_script( 'ls.lasse-stefanz', 'LS', array(
            'css3_multi_column_js_src' => get_stylesheet_directory_uri() . '/js/css3-multi-column.min.js',
        ) );
    }

    ls_setup_menus();
}
add_action('init', 'ls_setup');



function ls_setup_menus() {
    unregister_nav_menu('primary');

    register_nav_menus( array(
        'left' => __( 'Left Hand Navigation', 'lasse-stefanz' ),
        'right' => __( 'Right Hand Navigation', 'lasse-stefanz' ),
    ) );

    add_action('hobo_nav_menu_args', 'ls_nav_menu_args');
    add_action('hobo_inside_access', 'ls_inside_access', 1);
}

function ls_nav_menu_args($args = null) {

    $args['container_id'] = 'menu-left';
    $args['container_class'] = 'nav-menu';
    $args['theme_location'] = 'left';

    return $args;
}

function ls_inside_access() {

    wp_nav_menu( array( 'container' => 'nav', 'container_id' => 'menu-right', 'container_class' => 'nav-menu', 'theme_location' => 'right', 'fallback_cb' => null ) );
}


/* Disables the default stylesheet, useful for including the default style via sass */
add_filter('hobo_should_enqueue_stylesheet', create_function('$s', 'return !ls_uses_sass();'));

/**
 * Specifies wether to use sass or not.
 *
 * @since 1.0
 *
 * @return void
 * @author LS Produktions AB
 **/
function ls_uses_sass() {
    return false;
}

/**
 * Specifies wether to use a single concatenated stylesheet with sass.
 *
 * @since 1.0
 *
 * @return void
 * @author LS Produktions AB
 **/
function ls_uses_single_sass_stylesheet() {
    return false;
}


/**
 * Loads scripts in header instead of footer as per default
 **/
//add_filter('hobo_scripts_in_footer', '__return_false')


/**
 * Callback function for 'hobo_login_logo_filename' filter. Add custom logo to login form.
 *
 * @since 1.0
 *
 * @see 'hobo_login_logo_url' filter
 *
 * @param string $filename Name of file located in images directory to use as login page logo
 * @return string Url to logo used on login page
 * @author LS Produktions AB
 **/
function ls_login_logo_filename($filename) {
    return 'lslogo-whiteleather.png'; // Defaults to logo.png
}
add_filter('hobo_login_logo_filename', 'ls_login_logo_filename');


/**
 * Callback function for 'hobo_login_logo_filename' filter. Adjust size of, and add custom styling, to login form logo.
 *
 * @since 1.0
 * *
 * @param array $style Key-value encoded array of CSS properties
 * @return array
 * @author LS Produktions AB
 **/
function ls_login_logo_style($style) {

    $style['width'] = '346px'; // Width of login logo
    $style['height'] = '197px'; // Height of login logo
    $style['margin'] = '0 0 10px -10px'; // Wider than standard (326), so we'll compensate for that

    return $style;
}
add_filter('hobo_login_logo_style', 'ls_login_logo_style');



/**
 * Callback function for 'hobo_child_theme_name' filter. Lets hobo-theme know our names, enabling localization amongst other th0ings.
 *
 * @since 1.0
 * *
 * @return string Name of theme
 * @author LS Produktions AB
 **/
function ls_child_theme_name() {
    return basename(dirname(__FILE__));
}
add_filter('hobo_child_theme_name', 'ls_child_theme_name');


/**
 * Unregisters widget areas that aren't needed
 *
 * @since 1.0
 *
 * @return void
 * @author LS Produktions AB
 **/
function ls_widgets_init() {

    // Widget areas defined in hobo-theme
    $widget_areas = array(
        //'primary-widget-area',
        'secondary-widget-area',
        'first-footer-widget-area',
        'second-footer-widget-area',
        'third-footer-widget-area',
        'fourth-footer-widget-area',
    );

    foreach ($widget_areas as $area) {
        unregister_sidebar($area);
    }

    register_sidebar( array(
        'name' => __( 'Banners Widget Area', 'lasse-stefanz' ),
        'id' => 'home-widget-area',
        'description' => __( 'The home page widget area', 'lasse-stefanz' ),
        'before_widget' => '<li id="%1$s" class="widget-container %2$s">',
        'after_widget' => '</li>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ) );
}
add_action( 'widgets_init', 'ls_widgets_init', 20 );


/**
 * Callback function for 'hobo_class_for_element_id' filter. Add CSS classes to some standard elements
 *
 * @since 1.0
 *
 * @param array $style array of CSS classes
 * @param string $elem_id HTML id of element being evaluated
 * @return array List of classes for $elem_id
 * @author LS Produktions AB
 **/
function ls_class_for_element_id($classes, $elem_id) {

    switch ($elem_id) {
        case 'wrapper':
            //$classes[] = 'container';
            break;
        case 'header':
        case 'main':
        case 'footer':
            $classes[] = 'container';
            break;


    }

    return $classes;
}

/**
 * Callback function for 'hobo_inner_classes_for_parent' filter. Add CSS classes to some standard elements
 *
 * @since 1.0
 *
 * @param array $style array of CSS classes
 * @param string $elem_id HTML id of parent element being evaluated
 * @return array List of classes for $elem_id
 * @author LS Produktions AB
 **/
function ls_inner_classes_for_parent($classes, $elem_id) {

    switch ($elem_id) {
        case 'wrapper':
            //$classes[] = 'container';
            break;
        case 'header':
        case 'main':
        case 'footer':
            $classes[] = 'row-fluid';
            break;
    }

    return $classes;
}



/**
 * Disables the HTML5 Boilerplate .htaccess rules added by hobo-theme
 * Try this if you are getting Internal Server Errors when changing the permalink structure
 *
 * @since 1.0
 *
 * @param string $rules Rules generated by WordPress and hobo-theme
 * @return null
 * @author LS Produktions AB
 **/
function ls_rewrite_rules($rules) {
    return null;
}
//add_action('hobo_rewrite_rules', 'ls_rewrite_rules');


/**
 * Disables the Options keywords in the.htaccess rewrite rules
 * as this has been known to cause 500 Internal Server Error on some hosts (one.com)
 *
 * @param  boolean $disable Previous value
 * @return boolean          True
 */
function ls_rewrite_rules_disable_options($disable = null) {
    return true;
}
//add_filter('hobo_rewrite_rules_disable_options', 'ls_rewrite_rules_disable_options');


/**
 * Callback function for 'hobo_humans_txt_additional_humans' filter. Sets up humans.txt
 *
 * @since 1.0
 *
 * @param array $humans array with data for all of the humans
 * @return array
 * @author LS Produktions AB
 **/
function ls_humans($humans) {

    $humans = (array)$humans;

    /* Add additional humans */

    $humans[] = array(
        'Graphic design/Development' => 'Fredrik Broman',
        'Site' => 'https://www.facebook.com/synackdigital',
        'Twitter' => 'frebro',
        'Location' => 'Malmö, Sweden'
    );

    $humans[] = array(
        'Development' => 'Simon Fransson',
        'Site' => 'http://dessibelle.se',
        'Twitter' => 'dessibelle',
        'Location' => 'Malmö, Sweden'
    );

    $humans[] = array(
        'Content production' => 'Anna Neah',
        'Site' => 'http://www.mermusik.se',
        'Twitter' => null, //'jdoe',
        'Location' => 'Karlshamn, Sweden'
    );

    return $humans;
}
add_filter('hobo_humans_txt_additional_humans', 'ls_humans');


/* Enable .inner classes for the main layout elements */
add_action('hobo_add_inner_classes', create_function('$inner', 'return true;'));

/* Display an organization */
add_filter('hobo_humans_txt_author_title', create_function('$e', 'return "Client";'));
//add_filter('hobo_humans_txt_field_twitter', create_function('$e', 'return "org_twitter_account";'));
add_filter('hobo_humans_txt_field_location', create_function('$e', 'return "Kristianstad, Sweden";'));

/* Change the page title separator */
//add_filter('hobo_title_separator', create_function('$a', 'return "-";'));

include_once(dirname(__FILE__) . '/includes/theme.php');
include_once(dirname(__FILE__) . '/includes/message-board.php');
