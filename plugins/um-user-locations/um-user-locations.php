<?php
/*
Plugin Name: Ultimate Member - User Locations
Plugin URI: http://ultimatemember.com/extensions/user-locations
Description: Using the Google Maps API, display users on a map on the member directory page and allow users to add their location via their profile
Version: 1.0.5
Author: Ultimate Member
Author URI: http://ultimatemember.com/
Text Domain: um-user-locations
Domain Path: /languages
UM version: 2.1.6
*/

if ( ! defined( 'ABSPATH' ) ) exit;

require_once( ABSPATH.'wp-admin/includes/plugin.php' );

$plugin_data = get_plugin_data( __FILE__ );

define( 'um_user_locations_url', plugin_dir_url( __FILE__ ) );
define( 'um_user_locations_path', plugin_dir_path( __FILE__ ) );
define( 'um_user_locations_plugin', plugin_basename( __FILE__ ) );
define( 'um_user_locations_extension', $plugin_data['Name'] );
define( 'um_user_locations_version', $plugin_data['Version'] );
define( 'um_user_locations_textdomain', 'um-user-locations' );

define( 'um_user_locations_requires', '2.1.6' );

function um_user_locations_plugins_loaded() {
	$locale = ( get_locale() != '' ) ? get_locale() : 'en_US';
	load_textdomain( um_user_locations_textdomain, WP_LANG_DIR . '/plugins/' . um_user_locations_textdomain . '-' . $locale . '.mo' );
	load_plugin_textdomain( um_user_locations_textdomain, false, dirname( plugin_basename(  __FILE__ ) ) . '/languages/' );

}
add_action( 'init', 'um_user_locations_plugins_loaded', 0 );

add_action( 'plugins_loaded', 'um_user_locations_check_dependencies', -20 );

if ( ! function_exists( 'um_user_locations_check_dependencies' ) ) {
	function um_user_locations_check_dependencies() {
		if ( ! defined( 'um_path' ) || ! file_exists( um_path  . 'includes/class-dependencies.php' ) ) {
			//UM is not installed
			function um_user_locations_dependencies() {
				echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-locations' ), um_user_locations_extension ) . '</p></div>';
			}

			add_action( 'admin_notices', 'um_user_locations_dependencies' );
		} else {

			if ( ! function_exists( 'UM' ) ) {
				require_once um_path . 'includes/class-dependencies.php';
				$is_um_active = um\is_um_active();
			} else {
				$is_um_active = UM()->dependencies()->ultimatemember_active_check();
			}

			if ( ! $is_um_active ) {
				//UM is not active
				function um_user_locations_dependencies() {
					echo '<div class="error"><p>' . sprintf( __( 'The <strong>%s</strong> extension requires the Ultimate Member plugin to be activated to work properly. You can download it <a href="https://wordpress.org/plugins/ultimate-member">here</a>', 'um-user-locations' ), um_user_locations_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_user_locations_dependencies' );

			} elseif ( true !== UM()->dependencies()->compare_versions( um_user_locations_requires, um_user_locations_version, 'user-locations', um_user_locations_extension ) ) {
				//UM old version is active
				function um_user_locations_dependencies() {
					echo '<div class="error"><p>' . UM()->dependencies()->compare_versions( um_user_locations_requires, um_user_locations_version, 'user-locations', um_user_locations_extension ) . '</p></div>';
				}

				add_action( 'admin_notices', 'um_user_locations_dependencies' );

			} else {
				require_once um_user_locations_path . 'includes/core/um-user-locations-init.php';
			}
		}
	}
}


register_activation_hook( um_user_locations_plugin, 'um_user_locations_activation_hook' );
function um_user_locations_activation_hook() {
	//first install
	$version = get_option( 'um_user_locations_version' );
	if ( ! $version ) {
		update_option( 'um_user_locations_last_version_upgrade', um_user_locations_version );
	}

	if ( $version != um_user_locations_version ) {
		update_option( 'um_user_locations_version', um_user_locations_version );
	}

	//run setup
	if ( ! class_exists( 'um_ext\um_user_locations\core\Setup' ) ) {
		require_once um_user_locations_path . 'includes/core/class-setup.php';
	}

	$user_locations_setup = new um_ext\um_user_locations\core\Setup();
	$user_locations_setup->run_setup();
}