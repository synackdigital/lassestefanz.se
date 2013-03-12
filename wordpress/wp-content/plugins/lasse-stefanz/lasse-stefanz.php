<?php
/*
Plugin Name: Lasse Stefanz
Plugin URI: http://www.lassestefanz.se
Description: Adds site specific functionality
Author: LS Produktions AB
Author URI: http://www.lassestefanz.se
Version: 1.0b1
*/


define( 'LS_PLUGIN_PATH', plugin_dir_path(__FILE__) );
include_once( LS_PLUGIN_PATH . 'defines.php');

class LasseStefanz
{
    const PLUGIN_VERSION = '1.0b1';

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

        add_action('init', array(&$this, 'setup_instagram'));

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
        if (is_admin()) {
            return;
        }

        if ( false === ( $instagram_api_call = get_transient( 'instagram_api_call' ) ) ) {

            $ig = new LSInstagramDownloader(self::fan_photo_tags());
            $ig->syncImages();

            set_transient( 'instagram_api_call', true, 24 * HOUR_IN_SECONDS );
        }
    }

    /**
     * Sets up the admin menus
     * @return void
     */
    public function init_settings()
    {
        // Top level menu page (http://codex.wordpress.org/Function_Reference/add_menu_page)
        add_menu_page(
            __('Lasse Stefanz', 'lasse-stefanz'),
            __('Lasse Stefanz', 'lasse-stefanz'),
            $this->settings_capability(),
            self::$plugin_slug,
            array(&$this, 'plugin_settings'),
            plugins_url('icons/lasse-stefanz.png', __FILE__)
        );

        // Sub menu page (http://codex.wordpress.org/Function_Reference/add_submenu_page)
        add_submenu_page(
            self::$plugin_slug,
            __('Subpage', 'lasse-stefanz'),
            __('Subpage', 'lasse-stefanz'),
            $this->settings_capability(),
            trailingslashit(self::$plugin_slug) . 'tools',
            array(&$this, 'settings_subpage')
        );

        // Settings section
        add_settings_section(
            'ls_instagram_settings',
            __('Instagram settings', 'lasse-stefanz'),
            '__return_false',
            self::$plugin_slug
        );

        // Settings field
        add_settings_field(
            self::INSTAGRAM_TAGS_KEY,
            __('Tags for fan photos', 'lasse-stefanz'),
            array(&$this, 'render_settings_field'),
            self::$plugin_slug,
            'ls_instagram_settings',
            array(
                'field' => self::INSTAGRAM_TAGS_KEY,
                'type' => 'textarea',
                'description' => sprintf(__("Separate tags with comma. %1s character should be left out from tag names.", 'lasse-stefanz'), '<span class="code">#</span>')
            )
        );

        // Settings field
        add_settings_field(
            self::INSTAGRAM_IMPORT_OWNER_KEY,
            __('Owner of imported photos', 'lasse-stefanz'),
            array(&$this, 'instagram_import_owner_dropdown'),
            self::$plugin_slug,
            'ls_instagram_settings'
        );


        // Register the settings fields
        register_setting(self::$plugin_slug, self::INSTAGRAM_TAGS_KEY, array(&$this, 'sanitize_tag_list'));
        register_setting(self::$plugin_slug, self::INSTAGRAM_IMPORT_OWNER_KEY, 'intval');
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
     * @return [type] [description]
     */
    public function plugin_settings() {
        if ( !current_user_can( $this->settings_capability() ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        ?>


        <div class="wrap">

            <?php screen_icon('options-general'); ?>
            <?php //screen_icon(self::$plugin_slug); ?>

            <h2><?php _e('Lasse Stefanz settings', 'lasse-stefanz'); ?></h2>

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

    public function settings_subpage() {
        if ( !current_user_can( $this->settings_capability() ) )  {
            wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
        }

        ?>


        <div class="wrap">

            <?php screen_icon('options-general'); ?>
            <?php //screen_icon(self::$plugin_slug); ?>

            <h2><?php _e('Subpage settings', 'lasse-stefanz'); ?></h2>

            <form method="post" action="options.php">


            <div class="tool-box">
            <?php

                $slug = trailingslashit(self::$plugin_slug) . 'tools';

                do_settings_sections( $slug );
                settings_fields( $slug );

                submit_button();
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
            $taglist = preg_replace('/[^A-Za-z0-9-_,]+/i', '', $taglist);
            $tags = explode(',', $taglist);

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
        include_once('types-campaign.php');
        include_once('types-instagram-image.php');
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
}

$ls  = LasseStefanz::instance();

include_once( LS_PLUGIN_PATH . 'includes/instagram.php');
