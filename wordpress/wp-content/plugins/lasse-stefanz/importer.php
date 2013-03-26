<?php
/*
Plugin Name: Lasse Stefanz Importer
Plugin URI: http://www.lassestefanz.se
Description: Imports data from old website
Author: LS Produktions AB
Author URI: http://www.lassestefanz.se
Version: 1.0b1
*/


define( 'LSI_PLUGIN_PATH', plugin_dir_path(__FILE__) );
include_once( LSI_PLUGIN_PATH . 'defines.php');

class LasseStefanzImporter
{
    const PLUGIN_VERSION = '1.0b1';
    const SETTINGS_CAPABILITY = 'manage_options';

    protected static $instance;
    protected static $plugin_slug;

    /**
     * Constructor. Don't call directly, @see instance() instead.
     *
     * @see instance()
     * @return void
     * @author Simon Fransson
     **/
    public function __construct()
    {
        add_action('admin_init', array(&$this, 'admin_init'));

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
        // wp_enqueue_style( 'ls.admin.style', plugins_url('admin/css/style.css', __FILE__), array(), self::PLUGIN_VERSION );
    }

}

$lsi  = LasseStefanzImporter::instance();

