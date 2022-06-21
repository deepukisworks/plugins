<?php
namespace um_ext\um_social_activity\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Activity_Admin
 * @package um_ext\um_social_activity\core
 */
class Activity_Admin {


	/**
	 * Activity_Admin constructor.
	 */
	function __construct() {
	
		add_action( 'um_extend_admin_menu', array( &$this, 'um_extend_admin_menu' ), 5 );
		
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ), 10 );

		add_action( 'um_load_gutenberg_js', array( &$this, 'enqueue_gutenberg_blocks' ) );

		add_filter( 'views_edit-um_activity', array( &$this, 'views_um_activity' ) );
		
		add_action( 'load-post-new.php', array( &$this, 'prevent_backend_new' ), 9 );

		add_filter( 'parse_query', array( &$this, 'parse_query' ) );

		add_filter( 'manage_edit-um_activity_columns', array( &$this, 'manage_edit_um_activity_columns' ) );
		add_action( 'manage_um_activity_posts_custom_column', array( &$this, 'manage_um_activity_posts_custom_column' ), 10, 3 );

		$this->count_flagged();

		add_filter( 'wp_privacy_personal_data_exporters', array( &$this, 'plugin_register_exporters' ) );
	}


	/**
	 * Register exporter for Plugin GDPR complicity.
	 *
	 * @param $exporters
	 *
	 * @return array
	 */
	function plugin_register_exporters( $exporters ) {
		$exporters['um-social-activity-posts'] = array(
			'exporter_friendly_name' => um_activity_extension,
			'callback'               => array( &$this, 'social_activity_posts_exporter' ),
		);
		return $exporters;
	}


	function social_activity_posts_exporter( $email_address, $page = 1 ) {
		// Limit us to 500 comments at a time to avoid timing out.
		$number = 500;
		$page   = (int) $page;

		$data_to_export = array();
		$activity_posts = array();

		$user = get_user_by( 'email', $email_address );
		if ( $user && ! is_wp_error( $user ) ) {
			$user_id = $user->ID;

			$activity_posts = get_posts( array(
				'post_type'     => 'um_activity',
				'author'        => $user_id,
				'numberposts'   => $number,
				'offset'        => $number * ( $page - 1 ),
				'post_status'   => 'any',
			) );

			$post_prop_to_export = array(
				'ID'                    => __( 'Post ID', 'um-activity' ),
				'post_author'           => __( 'Post Author', 'um-activity' ),
				'post_author_email'     => __( 'Post Author Email', 'um-activity' ),
				'post_date'             => __( 'Post Date', 'um-activity' ),
				'post_title'            => __( 'Post Title', 'um-activity' ),
				'post_content'          => __( 'Post Content', 'um-activity' ),
				'post_link'             => __( 'Post URL', 'um-activity' ),
				'post_attachment'       => __( 'Post Image', 'um-activity' ),
			);
		}

		if ( empty( $activity_posts ) ) {
			$activity_posts = array();
		}

		foreach ( (array) $activity_posts as $post ) {
			$comment_data_to_export = array();

			foreach ( $post_prop_to_export as $key => $name ) {
				$value = '';

				switch ( $key ) {
					case 'post_author':
						$value = $user->display_name;
						break;
					case 'post_author_email':
						$value = $email_address;
						break;
					case 'ID':
					case 'post_date':
					case 'post_title':
						$value = $post->{$key};
						break;
					case 'post_content':
						$value = apply_filters( 'the_content', $post->post_content );
						break;
					case 'post_link':
						$value = UM()->Activity_API()->api()->get_permalink( $post->ID );
						break;
					case 'post_attachment':
						$photo_url = UM()->Activity_API()->api()->get_download_link( $post->ID, $user->ID );
						$value = empty( $photo_url ) ? __( '(none)', 'um-activity' ) : $photo_url;
						break;
				}

				if ( ! empty( $value ) ) {
					$comment_data_to_export[] = array(
						'name'  => $name,
						'value' => $value,
					);
				}
			}

			$data_to_export[] = array(
				'group_id'          => 'um-activity-posts',
				'group_label'       => __( 'Ultimate Member - Social Activity posts' ),
				'group_description' => __( 'User&#8217;s social activity post data.' ),
				'item_id'           => "um-activity-post-{$post->ID}",
				'data'              => $comment_data_to_export,
			);
		}

		$done = count( $activity_posts ) < $number;

		return array(
			'data' => $data_to_export,
			'done' => $done,
		);
	}


	/**
	 *
	 */
	function count_flagged() {
		global $pagenow;

		if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && sanitize_key( $_GET['post_type'] ) == 'um_activity' ) {

			$flags = new \WP_Query(
				array(
					'post_type' => 'um_activity',
					'meta_query' => array(
						array(
							'key' => '_reported',
							'value' => 0,
							'compare' => '>',
						)
					),
					'posts_per_page' => '-1',
				)
			);

			if ( isset( $flags->found_posts ) ) {
				update_option('um_activity_flagged', $flags->found_posts );
			}
		}
	}


	/**
	 * @param $q \WP_Query
	 * @return mixed
	 */
	function parse_query( $q ) {
		global $pagenow;

		if ( $pagenow == 'edit.php' && isset( $_GET['post_type'] ) && sanitize_key( $_GET['post_type'] ) == 'um_activity' ) {

			if ( isset( $_REQUEST['status'] ) && sanitize_key( $_REQUEST['status'] ) == 'flagged' ) {
				$q->set( 'meta_key', '_reported' );
				$q->set( 'meta_value', 0 );
				$q->set( 'meta_compare', '>' );
			}

		}

		// Don't display broken posts without information about a user.
		if ( 'um_activity' === $q->get( 'post_type' ) && empty( $q->get( 'meta_query' ) ) ) {
			$q->set( 'meta_query', array(
				'relation' => 'AND',
				array(
					'key'       => '_user_id',
					'value'     => 0,
					'compare'   => '>',
				),
			) );
		}

		return $q;
	}


	/***
	***	@
	***/
	function prevent_backend_new() {
		global $current_screen;
		if ( $current_screen->id == 'um_activity' ) {
			wp_die( __( 'This can be done from the frontend only.', 'um-activity' ) );
		}
	}


	/**
	 * Add flagged filter on Social Activity posts list table
	 * @param $views
	 *
	 * @return mixed
	 */
	function views_um_activity( $views ) {
		$array['flagged'] = __( 'Flagged', 'um-activity' );
		
		foreach ( $array as $view => $name ) {
			if ( isset( $_REQUEST['status'] ) && sanitize_key( $_REQUEST['status'] ) == $view || ( ! isset( $_REQUEST['status'] ) && $view == 'all' ) ) {
				$class = 'current';
			} else {
				$class = '';
			}
			$count = (int) get_option( "um_activity_{$view}" );
			$views[ $view ] = '<a href="?post_type=um_activity&status=' . esc_attr( $view ) . '" class="' . esc_attr( $class ) . '">' . $name . ' <span class="count">(' . $count . ')</span></a>';
		}
		
		return $views;
	}


	/**
	 * Add admin menu
	 */
	function um_extend_admin_menu() {
		$t_count = (int) get_option( 'um_activity_flagged' );
		
		$count = '<span class="awaiting-mod update-plugins count-' . esc_attr( $t_count ) . '"><span class="processing-count">' . number_format_i18n( $t_count ) . '</span></span>';
		
		add_submenu_page( 'ultimatemember', __( 'Social Activity', 'um-activity' ), sprintf( __( 'Social Activity %s', 'um-activity' ), $count ), 'manage_options', 'edit.php?post_type=um_activity', '' );
		
		add_submenu_page( 'ultimatemember', __( 'Hashtags', 'um-activity' ), __( 'Hashtags', 'um-activity' ), 'manage_options', 'edit-tags.php?taxonomy=um_hashtag', '' );
	}


	/**
	 *
	 */
	function admin_enqueue_scripts() {
		$screen = get_current_screen();

		if ( ! isset( $screen->id ) ) {
			return;
		}
		if ( ! strstr( $screen->id, 'um_activity' ) ) {
			return;
		}

		wp_register_style( 'um_admin_activity', um_activity_url . 'includes/admin/assets/css/um-admin-activity.css', array(), um_activity_version );
		wp_enqueue_style( 'um_admin_activity' );

		wp_register_script( 'um_admin_activity', um_activity_url . 'includes/admin/assets/js/um-admin-activity.js', array( 'jquery' ), um_activity_version, true );
		wp_enqueue_script( 'um_admin_activity' );
	}


	/**
	 * Enqueue UM Gutenberg blocks
	 */
	function enqueue_gutenberg_blocks() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um_activity_blocks', um_activity_url . 'assets/js/um-blocks' . $suffix . '.js', array( 'wp-i18n', 'wp-blocks', 'wp-components' ), um_activity_version, true );
		wp_set_script_translations( 'um_activity_blocks', 'um-activity' );
		wp_enqueue_script( 'um_activity_blocks' );
	}


	/**
	 * Custom columns
	 *
	 * @param array $columns
	 *
	 * @return array
	 */
	function manage_edit_um_activity_columns( $columns ) {

		unset( $columns['title'] );
		unset( $columns['tags'] );
		unset( $columns['date'] );

		$columns['a_author'] = __( 'Author', 'um-activity' );
		$columns['a_content'] = __( 'Content', 'um-activity' );
		$columns['a_hashtags'] = __( 'Hashtags', 'um-activity' );
		$columns['a_likes'] = __( 'Likes', 'um-activity' );
		$columns['a_comments'] = __( 'Comments', 'um-activity' );
		$columns['a_action'] = __( 'Action', 'um-activity' );

		return $columns;
	}

	/**
	 * Display custom columns
	 *
	 * @param $column_name
	 * @param $id
	 */
	function manage_um_activity_posts_custom_column( $column_name, $id ) {
		switch ( $column_name ) {

			case 'a_hashtags':
				$hashtags = wp_get_post_terms( $id, 'um_hashtag', $args = array( 'orderby' => 'count', 'order' => 'desc', 'fields' => 'all' ) );
				$res = '';
				if ( $hashtags ) {
					foreach( $hashtags as $hashtag ) {
						$res .= '<a target="_blank" href="' . esc_url( add_query_arg( 'hashtag', $hashtag->slug, um_get_core_page( 'activity' ) ) ) . '">#' . $hashtag->name . '</a> (' . $hashtag->count . ')&nbsp;&nbsp;';
					}
				}
				echo $res;
				break;

			case 'a_author':
				$author_id = UM()->Activity_API()->api()->get_author( $id );
				$author_data = get_userdata( $author_id );
				$author_name = is_object( $author_data ) ? $author_data->display_name : '';
				echo '<a href="' . esc_url( um_user_profile_url( $author_id ) ) . '" target="_blank" title="' . esc_attr( $author_name ) . '" class="authorimg">' . get_avatar( $author_id, 32 ) . '</a>';
				break;

			case 'a_content':

				$link = get_post_meta( get_the_ID(), '_shared_link', true );
				$author_id = UM()->Activity_API()->api()->get_author( $id );

				echo '<a href="' . esc_url( get_edit_post_link( $id ) ) . '" class="um-admin-tipsy-s" title="' . esc_attr__( 'Edit', 'um-activity' ) . '"><strong>' . UM()->Activity_API()->api()->get_post_time( $id ) . '</strong></a> - <a href="' . esc_url( UM()->Activity_API()->api()->get_permalink( $id ) ) . '" target="_blank">' . __( 'Permalink', 'um-activity' ) . '</a>';
				echo '<div class="um-admin-activity-c">' . UM()->Activity_API()->api()->get_content() . '</div>';
				echo '<div class="um-admin-activity-ph">' .  UM()->Activity_API()->api()->get_photo( get_the_ID(), 'backend', $author_id ) .'</div>';
				echo '<div class="um-admin-activity-if">' .  UM()->Activity_API()->api()->get_video( get_the_ID(), array( 'width' => 300 ) ) .'</div>';
				if ( $link ) {
					echo '<div class="um-activity-bodyinner-txt"> ' . $link . '</div>';
				}

				if ( UM()->Activity_API()->api()->reported( $id ) ) {
					$clear_report = add_query_arg( 'um_adm_action', 'wall_report' );
					$clear_report = add_query_arg( 'post_id', $id, $clear_report );
					echo '<div class="um-admin-activity-reported">' . sprintf( __( 'This post is flagged by community. <a href="%s">Clear report</a>', 'um-activity' ), esc_url( $clear_report ) ) . '</div>';
				}

				break;

			case 'a_likes':
				echo UM()->Activity_API()->api()->get_likes_number( $id );
				break;

			case 'a_comments':
				echo UM()->Activity_API()->api()->get_comments_number( $id );
				break;

			case 'a_action':
				$author_id = UM()->Activity_API()->api()->get_author( $id );
				$author_data = get_userdata( $author_id );
				$author_name = is_object( $author_data ) ? $author_data->display_name : '';
				echo UM()->Activity_API()->api()->get_action( $id );
				if ( UM()->Activity_API()->api()->get_wall( $id ) && $author_id != UM()->Activity_API()->api()->get_wall( $id ) ) {
					um_fetch_user( UM()->Activity_API()->api()->get_wall( $id ) );
					echo '<div class="um-admin-activity-resp"><i class="um-icon-forward"></i><a href="' . esc_url( um_user_profile_url() ) . '" target="_blank">' . get_avatar(  $author_id, 32 ) . esc_html( $author_name ) . '</a></div>';
				}
				break;

		}

	}

}