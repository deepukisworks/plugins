<?php
namespace um_ext\um_user_bookmarks\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Bookmark_Ajax
 * @package um_ext\um_user_bookmarks\core
 */

class Bookmark_Ajax {


	/**
	 * Bookmark_Ajax constructor.
	 */
	function __construct() {
		add_action( 'wp_ajax_um_bookmarks_modal_content', array( $this, 'load_modal_content' ) );
		add_action( 'wp_ajax_um_bookmarks_add', array( $this, 'add_bookmark' ) );
		add_action( 'wp_ajax_um_bookmarks_remove', array( $this, 'remove_bookmark' ) );
		add_action( 'wp_ajax_um_bookmarks_folder_add', array( $this, 'add_folder' ) );
		add_action( 'wp_ajax_um_bookmarks_view_folder', array( $this, 'view_folder' ) );
		add_action( 'wp_ajax_nopriv_um_bookmarks_view_folder', array( $this, 'view_folder' ) );
		add_action( 'wp_ajax_um_bookmarks_view_edit_folder', array( $this, 'view_edit_folder' ) );
		add_action( 'wp_ajax_um_bookmarks_delete_folder', array( $this, 'delete_folder' ) );

		add_action( 'wp_ajax_um_bookmarks_get_folder_view', array( $this, 'get_folders_list' ) );
		add_action( 'wp_ajax_nopriv_um_bookmarks_get_folder_view', array( $this, 'get_folders_list' ) );

		add_action( 'wp_ajax_um_bookmarks_update_folder', array( $this, 'update_folder' ) );
	}


	/**
	 * Returns folder option for new bookmark
	 */
	function load_modal_content() {
		$user_id = get_current_user_id();
		$post_id = absint( $_REQUEST['bookmark_post'] );
		$bookmarks = get_user_meta( $user_id, '_um_user_bookmarks' , true );

		$content = UM()->get_template( 'select-folder.php', um_user_bookmarks_plugin, array(
			'bookmarks' => $bookmarks,
			'post_id'   => $post_id
		) );

		wp_send_json_success( $content );
	}


	/**
	 * Add New bookmark
	 *
	 */
	function add_bookmark() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'] , 'um_user_bookmarks_new_bookmark' ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
		}
		if ( empty( $_POST['post_id'] ) || empty( $_POST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid data', 'um-user-bookmarks' ) );
		}

		// sanitize input data
		$folder_name = isset( $_POST['_um_user_bookmarks_folder'] ) ? sanitize_text_field( $_POST['_um_user_bookmarks_folder'] ) : '';
		$is_new = ! empty( $_POST['is_new'] );
		$is_private = ! empty( $_POST['is_private'] );
		$post_id = absint( $_POST['post_id'] );
		$user_id = absint( $_POST['user_id'] );

		$bookmark = [];
		$previous_bookmark = get_user_meta( $user_id, '_um_user_bookmarks', true );
		if ( ! empty( $previous_bookmark ) && is_array( $previous_bookmark ) ) {
			$bookmark = $previous_bookmark;
		}

		if ( $is_new ) {
			if ( empty( $folder_name ) ) {
				wp_send_json_error( sprintf( __( '%s name is required', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
			}

			$folder_slug = sanitize_title( $folder_name );
			if ( isset( $bookmark[ $folder_slug ] ) ) {
				wp_send_json_error( sprintf( __( '%s is already exists', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
			}

			$access = $is_private ? 'private' : 'public';

			$bookmark[ $folder_slug ] = array(
				'title'     => $folder_name,
				'type'      => $access,
				'bookmarks' => array(
					$post_id => array(
						'url'   => get_the_permalink( $post_id )
					)
				),
			);

		} else {

			$folder_slug = sanitize_title( $folder_name );
			if ( ! isset( $bookmark[ $folder_slug ] ) ) {
				wp_send_json_error( sprintf( __( '%s isn\'t exists', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
			}

			$bookmark[ $folder_slug ]['bookmarks'][ $post_id ] = array(
				'url'   => get_the_permalink( $post_id )
			);
		}

		$bookmark = apply_filters( 'um_user_bookmarks_data', $bookmark );

		if ( $bookmark && is_array( $bookmark ) ) {
			update_user_meta( $user_id , '_um_user_bookmarks' , $bookmark );

			$post_users = array();

			$old_post_users = get_post_meta( $post_id, '_um_in_users_bookmarks', true );
			if ( ! empty( $old_post_users ) && is_array( $old_post_users ) ) {
				$post_users = $old_post_users;
			}

			if ( ! in_array( $user_id, $post_users ) ) {
				$post_users[] = $user_id;
			}

			update_post_meta( $post_id, '_um_in_users_bookmarks', $post_users );

			wp_send_json_success( trim( UM()->User_Bookmarks()->get_button( 'remove', $post_id ) ) );
		}

		wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
	}


	/**
	 * Remove post from user bookmarks
	 */
	function remove_bookmark() {
		$post_id = intval( $_REQUEST['bookmark_post'] );

		if ( ! wp_verify_nonce( $_POST['_nonce'],'um_user_bookmarks_remove_' . $post_id ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
		}

		$user_id = get_current_user_id();

		do_action( 'um_before_bookmark_remove', $post_id );

		if ( ! UM()->User_Bookmarks()->is_bookmarked( $user_id, $post_id ) ) {
			wp_send_json_error( __( 'Not found in bookmarks', 'um-user-bookmarks' ) );
		}
		$user_bookmarks = get_user_meta( $user_id, '_um_user_bookmarks' , true );

		foreach ( $user_bookmarks as $key => $value ) {
			if ( ! empty( $value['bookmarks'][ $post_id ] ) ) {
				unset( $user_bookmarks[ $key ]['bookmarks'][ $post_id ] );
				break;
			}
		}

		update_user_meta( $user_id, '_um_user_bookmarks', $user_bookmarks );

		$post_users = get_post_meta( $post_id, '_um_in_users_bookmarks', true );
		$post_users = empty( $post_users ) ? array() : $post_users;

		if ( $k = array_search( $user_id, $post_users ) ) {
			unset( $post_users[ $k ] );
			$post_users = array_unique( $post_users );
		}

		update_post_meta( $post_id, '_um_in_users_bookmarks', $post_users );

		do_action( 'um_after_bookmark_removed', $post_id );

		if ( ! empty( $_REQUEST['return_button'] ) ) {
			wp_send_json_success( trim( UM()->User_Bookmarks()->get_button( 'add', $post_id ) ) );
		}

		wp_send_json_success();
	}


	/**
	 * Add New bookmark folder
	 */
	function add_folder() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'um_user_bookmarks_new_bookmark_folder' ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
		}
		if ( empty( $_POST['user_id'] ) ) {
			wp_send_json_error( __( 'Invalid data', 'um-user-bookmarks' ) );
		}

		// sanitize input data
		$folder_name = isset( $_POST['_um_user_bookmarks_folder'] ) ? sanitize_text_field( $_POST['_um_user_bookmarks_folder'] ) : '';
		$is_private = ! empty( $_POST['is_private'] );
		$user_id = absint( $_POST['user_id'] );

		if ( empty( $folder_name ) ) {
			wp_send_json_error( sprintf( __( '%s name is required', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
		}

		$bookmark = array();
		if ( $previous_bookmark = get_user_meta( $user_id, '_um_user_bookmarks', true ) ) {
			$bookmark = $previous_bookmark;
		}

		$folder_slug = sanitize_title( $folder_name );
		if ( isset( $bookmark[ $folder_slug ] ) ) {
			wp_send_json_error( sprintf( __( '%s is already exists', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
		}

		$access = $is_private ? 'private' : 'public';

		$bookmark[ $folder_slug ] = array(
			'title'     => $folder_name,
			'type'      => $access,
			'bookmarks' => array(),
		);

		$bookmark = apply_filters( 'um_user_bookmarks_data' , $bookmark );

		if ( $bookmark ) {
			update_user_meta( $user_id, '_um_user_bookmarks', $bookmark );
			wp_send_json_success();
		}

		wp_send_json_error( sprintf( __( 'Invalid %s', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
	}


	/**
	 * View Folder
	 *
	 */
	function view_folder() {
		if ( empty( $_POST['key'] ) || empty( $_POST['profile_id'] ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
		}

		// sanitize input data
		$data['key'] = sanitize_key( $_POST['key'] );
		$data['user'] = absint( $_POST['profile_id'] );

		$bookmarks = get_user_meta( $data['user'], '_um_user_bookmarks', true );
		if ( ! isset( $bookmarks[ $data['key'] ] ) ) {
			wp_send_json_error( sprintf( __( '%s doesn\'t exists', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
		}

		$data['title'] = $bookmarks[ $data['key'] ]['title'];

		wp_send_json_success( UM()->get_template( 'profile/single-folder.php', um_user_bookmarks_plugin, $data ) );
	}


	/**
	 * View Edit Folder form
	 *
	 */
	function view_edit_folder() {
		if ( ! isset( $_POST['key'] ) || ! isset( $_POST['user'] ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
		}

		$key = sanitize_key( $_POST['key'] );
		$user = absint( $_POST['user'] );

		$bookmarks = get_user_meta( $user, '_um_user_bookmarks',true );

		if ( ! isset( $bookmarks[ $key ] ) ) {
			wp_send_json_error( sprintf( __( '%s doesn\'t exists', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
		}

		$folder = $bookmarks[ $key ];
		$folder['title'] = sanitize_text_field( $folder['title'] );

		wp_send_json_success( UM()->get_template( 'profile/edit-folder.php', um_user_bookmarks_plugin, array(
			'key'       => $key,
			'user'      => $user,
			'private'   => ( $folder['type'] == 'private' ) ? true : false,
			'folder'    => $folder,
		) ) );
	}


	/**
	 * Delete bookmark folder & its data
	 *
	 */
	function delete_folder() {
		if ( ! wp_verify_nonce( $_POST['_nonce'], 'um_user_bookmarks_folder_delete' ) ) {
			wp_send_json_error( __( 'Invalid request.', 'um-user-bookmarks' ) );
		}
		if ( empty( $_POST['key'] ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
		}

		// sanitize input data
		$key = sanitize_key( $_POST['key'] );

		do_action( 'um_user_bookmarks_before_folder_delete', $key );

		$user_id = get_current_user_id();
		$bookmarks = get_user_meta( $user_id , '_um_user_bookmarks', true );
		if ( empty( $bookmarks ) || ! isset( $bookmarks[ $key ] ) ) {
			wp_send_json_error( sprintf( __( '%s doesn\'t exists', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
		}

		if ( ! empty( $bookmarks[ $key ]['bookmarks'] ) ) {
			foreach ( array_keys( $bookmarks[ $key ]['bookmarks'] ) as $post_id ) {
				$post_users = get_post_meta( $post_id, '_um_in_users_bookmarks', true );
				$post_users = empty( $post_users ) ? array() : $post_users;

				if ( $k = array_search( $user_id, $post_users ) ) {
					unset( $post_users[ $k ] );
					$post_users = array_unique( $post_users );
				}

				update_post_meta( $post_id, '_um_in_users_bookmarks', $post_users );
			}
		}

		unset( $bookmarks[ $key ] );

		update_user_meta( $user_id, '_um_user_bookmarks', $bookmarks );

		do_action( 'um_user_bookmarks_after_folder_delete', $key );

		wp_send_json_success();
	}


	/**
	 * Returns folder view
	 *
	 */
	function get_folders_list() {
		if ( ! wp_verify_nonce( $_POST['_nonce'], 'um_user_bookmarks_back' ) ) {
			wp_send_json_error( __( 'Invalid request.', 'um-user-bookmarks' ) );
		}

		if ( ! isset( $_POST['profile_id'] ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
		}

		$profile_id = intval( $_POST['profile_id'] );

		wp_send_json_success( UM()->User_Bookmarks()->profile()->get_user_profile_bookmarks( $profile_id ) );
	}


	/**
	 * Update bookmark folder details
	 *
	 */
	function update_folder() {
		if ( ! wp_verify_nonce( $_POST['_wpnonce'], 'um_user_bookmarks_update_folder' ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
		}

		// sanitize input data
		$folder_slug = isset( $_POST['folder_key'] ) ? sanitize_title( $_POST['folder_key'] ) : '';
		$folder_name = isset( $_POST['folder_title'] ) ? sanitize_text_field( $_POST['folder_title'] ) : '';
		$is_private = ! empty( $_POST['is_private'] );

		if ( empty( $folder_slug ) ) {
			wp_send_json_error( __( 'Invalid request', 'um-user-bookmarks' ) );
		}
		if ( empty( $folder_name ) ) {
			wp_send_json_error( sprintf( __( '%s name is required', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
		}
		$new_folder_slug = sanitize_title( $folder_name );

		$user_id = get_current_user_id();
		$user_bookmarks = get_user_meta( $user_id , '_um_user_bookmarks', true );

		if ( ! isset( $user_bookmarks[ $folder_slug ] ) ) {
			wp_send_json_error( sprintf( __( '%s doesn\'t exists', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
		}
		if ( $new_folder_slug != $folder_slug && isset( $user_bookmarks[ $new_folder_slug ] ) ) {
			wp_send_json_error( sprintf( __( '%s is already exists', 'um-user-bookmarks' ), UM()->User_Bookmarks()->get_folder_text() ) );
		}

		$type = $is_private ? 'private' : 'public';

		do_action( 'um_user_bookmarks_before_folder_update', $folder_slug );

		if ( $new_folder_slug != $folder_slug ) {
			$user_bookmarks[ $new_folder_slug ] = $user_bookmarks[ $folder_slug ];
			$user_bookmarks[ $new_folder_slug ]['title'] = $folder_name;
			$user_bookmarks[ $new_folder_slug ]['type'] = $type;
			unset( $user_bookmarks[ $folder_slug ] );
		} else {
			$user_bookmarks[ $folder_slug ]['title'] = $folder_name;
			$user_bookmarks[ $folder_slug ]['type'] = $type;
		}

		update_user_meta( $user_id, '_um_user_bookmarks', $user_bookmarks );

		do_action( 'um_user_bookmarks_after_folder_update', $folder_slug );

		wp_send_json_success( array( 'slug' => $new_folder_slug ) );
	}
}