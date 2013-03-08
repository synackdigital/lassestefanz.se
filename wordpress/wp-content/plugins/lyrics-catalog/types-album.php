<?php

include_once(dirname(__FILE__) . '/defines.php');

class LCAlbum extends HWPType {

    public function __construct($name, $labels = null, $collection = null, $args = null) {

        $this->package = 'lyrics-catalog';
        $this->shouldSetThumbnail(true);
        $this->setRewriteSlug(apply_filters('lc_rewrite_slug_for_type', __('albums', 'lyrics-catalog'), $name));

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

        $this->fields->addField( new PostSelectField(
            array(
                'name' => LC_ALBUM_BACKSIDE_IMAGE,
                'label' => __( 'Backside image', 'lyrics-catalog' ),
                'data_callback' => array(&$this, 'backsideImageItems')
            )
        ));
    }

    /**
     * Returns a list arguments user for taxonomy creation
     * @return array Arguments array
     */
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
            'orderby' => 'meta_value_num, date',
            'order' => 'DESC',
            'meta_key' => LC_ALBUM_RELEASE_YEAR,
        );

        return array_merge($defaults, $type_args, (array)$args);
    }


    public function backsideImageItems()
    {
        global $post;
        $id = $post->ID;

        $args = array(
            'post_type' => 'attachment',
            'post_mime_type' => 'image',
            'posts_per_page' => -1,
            'post_parent' => $id,
            'exclude' => get_post_thumbnail_id($id),
        );

        return get_posts($args);
    }


    /**
     * Returns the image size used for albums
     * @return string Image size string
     */
    public static function image_size()
    {
        return apply_filters('lc_album_image_size', 'medium');
    }

    /**
     * Returns the album year
     * @param  int $id Post ID
     * @return string     Album release year
     */
    public static function year($id = null)
    {
        return self::optionForKey(LC_ALBUM_RELEASE_YEAR, $id);
    }

    /**
     * Returns the album label
     * @param  int $id Post id
     * @return string     Album release year
     */
    public static function label($id = null)
    {
        return self::optionForKey(LC_ALBUM_LABEL, $id);
    }

    /**
     * Returns the album formats as term objects
     * @param  int $id Post ID
     * @return array     Term objects
     */
    public static function formats($id = null) {
        if (!$id) {
            global $post;
            $id = $post->ID;
        }

        return wp_get_post_terms($id, LC_ALBUM_FORMAT);
    }


    public static function songs($id = null)
    {
        if (!$id) {
            global $post;
            $id = $post->ID;
        }

        $args = LCSong::queryArgs(array(
            'meta_query' => array(
                array(
                    'key' => LC_SONG_ALBUM,
                    'value' => $id,
                )
            ),
        ));

        return get_posts($args);
    }
}

$album = LCAlbum::type('album', array('singular' => __('Album', 'lyrics-catalog'), 'plural' => __('Albums', 'lyrics-catalog')));


/* Template tags */

function lc_album_image_size() {

    $size = LCAlbum::image_size();

    if ($size)
        return $size;

    return 'medium';
}

function lc_album_year() {
    return LCAlbum::year();
}


function lc_album_label($id = null) {
    return LCAlbum::label($id);
}

function lc_album_formats($id = null, $separator = ', ') {
    $formats = LCAlbum::formats($id);

    $format_names = array();
    foreach ($formats as $format) {
        $format_names[] = $format->name;
    }

    if (count($formats))
        return implode($separator, $format_names);

    return null;
}

function lc_album_tracklisting($id = null) {
    $songs = LCAlbum::songs($id);

    if (!$songs || empty($songs))
        return null;

    $tracks = array();
    foreach ($songs as $song) {
        $url = get_permalink($song->ID);
        $title = get_the_title($song->ID);
        $tracks[] = sprintf('<li><a href="%s">%s</a>', $url, $title);
    }

    if (count($tracks)) {
        return sprintf('<ol class="tracklisting">%s</ol>', implode('', $tracks));
    }

    return null;
}
