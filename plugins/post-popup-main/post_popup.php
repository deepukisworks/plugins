<?php
/**
* Plugin Name: Post Popup
* Description: Post preview for single post
* Version: 1.1
*/

if (!defined('ABSPATH')) {
    die('-1');
}
if (!defined('POP_PLUGIN_NAME')) {
    define('POP_PLUGIN_NAME', 'Post Popup');
}
if (!defined('POP_PLUGIN_VERSION')) {
    define('POP_PLUGIN_VERSION', '1.0.0');
}
if (!defined('POP_PLUGIN_FILE')) {
    define('POP_PLUGIN_FILE', __FILE__);
}
if (!defined('POP_PLUGIN_DIR')) {
    define('POP_PLUGIN_DIR',plugins_url('', __FILE__));
}
if (!defined('POP_DOMAIN')) {
    define('POP_DOMAIN', 'pop');
}



if (!class_exists('POPMAIN')) {

  class POPMAIN {

    protected static $instance;

    //Load all includes files
    function includes() {

      //Admn site Layout
      include_once('admin/pop_backend.php');
      include_once('front/pop_front.php');

    }

    function init() {
      add_action('admin_enqueue_scripts', array($this, 'POP_load_admin_script_style'));
      add_action( 'wp_enqueue_scripts',  array($this, 'POP_load_script_style'));

    }

    //Add JS and CSS on Backend
    function POP_load_admin_script_style() {
      wp_enqueue_style( 'POP_backcss', POP_PLUGIN_DIR . '/includes/css/POP_back.css', false, '1.0.0' );
      wp_enqueue_script('POP_backendjs', POP_PLUGIN_DIR .'/includes/js/POP_backend.js', false, '1.0.0' );
     
    }

    //Add JS and CSS on Frontend
    function POP_load_script_style() {
      //wp_enqueue_script( 'POP_frontjsddsd', "https://cdnjs.cloudflare.com/ajax/libs/jquery/3.4.1/jquery.min.js");
      wp_enqueue_style( 'POP_frontcss', POP_PLUGIN_DIR . '/includes/css/POP_front.css', false, '1.0.0' );
      wp_enqueue_script( 'POP_frontjs', POP_PLUGIN_DIR . '/includes/js/POP_front.js', false, '1.0.0',true );
      wp_localize_script( 'POP_frontjs', 'ajax_url', admin_url('admin-ajax.php?action=popup_create') );
      $pop_custom = array( 'template_url' => POP_PLUGIN_DIR );
      wp_localize_script( 'POP_frontjs', 'pop_custom', $pop_custom );
    }

    

    //Plugin Rating
    public static function do_activation() {
      set_transient('pop-first-rating', true, MONTH_IN_SECONDS);
    }

    public static function instance() {
      if (!isset(self::$instance)) {
        self::$instance = new self();
        self::$instance->init();
        self::$instance->includes();
      }
      return self::$instance;
    }

  }

  add_action('plugins_loaded', array('POPMAIN', 'instance'));

  register_activation_hook(POP_PLUGIN_FILE, array('POPMAIN', 'do_activation'));
}
