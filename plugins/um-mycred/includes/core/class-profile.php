<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile
 */
class Profile {


	/**
	 * Profile constructor.
	 */
	function __construct() {
		add_filter( 'um_profile_tabs',  array( &$this, 'add_tab' ), 2000, 1 );
		add_filter( 'um_user_profile_tabs', array( &$this, 'add_subtabs' ), 2000, 1 );

		add_action( 'um_after_profile_header_name', array( &$this, 'um_mycred_show_badges_header' ) );
		add_action( 'um_profile_content_badges_default', array( &$this, 'um_profile_content_badges_default' ) );
		add_action( 'um_profile_content_badges_my_badges', array( &$this, 'um_profile_content_badges_my_badges' ) );
		add_action( 'um_profile_content_badges_all_badges', array( &$this, 'um_profile_content_badges_all_badges' ) );
	}


	/**
	 * Adds a main tab to display badges in profile
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	function add_tab( $tabs ) {
		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return $tabs;
		}

		$tabs['badges'] = array(
			'name' => __( 'Badges', 'um-mycred' ),
			'icon' => 'um-icon-ribbon-b',
		);

		return $tabs;
	}


	/**
	 * Add tabs based on user
	 *
	 * @param array $tabs
	 *
	 * @return array
	 */
	function add_subtabs( $tabs ) {
		if ( empty( $tabs['badges'] ) ) {
			return $tabs;
		}

		$display_name = um_user( 'display_name' );
		if ( strstr( $display_name, ' ' ) ) {
			$display_name = explode( ' ', $display_name );
			$display_name = $display_name[0];
		}

		$tabs['badges']['subnav_default'] = 'my_badges';
		$tabs['badges']['subnav'] = array(
			'my_badges'     => ( um_is_myprofile() ) ? __( 'Your Badges', 'um-mycred' ) : sprintf( __( '%s\'s Badges', 'um-mycred' ), $display_name ),
			'all_badges'    => __( 'All Badges', 'um-mycred' ),
		);

		return $tabs;
	}





	/**
	 * Display badges in header
	 */
	function um_mycred_show_badges_header() {
		if ( ! UM()->options()->get( 'mycred_show_badges_in_header' ) ) {
			return;
		}
		echo UM()->myCRED()->badges()->show( um_profile_id() );
	}


	/**
	 * Default badges tab
	 *
	 * @param $args
	 */
	function um_profile_content_badges_default( $args ) {
		echo UM()->myCRED()->badges()->show( um_profile_id() );
	}


	/**
	 * Show user badges
	 *
	 * @param $args
	 */
	function um_profile_content_badges_my_badges( $args ) {
		echo UM()->myCRED()->badges()->show( um_profile_id() );
	}


	/**
	 * Show all badges
	 *
	 * @param $args
	 */
	function um_profile_content_badges_all_badges( $args ) {
		echo UM()->myCRED()->badges()->show_all( 2 );
	}




}