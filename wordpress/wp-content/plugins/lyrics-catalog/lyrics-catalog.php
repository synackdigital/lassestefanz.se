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
        //add_action('admin_menu', array(&$this, 'setup_admin'));

        self::$plugin_slug = dirname( plugin_basename( __FILE__ ) );
        load_plugin_textdomain( 'sodraesplanaden', false, self::$plugin_slug . '/languages/' );
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
     * Sets up content types using HWPTypeKit
     *
     * @return void
     * @author Simon Fransson
     **/
    public function setup_types() {
        include_once('types-lyric.php');
        include_once('types-album.php');

    }

}

$lc  = LyricsCatalog::instance();
