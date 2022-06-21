<?php
namespace um_ext\um_friends\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Friends_Account
 * @package um_ext\um_friends\core
 */
class Friends_Account {


	/**
	 * Friends_Account constructor.
	 */
	function __construct() {
		add_action( 'um_post_account_update', array( &$this, 'account_update' ) );

		add_filter( 'um_account_page_default_tabs_hook', array( &$this, 'account_notification_tab' ), 10, 1 );
		add_filter( 'um_account_content_hook_notifications', array( &$this, 'account_tab' ), 50, 2 );
	}


	/**
	 * Update Account action
	 */
	function account_update() {
		/**
		 * issue helpscout#31301
		 */
		$current_tab = filter_input( INPUT_POST, '_um_account_tab' );
		if ( 'notifications' !== $current_tab ) {
			return;
		}

		$user_id = um_user( 'ID' );

		if ( isset( $_POST['_enable_new_friend'] ) ) {
			update_user_meta( $user_id, '_enable_new_friend', 'yes' );
		} else {
			update_user_meta( $user_id, '_enable_new_friend', 'no' );
		}

		if ( isset( $_POST['_enable_new_friend_request'] ) ) {
			update_user_meta( $user_id, '_enable_new_friend_request', 'yes' );
		} else {
			update_user_meta( $user_id, '_enable_new_friend_request', 'no' );
		}
	}


	/**
	 * Add Notifications tab to account page
	 *
	 * @param array $tabs
	 * @return array
	 */
	function account_notification_tab( $tabs ) {

		if ( empty( $tabs[400]['notifications'] ) ) {
			$tabs[400]['notifications'] = array(
				'icon'          => 'um-faicon-envelope',
				'title'         => __( 'Notifications', 'um-friends' ),
				'submit_title'  => __( 'Update Notifications', 'um-friends' ),
			);
		}

		return $tabs;
	}


	function account_tab( $output, $shortcode_args ) {
		if ( ! ( UM()->options()->get( 'new_friend_on' ) || UM()->options()->get( 'new_friend_request_on' ) ) ) {
			return $output;
		}

		if ( isset( $shortcode_args['_enable_new_friend'] ) && ! $shortcode_args['_enable_new_friend'] &&
		     isset( $shortcode_args['_enable_new_friend_request'] ) && ! $shortcode_args['_enable_new_friend_request'] ) {
			return $output;
		}

		$_enable_new_friend = UM()->Friends_API()->api()->enabled_email( get_current_user_id(), '_enable_new_friend' );
		$_enable_new_friend_request = UM()->Friends_API()->api()->enabled_email( get_current_user_id(), '_enable_new_friend_request' );

		$show_new_friend = false;
		if ( ! isset( $shortcode_args['_enable_new_friend'] ) || $shortcode_args['_enable_new_friend'] ) {
			if ( UM()->options()->get( 'new_friend_on' ) ) {
				UM()->account()->add_displayed_field( '_enable_new_friend', 'notifications' );
				$show_new_friend = true;
			}
		}

		$show_new_friend_request = false;
		if ( ! isset( $shortcode_args['_enable_new_friend_request'] ) || $shortcode_args['_enable_new_friend_request'] ) {
			if ( UM()->options()->get( 'new_friend_request_on' ) ) {
				UM()->account()->add_displayed_field( '_enable_new_friend_request', 'notifications' );
				$show_new_friend_request = true;
			}
		}

		$t_args = compact( '_enable_new_friend', '_enable_new_friend_request', 'show_new_friend', 'show_new_friend_request' );
		$output .= UM()->get_template( 'account-notifications.php', um_friends_plugin, $t_args );

		return $output;
	}

}