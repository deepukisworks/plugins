<?php

namespace um_ext\um_reviews\core;

if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Class Reviews_Email
 *
 * @example UM()->Reviews()->email();
 * @package um_ext\um_reviews\core
 */
class Reviews_Email {

	/**
	 * Reviews_Email constructor.
	 */
	public function __construct() {

	}

	/**
	 * Checks if user enabled email notification
	 *
	 * @param $user_id
	 * @return bool|int
	 */
	function enabled_email( $user_id, $key ) {
		$_enabled = true;
		if ( get_user_meta( $user_id, $key, true ) == 'yes' ) {
			$_enabled = 1;
		} elseif ( get_user_meta( $user_id, $key, true ) == 'no' ) {
			$_enabled = 0;
		}
		return $_enabled;
	}

	/**
	 * Send a mail "New Review Notification"
	 *
	 * @param int $review_id
	 */
	public function review_notice( $review_id ) {

		$review = get_post( $review_id );

		if ( $this->enabled_email( $review->_user_id, '_enable_new_reviews' ) ) {

			um_fetch_user( $review->_user_id );
			$user = get_userdata( $review->_user_id );
			$reviewer = get_userdata( $review->_reviewer_id );
			$reviews_url = add_query_arg( 'profiletab', 'reviews', um_user_profile_url( $review->_user_id ) );

			UM()->mail()->send( $user->user_email, 'review_notice', array(
					'plain_text'	 => 1,
					'path'				 => um_reviews_path . 'templates/email/',
					'tags'				 => array(
							'{rating}',
							'{reviews_link}',
							'{user}',
							'{reviewer}',
							'{review_content}'
					),
					'tags_replace' => array(
							sprintf( __( '%s star', 'um-reviews' ), $review->_rating ),
							$reviews_url,
							$user->display_name,
							$reviewer->display_name,
							stripslashes( $review->post_content )
					)
			) );
		}
	}

	/**
	 * Send a mail "New review is pending"
	 *
	 * @param int $review_id
	 */
	public function review_pending_notice( $review_id ) {

		$review = get_post( $review_id );

		if( empty($review->_status) ){

			$emails = um_multi_admin_email();
			if ( empty( $emails ) ) {
				return;
			}

			um_fetch_user( $review->_user_id );
			$user = get_userdata( $review->_user_id );
			$reviewer = get_userdata( $review->_reviewer_id );
			$reviews_url = add_query_arg( 'profiletab', 'reviews', um_user_profile_url( $review->_user_id ) );
			$review_admin_url = add_query_arg( array(
					'action' => 'edit',
					'post'	 => $review_id
			), admin_url( 'post.php' ) );

			foreach ( $emails as $email ) {
				UM()->mail()->send( $email, 'review_pending_notice', array(
						'admin'        => true,
						'tags'				 => array(
								'{rating}',
								'{reviews_link}',
								'{review_admin_link}',
								'{user}',
								'{reviewer}',
								'{review_content}'
						),
						'tags_replace' => array(
								sprintf( __( '%s star', 'um-reviews' ), $review->_rating ),
								$reviews_url,
								$review_admin_url,
								$user->display_name,
								$reviewer->display_name,
								stripslashes( $review->post_content )
						)
				) );
			}
		}
	}

	/**
	 * Send a mail "New Reply Notification"
	 *
	 * @param int $reply_id
	 */
	public function review_reply_notice( $reply_id ) {

		$reply = get_comment( $reply_id );
		$review = get_post( $reply->comment_post_ID );

		if ( $this->enabled_email( $review->_reviewer_id, '_enable_new_reviews_reply' ) ) {

			um_fetch_user( $review->_user_id );
			$user = get_userdata( $review->_user_id );
			$reviewer = get_userdata( $review->_reviewer_id );
			$reviews_url = add_query_arg( 'profiletab', 'reviews', um_user_profile_url( $review->_user_id ) );

			UM()->mail()->send( $reviewer->user_email, 'review_reply_notice', array(
					'plain_text'	 => 1,
					'path'				 => um_reviews_path . 'templates/email/',
					'tags'				 => array(
							'{reviews_link}',
							'{user}',
							'{reviewer}',
							'{review_content}',
							'{reply_content}'
					),
					'tags_replace' => array(
							$reviews_url,
							$user->display_name,
							$reviewer->display_name,
							stripslashes( $review->post_content ),
							stripslashes( $reply->comment_content )
					)
			) );
		}
	}

}
