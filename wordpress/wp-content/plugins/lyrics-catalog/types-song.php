<?php

include_once(dirname(__FILE__) . '/defines.php');

class LCSong extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = 'lyrics-catalog';
        $this->shouldSetThumbnail(false);
        $this->setRewriteSlug(__('song', 'lyrics-catalog'));

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
                'name' => LC_SONG_AUTHOR,
                'label' => __( 'Author', 'lyrics-catalog' ),
            )
        ));

        $this->fields->addField( new PostSelectField(
            array(
                'name' => LC_SONG_ALBUM,
                'label' => __( 'Album', 'lyrics-catalog' ),
                'required' => false,
                'data_callback' => array(&$this, 'albumsDropdownItems')
            )
        ));

    }

    /**
     * Returns a list arguments user for taxonomy creation
     * @return array Arguments array
     */
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
            'page-attributes',
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
            'orderby' => 'menu_order, title',
            'order' => 'ASC',
        );

        return array_merge($defaults, $type_args, (array)$args);
    }


    /**
     * Returns an array of all albums
     * @return array Array of albums
     */
    public function albumsDropdownItems()
    {
        if (class_exists('LCAlbum')) {
            $albums = get_posts($args = LCAlbum::queryArgs());

            return $albums;
        }

        return array();
    }
}


$lyric = LCSong::type('lyric', array('singular' => __('Lyric', 'lyrics-catalog'), 'plural' => __('Lyrics', 'lyrics-catalog')));
