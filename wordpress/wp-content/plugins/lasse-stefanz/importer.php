<?php
/*
Plugin Name: Lasse Stefanz Importer
Plugin URI: http://www.lassestefanz.se
Description: Imports data from old website
Author: LS Produktions AB
Author URI: http://www.lassestefanz.se
Version: 1.0b1
*/

define('LS_IMPORT_TIME_LIMIT', 45);

define( 'LSI_PLUGIN_PATH', plugin_dir_path(__FILE__) );
include_once( LSI_PLUGIN_PATH . '../lyrics-catalog/defines.php');

class LasseStefanzImporter
{
    const PLUGIN_VERSION = '1.0b1';
    const SETTINGS_CAPABILITY = 'manage_options';
    const IMAGES_HOSTNAME = 'www.lassestefanz.se';

    protected static $instance;
    protected static $plugin_slug;

    protected $album_types;

    /**
     * Constructor. Don't call directly, @see instance() instead.
     *
     * @see instance()
     * @return void
     * @author Simon Fransson
     **/
    public function __construct()
    {
        $this->album_types = array();

        // add_action('admin_init', array(&$this, 'admin_init'));

        self::$plugin_slug = dirname( plugin_basename( __FILE__ ) );
        load_plugin_textdomain( 'lasse-stefanz', false, self::$plugin_slug . '/languages/' );
    }

    /**
     * Singleton accessor, returns the instance
     *
     * @return void
     * @author Simon Fransson
     **/
    public static function instance() {
        if (!isset(self::$instance)) {
            $c = __CLASS__;
            self::$instance = new $c();
        }

        return self::$instance;
    }


    /**
     * Admin initialization. Sets up styles and scripts needed by the admin interface.
     * @return void
     */
    public function admin_init()
    {
        $this->perform_import();
    }


    /**
     * Generates a uniqueue GUID for the given row ID and table name from the __OLD__ site
     * @param  int $id    Numeric ID for row on old system
     * @param  string $table Table name where row resides on old system
     * @return string        URL type GUID
     */
    protected function generate_guid($id, $table)
    {
        return sprintf('http://%s/import/%s/%s/', self::IMAGES_HOSTNAME, $table, $id );
    }


    /**
     * Returns a URL pointing to the location of images etc. on the OLD web server
     * @param  string $path Absolute path on old webserver
     * @return string       Absolute URL pointing to image
     */
    protected function get_import_url($path)
    {
        if (!$path)
            return null;

        $dir = dirname($path);
        $filename = rawurlencode(basename($path));

        return sprintf('http://%s%s/%s', self::IMAGES_HOSTNAME, $dir, $filename );
    }


    /**
     * Downloads the file at the given URL the uplaods directory,
     * attaches it to the post with the given $post_id and creates
     * a meta realtion using $meta_key
     *
     * @param int $url      Attachment URL
     * @param int $post_id  Post ID
     * @param string $meta_key Meta key for the post/attachment relation.
     * Use null to skip meta relation. Defaults to '_thumbnail_id'
     * @return   ID of the attachment
     */
    function add_attachment_from_url($url, $post_id, $meta_key = '_thumbnail_id') {

        set_time_limit(LS_IMPORT_TIME_LIMIT);

        $filename = $this->download_attachment($url);

        if (!$filename)
            return 0;

        $wp_filetype = wp_check_filetype(basename($filename), null );

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($filename)),
            'post_content' => '',
            'post_status' => 'inherit',
            'guid' => $url
        );

        $attach_id = wp_insert_attachment( $attachment, $filename, $post_id );

        if ($attach_id) {
            // you must first include the image.php file
            // for the function wp_generate_attachment_metadata() to work
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
            wp_update_attachment_metadata( $attach_id,  $attach_data );

            // Set featured image (or other image key)
            if ($meta_key && is_string($meta_key)) {
                add_post_meta($post_id, $meta_key, $attach_id);
            }
        }

        return $attach_id;
    }

    /**
     * Downloads an attachment to the uploads directory
     * @param  string $url The URL of the attachment
     * @return string      Path on the local filesystem
     */
    function download_attachment($url) {

        set_time_limit(LS_IMPORT_TIME_LIMIT);

        $uploads = wp_upload_dir();
        $filename = sanitize_file_name( basename( rawurldecode( $url ) ) );

        var_dump($url);

        var_dump($filename);

        $path = trailingslashit( $uploads['path'] ) . $filename;

        var_dump($path);

        $r = file_put_contents($path, file_get_contents($url));

        if (!$r)
            return $r;

        return $path;
    }


    /**
     * Inserts or updates the supplied post data and returns the resulting post ID
     * @param  array $post Post data
     * @return int       Post ID
     */
    protected function store_post($post)
    {
        $func = array_key_exists('ID', $post) ? 'wp_update_post' : 'wp_insert_post';
        $post_id = $func( $post );

        return $post_id;
    }

    /**
     * Returns an array of post boilerplate for use when inserting and updating posts
     * @param  string $guid Uniqueue GUID use for identifying potintially imported posts
     * @param  string $type Post type name
     * @param  array $data Post data that will be merged with the boiplerplate data. Data supplied here will override boilerplate data. Optional.
     * @return Array       Post data
     */
    protected function post_boilerplate($guid, $type, $data = null)
    {
        $created_time = time();

        $post['guid'] = $guid;
        $post['post_type'] = $type;
        $post['post_date'] = date_i18n('Y-m-d H:i:s', $created_time, false);
        $post['post_date_gmt'] = date_i18n('Y-m-d H:i:s', $created_time, true);
        $post['post_modified'] = date_i18n('Y-m-d H:i:s', $created_time, false);
        $post['post_modified_gmt'] = date_i18n('Y-m-d H:i:s', $created_time, true);
        $post['comment_status'] = 'closed';
        $post['ping_status'] = 'closed';

        $post['post_status'] = 'publish';
        $post['post_author'] = get_current_user_id();

        return array_merge($post, $data);
    }

    /**
     * Returns the album format term ID for the given old album type
     * @param  int $id Album type id in old system
     * @return int     Term ID for the new album format term
     */
    protected function get_album_format_id($id)
    {
        if (array_key_exists($id, $this->album_types)) {
            return $this->album_types[$id];
        }

        return null;
    }


    /**
     * Starts the import
     * @return void
     */
    protected function perform_import()
    {
        global $wpdb;
        $status = $wpdb->query("SHOW TABLE STATUS WHERE Name = 'latar'");

        if (!$status) {
            $this->execute_stored_sql();
        }

        $this->import_album_types();
        $this->import_albums();
        $this->import_songs();
    }

    /**
     * Sets up the database. Creates tables and imports the data needed for import
     * @return void
     */
    protected function execute_stored_sql()
    {
        set_time_limit(LS_IMPORT_TIME_LIMIT);

        $command = sprintf('export PATH=/Applications/MAMP/Library/bin/:$PATH; mysql --user=%s --password=%s --host=%s --database=%s < %s',
            DB_USER,
            DB_PASSWORD,
            DB_HOST,
            DB_NAME,
            plugin_dir_path(__FILE__) . 'data/lassestefanz_2013-03-26.sql'
        );

        $output = shell_exec($command);
    }

    /**
     * Imports album types
     * @return void
     */
    protected function import_album_types()
    {
        global $wpdb;

        $data = $wpdb->get_results("SELECT * FROM SkivTyper");

        foreach ($data as $row) {

            set_time_limit(LS_IMPORT_TIME_LIMIT);

            $retval = term_exists( $row->Title, 'LC_ALBUM_FORMAT' );
            if (!$retval) {
                $retval = wp_insert_term(
                    $row->Title,
                    LC_ALBUM_FORMAT
                );
            }

            if (is_array($retval) && array_key_exists('term_id', $retval)) {
                $term_id = $retval['term_id'];
            }

            $this->album_types[$row->SkivTypID] = $term_id;
        }
    }


    protected function all_posts_of_type($type)
    {
        global $wpdb;
        $existing_posts = $wpdb->get_results(
                $wpdb->prepare("SELECT * FROM $wpdb->posts WHERE post_type = %s AND post_status = 'publish'", $type),
                ARRAY_A
            );
        $posts = array();

        // Iterate over existing posts
        foreach ($existing_posts as $post) {
            $posts[$post['guid']] = $post;
        }
        unset($existing_posts);

        return $posts;
    }

    /**
     * Imports albums
     * @return void
     */
    protected function import_albums()
    {

        $table = 'Skivor';
        $type = 'album';

        global $wpdb;
        $data = $wpdb->get_results("SELECT * FROM $table");
        $albums = $this->all_posts_of_type($type);

        // Iterate over import data
        foreach ($data as $album) {

            set_time_limit(LS_IMPORT_TIME_LIMIT);

            $post = array();

            $guid = $this->generate_guid($album->SkivID, $table);

            if (array_key_exists($guid, $albums)) {
                $post = $albums[$guid];
            }

            $post = $this->post_boilerplate($guid, $type, $post);
            $post['post_title'] = $album->Titel;

            $post_id = $this->store_post($post);

            // Update meta values and taxonomies
            if ($post_id) {
                $label = $album->Label;
                $album_format = $this->get_album_format_id($album->SkivTypID);
                $year = $album->UtgivningsAr;
                $cover_url = $this->get_import_url( $album->ImageUrlBig );
                $back_cover_url = $this->get_import_url( $album->ImageUrlBigBack );

                if ($year) {
                    update_post_meta( $post_id, LC_ALBUM_RELEASE_YEAR, $year );
                }

                if ($label) {
                    update_post_meta( $post_id, LC_ALBUM_LABEL, $label );
                }

                if ($album_format) {
                    wp_set_post_terms( $post_id, array($album_format), LC_ALBUM_FORMAT, $append = false );
                }

                if ($cover_url && !get_post_meta( $post_id, '_thumbnail_id', true )) {
                    $this->add_attachment_from_url($cover_url, $post_id);
                }

                if ($back_cover_url && !get_post_meta( $post_id, LC_ALBUM_BACKSIDE_IMAGE, true )) {
                    $this->add_attachment_from_url($back_cover_url, $post_id, LC_ALBUM_BACKSIDE_IMAGE);
                }
            }

        }
    }


    /**
     * Import songs
     * @return void
     */
    protected function import_songs()
    {
        $table = 'latar';
        $type = 'song';

        global $wpdb;
        $data = $wpdb->get_results("SELECT * FROM $table");
        $songs = $this->all_posts_of_type($type);
        $albums = $this->all_posts_of_type('album');

        var_dump($albums);

        // Iterate over import data
        foreach ($data as $song) {

            set_time_limit(LS_IMPORT_TIME_LIMIT);

            $post = array();
            $guid = $this->generate_guid($song->Id, $table);

            if (array_key_exists($guid, $songs)) {
                $post = $songs[$guid];
            }

            $post = $this->post_boilerplate($guid, $type, $post);
            $post['post_title'] = $song->Titel;
            $post['post_content'] = $song->Texten;
            if ($song->CreatedDateTime != '0000-00-00 00:00:00') {
                $post['post_date'] = $song->CreatedDateTime;
            }


            $post_id = $this->store_post($post);

            // Update meta values and taxonomies
            if ($post_id) {
                $author = $song->Kompositor;
                $publisher = $song->Forlag;
                $old_album_id = $song->SkivID;
                $album_guid = $this->generate_guid($old_album_id, 'Skivor');

                var_dump($album_guid);

                if (array_key_exists($album_guid, $albums)) {
                    $album = $albums[$album_guid];
                    update_post_meta( $post_id, LC_SONG_ALBUM, $album['ID'] );
                }

                if ($publisher) {
                    // Treat Warner/Chappell differently
                    $publisher = preg_replace('/(warner)\s*\/\s*(chappell)/i', '$1###$2', $publisher);
                    $publisher = str_replace(array('/', '###'), array(',', '/'), $publisher);

                    wp_set_post_terms( $post_id, $publisher, LC_SONG_PUBLISHER, $append = false );
                }

                if ($author) {
                    update_post_meta( $post_id, LC_SONG_AUTHOR, $author );
                }
            }

        }
    }
}

$lsi  = LasseStefanzImporter::instance();

