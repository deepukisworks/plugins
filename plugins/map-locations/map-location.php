<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://beesm.art/
 * @since             1.0.0
 * @package           Map_Location
 *
 * @wordpress-plugin
 * Plugin Name:       map-location
 * Plugin URI:        https://beesm.art/
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Developer
 * Author URI:        https://beesm.art/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       map-location
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'MAP_LOCATION_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-map-location-activator.php
 */
function activate_map_location() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-map-location-activator.php';
	Map_Location_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-map-location-deactivator.php
 */
function deactivate_map_location() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-map-location-deactivator.php';
	Map_Location_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_map_location' );
register_deactivation_hook( __FILE__, 'deactivate_map_location' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */

require plugin_dir_path( __FILE__ ) . 'includes/class-map-location.php';
require plugin_dir_path( __FILE__ ) . 'public/partials/map-location-public-display.php';


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */

function run_map_location() {

	$plugin = new Map_Location();
	$plugin->run();

}
run_map_location();

require plugin_dir_path( __FILE__ ) . 'includes/admin-pages.php';
require plugin_dir_path( __FILE__ ) . 'admin/partials/map-location-admin-display.php';