<?php
namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Enqueue
 * @package um_ext\um_mycred\core
 */
class Enqueue {


	/**
	 * Enqueue constructor.
	 */
	function __construct() {
		add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), 0 );
		add_action( 'admin_enqueue_scripts',  array( &$this, 'admin_scripts' ), 100 );
	}


	/**
	 * Register frontend scripts/styles
	 */
	function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um_mycred', um_mycred_url . 'assets/js/um-mycred' . $suffix . '.js', array( 'jquery', 'um_tipsy' ), um_mycred_version, true );
		wp_register_style( 'um_mycred', um_mycred_url . 'assets/css/um-mycred' . $suffix . '.css', array(), um_mycred_version );
	}


	/**
	 * Enqueue admin scripts
	 */
	function admin_scripts() {
		global $plugin_page;
		if ( isset( $plugin_page ) && 'mycred-hooks' === $plugin_page ) {
			UM()->admin_enqueue()->load_fonticons();
		}
	}
}