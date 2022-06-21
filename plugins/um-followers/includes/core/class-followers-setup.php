<?php
namespace um_ext\um_followers\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Followers_Setup
 * @package um_ext\um_followers\core
 */
class Followers_Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * Followers_Setup constructor.
	 */
	function __construct() {
		//settings defaults
		$this->settings_defaults = array(
			'followers_show_stats'              => 1,
			'followers_show_button'             => 1,
			'followers_allow_admin_to_follow'   => 0,
			'new_follower_on'                   => 1,
			'new_follower_sub'                  => '{follower} is now following you on {site_name}!',
			'new_follower'                      => 'Hi {followed},<br /><br />' .
				'{follower} has just followed you on {site_name}.<br /><br />' .
				'View his/her profile:<br />' .
				'{follower_profile}<br /><br />' .
				'Click on the following link to see your followers:<br />' .
				'{followers_url}<br /><br />' .
				'This is an automated notification from {site_name}. You do not need to reply.',
		);

		$notification_types_templates = array(
			'new_follow'    => __( '<strong>{member}</strong> has just followed you!', 'um-followers' ),
		);

		foreach ( $notification_types_templates as $k => $template ) {
			$this->settings_defaults[ 'log_' . $k ] = 1;
			$this->settings_defaults[ 'log_' . $k . '_template' ] = $template;
		}
	}


	/**
	 *
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			//set new options to default
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}

		}

		update_option( 'um_options', $options );
	}


	/**
	 * sql setup
	 */
	function sql_setup() {
		global $wpdb;

		if ( get_option( 'ultimatemember_followers_db' ) == um_followers_version ) {
			return;
		}

		$charset_collate = $wpdb->get_charset_collate();

		/*
		 * Pay an attention if the customer has active strict mode in the database
		 * the table cannot be created. The reason for this not supporting strict mode by WordPress by default.
		 * see ticket https://core.trac.wordpress.org/ticket/8857#comment:19
		 *
		 * For strict mode the datetime value can be in the range from 1000-01-01 00:00:00 to 9999-12-31 23:59:59
		 * ref.: https://www.mysqltutorial.org/mysql-datetime/
		 *
		 * We support wp.org logic because it's plugin for WordPress. So please temporarily disable strict-mode or create DB table
		 * manually via hosting CPanel
		 */
		$sql = "CREATE TABLE {$wpdb->prefix}um_followers (
id int(11) unsigned NOT NULL auto_increment,
time datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
user_id1 int(11) unsigned NOT NULL,
user_id2 int(11) unsigned NOT NULL,
PRIMARY KEY  (id)
) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		update_option( 'ultimatemember_followers_db', um_followers_version );
	}


	/**
	 *
	 */
	function run_setup() {
		$this->sql_setup();
		$this->set_default_settings();
	}
}