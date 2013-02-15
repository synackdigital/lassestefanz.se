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

        /*
        $this->fields->addField( new URLField(
            array(
                'name' => SE_PROJECT_EXTERNAL_URL,
                'label' => __( 'External URL', 'lyrics-catalog' ),
            )
        ));

        $this->fields->addField( new URLField(
            array(
                'name' => SE_PROJECT_EXTERNAL_URL_TITLE,
                'label' => __( 'External URL title', 'lyrics-catalog' ),
                'description' => sprintf(__('Will be used as link title for the above URL. Leave empty to use the default title, "%s".', 'lyrics-catalog'), __('External link', 'lyrics-catalog'))
            )
        ));
        */
    }

    public function defaultArgs() {
        $args = parent::defaultArgs();

        $args['supports'] = array(
            'title',
            'editor',
            'revisions',
        );

        return $args;
    }

    public static function queryArgs($args = null) {
        $defaults = parent::queryArgs();
        $type_args = array(
            'orderby' => 'date',
            'order' => 'DESC',
        );

        return array_merge($defaults, $type_args, (array)$args);
    }


}


$album = LCAlbum::type('album', array('singular' => __('Album', 'lyrics-catalog'), 'plural' => __('Albums', 'lyrics-catalog')));
