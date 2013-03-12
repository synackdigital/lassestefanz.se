<?php

include_once(dirname(__FILE__) . '/defines.php');

class LSInstagramImage extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = basename(dirname(__FILE__));
        $this->shouldSetThumbnail(true);
        $this->setRewriteSlug(apply_filters('ls_rewrite_slug_for_type', __('images', 'lasse-stefanz'), $name));

        parent::__construct($name, $labels, $collection, $args);
    }

    /**
     * Initializes post type fields
     * @return void
     */
    protected function initializeFields() {

        $this->fields = new FieldCollection();

        $this->fields->addField( new CustomField(
            array(
                'name' => LC_IGIM_ID,
                'label' => __( 'Image ID', 'lasse-stefanz' ),
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LC_IGIM_TITLE,
                'label' => __( 'Image Title', 'lasse-stefanz' ),
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LC_IGIM_USER,
                'label' => __( 'Username', 'lasse-stefanz' ),
            )
        ));

        $this->fields->addField( new URLField(
            array(
                'name' => LC_IGIM_URL,
                'label' => __( 'Image URL', 'lasse-stefanz' ),
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
        );
        $args['menu_position'] = 10;

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


}

$instagram_image = LSInstagramImage::type('igimage', array('singular' => __('Instagram Image', 'lasse-stefanz'), 'plural' => __('Instagram Images', 'lasse-stefanz')));

