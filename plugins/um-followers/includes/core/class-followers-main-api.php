<?php
namespace um_ext\um_followers\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Followers_Main_API
 * @package um_ext\um_followers\core
 */
class Followers_Main_API {


	/**
	 * Followers_Main_API constructor.
	 */
	function __construct() {
		global $wpdb;
		$this->table_name = $wpdb->prefix . "um_followers";
	}


	/**
	 * @param $args
	 *
	 * @return array
	 */
	function rest_get_following( $args ) {

		/**
		 * @var $id
		 */
		extract( $args );

		$response = array();
		$error = array();

		if ( ! $id ) {
			$error['error'] = __( 'You must provide a user ID', 'um-followers' );
			return $error;
		}

		$results = UM()->Followers_API()->api()->following( $id );
		if ( ! $results ) {
			$error['error'] = __( 'No users were found', 'um-followers' );
			return $error;
		}
		$response['following']['count'] = $this->count_following_plain( $id );
		foreach ( $results as $k => $v ) {
			$user = get_userdata( $v['user_id1'] );
			$response['following']['users'][ $k ]['ID'] = $v['user_id1'];
			$response['following']['users'][ $k ]['username'] = $user->user_login;
			$response['following']['users'][ $k ]['display_name'] = $user->display_name;
		}

		return $response;
	}


	/**
	 * @param $args
	 *
	 * @return array
	 */
	function rest_get_followers( $args ) {
		/**
		 * @var $id
		 */
		extract( $args );

		$response = array();
		$error = array();

		if ( ! $id ) {
			$error['error'] = __( 'You must provide a user ID', 'um-followers' );
			return $error;
		}

		$results = $this->followers( $id );
		if ( ! $results ) {
			$error['error'] = __( 'No users were found', 'um-followers' );
			return $error;
		}
		$response['followers']['count'] = $this->count_followers_plain( $id );
		foreach ( $results as $k => $v ) {
			$user = get_userdata( $v['user_id2'] );
			$response['followers']['users'][$k]['ID'] = $v['user_id2'];
			$response['followers']['users'][$k]['username'] = $user->user_login;
			$response['followers']['users'][$k]['display_name'] = $user->display_name;
		}

		return $response;
	}


	/**
	 * Checks if user enabled email notification
	 *
	 * @param $user_id
	 *
	 * @return bool|int
	 */
	function enabled_email( $user_id ) {
		$_enable_new_follow = true;
		if ( get_user_meta( $user_id, '_enable_new_follow', true ) == 'yes' ) {
			$_enable_new_follow = 1;
		} else if ( get_user_meta( $user_id, '_enable_new_follow', true ) == 'no' ) {
			$_enable_new_follow = 0;
		}
		return $_enable_new_follow;
	}


	/**
	 * Show the followers list URL
	 *
	 * @param $user_id
	 *
	 * @return bool|string
	 */
	function followers_link( $user_id ) {
		$nav_link = um_user_profile_url( $user_id );
		$nav_link = add_query_arg( 'profiletab', 'followers', $nav_link );
		return $nav_link;
	}


	/**
	 * Show the following list URL
	 *
	 * @param $user_id
	 *
	 * @return bool|string
	 */
	function following_link( $user_id ) {
		$nav_link = um_user_profile_url( $user_id );
		$nav_link = add_query_arg( 'profiletab', 'following', $nav_link );
		return $nav_link;
	}


	/**
	 * Show the follow button for two users
	 *
	 * @param $user_id1
	 * @param $user_id2
	 *
	 * @return string
	 */
	function follow_button( $user_id1, $user_id2 ) {
		wp_enqueue_script( 'um_followers' );
		wp_enqueue_style( 'um_followers' );

		$res = '';
		$hide_follow_button = apply_filters( 'um_followers_hide_button', false );
		if ( $hide_follow_button || ( current_user_can( 'manage_options' ) && UM()->options()->get( 'followers_allow_admin_to_follow' ) == 0 ) ) {
			return $res;
		}

		if ( ! is_user_logged_in() ) {
			$redirect = um_get_core_page( 'login' );

			if ( UM()->is_request( 'ajax' ) ) {
				if ( isset( $_REQUEST['post_refferer'] ) ) {
					$link = get_permalink( absint( $_REQUEST['post_refferer'] ) );
				}

				if ( empty( $link ) ) {
					$link = um_get_core_page( 'members' );
				}
			} else {
				$link = UM()->permalinks()->get_current_url();
			}

			$redirect = add_query_arg( 'redirect_to', $link, $redirect );
			$redirect = apply_filters( 'um_followers_button_redirect_url', $redirect );
			$res = '<a href="' . esc_url( $redirect ) . '" class="um-login-to-follow-btn um-button um-alt">' . __( 'Follow', 'um-followers' ) . '</a>';
			return $res;
		}

		if ( $this->can_follow( $user_id1, $user_id2 ) ) {

			if ( ! $this->followed( $user_id1, $user_id2 ) ) {
				$res = '<a href="javascript:void(0);" class="um-follow-btn um-button um-alt" data-user_id="' . esc_attr( $user_id1 ) . '">'. esc_html__( 'Follow', 'um-followers' ). '</a>';
			} else {
				$res = '<a href="javascript:void(0);" class="um-unfollow-btn um-button" data-user_id="' . esc_attr( $user_id1 ) . '" data-following="' . esc_attr__( 'Following', 'um-followers' ).'" data-unfollow="' . esc_attr__( 'Unfollow', 'um-followers' ) . '">' . esc_html__( 'Following', 'um-followers' ) . '</a>';
			}

		}
		return $res;
	}


	/**
	 * If user can follow
	 *
	 * @param $user_id1
	 * @param $user_id2
	 * @return bool
	 */
	function can_follow( $user_id1, $user_id2 ) {
		if ( ! is_user_logged_in() ) {
			return true;
		}

		$roles1 = UM()->roles()->get_all_user_roles( $user_id1 );

		$role2 = UM()->roles()->get_priority_user_role( $user_id2 );
		$role_data2 = UM()->roles()->role_data( $role2 );
		$role_data2 = apply_filters( 'um_user_permissions_filter', $role_data2, $user_id2 );

		if ( ! $role_data2['can_follow'] ) {
			return false;
		}

		if ( ! empty( $role_data2['can_follow_roles'] ) &&
			 ( empty( $roles1 ) || count( array_intersect( $roles1, maybe_unserialize( $role_data2['can_follow_roles'] ) ) ) <= 0 ) ) {
			return false;
		}

		if ( $user_id1 != $user_id2 && is_user_logged_in() ) {
			return true;
		}

		return false;
	}


	/**
	 * Get the count of followers
	 *
	 * @param int $user_id
	 * @return null|string
	 */
	function count_followers_plain( $user_id = 0 ) {
		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$this->table_name}
			WHERE user_id1 = %d AND
				  user_id2 IN ( SELECT ID FROM {$wpdb->users} )",
			$user_id
		) );

		return isset( $count ) ? (int) $count : 0;
	}


	/**
	 * Get the count of followers in nice format
	 *
	 * @param int $user_id
	 * @return string
	 */
	function count_followers( $user_id = 0 ) {
		wp_enqueue_script( 'um_followers' );
		wp_enqueue_style( 'um_followers' );

		$count = $this->count_followers_plain( $user_id );
		return '<span class="um-ajax-count-followers">' . number_format( $count ) . '</span>';
	}


	/**
	 * Get the count of following
	 *
	 * @param int $user_id
	 * @return null|string
	 */
	function count_following_plain( $user_id = 0 ) {
		global $wpdb;

		$count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*)
			FROM {$this->table_name}
			WHERE user_id2 = %d AND
				  user_id1 IN ( SELECT ID FROM {$wpdb->users} )",
			$user_id
		) );

		return isset( $count ) ? (int) $count : 0;
	}


	/**
	 * Get the count of following in nice format
	 *
	 * @param int $user_id
	 * @return string
	 */
	function count_following( $user_id = 0 ) {
		wp_enqueue_script( 'um_followers' );
		wp_enqueue_style( 'um_followers' );

		$count = $this->count_following_plain( $user_id );
		return '<span class="um-ajax-count-following">' . number_format( $count ) . '</span>';
	}


	/**
	 * Add a follow action
	 *
	 * @param $user_id1
	 * @param $user_id2
	 * @return bool|false|int
	 */
	function add( $user_id1, $user_id2 ) {
		global $wpdb;

		// if already followed do not add
		if ( $this->followed( $user_id1, $user_id2 ) ) {
			return false;
		}

		$result = $wpdb->insert(
			$this->table_name,
			array(
				'time' => current_time( 'mysql' ),
				'user_id1' => $user_id1,
				'user_id2' => $user_id2
			),
			array(
				'%s',
				'%d',
				'%d'
			)
		);

		return $result;
	}


	/**
	 * Removes a follow connection
	 *
	 * @param $user_id1
	 * @param $user_id2
	 * @return bool
	 */
	function remove( $user_id1, $user_id2 ) {
		global $wpdb;

		// If user is not followed do not do anything
		if ( ! $this->followed( $user_id1, $user_id2 ) ) {
			return false;
		}

		$wpdb->delete(
			$this->table_name,
			array(
				'user_id1' => $user_id1,
				'user_id2' => $user_id2
			)
		);

		return true;
	}


	/**
	 * Checks if user is follower of another user
	 *
	 * @param $user_id1
	 * @param $user_id2
	 * @return bool
	 */
	function followed( $user_id1, $user_id2 ) {
		global $wpdb;

		$results = $wpdb->get_var( $wpdb->prepare(
			"SELECT user_id1
			FROM {$this->table_name}
			WHERE user_id1 = %d AND
				  user_id2 = %d AND
				  user_id1 IN ( SELECT ID FROM {$wpdb->users} ) AND
				  user_id2 IN ( SELECT ID FROM {$wpdb->users} )
			LIMIT 1",
			$user_id1,
			$user_id2
		) );

		return ! empty( $results );
	}


	/**
	 * Get followers as array
	 *
	 * @param int $user_id1
	 * @param array $args
	 * @return array|bool|null|object
	 */
	function followers( $user_id1, $args = array() ) {
		global $wpdb;

		if ( ! empty( $args ) && $args['max'] > 0 ) {
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT user_id2
				FROM {$this->table_name}
				WHERE user_id1 = %d AND
					  user_id2 IN ( SELECT ID FROM {$wpdb->users} )
				ORDER BY time DESC 
				LIMIT %d",
				$user_id1,
				$args['max']
			), ARRAY_A );
		} else {
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT user_id2
				FROM {$this->table_name}
				WHERE user_id1 = %d AND
					  user_id2 IN ( SELECT ID FROM {$wpdb->users} )
				ORDER BY time DESC",
				$user_id1
			), ARRAY_A );
		}

		return ! empty( $results ) ? $results : false;
	}


	/**
	 * Get following as array
	 *
	 * @param int $user_id2
	 * @param array $args
	 * @return array|bool|null|object
	 */
	function following( $user_id2, $args = array() ) {
		global $wpdb;

		if ( ! empty( $args ) && $args['max'] > 0 ) {
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT user_id1
				FROM {$this->table_name}
				WHERE user_id2 = %d AND
					  user_id1 IN ( SELECT ID FROM {$wpdb->users} )
				ORDER BY time DESC 
				LIMIT %d",
				$user_id2,
				$args['max']
			), ARRAY_A );
		} else {
			$results = $wpdb->get_results( $wpdb->prepare(
				"SELECT user_id1
				FROM {$this->table_name}
				WHERE user_id2 = %d AND
					  user_id1 IN ( SELECT ID FROM {$wpdb->users} )
				ORDER BY time DESC",
				$user_id2
			), ARRAY_A );
		}

		return ! empty( $results ) ? $results : false;
	}


	/**
	 * Ajax handler on click Follow button
	 */
	function ajax_followers_follow() {
		UM()->check_ajax_nonce();

		/**
		 * @var $user_id
		 */
		extract( $_POST );

		$user_id2 = get_current_user_id();

		if ( ! isset( $user_id ) || ! is_numeric( $user_id ) ) {
			wp_send_json_error();
		}

		if ( ! $this->can_follow( $user_id, $user_id2 ) || $this->followed( $user_id, $user_id2 ) ) {
			wp_send_json_error();
		}

		$this->add( $user_id, $user_id2 );

		$output = array();
		$output['btn'] = $this->follow_button( $user_id, $user_id2 ); // following user id , current user id
		$output['count'] = $this->count_followers_plain( $user_id );

		do_action( 'um_followers_after_user_follow', $user_id, $user_id2 );

		wp_send_json_success( $output );
	}


	/**
	 * Ajax handler on click UnFollow button
	 */
	function ajax_followers_unfollow() {
		UM()->check_ajax_nonce();

		/**
		 * @var $user_id
		 */
		extract( $_POST );

		$user_id2 = get_current_user_id();

		if ( ! isset( $user_id ) || ! is_numeric( $user_id ) ) {
			wp_send_json_error();
		}

		if ( ! $this->can_follow( $user_id, $user_id2 ) || ! $this->followed( $user_id, $user_id2 ) ) {
			wp_send_json_error();
		}

		$this->remove( $user_id, $user_id2 );

		$output = array();
		$output['btn'] = $this->follow_button( $user_id, $user_id2 );
		$output['count'] = $this->count_followers_plain( $user_id );

		do_action( 'um_followers_after_user_unfollow', $user_id, $user_id2 );

		wp_send_json_success( $output );
	}


	function ajax_get_users() {
		UM()->check_ajax_nonce();

		if ( empty( $_POST['type'] ) || ! in_array( $_POST['type'], [ 'followers', 'following' ] ) ) {
			wp_send_json_error( __( 'Invalid data type', 'um-followers' ) );
		}

		$type = $_POST['type'];

		if ( empty( $_POST['user_id'] ) || ! is_numeric( $_POST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid data type', 'um-followers' ) );
		}

		$user_id = absint( $_POST['user_id'] );
		if ( ! um_can_view_profile( $user_id ) ) {
			wp_send_json_error( __( 'You aren\'t able to see this information', 'um-followers' ) );
		}


		$args = [];
		if ( ! empty( $_POST['max'] ) && is_numeric( $_POST['max'] ) ) {
			$args['max'] = absint( $_POST['max'] );
		}

		$offset = 0;
		if ( ! empty( $_POST['offset'] ) && is_numeric( $_POST['offset'] ) ) {
			$offset = absint( $_POST['offset'] );
		}
		global $wpdb;

		if ( $type == 'followers' ) {
			$all_count = UM()->Followers_API()->api()->count_followers_plain( $user_id );
			if ( $offset > 0 ) {
				$limit = $all_count - $offset;

				$users = $wpdb->get_results( $wpdb->prepare(
					"SELECT user_id2
					FROM {$this->table_name}
					WHERE user_id1 = %d AND
						  user_id2 IN ( SELECT ID FROM {$wpdb->users} )
					ORDER BY time DESC 
					LIMIT %d, %d",
					$user_id,
					$offset,
					$limit
				), ARRAY_A );
			} else {
				$users = UM()->Followers_API()->api()->followers( $user_id, $args );
			}
		} else {
			$all_count = UM()->Followers_API()->api()->count_following_plain( $user_id );
			if ( $offset > 0 ) {
				$limit = $all_count - $offset;

				$users = $wpdb->get_results( $wpdb->prepare(
					"SELECT user_id1
					FROM {$this->table_name}
					WHERE user_id2 = %d AND
						  user_id1 IN ( SELECT ID FROM {$wpdb->users} )
					ORDER BY time DESC 
					LIMIT %d, %d",
					$user_id,
					$args['max'],
					$limit
				), ARRAY_A );
			} else {
				$users = UM()->Followers_API()->api()->following( $user_id, $args );
			}
		}

		$response = [ 'users' => [], ];
		if ( $users ) {
			$users_data = [];
			foreach ( $users as $user ) {
				$user_id = array_key_exists( 'user_id1', $user ) ? $user['user_id1'] : $user['user_id2'];

				um_fetch_user( $user_id );
				$users_data[] = array(
					'url'    => um_user_profile_url(),
					'avatar' => get_avatar( $user_id, 40 ),
					'name'   => esc_attr( um_user( 'display_name' ) ),
				);
			}

			$response['users'] = $users_data;

			if ( $offset == 0 ) {
				$response['all'] = $all_count - count( $users );
			}
		}

		wp_send_json_success( $response );
	}
}