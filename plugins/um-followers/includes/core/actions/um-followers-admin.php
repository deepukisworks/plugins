<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Delete multiselect fields
 *
 * @param int $post_id
 */
function um_followers_multi_choice_keys( $post_id ) {
	delete_post_meta( $post_id, '_um_can_follow_roles' );
}
add_action( 'um_admin_before_saving_role_meta', 'um_followers_multi_choice_keys' );


/**
 * When user is removed all their following data should be removed
 *
 * @param int $user_id
 */
function um_followers_delete_user_data( $user_id ) {
	global $wpdb;

	$wpdb->query( $wpdb->prepare(
		"DELETE 
		FROM {$wpdb->prefix}um_followers 
		WHERE user_id1 = %d OR 
			  user_id2 = %d",
		$user_id,
		$user_id
	) );
}
add_action( 'um_delete_user', 'um_followers_delete_user_data', 10, 1 );