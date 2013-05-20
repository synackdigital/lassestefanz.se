<?php
/*
Plugin Name: Lasse Stefanz
Plugin URI: http://www.lassestefanz.se
Description: Adds site specific functionality
Author: LS Produktions AB
Author URI: http://www.lassestefanz.se
Version: 1.0.1
*/


define( 'LS_PLUGIN_PATH', plugin_dir_path(__FILE__) );
include_once( LS_PLUGIN_PATH . 'defines.php');


class LasseStefanz
{
    const PLUGIN_VERSION = '1.0.1';
    const FORCE_LOOPIA_MEDIA_DNS = false;

    const INSTAGRAM_TAGS_KEY = 'ls_instagram_tags';
    const INSTAGRAM_IMPORT_OWNER_KEY = 'ls_instagram_import_owner';
    const INSTAGRAM_INSTRUCTIONS_KEY = 'ls_instagram_instructions';
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
        add_action('admin_enqueue_scripts', array(&$this, 'add_admin_scripts'));

        if (WP_DEBUG) {
            add_action('init', array(__CLASS__, 'setup_roles'));
        }
        register_activation_hook( __FILE__, function() {
            add_action('init', array(__CLASS__, 'setup_roles'));
        } );

        add_action('admin_footer', array(&$this, 'setup_instagram'));

        add_action('wp_ajax_instagram_sync', array(&$this, 'wp_ajax_instagram_sync'));
        // add_action('admin_init', array(&$this, 'instagram_debug'));

        add_filter('sanitize_file_name', 'remove_accents'); // We don't want any trouble when moving files up and down from web server

        add_action( 'save_post', array(__CLASS__, 'set_event_publish_date') );
        add_action( 'publish_post', array(__CLASS__, 'set_event_publish_date'), 2000 );

        /* Venue images */
        add_action( 'add_meta_boxes_event_page_venues', array(&$this, 'venue_metaboxes') );
        add_action( 'wp_ajax_set_venue_thumbnail', array( &$this, 'set_venue_thumbnail' ) );
        $this->setup_event_images();

        if (version_compare(self::PLUGIN_VERSION, '1.0') < 0 || self::FORCE_LOOPIA_MEDIA_DNS) {
            add_filter('option_upload_url_path', array(&$this, 'override_upload_path_url'));
        }

        self::$plugin_slug = dirname( plugin_basename( __FILE__ ) );
        load_plugin_textdomain( 'ls-plugin', false, self::$plugin_slug . '/languages/' );
    }


    public static function set_event_publish_date( $post_id = null ) {

        //verify post is not a revision
        if ( !wp_is_post_revision( $post_id ) ) {
            $post_type = get_post_type( $post_id );

            if ($post_type == 'event') {

                remove_action( 'save_post', array(__CLASS__, 'set_event_publish_date') );
                remove_action( 'publish_post', array(__CLASS__, 'set_event_publish_date') );

                $event = eo_get_by_postid($post_id);
                if ($event) {
                    $date = trim($event->StartDate).' '.trim($event->StartTime);
                } else {
                    if (array_key_exists('eo_input', $_POST)) {
                        $eo_input = $_POST['eo_input'];
                        $date = $eo_input['StartDate'].' '.$eo_input['StartTime'];
                    }
                }

                $event_ts = intval(eo_format_date($date, 'U'));
                $now = time();
                $diff = $event_ts - $now;
                $date_treshold = 3600 * 24 * 122; //365.25 * (1 / 3); // Four months

                if ($diff >= $date_treshold) {

                    $pub_date = $event_ts - $date_treshold;

                    $post = array();
                    $post['ID'] = $post_id;
                    $post['post_date'] = date_i18n('Y-m-d H:i:s', $pub_date, false);
                    $post['post_date_gmt'] = date_i18n('Y-m-d H:i:s', $pub_date, true);
                    $post['post_status'] = 'future';

                    wp_update_post( $post );
                }
            }
        }

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


    public static function setup_roles()
    {
        /* For your debugging needs */
        if (false && WP_DEBUG && is_admin()) {
            global $table_prefix;
            $option = get_option($table_prefix . 'user_roles');
            unset($option[LS_ROLE_BOOKING_AGENT]);
            update_option( $table_prefix . 'user_roles', $option );

            echo __('Booking agent', 'ls-plugin');
            var_dump($option);
            die();

        }

        // Ändra och lägga till i spelplanen

        $editor = get_role('author');

        $caps = array_merge($editor->capabilities, array(
            // 'read' => true,
            // 'level_1' => true,
            // 'level_0' => true,

            // 'edit_posts' => true
            // 'delete_posts' => true

            // 'delete_posts' => true,
            // 'delete_published_posts' => true,
            // 'edit_posts' => true,
            // 'edit_published_posts' => true,
            // 'publish_posts' => true,
            // 'upload_files' => true,

            // 'edit_posts' => false,
            // 'publish_posts' => false,
            // 'delete_posts' => false,
            // 'edit_others_posts' => false,
            // 'delete_others_posts' => false,
            // 'read_private_posts' => false,
            // 'manage_comments' => false,
            // 'manage_post_categories' => false,

            'edit_posts' => false,
            'edit_published_posts' => false,
            'publish_posts' => false,

            LS_ROLE_BOOKING_AGENT => true,
            'delete_others_events' => true,
            'delete_events' => true,
            'delete_private_events' => true,
            'delete_published_events' => true,
            'edit_others_events' => true,
            'edit_events' => true,
            'edit_private_events' => true,
            'edit_published_events' => true,
            'publish_events' => true,
            'read_private_events' => true,
            'manage_venues' => true,
            'manage_event_categories' => true,
        ));

        $booking_agent_role = add_role(LS_ROLE_BOOKING_AGENT, __('Booking agent', 'ls-plugin'), $caps);

        add_action( 'admin_menu', array(__CLASS__, 'setup_booking_admin_menu') );
        add_action( 'wp_before_admin_bar_render', array(__CLASS__, 'setup_booking_admin_bar') );
    }

    public static function setup_booking_admin_menu() {
        if (current_user_can( LS_ROLE_BOOKING_AGENT )) {
            remove_menu_page( 'edit.php' );
            remove_menu_page( 'edit-comments.php' );
        }
    }

    public static function setup_booking_admin_bar() {
        if (current_user_can( LS_ROLE_BOOKING_AGENT )) {
            global $wp_admin_bar;
            $wp_admin_bar->remove_menu('posts');
            $wp_admin_bar->remove_menu('comments');
        }
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
     * Callback for admin_enqueue_scripts, to add js for event post type,
     * in order to make all day events the default.
     * @param [type] $hook [description]
     */
    public function add_admin_scripts( $hook ) {

        global $post;

        if ( $hook == 'post-new.php' ) {
            if ( 'event' === $post->post_type ) {
                wp_enqueue_script( 'ls.admin.event', plugins_url('admin/js/event.js', __FILE__), array('jquery'), self::PLUGIN_VERSION );
            }
        }
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
        if (!current_user_can( 'administrator' ))
            return;

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
            self::INSTAGRAM_INSTRUCTIONS_KEY,
            __('Descriptive text for instagram submissions', 'ls-plugin'),
            array(&$this, 'render_settings_field'),
            self::$plugin_slug,
            'ls_instagram_settings',
            array(
                'field' => self::INSTAGRAM_INSTRUCTIONS_KEY,
                'type' => 'input',
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
        register_setting(self::$plugin_slug, self::INSTAGRAM_INSTRUCTIONS_KEY);
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

        include_once(dirname(__FILE__) . '/types-post.php');

        // include_once(dirname(__FILE__) . '/includes/slideshow.php');
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
            'container_class' => array('flexslider', 'carousel'),
            'list_class' => 'slides',
            'item_class' => 'slide',
        ) );
        extract($args);

        $container_class = implode(' ', (array)$container_class);
        $list_class = implode(' ', (array)$list_class);
        $item_class = implode(' ', (array)$item_class);

        if (class_exists('LSInstagramImage')) {
            $pargs = LSInstagramImage::queryArgs(array(
                'posts_per_page' => $num,
            ));

            $photos = get_posts($pargs);
        }

        $inst = self::instance();

        $markup = array();
        foreach ($photos as $photo) {
            $markup[] = self::instagram_feed_element($photo, array('class' => $item_class));
        }

        if (count($markup)) {
            $markup = sprintf('<div class="instagram-feed %s"><ul class="instagram-images %s">%s</ul></div>', esc_attr( $container_class ), esc_attr( $list_class ), implode("", $markup));

            return $markup;
        }

        return null;
    }

    public static function instagram_instructions() {
        return get_option(self::INSTAGRAM_INSTRUCTIONS_KEY);
    }

    public static function instagram_feed_element($post, $args = null)
    {
        if (class_exists('LSInstagramImage')) {

            $args = wp_parse_args( $args, array(
                'class' => null,
            ) );
            extract($args);

            $size = apply_filters( 'ls_instagram_feed_image_size', LS_IGIM_SIZE_LOW );
            $markup = LSInstagramImage::getImageMarkup($post->ID, $size);

            if ($markup) {
                $ig_url = LSInstagramImage::getInstagramURL($post->ID);
                if ($ig_url) {
                    $markup = sprintf('<a href="%s">%s</a>', esc_attr($ig_url), $markup);
                }

                $ts = strtotime($post->post_date);
                $date = date_i18n('l j F, Y', $ts);
                $isodate = date_i18n('c', $ts);

                $meta = null;
                if ($date) {
                    $meta .= sprintf('<p class="instagram-time"><time datetime="%s">%s</time></p>', esc_attr($isodate), esc_html($date));
                }

                $username = LSInstagramImage::getInstagramUser($post->ID);
                if ($username) {
                    $meta .= sprintf('<p class="instagram-user"><a href="http://instagram.com/%s">@%s</a></p>', esc_attr($username), esc_html($username));
                }

                if (!empty($meta)) {
                    $markup .= sprintf('<div class="meta">%s</div>', $meta);
                }

                $class = (array)$class;
                $class[] = $size;
                $class = implode(' ', $class);

                return sprintf('<li class="instagram-image %s">%s</li>', esc_attr($class), $markup);
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

        $is_venue_image_upload_js_response = false;

        if (is_admin() && array_key_exists('HTTP_REFERER', $_SERVER)) {
            $pattern = '/' . preg_quote(admin_url( 'media-upload.php' ), '/') . '\?venue_id=([0-9]+).*/i';
            $referer = $_SERVER['HTTP_REFERER'];

            $is_venue_image_upload_js_response = (bool)preg_match($pattern, $referer);
        }

        // var_dump($is_venue_image_upload);
        // var_dump($is_venue_image_upload_js_response);

        if ($is_venue_image_upload || $is_venue_image_upload_js_response) {
            add_filter( 'attachment_fields_to_edit', array(&$this, 'attachment_fields_to_edit'), 100, 2 );
        }

        if (is_admin()) {
            add_action('init', array(&$this, 'event_image_scripts'));
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
        } else if (array_key_exists('HTTP_REFERER', $_SERVER)) {
            $referer = $_SERVER['HTTP_REFERER'];
            $query = parse_url($referer, PHP_URL_QUERY);
            $vars = array('venue_id' => 0);
            parse_str($query, $vars);

            $venue_id = $vars['venue_id'];
        }

        $type = null;
        if (array_key_exists('type', $_GET)) {
            $type = $_GET['type'];
        }

        $image_id = $post->ID;
        $thumbnail = null;

        if ( $venue_id ) {
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

        $filter = create_function('$url, $original_url = null, $_context = null', 'return preg_replace("/(post_id=[0-9]+)/", "venue_id=' . $venue->term_id . '", $url);');

        add_filter('clean_url', $filter, 100, 3);

        $upload_iframe_src = esc_url( get_upload_iframe_src('image', 0 ) );
        // echo "<a href='$upload_iframe_src'>$upload_iframe_src</a>";

        echo $this->_wp_venue_thumbnail_html( $thumb_id, $venue );

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
                $return = $this->_wp_venue_thumbnail_html( null, $venue_id );
                $json ? wp_send_json_success( $return ) : wp_die( $return );
            } else {
                wp_die( 0 );
            }
        }

        if ( eo_update_venue_meta( $venue_id, LS_VENUE_IMAGE, $thumbnail_id ) ) {
            $return = $this->_wp_venue_thumbnail_html( $thumbnail_id, $venue_id );
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
    function _wp_venue_thumbnail_html( $thumbnail_id = null, $venue = null ) {
        global $content_width, $_wp_additional_image_sizes;

        if (!is_object($venue)) {
            $venue = get_term($venue, 'event-venue');
        }

        $upload_iframe_src = esc_url( get_upload_iframe_src('image', $venue->term_id ) );
        $set_thumbnail_link = '<p class="hide-if-no-js"><a title="' . esc_attr__( 'Set venue image', 'ls-plugin' ) . '" href="%s" id="set-venue-thumbnail" class="thickbox">%s</a></p>';
        $content = sprintf( $set_thumbnail_link, $upload_iframe_src, esc_html__( 'Set venue image', 'ls-plugin' ) );

        if ( $thumbnail_id && get_post( $thumbnail_id ) ) {
            $old_content_width = $content_width;
            $content_width = 266;
            if ( !isset( $_wp_additional_image_sizes['post-thumbnail'] ) )
                $thumbnail_html = wp_get_attachment_image( $thumbnail_id, array( $content_width, $content_width ) );
            else
                $thumbnail_html = wp_get_attachment_image( $thumbnail_id, 'post-thumbnail' );
            if ( !empty( $thumbnail_html ) ) {
                $ajax_nonce = wp_create_nonce( 'set_venue_thumbnail-' . $venue->term_id );
                $content = sprintf( $set_thumbnail_link, $upload_iframe_src, $thumbnail_html );
                $content .= '<p class="hide-if-no-js"><a href="#" id="remove-post-thumbnail" onclick="LSRemoveThumbnail(\'' . $ajax_nonce . '\');return false;">' . esc_html__( 'Remove venue image', 'ls-plugin' ) . '</a></p>';
            }
            $content_width = $old_content_width;
        }

        return apply_filters( 'ls_admin_venue_thumbnail_html', $content, $venue->term_id );
    }

    /**
     * Get event venue image thumbnail ID
     * @param  int $id Event term id
     * @return int     Image id
     */
    public static function venue_image_id($id = null)
    {
        if (!$id) {
            $id = eo_get_venue();
            // $term = get_queried_object();
            // if (is_object($term) && property_exists($term, 'term_id')) {
            //     $id = $term->term_id;
            // }
        }

        if ($id && function_exists('eo_get_venue_meta')) {
            return eo_get_venue_meta( $id, LS_VENUE_IMAGE );
        }

        return null;
    }

    public static function venue_image($id = null, $args = null)
    {
        if (!$id) {
            $id = eo_get_venue();
        }
        $image_id = self::venue_image_id($id);

        if ($image_id) {

            $size = apply_filters('ls_venue_image_size', 'medium');
            $venue = get_term($id, 'event-venue');

            $args = wp_parse_args( $args, array(
                'size' => $size,
                'title' => $venue->name,
                'class' => null,
            ) );
            extract($args);

            return wp_get_attachment_image( $image_id, $size, false, array(
                'title' => $title,
                'alt' => $title,
                'class' => $class,
            ) );
        }

        return null;
    }
}

$ls = LasseStefanz::instance();

include_once( LS_PLUGIN_PATH . 'includes/instagram.php');

function ls_load_widgets() {
    if (class_exists('Tribe_Image_Widget')) {
        include_once( LS_PLUGIN_PATH . 'includes/widgets.LSImageWidget.php');
    }
}
add_action('plugins_loaded', 'ls_load_widgets');


function ls_get_venue_image($id = null, $args = null) {
    return LasseStefanz::venue_image($id, $args);
}

function ls_venue_image($id = null, $args = null) {
    echo ls_get_venue_image($id, $args);
}

