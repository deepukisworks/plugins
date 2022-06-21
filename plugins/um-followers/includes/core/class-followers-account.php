<?php
namespace um_ext\um_followers\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Followers_Account
 * @package um_ext\um_friends\core
 */
class Followers_Account {


	/**
	 * Followers_Account constructor.
	 */
	function __construct() {
		add_action( 'um_post_account_update', array( &$this, 'account_update' ) );

		add_filter( 'um_account_page_default_tabs_hook', array( &$this, 'account_notification_tab' ), 10, 1 );
		add_filter( 'um_account_content_hook_notifications', array( &$this, 'account_tab' ), 49, 2 );
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

		if ( isset( $_POST['_enable_new_follow'] ) ) {
			update_user_meta( $user_id, '_enable_new_follow', 'yes' );
		} else {
			update_user_meta( $user_id, '_enable_new_follow', 'no' );
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
				'title'         => __( 'Notifications', 'um-followers' ),
				'submit_title'  => __( 'Update Notifications', 'um-followers' ),
			);
		}

		return $tabs;
	}


	function account_tab( $output, $shortcode_args ) {
		if ( ! UM()->options()->get( 'new_follower_on' ) ) {
			return $output;
		}

		if ( isset( $shortcode_args['_enable_new_follow'] ) && 0 == $shortcode_args['_enable_new_follow'] ) {
			return $output;
		}

		UM()->account()->add_displayed_field( '_enable_new_follow', 'notifications' );

		$_enable_new_follow = UM()->Followers_API()->api()->enabled_email( get_current_user_id() );

		$t_args = compact( '_enable_new_follow' );
		$output .= UM()->get_template( 'account-notifications.php', um_followers_plugin, $t_args );

		return $output;
	}

}