<?php

include_once(dirname(__FILE__) . '/defines.php');

class LCSong extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = basename(dirname(__FILE__));
        $this->shouldSetThumbnail(false);
        $this->setRewriteSlug(apply_filters('lc_rewrite_slug_for_type', __('songs', 'lyrics-catalog'), $name));

        parent::__construct($name, $labels, $collection, $args);
    }

    protected function defaultMetaboxTitle() {
        return __( 'Song information', 'lyrics-catalog' );
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
            'posts_per_page' => -1,
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
            $albums = get_posts($args = LCAlbum::queryArgs(array(
                'posts_per_page' => -1,
            )));

            return $albums;
        }

        return array();
    }

    /**
     * Returns the ID of the songs album
     * @param  int $id Song ID
     * @return int     Album ID
     */
    public static function albumID($id = null)
    {
        return self::optionForKey(LC_SONG_ALBUM, $id);
    }

    /**
     * Returns the songs album
     * @param  int $id Song ID
     * @return object     Album object
     */
    public static function album($id = null)
    {
        return get_post(self::albumID($id));
    }

    /**
     * Returns the songs authors
     * @param  int $id Song ID
     * @return string     Authors
     */
    public static function authors($id = null)
    {
        return self::optionForKey(LC_SONG_AUTHOR, $id);
    }

    /**
     * Returns the songs publisher term objects
     * @param  int $id Song ID
     * @return array     Array of publisher terms
     */
    public static function publisher($id = null) {
        if (!$id) {
            global $post;
            $id = $post->ID;
        }

        return wp_get_post_terms($id, LC_SONG_PUBLISHER);
    }
}

$song = LCSong::type('song', array('singular' => __('Song', 'lyrics-catalog'), 'plural' => __('Songs', 'lyrics-catalog')));


/*
 * Template Tags
 */

function lc_song_album($id = null) {
    $album = LCSong::album($id);

    if ($album) {
        return sprintf('<a href="%s">%s</a>', get_permalink($album->ID), get_the_title($album->ID));
    }

    return null;
}

function lc_song_authors($id = null) {
    return LCSong::authors($id);
}

function lc_song_publisher($id = null, $separator = ', ') {
    $publishers = LCSong::publisher($id);

    $publisher_names = array();
    foreach ($publishers as $publisher) {
        $publisher_names[] = $publisher->name;
    }

    if (count($publishers))
        return implode($separator, $publisher_names);

    return null;
}
