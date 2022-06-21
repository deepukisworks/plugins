<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Filter user permissions in bbPress
 *
 * @param array $meta
 * @param int $user_id
 *
 * @return array
 */
function um_followers_user_permissions_filter( $meta, $user_id ) {
	if ( ! isset( $meta['can_follow'] ) ) {
		$meta['can_follow'] = 1;
	}

	return $meta;
}
add_filter( 'um_user_permissions_filter', 'um_followers_user_permissions_filter', 10, 4 );


/**
 * Filter for Groups invites
 *
 * @param array $options
 *
 * @return array
 */
function um_followers_groups_invite_people( $options ) {
	$array_invite_people_opts['followers'] = __( 'Followers only', 'um-followers' );
	return $options;
}
add_filter( 'um_groups_invite_people', 'um_followers_groups_invite_people', 10, 1 );


/**
 * Creates options in Role page
 *
 * @param $roles_metaboxes
 *
 * @return array
 */
function um_followers_add_role_metabox( $roles_metaboxes ) {

	$roles_metaboxes[] = array(
		'id'        => "um-admin-form-followers{" . um_followers_path . "}",
		'title'     => __( 'Followers', 'um-followers' ),
		'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
		'screen'    => 'um_role_meta',
		'context'   => 'normal',
		'priority'  => 'default'
	);

	return $roles_metaboxes;
}
add_filter( 'um_admin_role_metaboxes', 'um_followers_add_role_metabox', 10, 1 );


/**
 * Add options for field's privacy
 *
 * @param array $options
 *
 * @return array
 */
function um_followers_privacy_options( $options ) {
	$options['-5'] = __( 'Only followers', 'um-followers' );

	// check if there is 'only friends' option
	if ( isset( $options['-4'] ) ) {
		$options['-6'] = __( 'Only friends and followers', 'um-followers' );
	}

	return $options;
}
add_filter( 'um_field_privacy_options', 'um_followers_privacy_options', 10, 1 );