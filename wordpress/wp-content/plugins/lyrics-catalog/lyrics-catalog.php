<?php
/*
Plugin Name: Lyrics catalog
Plugin URI: http://dessibelle.se
Description: Adds custom post types for songs/lyrics and albums
Author: Simon Fransson
Author URI: http://www.dessibelle.se
Version: 1.0b2
*/


define( 'LC_PLUGIN_PATH', plugin_dir_path(__FILE__) );
include_once( LC_PLUGIN_PATH . 'defines.php');

class LyricsCatalog
{
    const PLUGIN_VERSION = '1.0b2';

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

        //add_action('init', array(&$this, 'setup_templates'));

        $this->__setup_template_files();

        self::$plugin_slug = dirname( plugin_basename( __FILE__ ) );
        load_plugin_textdomain( 'lyrics-catalog', false, self::$plugin_slug . '/languages/' );
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




    protected function __setup_template_files()
    {
        self::$template_files = array();
        $types = array('song', 'album');
        $templates = array('content');

        foreach ($templates as $template) {
            foreach ($types as $type) {
                self::$template_files[] = sprintf('%s-%s.php', $template, $type);
            }
        }
    }

    public function setup_templates()
    {
        foreach (self::$template_files as $template) {
            if ( $overridden_template = locate_template( $template ) ) {
                // locate_template() returns path to file
                // if either the child theme or the parent theme have overridden the template
                load_template( $overridden_template );
            } else {
                // If neither the child nor parent theme have overridden the template,
                // we load the template from the 'templates' sub-directory of the directory this file is in
                load_template( dirname( __FILE__ ) . '/templates/' . $template );
            }
        }
    }


    /**
     * Admin initialization. Sets up styles and scripts needed by the admin interface.
     * @return void
     */
    public function admin_init()
    {
        wp_enqueue_style( 'lc.admin.style', plugins_url('admin/css/style.css', __FILE__), array(), self::PLUGIN_VERSION );
    }


    /**
     * Sets up content types using HWPTypeKit
     *
     * @return void
     * @author Simon Fransson
     **/
    public function setup_types() {
        include_once('types-album.php');
        include_once('types-song.php');
    }

}

$lc  = LyricsCatalog::instance();

include_once(LC_PLUGIN_PATH . 'includes/templates.php');
