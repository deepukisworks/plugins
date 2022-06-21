<?php
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Private_Content
 */
class UM_Private_Content {

	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Private_Content
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_Private_Content_API constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_private_content'] = $this;

		add_filter( 'plugins_loaded', array( &$this, 'init' ) );

		add_filter( 'um_call_object_Private_Content', array( &$this, 'get_this' ) );

		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

		add_action( 'init',  array( &$this, 'create_cpt' ), 2 );

		if ( UM()->is_request( 'admin' ) ) {
			$this->admin_upgrade();
		}

		$this->admin();
		$this->shortcode();

		add_action( 'user_register', array( &$this, 'add_private_content' ), 12, 1 );

		add_action( 'um_delete_user', array( &$this, 'delete_private_content' ), 10, 1 );
	}


	/**
	 * Delete user activities on account deletion
	 *
	 * @param $user_id
	 */
	function delete_private_content( $user_id ) {

		$private_post_id = get_user_meta( $user_id, '_um_private_content_post_id', true );
		if ( ! empty( $private_post_id ) ) {
			wp_delete_post( $private_post_id, true );
		}

	}


	/**
	 * @param $user_id
	 */
	function add_private_content( $user_id ) {
		$post_id = wp_insert_post( array(
			'post_title'    => 'private_content_' . $user_id,
			'post_type'     => 'um_private_content',
			'post_status'   => 'publish',
			'post_content'  => ''
		) );

		update_user_meta( $user_id, '_um_private_content_post_id', $post_id );
	}


	/**
	 * @param $user_id
	 *
	 * @return bool|string|null
	 */
	function get_private_content_post_link( $user_id ) {
		$private_post_id = get_user_meta( $user_id, '_um_private_content_post_id', true );
		$post = get_post( $private_post_id );

		if ( ! empty( $post ) ) {
			return get_edit_post_link( $post->ID );
		} else {
			return false;
		}
	}


	/**
	 * @param $defaults
	 *
	 * @return array
	 */
	function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * Init
	 */
	function init() {
		require_once um_private_content_path . 'includes/core/filters/um-private-content-settings.php';
		require_once um_private_content_path . 'includes/core/filters/um-private-content-tabs.php';
	}


	/**
	 * @return um_ext\um_private_content\core\Private_Content_Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_private_content_setup'] ) ) {
			UM()->classes['um_private_content_setup'] = new um_ext\um_private_content\core\Private_Content_Setup();
		}
		return UM()->classes['um_private_content_setup'];
	}


	/**
	 * @return um_ext\um_private_content\core\Private_Content_Shortcode()
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_private_content_shortcode'] ) ) {
			UM()->classes['um_private_content_shortcode'] = new um_ext\um_private_content\core\Private_Content_Shortcode();
		}
		return UM()->classes['um_private_content_shortcode'];
	}


	/**
	 * @return um_ext\um_private_content\core\Private_Content_Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_private_content_admin'] ) ) {
			UM()->classes['um_private_content_admin'] = new um_ext\um_private_content\core\Private_Content_Admin();
		}
		return UM()->classes['um_private_content_admin'];
	}


	/**
	 * @return um_ext\um_private_content\admin\core\Admin_Upgrade()
	 */
	function admin_upgrade() {
		if ( empty( UM()->classes['um_private_content_admin_upgrade'] ) ) {
			UM()->classes['um_private_content_admin_upgrade'] = new um_ext\um_private_content\admin\core\Admin_Upgrade();
		}
		return UM()->classes['um_private_content_admin_upgrade'];
	}


	/**
	 * creates needed cpt
	 */
	function create_cpt() {

		register_post_type( 'um_private_content', array(
			'labels'            => array(
				'name'                  => __( 'Private Contents', 'um-private-content' ),
				'singular_name'         => __( 'Private Content', 'um-private-content' ),
				'add_new'               => __( 'Add New Private Content', 'um-private-content' ),
				'add_new_item'          => __( 'Add New Private Content', 'um-private-content' ),
				'edit_item'             => __( 'Edit Private Content', 'um-private-content' ),
				'not_found'             => __( 'You did not create any private contents yet', 'um-private-content' ),
				'not_found_in_trash'    => __( 'Nothing found in Trash', 'um-private-content' ),
				'search_items'          => __( 'Search Private Contents', 'um-private-content' ),
			),
			'show_ui'           => true,
			'show_in_menu'      => false,
			'public'            => false,
			'supports'          => array( 'editor' ),
			'capability_type'   => 'page',
		) );

	}

}

//create class var
add_action( 'plugins_loaded', 'um_init_private_content', -10, 1 );
function um_init_private_content() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Private_Content', true );
	}
}