<?php

include_once(dirname(__FILE__) . '/defines.php');

class LCSong extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = 'lyrics-catalog';
        $this->shouldSetThumbnail(false);
        $this->setRewriteSlug(__('song', 'lyrics-catalog'));

        parent::__construct($name, $labels, $collection, $args);
    }

    protected function initializeFields() {

        $this->fields = new FieldCollection();

        $this->fields->addField( new CustomField(
            array(
                'name' => LC_SONG_AUTHOR,
                'label' => __( 'Author', 'lyrics-catalog' ),
            )
        ));

        $this->fields->addField( new PostSelectField(
            array(
                'name' => LC_SONG_ALBUM,
                'label' => __( 'Album', 'lyrics-catalog' ),
                'required' => false,
                'data_callback' => array(&$this, 'albumOptionsArray')
            )
        ));

    }

    protected function keywordTaxonomies() {

        return array(
            array(
                'single' => __( 'Publisher', 'lyrics-catalog' ),
                'multiple' => __( 'Publishers', 'lyrics-catalog' ),
                'slug' => LC_SONG_PUBLISHER,
                'rewrite_slug' => __('publisher', 'lyrics-catalog'),
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
            'page-attributes',
        );


        return $args;
    }

    public static function queryArgs($args = null) {
        $defaults = parent::queryArgs();
        $type_args = array(
            'orderby' => 'menu_order, title',
            'order' => 'ASC',
        );

        return array_merge($defaults, $type_args, (array)$args);
    }


    public function albumOptionsArray()
    {
        if (class_exists('LCAlbum')) {
            $albums = get_posts($args = LCAlbum::queryArgs());

            return $albums;
        }

        return array();
    }
}


$lyric = LCSong::type('lyric', array('singular' => __('Lyric', 'lyrics-catalog'), 'plural' => __('Lyrics', 'lyrics-catalog')));
