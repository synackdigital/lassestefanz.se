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

        add_image_size(
            LS_SQUARE_BANNER_SIZE,
            LS_SQUARE_BANNER_SIZE_WIDTH,
            LS_SQUARE_BANNER_SIZE_HEIGHT,
            LS_SQUARE_BANNER_SIZE_CROP
        );

        add_image_size(
            LS_BANNER_LANDSCAPE_SIZE,
            LS_BANNER_LANDSCAPE_SIZE_WIDTH,
            LS_BANNER_LANDSCAPE_SIZE_HEIGHT,
            LS_BANNER_LANDSCAPE_SIZE_CROP
        );

        add_image_size(
            LS_BANNER_PORTRAIT_SIZE,
            LS_BANNER_PORTRAIT_SIZE_WIDTH,
            LS_BANNER_PORTRAIT_SIZE_HEIGHT,
            LS_BANNER_PORTRAIT_SIZE_CROP
        );
    }

}
add_action('init', 'ls_setup_images');

add_filter('lc_album_image_size', function() { return LS_ALBUM_IMAGE_SIZE; });
add_filter('ls_campaign_image_size', function() { return LS_CAMPAIGN_IMAGE_SIZE; });

add_filter('ls_image_widget_image_size_names_choose', 'ls_image_widget_sizes');

function ls_image_widget_sizes($sizes) {
    return array(
        LS_SQUARE_BANNER_SIZE => __('Square Banner', 'lasse-stefanz'),
        LS_BANNER_LANDSCAPE_SIZE => __('Landscape Banner', 'lasse-stefanz'),
        LS_BANNER_PORTRAIT_SIZE => __('Portrait Banner', 'lasse-stefanz'),
    );
}

function ls_home_query($query)
{
    if ((is_home() || is_front_page()) && $query->is_main_query()) {
        $query->set( 'posts_per_page', '2' );
    }
}
add_action( 'pre_get_posts', 'ls_home_query' );

function ls_setup_hero()
{
    if (ls_has_hero()) {
        add_action('hobo_before_main', 'ls_insert_hero');
    } else if (is_page()) {
        add_action('hobo_before_main', 'ls_insert_featured_image');
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

function ls_insert_featured_image() {
    get_template_part( 'featured', 'image' );
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
    if (class_exists('LSCampaign'))
    {
        return LSCampaign::slideshow();
    }

    // if (class_exists('LSSlideshow'))
    // {
    //     return LSSlideshow::campaign_slideshow($args, $extra_args, $echo);
    // }
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


function ls_news_url()
{
    return get_category_link( get_option( 'default_category', 0 ) );
}

function ls_news_link($title = null, $args = null)
{
    $args = wp_parse_args( $args, array(
        'class' => array('news-link'),
        'echo' => true,
    ) );
    extract($args);

    if (!$title || empty($title)) {
        $cat = get_category( get_option( 'default_category', 0 ) );

        $title = sprintf(__('More %s', 'lasse-stefanz'), strtolower($cat->name));
        $title = apply_filters('ls_news_link_title', $title, $args);
    }

    if (is_array($class)) {
        $class = implode(" ", $class);
    }

    $class_attr = null;
    if (!empty($class)) {
        $class_attr = sprintf(' class="%s"', $class);
    }

    $url = ls_news_url();

    $link = null;
    if (!empty($url)) {
        $link = sprintf('<a href="%s"%s>%s</a>', esc_url($url), $class_attr, $title);
    }

    if ($echo)
        echo $link;

    return $link;
}

function ls_event_date_format() {
    return __('j F', 'lasse-stefanz');
}

function ls_event_datetime_attr_format() {
    return 'Y-m-d\TH:i:sP';
}

function ls_upcoming_events($fargs = null)
{
    $fargs = wp_parse_args( $fargs, array(
        'atts' => array(),
        'args' => array(),
        'echo' => true,
        'template' => sprintf('<div class="eo-event-content text-overflow"><time class="date" datetime="%%start{%s}%%">%%start{%s}%%</time> <a class="title" href="%%event_url%%">%%event_title%%</a></div>',
            ls_event_datetime_attr_format(), ls_event_date_format()),
    ) );
    extract($fargs);

    $atts = wp_parse_args( $args, array(
        'numberposts' => 3 * 4,
        'event_start_after' => 'today',
        'showpastevents' => false,
    ) );
    $args = wp_parse_args( $args, array(
        'class' => 'slides eo-events eo-events-shortcode',
        'no_events' => null,
        'template' => $template
    ) );

    return eventorganiser_list_events($atts, $args, $echo);
}


if ( ! function_exists( 'hobo_comment' ) ) :
/**
 * Template for comments and pingbacks.
 *
 * To override this walker in a child theme without modifying the comments template
 * simply create your own hobo_comment(), and that function will be used instead.
 *
 * Used as a callback by wp_list_comments() for displaying the comments.
 *
 * @since Twenty Ten 1.0
 */
function hobo_comment( $comment, $args, $depth ) {
    $GLOBALS['comment'] = $comment;
    switch ( $comment->comment_type ) :
        case '' :
    ?>
    <li <?php comment_class(); ?> id="li-comment-<?php comment_ID(); ?>">
        <div id="comment-<?php comment_ID(); ?>">
        <div class="comment-author vcard">
            <?php echo get_avatar( $comment, 40 ); ?>
            <?php printf( __( '%s <span class="says">says:</span>', 'hobo' ), sprintf( '<cite class="fn">%s</cite>', get_comment_author_link() ) ); ?>
        </div><!-- .comment-author .vcard -->
        <?php if ( $comment->comment_approved == '0' ) : ?>
            <em class="comment-awaiting-moderation"><?php _e( 'Your comment is awaiting moderation.', 'hobo' ); ?></em>
            <br />
        <?php endif; ?>

        <div class="comment-meta commentmetadata"><a href="<?php echo esc_url( get_comment_link( $comment->comment_ID ) ); ?>">
            <?php
                /* translators: 1: date, 2: time */
                printf( __( '%1$s at %2$s', 'hobo' ), get_comment_date(),  get_comment_time() ); ?></a><?php edit_comment_link( __( '(Edit)', 'hobo' ), ' ' );
            ?>
        </div><!-- .comment-meta .commentmetadata -->

        <div class="comment-body"><?php comment_text(); ?></div>

        <?php if (is_user_logged_in() && current_user_can( 'moderate_comments' )) : ?>
        <div class="reply">
            <?php comment_reply_link( array_merge( $args, array( 'depth' => $depth, 'max_depth' => $args['max_depth'] ) ) ); ?>
        </div><!-- .reply -->
        <?php endif; ?>
    </div><!-- #comment-##  -->

    <?php
            break;
        case 'pingback'  :
        case 'trackback' :
    ?>
    <li class="post pingback">
        <p><?php _e( 'Pingback:', 'hobo' ); ?> <?php comment_author_link(); ?><?php edit_comment_link( __( '(Edit)', 'hobo' ), ' ' ); ?></p>
    <?php
            break;
    endswitch;
}
endif;
