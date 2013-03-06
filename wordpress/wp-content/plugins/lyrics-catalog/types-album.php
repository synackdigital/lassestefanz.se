<?php

include_once(dirname(__FILE__) . '/defines.php');

class LCAlbum extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = 'lyrics-catalog';
        $this->shouldSetThumbnail(true);
        $this->setRewriteSlug(__('album', 'lyrics-catalog'));

        parent::__construct($name, $labels, $collection, $args);
    }

    protected function initializeFields() {

        $this->fields = new FieldCollection();

        $this->fields->addField( new CustomField(
            array(
                'name' => LC_ALBUM_RELEASE_YEAR,
                'label' => __( 'Year', 'lyrics-catalog' ),
            )
        ));

        $this->fields->addField( new CustomField(
            array(
                'name' => LC_ALBUM_LABEL,
                'label' => __( 'Label', 'lyrics-catalog' ),
            )
        ));

    }

    protected function categoryTaxonomies() {

        return array(
            array(
                'single' => __( 'Album format', 'lyrics-catalog' ),
                'multiple' => __( 'Album formats', 'lyrics-catalog' ),
                'slug' => LC_ALBUM_FORMAT,
                'rewrite_slug' => __('format', 'lyrics-catalog'),
                'types' => array( $this->name )
            )
        );
    }

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

    public static function queryArgs($args = null) {
        $defaults = parent::queryArgs();
        $type_args = array(
            'orderby' => 'meta_value_num, date',
            'order' => 'DESC',
            'meta_key' => LC_ALBUM_RELEASE_YEAR,
        );

        return array_merge($defaults, $type_args, (array)$args);
    }


}


$album = LCAlbum::type('album', array('singular' => __('Album', 'lyrics-catalog'), 'plural' => __('Albums', 'lyrics-catalog')));
