<?php
namespace um_ext\um_reviews\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Reviews_Account
 * @package um_ext\um_reviews\core
 */
class Reviews_Account {


	/**
	 * Reviews_Account constructor.
	 */
	function __construct() {
		add_action( 'um_post_account_update', array( &$this, 'account_update' ) );

		add_filter( 'um_account_page_default_tabs_hook', array( &$this, 'account_notification_tab' ), 10, 1 );
		add_filter( 'um_account_content_hook_notifications', array( &$this, 'account_tab' ), 62, 2 );
	}


	/**
	 * Update Account action
	 */
	function account_update() {
		$current_tab = filter_input( INPUT_POST, '_um_account_tab' );
		if ( 'notifications' !== $current_tab ) {
			return;
		}

		$user_id = um_user( 'ID' );

		if ( isset( $_POST['_enable_new_reviews'] ) ) {
			update_user_meta( $user_id, '_enable_new_reviews', 'yes' );
		} else {
			update_user_meta( $user_id, '_enable_new_reviews', 'no' );
		}
		if ( isset( $_POST['_enable_new_reviews_reply'] ) ) {
			update_user_meta( $user_id, '_enable_new_reviews_reply', 'yes' );
		} else {
			update_user_meta( $user_id, '_enable_new_reviews_reply', 'no' );
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
				'title'         => __( 'Notifications', 'um-reviews' ),
				'submit_title'  => __( 'Update Notifications', 'um-reviews' ),
			);
		}

		return $tabs;
	}


	function account_tab( $output, $shortcode_args ) {
		if ( ! ( UM()->options()->get( 'review_notice_on' ) || UM()->options()->get( 'review_reply_notice_on' ) ) ) {
			return $output;
		}

		if ( isset( $shortcode_args['_enable_new_reviews'] ) && 0 == $shortcode_args['_enable_new_reviews'] &&
		     isset( $shortcode_args['_enable_new_reviews_reply'] ) && 0 == $shortcode_args['_enable_new_reviews_reply'] ) {
			return $output;
		}

		$show_new_reviews = false;
		if ( ! isset( $shortcode_args['_enable_new_reviews'] ) || $shortcode_args['_enable_new_reviews'] ) {
			if ( UM()->options()->get( 'review_notice_on' ) ) {
				UM()->account()->add_displayed_field( '_enable_new_reviews', 'notifications' );
				$show_new_reviews = true;
			}
		}


		$show_new_reviews_reply = false;
		if ( ! isset( $shortcode_args['_enable_new_reviews_reply'] ) || $shortcode_args['_enable_new_reviews_reply'] ) {
			if ( UM()->options()->get( 'review_reply_notice_on' ) ) {
				UM()->account()->add_displayed_field( '_enable_new_reviews_reply', 'notifications' );
				$show_new_reviews_reply = true;
			}
		}

		$_enable_new_reviews = UM()->Reviews()->email()->enabled_email( get_current_user_id(), '_enable_new_reviews' );
		$_enable_new_reviews_reply = UM()->Reviews()->email()->enabled_email( get_current_user_id(), '_enable_new_reviews_reply' );

		$t_args = compact( '_enable_new_reviews', '_enable_new_reviews_reply', 'show_new_reviews', 'show_new_reviews_reply' );

		$output .= UM()->get_template( 'account_notifications.php', um_reviews_plugin, $t_args );

		return $output;
	}

}