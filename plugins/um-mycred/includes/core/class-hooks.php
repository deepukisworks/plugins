<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Hooks
 */
class Hooks {


	/**
	 * Hooks constructor.
	 */
	function __construct() {
		add_action( 'plugins_loaded', array( &$this, 'init_hooks' ), 1 );
		add_action( 'plugins_loaded', array( &$this, 'load_hooks' ), 2 );
	}


	/**
	 * Init myCRED Custom Hooks
	 *
	 */
	function init_hooks() {
		add_filter( 'mycred_setup_hooks', array( $this, 'register_custom_hooks' ), 10, 2 );
		add_filter( 'mycred_all_references', array( $this, 'references' ), 10, 1 );
	}


	/**
	 * Core Hooks
	 *
	 * @param array $installed
	 * @param int $point_type
	 *
	 * @return array
	 */
	function register_custom_hooks( $installed, $point_type ) {

		// Register
		$installed['um-user-register'] = array(
			'title'        => __( 'Ultimate Member - Registration', 'um-mycred' ),
			'description'  => __( 'Award %_plural% for register hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Register_Hooks' )
		);

		// Login
		$installed['um-user-login'] = array(
			'title'        => __( 'Ultimate Member - Login', 'um-mycred' ),
			'description'  => __( 'Award %_plural% for login hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Login_Hooks' )
		);

		// Profile
		$installed['um-user-profile'] = array(
			'title'        => __( 'Ultimate Member - Profile', 'um-mycred' ),
			'description'  => __( 'Award %_plural% for profile hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Profile_Hooks' )
		);

		// Account
		$installed['um-user-account'] = array(
			'title'        => __( 'Ultimate Member - Account', 'um-mycred' ),
			'description'  => __( 'Award %_plural% for account hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Account_Hooks' )
		);

		// Member Directory
		$installed['um-member-directory'] = array(
			'title'        => __( 'Ultimate Member - Member Directory', 'um-mycred' ),
			'description'  => __( 'Award %_plural% for Member Directory hooks', 'um-mycred' ),
			'callback'     => array( 'UM_myCRED_Member_Directory_Hooks' )
		);


		$installed = apply_filters( 'um_mycred_hooks_installed__filter', $installed );

		return $installed;
	}


	/**
	 * @param array $hooks
	 *
	 * @return array
	 */
	function references( $hooks ) {

		$hooks = array_merge( $hooks, array(
			'um-user-register'  => __( 'Ultimate Member - Completing registration', 'um-mycred' ),
			'um-user-login'     => __( 'Ultimate Member - Logging via UM Login Form', 'um-mycred' ),
			'profile_photo'     => __( 'Ultimate Member - Uploading Profile Photo', 'um-mycred' ),
			'cover_photo'       => __( 'Ultimate Member - Uploading Cover Photo', 'um-mycred' ),
			'update_profile'    => __( 'Ultimate Member - Updating Profile', 'um-mycred' ),
			'update_account'    => __( 'Ultimate Member - Updating Account', 'um-mycred' ),
			'member_search'     => __( 'Ultimate Member - Using Search in Members Directory', 'um-mycred' ),
		) );

		return $hooks;
	}


	/**
	 *
	 */
	function load_hooks() {
		do_action( 'um_mycred_load_hooks' );

		require_once um_mycred_path . 'includes/core/hooks/um-mycred-account.php';
		require_once um_mycred_path . 'includes/core/hooks/um-mycred-login.php';
		require_once um_mycred_path . 'includes/core/hooks/um-mycred-member-directory.php';
		require_once um_mycred_path . 'includes/core/hooks/um-mycred-profile.php';
		require_once um_mycred_path . 'includes/core/hooks/um-mycred-register.php';
	}


	/**
	 * @param $hook_callback
	 */
	function build_hook_widget( $hook_callback ) {
		require um_mycred_path . 'includes/admin/templates/hook-widget.php';
	}


	/**
	 * @param $data
	 * @param $hook_callback
	 *
	 * @return mixed
	 */
	function sanitise_preferences( $data, $hook_callback ) {
		$new_data = $data;

		foreach ( $hook_callback->um_hooks as $hook => $k ) {
			// Apply defaults if any field is left empty
			$new_data[ $hook ]['creds'] = ! empty( $data[ $hook ]['creds'] ) ? $data[ $hook ]['creds'] : $hook_callback->defaults[ $hook ]['creds'];
			$new_data[ $hook ]['log'] = ! empty( $data[ $hook ]['log'] ) ? sanitize_text_field( $data[ $hook ]['log'] ) : $hook_callback->defaults[ $hook ]['log'];
			$new_data[ $hook ]['limit_by'] = ! empty( $data[ $hook ]['limit_by'] ) ? sanitize_text_field( $data[ $hook ]['limit_by'] ) : $hook_callback->defaults[ $hook ]['limit_by'];

			$limit = ! empty( $data[ $hook ]['limit'] ) ? sanitize_text_field( $data[ $hook ]['limit'] ) : $hook_callback->defaults[ $hook ]['limit'];
			if ( $limit != '' ) {
				$new_data[ $hook ]['limit'] = $limit . '/' . $new_data[ $hook ]['limit_by'];
				unset( $new_data[ $hook ]['limit_by'] );
			}
		}

		$new_data = apply_filters( 'um_mycred_sanitise_pref', $new_data, $hook_callback );

		return $new_data;
	}

}