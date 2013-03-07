<?php
/**
 * Template functions
 *
 * @package template-functions
*/


/**
 * Load a template part into a template
 *
 * Identical to {@see `get_template_part()`} except that it uses {@see `lc_locate_template()`}
 * instead of {@see `locate_template()`}.
 *
 * Makes it easy for a theme to reuse sections of code in a easy to overload way
 * for child themes. Looks for and includes templates {$slug}-{$name}.php
 *
 * You may include the same template part multiple times.
 *
 * @uses lc_locate_template()
 * @since 1.7
 * @uses do_action() Calls `get_template_part_{$slug}` action.
 *
 * @param string $slug The slug name for the generic template.
 * @param string $name The name of the specialised template.
 */
function lc_get_template_part( $slug, $name = null ) {
    do_action( "get_template_part_{$slug}", $slug, $name );

    $templates = array();
    if ( isset($name) )
        $templates[] = "{$slug}-{$name}.php";

    $templates[] = "{$slug}.php";

    lc_locate_template($templates, true, false);
}


/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches the child theme first, then the parent theme before checking the plug-in templates folder.
 * So parent themes can override the default plug-in templates, and child themes can over-ride both.
 *
 * Behaves almost identically to {@see locate_template()}
 *
 * @since 1.7
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true. Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function lc_locate_template($template_names, $load = false, $require_once = true ) {
    $located = '';

    $template_dir = get_stylesheet_directory(); //child theme
    $parent_template_dir = get_template_directory(); //parent theme

    $stack = apply_filters( 'lc_template_stack', array( $template_dir, $parent_template_dir, LC_PLUGIN_PATH . 'templates' ) );

    foreach ( (array) $template_names as $template_name ) {
        if ( !$template_name )
            continue;
        foreach ( $stack as $template_stack ){
            if ( file_exists( trailingslashit( $template_stack ) . $template_name ) ) {
                $located = trailingslashit( $template_stack ) . $template_name;
                break;
            }
        }
    }

    if ( $load && '' != $located )
        load_template( $located, $require_once );

    return $located;
}


/**
 * Checks to see if appropriate templates are present in active template directory.
 * Otherwises uses templates present in plugin's template directory.
 * Hooked onto template_include'
 *
 * @ignore
 * @since 1.0.0
 * @param string $template Absolute path to template
 * @return string Absolute path to template
 */
function lc_set_template( $template ){

    if ( is_singular( array('song', 'album') ) ) {
        //Viewing a single event

        //Hide next/previous post link
        add_filter("next_post_link",'__return_false');
        add_filter("previous_post_link",'__return_false');

        //Prepend our event details
        add_filter('the_content','_lc_single_content');
    }

    return $template;
}
add_filter('template_include', 'lc_set_template');



function _lc_single_content( $content ){

    //Sanity check!
    if ( !is_singular( array('song', 'album') ) )
        return $content;

    global $post;
    $type = $post->post_type;

    //Object buffering
    ob_start();
    lc_get_template_part('meta', $type);
    $type_content = ob_get_contents();
    ob_end_clean();

    $type_content = apply_filters('lc_pre_type_content', $type_content, $content);

    return $type_content . $content;
}

