<?php

include_once(dirname(__FILE__) . '/defines.php');

class LSCampaign extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = basename(dirname(__FILE__));
        $this->shouldSetThumbnail(true);
        $this->setRewriteSlug(apply_filters('ls_rewrite_slug_for_type', __('campaigns', 'ls-plugin'), $name));

        parent::__construct($name, $labels, $collection, $args);
    }

    /**
     * Initializes post type fields
     * @return void
     */
    protected function initializeFields() {

        $this->fields = new FieldCollection();

        /*
        $this->fields->addField( new CustomField(
            array(
                'name' => LC_ALBUM_RELEASE_YEAR,
                'label' => __( 'Year', 'ls-plugin' ),
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LC_ALBUM_LABEL,
                'label' => __( 'Label', 'ls-plugin' ),
            )
        ));

        $this->fields->addField( new PostSelectField(
            array(
                'name' => LC_ALBUM_BACKSIDE_IMAGE,
                'label' => __( 'Backside image', 'ls-plugin' ),
                'data_callback' => array(&$this, 'backsideImageItems')
            )
        ));
        */
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
        );

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
            'orderby' => 'date',
            'order' => 'DESC',
            'posts_per_page' => 3,

        );

        return array_merge($defaults, $type_args, (array)$args);
    }


    /**
     * Returns the image size used for campaigns
     * @return string Image size string
     */
    public static function image_size()
    {
        return apply_filters('ls_campaign_image_size', 'large');
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

