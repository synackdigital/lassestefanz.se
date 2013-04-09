<?php

include_once(dirname(__FILE__) . '/defines.php');

class LSCampaign extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = basename(dirname(__FILE__));
        $this->shouldSetThumbnail(true);
        $this->setRewriteSlug(apply_filters('ls_rewrite_slug_for_type', __('campaigns', 'ls-plugin'), $name));

        add_action( 'template_redirect', array(&$this, 'redirect') );

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
            'posts_per_page' => -1,
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

            wp_redirect( wp_sanitize_redirect($url), 301 );
            die();
        }
    }


    public static function redirectURL($id = null)
    {
        $page = self::campaignPage($id);

        if ($page) {
            return get_permalink($page->ID);
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

}

$campaign = LSCampaign::type('campaign', array('singular' => __('Campaign', 'ls-plugin'), 'plural' => __('Campaigns', 'ls-plugin')));


/* Template tags */

function ls_campaign_image_size() {

    $size = LSCampaign::image_size();

    if ($size)
        return $size;

    return 'large';
}

