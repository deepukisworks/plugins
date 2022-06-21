<?php
namespace um_ext\um_user_tags\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Tags_Admin
 * @package um_ext\um_user_tags\core
 */
class User_Tags_Admin {


	/**
	 * User_Tags_Admin constructor.
	 */
	function __construct() {
		add_action( 'um_extend_admin_menu', array( &$this, 'um_extend_admin_menu' ), 5 );

		add_filter( 'um_settings_structure', array( &$this, 'extend_settings' ), 10, 1 );

		add_filter( 'um_admin_role_metaboxes', array( &$this, 'add_role_metabox' ), 10, 1 );
	}


	/**
	 * Creates options in Role page
	 *
	 * @param array $roles_metaboxes
	 *
	 * @return array
	 */
	function add_role_metabox( $roles_metaboxes ) {

		$roles_metaboxes[] = array(
			'id'        => "um-admin-form-user-tags{" . um_user_tags_path . "}",
			'title'     => __( 'User Tags', 'um-user-tags' ),
			'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
			'screen'    => 'um_role_meta',
			'context'   => 'normal',
			'priority'  => 'default'
		);

		return $roles_metaboxes;
	}


	/**
	 * Add User Tags submenu
	 */
	function um_extend_admin_menu() {
		add_submenu_page( 'ultimatemember', __( 'User Tags', 'um-user-tags' ), __( 'User Tags', 'um-user-tags' ), 'manage_options', 'edit-tags.php?taxonomy=um_user_tag', '' );
	}


	/**
	 * Extend settings
	 *
	 * @param array $settings
	 *
	 * @return mixed
	 */
	function extend_settings( $settings ) {
		$settings['licenses']['fields'][] = array(
			'id'        => 'um_user_tags_license_key',
			'label'     => __( 'User Tags License Key', 'um-user-tags' ),
			'item_name' => 'User Tags',
			'author'    => 'Ultimate Member',
			'version'   => um_user_tags_version,
		);


		$forms_query = new \WP_Query;
		$member_directories = $forms_query->query( array(
			'post_type'         => 'um_directory',
			'posts_per_page'    => -1,
			'fields'            => array( 'ID', 'post_title' ),
		) );

		$directories = array(
			''  => __( '(None)', 'um-user-tags' ),
		);
		if ( ! empty( $member_directories ) && ! is_wp_error( $member_directories ) ) {
			foreach ( $member_directories as $directory ) {
				$directories[ $directory->ID ] = $directory->post_title;
			}
		}


		$fields = array(
			array(
				'id'          => 'user_tags_slug',
				'type'        => 'text',
				'label'       => __( 'User tag slug', 'um-user-tags' ),
				'tooltip'     => __( 'Base permalink for user tag', 'um-user-tags' ),
				'description' => __( 'Once this setting is changed you should update WordPress rewrite rules. Go to the page [Settings > Permalinks] and click the "Save Changes" button.', 'um-user-tags' ),
				'size'        => 'small'
			),
			array(
				'id'       => 'user_tags_max_num',
				'type'     => 'text',
				'label'    => __( 'Maximum number of tags to display in user profile', 'um-user-tags' ),
				'tooltip'  => __( 'Remaining tags will appear by clicking on a link', 'um-user-tags'),
				'validate' => 'numeric',
				'size'     => 'small'
			),
		);

		if ( UM()->options()->get( 'members_page' ) ) {
			$fields[] = array(
				'id'        => 'user_tags_base_directory',
				'type'      => 'select',
				'label'     => __( 'Base member directory', 'um-user-tags' ),
				'tooltip'   => __( 'Select base member directory to use its settings for displaying users with this tag', 'um-user-tags' ),
				'options'   => $directories,
				'size'      => 'small',
			);
		}


		$key = ! empty( $settings['extensions']['sections'] ) ? 'user_tags' : '';
		$settings['extensions']['sections'][ $key ] = array(
			'title'     => __( 'User Tags', 'um-user-tags' ),
			'fields'    => $fields,
		);

		return $settings;
	}
}