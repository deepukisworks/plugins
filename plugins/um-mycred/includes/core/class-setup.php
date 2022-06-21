<?php
namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Setup
 *
 * @package um_ext\um_mycred\core
 */
class Setup {


	/**
	 * @var array
	 */
	var $settings_defaults;


	/**
	 * Setup constructor.
	 */
	function __construct() {
		//settings defaults

		$point_type = defined( 'MYCRED_DEFAULT_TYPE_KEY' ) ? MYCRED_DEFAULT_TYPE_KEY : 'mycred_default';

		$this->settings_defaults = array(
			'mycred_badge_size'             => 80,
			'account_tab_points'            => 1,
			'mycred_refer'                  => 0,
			'mycred_show_badges_in_header'  => 0,
			'mycred_show_badges_in_members' => 0,
			'mycred_hide_role'              => 0,
			'mycred_show_bb_rank'           => 0,
			'mycred_show_bb_points'         => 0,
			'mycred_show_bb_progress'       => 0,
			'profile_tab_badges'            => 1,
			'profile_tab_badges_privacy'    => 0,
			'mycred_point_types'            => array( $point_type ),
		);

		$notifications_templates = array(
			'mycred_award'          => __( 'You have received <strong>{mycred_points}</strong> for <strong>{mycred_task}</strong>', 'um-mycred' ),
			'mycred_deduct'         => __( '<strong>{mycred_points}</strong> deduction for <strong>{mycred_task}</strong>', 'um-mycred' ),
			'mycred_points_sent'    => __( 'You have just got <strong>{mycred_points}</strong> from <strong>{mycred_sender}</strong>', 'um-mycred' ),
		);

		foreach ( $notifications_templates as $k => $template ) {
			$this->settings_defaults[ 'log_' . $k ] = 1;
			$this->settings_defaults[ 'log_' . $k . '_template' ] = $template;
		}
	}


	/**
	 * Set default settings
	 */
	function set_default_settings() {
		$options = get_option( 'um_options', array() );

		foreach ( $this->settings_defaults as $key => $value ) {
			if ( ! isset( $options[ $key ] ) ) {
				$options[ $key ] = $value;
			}
		}

		update_option( 'um_options', $options );
	}


	/**
	 * Run UM:myCRED setup
	 */
	function run_setup() {
		$this->set_default_settings();
	}
}