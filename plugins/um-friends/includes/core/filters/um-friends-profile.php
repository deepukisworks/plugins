<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * More profile privacy options
 *
 * @param array $options
 *
 * @return array
 */
function um_friends_profile_privacy_options( $options ) {
	$options = array_merge( $options, array(
		'friends' => __( 'Friends only', 'um-friends' ),
	) );

	return $options;
}
add_filter( 'um_profile_privacy_options', 'um_friends_profile_privacy_options', 100, 1 );


/**
 * Show field only for friends
 * @param bool $can_view
 * @param array $data
 *
 * @return bool
 */
function um_friends_can_view_field( $can_view, $data ) {
	if ( isset( $data['public'] ) && ( $data['public'] == '-4' || $data['public'] == '-6' ) ) {
		if ( ! is_user_logged_in() ) {
			$can_view = false;
		} else {
			if ( ! um_is_user_himself() && ! UM()->Friends_API()->api()->is_friend( get_current_user_id(), um_get_requested_user() ) ) {
				$can_view = apply_filters( 'um_friends_not_friend_maybe_other', false, $data );
			}
		}
	}

	return $can_view;
}
add_filter( 'um_can_view_field_custom', 'um_friends_can_view_field', 10, 2 );


/**
 * Show field 'only for friends and followers',
 * case if not follower maybe friend
 *
 * @param bool $can_view
 * @param array $data
 *
 * @return bool
 */
function um_friends_not_follower_maybe_other( $can_view, $data ) {
	if ( isset( $data['public'] ) && $data['public'] == '-6' ) {
		if ( UM()->Friends_API()->api()->is_friend( get_current_user_id(), um_get_requested_user() ) ) {
			$can_view = true;
		}
	}

	return $can_view;
}
add_filter( 'um_followers_not_follower_maybe_other', 'um_friends_not_follower_maybe_other', 10, 2 );


/**
 * Make private messaging privacy
 *
 * @param array $options
 *
 * @return array
 */
function um_friends_messaging_privacy_options( $options ) {
	$options['friends'] = __( 'Friends', 'um-friends' );
	return $options;
}
add_filter( 'um_messaging_privacy_options', 'um_friends_messaging_privacy_options', 10, 1 );
add_filter( 'um_user_notes_privacy_options_dropdown', 'um_friends_messaging_privacy_options', 10, 1 );


/**
 * @param bool $can_view
 * @param string $privacy
 * @param int $user_id
 *
 * @return bool
 */
function um_user_notes_custom_privacy( $can_view, $privacy, $user_id ) {
	if ( $privacy == 'friends' && ! UM()->Friends_API()->api()->is_friend( $user_id, um_profile_id() ) ) {
		return false;
	}

	return $can_view;
}
add_filter( 'um_user_notes_custom_privacy', 'um_user_notes_custom_privacy', 10, 3 );


/**
 * @param $options
 * @param null $user_id
 *
 * @return array
 */
function um_user_notes_exclude_activity( $options, $user_id = null ) {
	if ( empty( $user_id ) || ! UM()->Friends_API()->api()->is_friend( $user_id, um_profile_id() ) ) {
		$options[] = 'friends';
	}

	return $options;
}
add_filter( 'um_user_notes_exclude_activity', 'um_user_notes_exclude_activity', 10, 2 );


/**
 * Extend profile tabs
 *
 * @param array $tabs
 *
 * @return array
 */
function um_friends_add_tabs( $tabs ) {
	$tabs['friends'] = array(
		'name' => __( 'Friends', 'um-friends' ),
		'icon' => 'um-faicon-users',
	);

	return $tabs;
}
add_filter( 'um_profile_tabs', 'um_friends_add_tabs', 2000 );


/**
 * Add tabs based on user
 *
 * @param array $tabs
 *
 * @return array
 */
function um_friends_user_add_tab( $tabs ) {
	if ( empty( $tabs['friends'] ) ) {
		return $tabs;
	}

	$user_id = um_user( 'ID' );
	if ( ! $user_id ) {
		return $tabs;
	}

	$username = um_user( 'display_name' );

	$myfriends = ( um_is_myprofile() ) ? __( 'My Friends', 'um-friends' ) : sprintf( __( '%s\'s friends', 'um-friends' ), $username );
	$myfriends .= '<span>' . UM()->Friends_API()->api()->count_friends( $user_id, false ) . '</span>';

	$new_reqs = UM()->Friends_API()->api()->count_friend_requests_received( $user_id );

	if ( $new_reqs > 0 ) {
		$class = 'um-friends-notf';
	} else {
		$class = '';
	}

	$tabs['friends']['subnav_default'] = 'myfriends';
	$tabs['friends']['subnav'] = array(
		'myfriends' => $myfriends,
	);

	if ( um_is_myprofile() ) {
		// Display number of requests on the friends tab
		$tabs['friends']['notifier'] = (int) $new_reqs;
		
		$tabs['friends']['subnav']['friendreqs'] = __( 'Friend Requests', 'um-friends') . '<span class="'. $class . '">' . $new_reqs . '</span>';
		$tabs['friends']['subnav']['sentreqs'] = __( 'Friend Requests Sent', 'um-friends') . '<span>' . UM()->Friends_API()->api()->count_friend_requests_sent( $user_id ) . '</span>';
	}

	return $tabs;
}
add_filter( 'um_user_profile_tabs', 'um_friends_user_add_tab', 1000, 1 );


/**
 * Check if user can view user profile
 *
 * @param $can_view
 * @param int $user_id
 *
 * @return string
 */
function um_friends_can_view_main( $can_view, $user_id ) {
	if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {
		$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Friends only', 'um-friends' ) );
		$is_private_case = UM()->user()->is_private_case( $user_id, 'friends' );
		if ( ( $is_private_case || $is_private_case_old ) && ! current_user_can( 'manage_options' ) ) { //Enable admin to be able to view
			$can_view = __( 'You must be a friend of this user to view their profile', 'um-friends' );
		}
	}

	return $can_view;
}
add_filter( 'um_profile_can_view_main', 'um_friends_can_view_main', 10, 2 );


/**
 * Test case to hide profile
 *
 * @param $default
 * @param $option
 * @param $user_id
 *
 * @return bool
 */
function um_friends_private_filter_hook( $default, $option, $user_id ) {
	// user selected this option in privacy
	if ( $option == 'friends' || $option == __( 'Friends only', 'um-friends' ) ) {
		if ( ! UM()->Friends_API()->api()->is_friend( $user_id, get_current_user_id() ) ) {
			return true;
		}
	}

	return $default;
}
add_filter( 'um_is_private_filter_hook', 'um_friends_private_filter_hook', 100, 3 );


/**
 * Case if user can message only with friends
 *
 * @param $restrict
 * @param $who_can_pm
 * @param $recipient
 *
 * @return bool
 */
function um_friends_can_message_restrict( $restrict, $who_can_pm, $recipient ) {
	// user selected this option in privacy
	if ( $who_can_pm == 'friends' ) {
		if ( ! UM()->Friends_API()->api()->is_friend( get_current_user_id(), $recipient ) ) {
			return true;
		}
	}

	return $restrict;
}
add_filter( 'um_messaging_can_message_restrict', 'um_friends_can_message_restrict', 10, 3 );


/**
 * @param $content
 * @param $user_id
 * @param $post_id
 * @param $status
 *
 * @return mixed
 */
function um_friends_activity_mention_integration( $content, $user_id, $post_id, $status ) {
	if ( ! UM()->options()->get( 'activity_friends_mention' ) ) {
		return $content;
	}

	$mention = array();
	$mentioned_in_post = get_post_meta( $post_id, '_mentioned', true );
	$mentioned_in_post = $mentioned_in_post ? $mentioned_in_post : array();

	$friends = UM()->Friends_API()->api()->friends( $user_id );
	if ( $friends ) {
		foreach ( $friends as $k => $arr ) {
			/**
			 * @var int $user_id1
			 */
			extract( $arr );

			if ( $user_id1 == $user_id ) {
				continue;
			}

			um_fetch_user( $user_id1 );
			if ( ! stristr( $content, um_user( 'display_name' ) ) ) {
				continue;
			}

			if ( ! empty( $mentioned_in_post ) && in_array( $user_id1, $mentioned_in_post ) ) {
				$user_mentioned_in_post = true;
			} else {
				$user_mentioned_in_post = false;
			}

			$user_link = '<a href="' . um_user_profile_url() . '" class="um-link um-user-tag">' . um_user( 'display_name' ) . '</a>';
			$content = str_ireplace( '@' . um_user( 'display_name' ), $user_link, $content );

			if ( $user_mentioned_in_post == false ) {
				do_action( 'um_friends_new_mention', $user_id, $user_id1, $post_id );
				$mention[] = $user_id1;
			}
		}

		foreach ( $friends as $k => $arr ) {
			/**
			 * @var int $user_id2
			 */
			extract( $arr );

			if ( $user_id2 == $user_id ) {
				continue;
			}

			um_fetch_user( $user_id2 );
			if ( ! stristr( $content, um_user( 'display_name' ) ) ) {
				continue;
			}

			if ( ! empty( $mentioned_in_post ) && in_array( $user_id2, $mentioned_in_post ) ) {
				$user_mentioned_in_post = true;
			} else {
				$user_mentioned_in_post = false;
			}

			$user_link = '<a href="' . um_user_profile_url() . '" class="um-link um-user-tag">' . um_user( 'display_name' ) . '</a>';
			$content = str_ireplace( '@' . um_user( 'display_name' ), $user_link, $content );

			if ( $user_mentioned_in_post == false ) {
				do_action( 'um_friends_new_mention', $user_id, $user_id2, $post_id );
				$mention[] = $user_id2;
			}
		}
	}

	if ( ! empty( $mention ) ) {
		$mention = array_merge( $mentioned_in_post, $mention );
		update_post_meta( $post_id, '_mentioned', $mention );
	}

	return $content;
}
add_filter( 'um_activity_mention_integration', 'um_friends_activity_mention_integration', 10, 4 );


/**
 * Add options for profile tabs' privacy
 *
 * @param array $options
 *
 * @return array
 */
function um_friends_profile_tabs_privacy_options( $options ) {
	$options[7] = __( 'Only friends', 'um-friends' );

	// check if there is 'only followers' option
	if ( isset( $options[6] ) ) {
		$options[8] = __( 'Only friends and followers', 'um-friends' );
	}

	return $options;
}
add_filter( 'um_profile_tabs_privacy_list', 'um_friends_profile_tabs_privacy_options', 10, 1 );


/**
 * Show profile tab only for friends
 *
 * @param bool $can_view
 * @param int $privacy
 * @param string $tab
 * @param array $tab_data
 * @param int $user_id
 *
 * @return bool
 */
function um_friends_can_view_profile_tab( $can_view, $privacy, $tab, $tab_data, $user_id ) {
	if ( ! in_array( $privacy, [ 7, 8 ] ) ) {
		return $can_view;
	}

	if ( ! is_user_logged_in() ) {
		$can_view = false;
	} else {
		if ( get_current_user_id() == $user_id ) {
			$can_view = false;
		} else {
			if ( ! UM()->Friends_API()->api()->is_friend( get_current_user_id(), $user_id ) ) {
				$can_view = apply_filters( 'um_friends_profile_tab_not_friend_maybe_other', false, $privacy, $user_id );
			}
		}
	}

	return $can_view;
}
add_filter( 'um_profile_menu_can_view_tab', 'um_friends_can_view_profile_tab', 10, 5 );


/**
 * Show profile tab 'only for friends and followers',
 * case if not follower maybe friend
 *
 * @param bool $can_view
 * @param int $privacy
 * @param int $user_id
 *
 * @return bool
 */
function um_friends_profile_tab_not_follower_maybe_other( $can_view, $privacy, $user_id ) {
	if ( $privacy == 8 ) {
		if ( UM()->Friends_API()->api()->is_friend( get_current_user_id(), $user_id ) ) {
			$can_view = true;
		}
	}

	return $can_view;
}
add_filter( 'um_followers_profile_tab_not_follower_maybe_other', 'um_friends_profile_tab_not_follower_maybe_other', 10, 3 );