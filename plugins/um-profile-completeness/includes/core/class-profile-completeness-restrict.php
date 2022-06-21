<?php
namespace um_ext\um_profile_completeness\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile_Completeness_Restrict
 * @package um_ext\um_profile_completeness\core
 */
class Profile_Completeness_Restrict {


	/**
	 * Profile_Completeness_Restrict constructor.
	 */
	function __construct() {
		add_action( 'template_redirect', array( &$this, 'template_redirect' ), 999999999 );

		add_action( 'wp_insert_comment', array( &$this, 'wp_insert_comment' ), 9999999 );

		add_filter( 'um_groups_can_post_on_wall', array( &$this, 'prevent_group_post' ), 9999999, 1 );
		add_filter( 'um_groups_can_post_comment_on_wall', array( &$this, 'prevent_group_post' ), 9999999, 1 );
		add_filter( 'um_groups_can_post_on_wall_restrict_text', array( &$this, 'prevent_group_post_restrict_text' ), 9999999, 1 );

		add_filter( 'bbp_new_topic_pre_extras', array( &$this, 'forum_restrict' ), 9999999 );
		add_filter( 'bbp_new_reply_pre_extras', array( &$this, 'forum_restrict' ), 9999999 );


		add_filter( 'fmwp_user_can_create_topic', array( &$this, 'can_create_topic' ), 9999999, 3 );
		add_filter( 'fmwp_user_can_create_reply', array( &$this, 'can_create_reply' ), 9999999, 3 );
		add_filter( 'fmwp_create_topic_disabled_text', array( &$this, 'topic_disabled_text' ), 9999999, 2 );
		add_filter( 'fmwp_reply_disabled_reply_text', array( &$this, 'disabled_reply_text' ), 9999999, 2 );

		add_filter( 'um_profile_completeness_restrict_page', array( &$this, 'prevent_browse_exclude_pages' ), 20 );
	}


	/**
	 * ACCESS / PROFILES
	 */
	function template_redirect() {
		if ( ! is_user_logged_in() || is_admin() || wp_doing_ajax() ) {
			return;
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );
		if( ! is_array( $result ) ){
			return;
		}
		
		$exclude_page = apply_filters( 'um_profile_completeness_restrict_page', um_is_core_page( 'account' ) );

		if ( $result['req_progress'] > $result['progress'] ) {
			// Global
			if ( $result['prevent_browse'] && ! isset( $_REQUEST['um_action'] ) && ! $exclude_page ) {
				if ( empty( $result['prevent_browse_redirect'] ) || empty( $result['prevent_browse_redirect_url'] ) ) {
					$redirect = add_query_arg( 'notice', 'incomplete_access', um_get_core_page( 'user' ) );
					$redirect = add_query_arg( 'um_action', 'edit', $redirect );
					$redirect = add_query_arg( 'profiletab', 'main', $redirect );
				} else {
					$redirect = $result['prevent_browse_redirect_url'];
				}

				exit( wp_redirect( $redirect ) );
			}

			// Profile view
			if ( $result['prevent_profileview'] ) {
				if ( um_get_requested_user() && um_get_requested_user() != get_current_user_id() ) {
					$edit_profile_url = add_query_arg( 'notice', 'incomplete_view', um_get_core_page( 'user' ) );
					$edit_profile_url = add_query_arg( 'um_action', 'edit', $edit_profile_url );
					$edit_profile_url = add_query_arg( 'profiletab', 'main', $edit_profile_url );

					exit( wp_redirect( $edit_profile_url ) );
				}
			}

		}
	}


	/**
	 * Filter: Show pages, that don't depends on "Require profile to be complete to browse the site" option.
	 * @hook um_profile_completeness_restrict_page
	 * @global \WP_Post $post
	 * @global \SitePress $sitepress
	 * @param boolean $exclude_page
	 * @return boolean
	 */
	public function prevent_browse_exclude_pages( $exclude_page ) {
		global $post;

		if ( um_is_myprofile() ) {
			return true;
		}

		if ( ! empty( $post ) ) {
			$post_id = isset( $post->ID ) ? $post->ID : 0;

			$progress = UM()->Profile_Completeness_API()->get_progress( get_current_user_id() );

			if ( is_array( $progress ) ) {
				$exclude_pages = array();
				if ( ! empty( $progress['prevent_browse_exclude_pages'] ) ) {
					$exclude_pages = array_map( 'trim', explode( ',', $progress['prevent_browse_exclude_pages'] ) );
				}

				if ( ! empty( $progress['prevent_browse_redirect'] ) && ! empty( $progress['prevent_browse_redirect_url'] ) ) {
					//exclude if redirect URL is home URL
					if ( is_home() && get_home_url( get_current_blog_id() ) == $progress['prevent_browse_redirect_url'] ) {
						$exclude_page = true;
					}

					//exclude page, which is found via url_to_post_id
					$exclude_redirect = url_to_postid( $progress['prevent_browse_redirect_url'] );
					if ( ! empty( $exclude_redirect ) ) {
						$exclude_pages[] = $exclude_redirect;
						$exclude_pages = array_unique( $exclude_pages );
					}
				}

				if ( UM()->external_integrations()->is_wpml_active() ) {
					global $sitepress;
					$post_id = wpml_object_id_filter( $post_id, 'page', true, $sitepress->get_default_language() );
				}

				if ( in_array( $post_id, $exclude_pages ) ) {
					$exclude_page = true;
				}
			}
		}

		return $exclude_page;
	}


	/**
	 * COMMENTS
	 *
	 * @param $cid
	 */
	function wp_insert_comment( $cid ) {
		if ( ! is_user_logged_in() || is_admin() || wp_doing_ajax() ) {
			return;
		}

		$comment = get_comment( $cid );
		if ( get_post_type( $comment->comment_post_ID ) == "um_groups_discussion" ) {
			return;
		}

		//shouldn't affect group discussion comment
		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );
		if ( $result['progress'] < $result['req_progress'] ) {

			if ( $result['prevent_comment'] ) {
				wp_delete_comment( $cid, true );
				exit( wp_redirect( add_query_arg( 'notice', 'incomplete_comment', um_edit_profile_url() ) ) );
			}
		}
	}


	/**
	 * Restrict UM:Groups posting on group wall
	 *
	 * @return bool
	 */
	function prevent_group_post( $res ) {
		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );

		if ( is_array( $result ) && $result['progress'] < $result['req_progress'] && $result['prevent_group_post'] ) {
			return false;
		}
		return $res;
	}

	/**
	 * Restrict UM:Groups posting on group wall restrict text
	 * @param $text
	 *
	 * @return string
	 */
	function prevent_group_post_restrict_text( $text ) {
		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );
		if ( is_array( $result ) && $result['progress'] < $result['req_progress'] && $result['prevent_group_post'] ) {
			$text = esc_html__( 'Please complete your profile to post and comment.', 'um-profile-completeness' );
		}
		
		return $text;
	}

	/**
	 * bbPress
	 *
	 * @param $forum_id
	 */
	function forum_restrict( $forum_id ) {
		if ( ! is_user_logged_in() || is_admin() || wp_doing_ajax() ) {
			return;
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );
		if ( $result['progress'] < $result['req_progress'] ) {
			if ( $result['prevent_bb'] ) {
				exit( wp_redirect( add_query_arg( 'notice', 'incomplete_forum', um_edit_profile_url() ) ) );
			}
		}
	}


	function can_create_topic( $can_create, $user_id, $forum_id ) {
		if ( ! $can_create ) {
			return $can_create;
		}

		if ( ! is_user_logged_in() || is_admin() || wp_doing_ajax() ) {
			return $can_create;
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $user_id );
		if ( $result['progress'] < $result['req_progress'] ) {
			if ( $result['prevent_forumwp'] ) {
				$can_create = false;
			}
		}

		return $can_create;
	}


	/**
	 * Change user permissions to create reply
	 *
	 * @param $can_create
	 * @param $user_id
	 * @param $topic_id
	 *
	 * @return bool
	 */
	function can_create_reply( $can_create, $user_id, $topic_id ) {
		if ( ! $can_create ) {
			return $can_create;
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $user_id );
		if ( $result['progress'] < $result['req_progress'] ) {
			if ( $result['prevent_forumwp'] ) {
				$can_create = false;
			}
		}

		return $can_create;
	}

	function topic_disabled_text( $text, $forum_id ){
		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );
		if ( $result['progress'] < $result['req_progress'] ) {
			if ( $result['prevent_forumwp'] ) {
				$text = esc_html__( 'Please complete your profile to create a topic.', 'um-profile-completeness' );
			}
		}

		return $text;
	}

	function disabled_reply_text( $text, $forum_id ){
		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );
		if ( $result['progress'] < $result['req_progress'] ) {
			if ( $result['prevent_forumwp'] ) {
				$text = esc_html__( 'Please complete your profile to reply.', 'um-profile-completeness' );
			}
		}

		return $text;
	}

}