<?php

include_once(dirname(__FILE__) . '/defines.php');
include_once(dirname(__FILE__) . '/includes/maps.php');


class LSCampaign extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = basename(dirname(__FILE__));
        $this->shouldSetThumbnail(true);
        $this->setRewriteSlug(apply_filters('ls_rewrite_slug_for_type', __('campaigns', 'ls-plugin'), $name));

        add_action( 'template_redirect', array(&$this, 'redirect') );

        if (class_exists('LSEventMaps')) {
            $maps = new LSEventMaps();
        }

        parent::__construct($name, $labels, $collection, $args);
    }

    /**
     * Initializes post type fields
     * @return void
     */
    protected function initializeFields() {

        $this->fields = new FieldCollection();

        $this->fields->addField( new URLField(
            array(
                'name' => LS_CAMPAIGN_URL,
                'label' => __( 'Link to URL', 'ls-plugin' ),
            )
        ));

        $this->fields->addField( new PostSelectField(
            array(
                'name' => LS_CAMPAIGN_PAGE_ID,
                'label' => __( 'Link to page', 'ls-plugin' ),
                'description' => __('This settings takes precedence in case both a page and a URL is supplied.', 'ls-plugin'),
                'data_callback' => array(&$this, 'campaignLinkPages'),
                'required' => false,
            )
        ));

    }

    /**
     * Returns a list arguments user for taxonomy creation
     * @return array Arguments array
     */
    protected function categoryTaxonomies() {

        return array( );
    }

    /**
     * Default post type arguments
     * @return array Post type arguments
     */
    public function defaultArgs() {
        $args = parent::defaultArgs();

        $args['supports'] = array(
            'title',
            'editor',
            'revisions',
            'thumbnail',
            'page-attributes',
        );

        $args['supports'] = array_diff($args['supports'], array('editor'));


        return $args;
    }


    /**
     * Query arguments for post type
     * @param  array $args Additional arguments
     * @return array       Array of arguments
     */
    public static function queryArgs($args = null) {
        $defaults = parent::queryArgs();
        $type_args = array(
            'orderby' => 'menu_order, date',
            'order' => 'DESC',
            'posts_per_page' => 3,

        );

        return array_merge($defaults, $type_args, (array)$args);
    }


    /**
     * Returns a list of all pages for use with the pages dropdown
     * @return array Array of page objects
     */
    public function campaignLinkPages()
    {
        return get_posts(array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'order' => 'ASC',
            'orderby' => 'title',
            'posts_per_page' => 500,
        ));
    }

    /**
     * Returns the image size used for campaigns
     * @return string Image size string
     */
    public static function image_size()
    {
        return apply_filters('ls_campaign_image_size', 'large');
    }


    public function redirect()
    {
        $o = get_queried_object();

        if ($o && get_post_type( $o ) == $this->typeName()) {

            $url = self::redirectURL($o->ID);

            if (empty($url)) {
                $url = get_home_url( );
            }

            wp_redirect( wp_sanitize_redirect($url), 301 );
            die();
        }
    }


    public static function redirectURL($id = null)
    {
        $page = self::campaignPage($id);

        if ($page) {
            return get_permalink($page);
        }

        $url = self::campaignURL($id);

        if ($url) {
            return $url;
        }

        return null;
    }

    public static function campaignURL($id = null)
    {
        return self::optionForKey(LS_CAMPAIGN_URL);
    }


    public static function campaignPageID($id = null)
    {
        return self::optionForKey(LS_CAMPAIGN_PAGE_ID);
    }

    public static function campaignPage($id = null)
    {
        $pid = self::campaignPageID($id);

        if (is_numeric($pid) && $pid > 0) {
            return get_post( $pid );
        }

        return null;
    }

    public static function slideshow($args = null)
    {
        $args = wp_parse_args( $args, array(
            'container_class' => 'flexslider',
            'list_class' => 'slides',
            'item_class' => 'slide',
            'num' => 3,
            'posts' => null,
            'echo' => true,
        ) );
        extract($args);

        if (!$posts) {
            $posts = get_posts(self::queryArgs(array(
                'posts_per_page' => $num,
            )));
        }

        $items = array();
        foreach ($posts as $slide) {
            $items[] = self::slide_markup($slide, array('class' => $item_class));
        }

        $output = null;

        if (count($items))
            $output = sprintf('<div class="%s"><ul class="%s">%s</ul></div>', esc_attr($container_class), esc_attr($list_class), implode('', $items));

        if ($echo)
            echo $output;

        return $output;
    }


    public static function slide_markup($post, $args = null)
    {
        $args = wp_parse_args( $args, array(
            'class' => null,
            'title' => null,
        ) );
        extract($args);

        $class = implode(" ", (array)$class);

        $title = empty($title) ? apply_filters('the_title', $post->post_title) : $title;
        $title = esc_html(apply_filters( 'ls_campaign_slideshow_slide_title', $title, $post, $args ));

        $thumb_data = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), self::image_size() );
        $thumb_url = count($thumb_data) ? esc_url($thumb_data[0]) : null;

        $campaign_url = get_permalink($post->ID);
        if (!empty($campaign_url)) {
            $title = sprintf('<a href="%s">%s</a>', esc_attr($campaign_url), $title);
        }



        $markup = sprintf('<li class="%s"><div style="background-image: url(%s);" class="slide-content"><h2 class="slide-title">%s</h2></div></li>', esc_attr( $class ), esc_attr( $thumb_url ), $title );

        return $markup;
    }
}

$campaign = LSCampaign::type('campaign', array('singular' => __('Campaign', 'ls-plugin'), 'plural' => __('Campaigns', 'ls-plugin')));


/* Template tags */

function ls_campaign_image_size() {

    $size = LSCampaign::image_size();

    if ($size)
        return $size;

    return 'large';
}

