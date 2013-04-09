<?php
/*
Plugin Name: Lasse Stefanz
Plugin URI: http://www.lassestefanz.se
Description: Adds site specific functionality
Author: LS Produktions AB
Author URI: http://www.lassestefanz.se
Version: 1.0b2
*/


define( 'LS_PLUGIN_PATH', plugin_dir_path(__FILE__) );
include_once( LS_PLUGIN_PATH . 'defines.php');

class LasseStefanz
{
    const PLUGIN_VERSION = '1.0b2';

    const INSTAGRAM_TAGS_KEY = 'ls_instagram_tags';
    const INSTAGRAM_IMPORT_OWNER_KEY = 'ls_instagram_import_owner';
    const SETTINGS_CAPABILITY = 'manage_options';

    protected static $instance;
    protected static $plugin_slug;
    protected static $template_files;

    /**
     * Constructor. Don't call directly, @see instance() instead.
     *
     * @see instance()
     * @return void
     * @author Simon Fransson
     **/
    public function __construct()
    {
        add_action('htk_loaded', array(&$this, 'setup_types'));
        add_action('admin_init', array(&$this, 'admin_init'));
        add_action('admin_menu', array(&$this, 'init_settings'));

        add_action('admin_footer', array(&$this, 'setup_instagram'));

        add_action('wp_ajax_instagram_sync', array(&$this, 'wp_ajax_instagram_sync'));
        // add_action('admin_init', array(&$this, 'instagram_debug'));

        add_filter('sanitize_file_name', 'remove_accents'); // We don't want any trouble when moving files up and down from web server

        /* Venue images */
        add_action( 'add_meta_boxes_event_page_venues', array(&$this, 'venue_metaboxes') );
        add_action( 'wp_ajax_set_venue_thumbnail', array( &$this, 'set_venue_thumbnail' ) );
        $this->setup_event_images();

        if (version_compare(self::PLUGIN_VERSION, '1.0') < 0) {
            add_filter('option_upload_url_path', array(&$this, 'override_upload_path_url'));
        }

        self::$plugin_slug = dirname( plugin_basename( __FILE__ ) );
        load_plugin_textdomain( 'ls-plugin', false, self::$plugin_slug . '/languages/' );
    }

    // TODO: Remove this function
    public function instagram_debug()
    {
        $ig = new LSInstagramDownloader(self::fan_photo_tags());
        $ig->syncImages();
    }


    public function override_upload_path_url($path)
    {
        if (!empty($path)) {
            return 'http://media.lassestefanz.se.loopiadns.com';
        }

        return $path;
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
        wp_enqueue_style( 'ls.admin.style', plugins_url('admin/css/style.css', __FILE__), array(), self::PLUGIN_VERSION );
    }

    /**
     * Returns the capability needed to access plugin settings
     * @return string Capability name
     */
    protected static function settings_capability()
    {
        $capability = apply_filters('ls_settings_capability', self::SETTINGS_CAPABILITY);

        return $capability;
    }

    public function setup_instagram()
    {
        ?>
        <script type="text/javascript">
            var data = {
                action: 'instagram_sync',
            };

            jQuery.post(ajaxurl, data, function(response) { return; });
        </script>
        <?php
    }


    public function wp_ajax_instagram_sync($value='')
    {
        header("Content-Type: application/json; charset=utf8");

        if ( false === ( $instagram_api_call = get_transient( 'instagram_api_call' ) ) ) {

            $ig = new LSInstagramDownloader(self::fan_photo_tags());
            $ig->syncImages();

            $instagram_api_call = true;

            set_transient( 'instagram_api_call', $instagram_api_call, 10 * MINUTE_IN_SECONDS );
        }

        echo json_encode(array(
            'instagram_sync' => !$instagram_api_call,
        ));

        die();
    }


    /**
     * Sets up the admin menus
     * @return void
     */
    public function init_settings()
    {
        // Top level menu page (http://codex.wordpress.org/Function_Reference/add_menu_page)
        add_menu_page(
            __('Lasse Stefanz', 'ls-plugin'),
            __('Lasse Stefanz', 'ls-plugin'),
            $this->settings_capability(),
            self::$plugin_slug,
            array(&$this, 'plugin_settings'),
            plugins_url('icons/lasse-stefanz.png', __FILE__)
        );

        // Sub menu page (http://codex.wordpress.org/Function_Reference/add_submenu_page)
        if (class_exists('LasseStefanzImporter')) {
            add_submenu_page(
                self::$plugin_slug,
                __('Import', 'ls-plugin'),
                __('Import', 'ls-plugin'),
                $this->settings_capability(),
                trailingslashit(self::$plugin_slug) . 'import',
                array(&$this, 'settings_import')
            );

            add_action( 'admin_action_lsimport', array(&$this, 'perform_import') );

            if (array_key_exists('lsimportstatus', $_GET) && $_GET['lsimportstatus'] == 'complete') {
                add_action('admin_notices', array(&$this, 'my_admin_notice'));
            }
        }

        // Settings section
        add_settings_section(
            'ls_instagram_settings',
            __('Instagram settings', 'ls-plugin'),
            '__return_false',
            self::$plugin_slug
        );

        // Settings field
        add_settings_field(
            self::INSTAGRAM_TAGS_KEY,
            __('Tags for fan photos', 'ls-plugin'),
            array(&$this, 'render_settings_field'),
            self::$plugin_slug,
            'ls_instagram_settings',
            array(
                'field' => self::INSTAGRAM_TAGS_KEY,
                'type' => 'textarea',
                'description' => sprintf(__("Separate tags with comma. %1s character should be left out from tag names.", 'ls-plugin'), '<span class="code">#</span>')
            )
        );

        // Settings field
        add_settings_field(
            self::INSTAGRAM_IMPORT_OWNER_KEY,
            __('Owner of imported photos', 'ls-plugin'),
            array(&$this, 'instagram_import_owner_dropdown'),
            self::$plugin_slug,
            'ls_instagram_settings'
        );


        // Register the settings fields
        register_setting(self::$plugin_slug, self::INSTAGRAM_TAGS_KEY, array(&$this, 'sanitize_tag_list'));
        register_setting(self::$plugin_slug, self::INSTAGRAM_IMPORT_OWNER_KEY, 'intval');
    }

    function my_admin_notice(){
        echo '<div class="updated">' . wpautop(__('Import complete', 'ls-plugin')) . '</div>';
    }

    public function perform_import()
    {
        if (class_exists('LasseStefanzImporter')) {
            LasseStefanzImporter::instance()->perform_import();

            wp_redirect( $_REQUEST['_wp_http_referer'] . '&lsimportstatus=complete' );
            die();
        }
    }

    public static function instagram_import_owner_dropdown()
    {
        wp_dropdown_users(array(
            'name' => self::INSTAGRAM_IMPORT_OWNER_KEY,
            'id' => self::INSTAGRAM_IMPORT_OWNER_KEY,
            'selected' => get_option(self::INSTAGRAM_IMPORT_OWNER_KEY),
        ));
    }

    /**
     * Renders the specified settings field
     *
     * @param array $args Array of arguments (field, name, id, value)
     * @return void
     * @author Simon Fransson
     **/
    public function render_settings_field($args = null) {
        $defaults = array(
            'field' => null,
            'type' => null,
            'name' => null,
            'id' => null,
            'value' => null,
            'class' => array('regular-text'),
            'description' => null,
            'after' => null,
            'before' => null,
            'attributes' => array(),
        );
        $args = wp_parse_args($args, $defaults);
        extract($args);

        if (!$field) {
            return null;
        }

        if (!$value) {
            $value = get_option($field);
        }

        if (!$name) {
            $name = $field;
        }

        if (!$id) {
            $id = $field;
        }

        $extra_attrs = array();
        foreach ($attributes as $attr => $attr_val) {
            if (is_numeric($attr)) {
                $extra_attrs[] = $attr_val;
            } else {
                $extra_attrs[] = "${attr}=\"${attr_val}\"";
            }
        }


        if ($before) {
            echo $before;
        }

        switch ($type) {
            case 'page':
                return $this->render_page_setting(array(
                    'name' => $name,
                    'id' => $id,
                    'selected' => $value,
                ));
            break;
            case 'none':
                break;
            case 'textarea':
                $class[] = 'large-text';
                printf('<textarea type="text" name="%s" id="%s" class="%s" %s>%s</textarea>', $name, $id, implode(" ", (array)$class), implode(" ", $extra_attrs), $value);
                break;
            default:
                printf('<input type="text" name="%s" id="%s" value="%s" class="%s" %s>', $name, $id, $value, implode(" ", (array)$class), implode(" ", $extra_attrs));
            break;
        }

        $description = strval($description);
        if (!empty($description)) {
            printf(' <span class="description">%s</span>', $description);
        }

        if ($after) {
            echo $after;
        }
    }

    /**
     * Renders the main settings page for the plugin
     * @return void
     */
    public function plugin_settings() {
        if ( !current_user_can( $this->settings_capability() ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        ?>


        <div class="wrap">

            <?php screen_icon('options-general'); ?>
            <?php //screen_icon(self::$plugin_slug); ?>

            <h2><?php _e('Lasse Stefanz settings', 'ls-plugin'); ?></h2>

            <form method="post" action="options.php">


            <div class="tool-box">
            <?php

                do_settings_sections( self::$plugin_slug );
                settings_fields( self::$plugin_slug );

                submit_button();
            ?>
            </div>


            </form>
        </div>

        <?php
    }


    /**
     * Renders the plugins import settings page
     * @return void
     */
    public function settings_import() {
        if ( !current_user_can( $this->settings_capability() ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        ?>


        <div class="wrap">

            <?php screen_icon('tools'); ?>

            <h2><?php _e('Import data from old website', 'ls-plugin'); ?></h2>

            <?php

            echo wpautop(
                    sprintf(__("Click the import button below to import albums and songs from the old website. Images will be downloaded from the current/previous locations, so you will have to make them available at these URL's in order for the web server to download them, for example using the %s file on your system. Another option is to change the setting for the %s constant in the file %s inside this plugin directory, which will allow you to download the images from an alternative host name.", 'ls-plugin'),
                        '<span class="code">/etc/hosts</span>',
                        '<span class="code">IMAGES_HOSTNAME</span>',
                        '<span class="code">importer.php</span>'
                    )
                );

            echo wpautop(__("Performing this import can potentially damage your system or your data, and it might also be very slow, especially on shared hosting environments. It is therefore highly recommended that you backup your files and data, and that you run the import on a local machine of possible.", 'ls-plugin'));

            ?>

            <form method="post" action="options.php">

            <div class="tool-box">

                <?php
                    $slug = trailingslashit(self::$plugin_slug) . 'import';
                    // do_settings_sections( $slug );
                    // settings_fields( $slug );
                ?>

                <input type="hidden" name="option_page" value="<?php echo $slug; ?>">
                <input type="hidden" name="action" value="lsimport">
                <?php wp_nonce_field( $slug, '_wpnonce', admin_url( 'admin.php?page=' . $slug )); ?>


                <?php
                    submit_button(__('Import', 'ls-plugin'));
                ?>
            </div>


            </form>
        </div>

        <?php
    }


    /**
     * Sanitizes the value entered in the instagram tags field
     * @param  string $tags User submitted tag list
     * @return string       Sanitized tag list
     */
    public function sanitize_tag_list($tags)
    {
        $sanitized_tags = self::tag_list_to_array($tags);

        if (count($sanitized_tags)) {
            $tags = implode(', ', $sanitized_tags);
        } else {
            $tags = '';
        }

        return $tags;
    }

    /**
     * Filters out restricted characters from a list of tag names and return the tags as an array
     * @param  string $taglist Comma separated list of tags
     * @return array           Array of tags
     */
    public static function tag_list_to_array($taglist)
    {
        if (strlen($taglist)) {
            $taglist = preg_replace('/[^A-Za-z0-9_,]+/i', '', $taglist);
            $tags = array_unique(array_filter(explode(',', $taglist)));

            return $tags;
        }

        return array();
    }

    /**
     * Sets up content types using HWPTypeKit
     *
     * @return void
     * @author Simon Fransson
     **/
    public function setup_types() {
        include_once(dirname(__FILE__) . '/types-campaign.php');
        include_once(dirname(__FILE__) . '/types-instagram-image.php');

        include_once(dirname(__FILE__) . '/includes/slideshow.php');
    }


    /**
     * Returns an array of Instagram tags to use for fan photod
     * @return array Array of tags
     */
    public static function fan_photo_tags()
    {
        return self::tag_list_to_array(get_option(self::INSTAGRAM_TAGS_KEY));
    }

    /**
     * Returns the user ID of the imported photos owner
     * @return int User id of selected user
     */
    public static function fan_photo_owner()
    {
        return get_option(self::INSTAGRAM_IMPORT_OWNER_KEY, 1);
    }


    public static function instagram_feed($args = null)
    {
        $args = wp_parse_args( $args, array(
            'num' => 20,
            'size' => LS_IGIM_SIZE_LOW,
        ) );
        extract($args);

        if (class_exists('LSInstagramImage')) {
            $pargs = LSInstagramImage::queryArgs(array(
                'posts_per_page' => $num,
            ));

            $photos = get_posts($pargs);
        }

        $inst = self::instance();
        $markup = array_filter(array_map( array(&$inst, 'instagram_feed_element'), $photos ));

        if (count($markup)) {
            $markup = sprintf('<div class="instagram-feed"><ul class="instagram-images">%s</ul></div>', implode("", $markup));

            return $markup;
        }

        return null;
    }

    public static function instagram_feed_element($post)
    {
        if (class_exists('LSInstagramImage')) {
            $size = apply_filters( 'ls_instagram_feed_image_size', LS_IGIM_SIZE_LOW );
            $markup = LSInstagramImage::getImageMarkup($post->ID, $size);



            if ($markup) {
                $ig_url = LSInstagramImage::getInstagramURL($post->ID);
                if ($ig_url) {
                    $markup = sprintf('<a href="%s">%s</a>', $ig_url, $markup);
                }

                $ts = strtotime($post->post_date);
                $date = date_i18n('l j F, Y', $ts);
                $isodate = date_i18n('c', $ts);

                $meta = null;
                if ($date) {
                    $meta .= sprintf('<p class="instagram-time"><time datetime="%s">%s</time></p>', $isodate, $date);
                }

                $username = LSInstagramImage::getInstagramUser($post->ID);
                if ($username) {
                    $meta .= sprintf('<p class="instagram-user"><a href="http://instagram.com/%s">@%s</a></p>', $username, $username);
                }

                if (!empty($meta)) {
                    $markup .= sprintf('<div class="meta">%s</div>', $meta);
                }

                return sprintf('<li class="instagram-image %s">%s</li>', $size, $markup);
            }
        }

        return null;
    }


    public function setup_event_images()
    {
        $is_venue_image_upload = is_admin() && array_key_exists('type', $_GET) &&
            array_key_exists('venue_id', $_GET) &&
            $_GET['type'] == 'image' &&
            is_numeric($_GET['venue_id']);

        if ($is_venue_image_upload) {
            add_action('init', array(&$this, 'event_image_scripts'));
            add_filter( 'attachment_fields_to_edit', array(&$this, 'attachment_fields_to_edit'), 100, 2 );
        }
    }


    public function event_image_scripts()
    {
        if (is_admin()) {
            wp_enqueue_script( 'set-venue-thumbnail', plugins_url( 'admin/js/venue.js', __FILE__ ), array( 'jquery' ), self::PLUGIN_VERSION, true );
            wp_localize_script( 'set-venue-thumbnail', 'setVenueThumbnailL10n', array(
                    'setThumbnail' => __( 'Use as featured image' ),
                    'saving' => __( 'Saving...' ),
                    'error' => __( 'Could not set that as the thumbnail image. Try a different attachment.' ),
                    'done' => __( 'Done' )
                ) );
        }
    }

    public function attachment_fields_to_edit($form_fields, $post)
    {
        $venue_id = 0;
        if (array_key_exists('venue_id', $_GET)) {
            $venue_id = $_GET['venue_id'];
        }

        $type = null;
        if (array_key_exists('type', $_GET)) {
            $type = $_GET['type'];
        }

        $image_id = $post->ID;
        $thumbnail = null;

        if ( 'image' == $type && $venue_id ) {
            $ajax_nonce = wp_create_nonce( "set_venue_thumbnail-$venue_id" );
            $thumbnail = "<a class='button button-primary wp-venue-thumbnail' id='wp-venue-thumbnail-" . $image_id . "' href='#' onclick='LSSetAsVenueThumbnail(\"$image_id\", \"$ajax_nonce\");return false;'>" . esc_html__( "Use as venue image", 'ls-plugin' ) . "</a>";
        }

        if ($thumbnail) {
            $form_fields['buttons'] = array( 'tr' => "\t\t<tr class='submit'><td></td><td class='savesend'>$thumbnail</td></tr>\n" );

            unset($form_fields['image-size']);
            unset($form_fields['align']);
        }

        return $form_fields;
    }

    public function venue_metaboxes($venue)
    {
        add_meta_box( 'ls_venue_image', __('Image', 'ls-plugin'), array(&$this, 'venue_image_metabox'), $screen = null, 'side', $priority = 'default', array('venue' => $venue) );
    }

    public function venue_image_metabox($venue)
    {
        $attachment_id = 0;

        $faux_post = new stdClass();
        $faux_post->ID = 0;

        $thumb_id = eo_get_venue_meta( $venue->term_id, LS_VENUE_IMAGE, true );

        $filter = create_function('$url, $original_url = null, $_context = null', 'return str_replace("?", "?venue_id=' . $venue->term_id . '&#038;", $url);');

        add_filter('clean_url', $filter, 100, 3);

        $upload_iframe_src = esc_url( get_upload_iframe_src('image', 0 ) );
        echo "<a href='$upload_iframe_src'>$upload_iframe_src</a>";

        // TODO: We might need a localized version of this function
        echo _wp_post_thumbnail_html( $thumb_id, $faux_post );
        // echo $this->_wp_post_thumbnail_html( null, $venue, 'event-venue' );

        remove_filter('clean_url', $filter, 100, 3);
    }


    public function set_venue_thumbnail()
    {
        $json = ! empty( $_REQUEST['json'] ); // New-style request

        $venue_id = intval( $_POST['venue_id'] );
        if ( ! current_user_can( 'manage_venues' ) )
            wp_die( -1 );

        $thumbnail_id = intval( $_POST['thumbnail_id'] );

        if ( $json )
            check_ajax_referer( "update-post_$venue_id" );
        else
            check_ajax_referer( "set_venue_thumbnail-$venue_id" );

        // TODO: Better return data, + where does this data go?

        if ( $thumbnail_id == '-1' ) {
            if ( eo_delete_venue_meta( $venue_id, LS_VENUE_IMAGE ) ) {
                $return = "<p>Deleted venue image</p>"; //_wp_post_thumbnail_html( null, $post_ID );
                $json ? wp_send_json_success( $return ) : wp_die( $return );
            } else {
                wp_die( 0 );
            }
        }

        if ( eo_update_venue_meta( $venue_id, LS_VENUE_IMAGE, $thumbnail_id ) ) {
            $return = "<p>Added venue image</p>"; //_wp_post_thumbnail_html( $thumbnail_id, $post_ID );
            $json ? wp_send_json_success( $return ) : wp_die( $return );
        }

        wp_die( 0 );
    }

    /**
     * Output HTML for the post thumbnail meta-box.
     *
     * @since 2.9.0
     *
     * @param int $thumbnail_id ID of the attachment used for thumbnail
     * @param mixed $term The term_id or object associated with the thumbnail, defaults to global $term.
     * @return string html
     */
    // function _wp_post_thumbnail_html( $thumbnail_id = null, $post = null ) {
    //     global $content_width, $_wp_additional_image_sizes;

    //     $post = get_post( $post );

    //     $upload_iframe_src = esc_url( get_upload_iframe_src('image', $post->ID ) );
    //     $set_thumbnail_link = '<p class="hide-if-no-js"><a title="' . esc_attr__( 'Set featured image' ) . '" href="%s" id="set-post-thumbnail" class="thickbox">%s</a></p>';
    //     $content = sprintf( $set_thumbnail_link, $upload_iframe_src, esc_html__( 'Set featured image' ) );

    //     if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
    //         $old_content_width = $content_width;
    //         $content_width = 266;
    //         if ( !isset( $_wp_additional_image_sizes['post-thumbnail'] ) )
    //             $thumbnail_html = wp_get_attachment_image( $thumbnail_id, array( $content_width, $content_width ) );
    //         else
    //             $thumbnail_html = wp_get_attachment_image( $thumbnail_id, 'post-thumbnail' );
    //         if ( !empty( $thumbnail_html ) ) {
    //             $ajax_nonce = wp_create_nonce( 'set_post_thumbnail-' . $post->ID );
    //             $content = sprintf( $set_thumbnail_link, $upload_iframe_src, $thumbnail_html );
    //             $content .= '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail" onclick="WPRemoveThumbnail(\'' . $ajax_nonce . '\');return false;">' . esc_html__( 'Remove featured image' ) . '</a></p>';
    //         }
    //         $content_width = $old_content_width;
    //     }

    //     return apply_filters( 'admin_post_thumbnail_html', $content, $post->ID );
    // }
}

$ls = LasseStefanz::instance();

include_once( LS_PLUGIN_PATH . 'includes/instagram.php');
