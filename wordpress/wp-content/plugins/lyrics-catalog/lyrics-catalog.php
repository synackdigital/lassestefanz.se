<?php
/*
Plugin Name: Lyrics catalog
Plugin URI: http://dessibelle.se
Description: Adds custom post types for songs/lyrics and albums
Author: Simon Fransson
Author URI: http://www.dessibelle.se
Version: 1.0b1
*/


include_once( dirname(__FILE__) . '/defines.php');

class LyricsCatalog
{
    const PLUGIN_VERSION = '1.0b1';

    protected static $instance;
    protected static $plugin_slug;
    protected static $instagram_redirect_uri;

    protected $contact_form;

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
