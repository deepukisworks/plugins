<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Admin
 */
class Admin {

	/**
	 * Admin constructor.
	 */
	function __construct() {
		add_filter( 'um_settings_structure', array( &$this, 'extends_settings' ), 10, 1 );
		add_filter( 'um_change_settings_before_save', array( &$this, 'settings_before_save' ) );
		add_filter( 'um_admin_role_metaboxes', array( &$this, 'add_role_metabox' ), 10, 1 );
	}


	/**
	 * Extend settings
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	function extends_settings( $settings ) {

		$settings['licenses']['fields'][] = array(
			'id'        => 'um_mycred_license_key',
			'label'     => __( 'myCRED License Key', 'um-mycred' ),
			'item_name' => 'myCRED',
			'author'    => 'Ultimate Member',
			'version'   => um_mycred_version,
		);

		$get_all_point_types = mycred_get_types();

		$fields = array(
			array(
				'id'        => 'account_tab_points',
				'type'      => 'checkbox',
				'label'     => __( 'Account Tab', 'um-mycred' ),
				'tooltip'   => __( 'Show or hide an account tab that shows the user balance', 'um-mycred' ),
			),
			array(
				'id'            => 'mycred_refer',
				'type'          => 'checkbox',
				'label'         => __( 'Show user affiliate link in account page', 'um-mycred' ),
				'conditional'   => array( 'account_tab_points', '=', 1 ),
			),
			array(
				'id'            => 'mycred_point_types',
				'type'          => 'multi_checkbox',
				'label'         => __( 'Choose point types to show in account page', 'um-mycred' ),
				'options'       => $get_all_point_types,
				'conditional'   => array( 'account_tab_points', '=', 1 ),
			),
		);


		if ( function_exists( 'mycred_get_users_badges' ) ) {
			$fields = array_merge( $fields, array(
				array(
					'id'        => 'mycred_badge_size',
					'type'      => 'text',
					'validate'  => 'numeric',
					'label'     => __( 'Width / height of badge in pixels', 'um-mycred' ),
					'tooltip'   => __( 'Badges appearing in profile tab', 'um-mycred' ),
					'size'      => 'small',
				),
				array(
					'id'        => 'mycred_show_badges_in_header',
					'type'      => 'checkbox',
					'label'     => __( 'Show user badges in profile header?', 'um-mycred' ),
				),
				array(
					'id'        => 'mycred_show_badges_in_members',
					'type'      => 'checkbox',
					'label'     => __( 'Show user badges in Member Directories?', 'um-mycred' ),
				),
			) );
		}


		$key = ! empty( $settings['extensions']['sections'] ) ? 'mycred' : '';
		$settings['extensions']['sections'][ $key ] = array(
			'title'     => __( 'myCRED', 'um-mycred' ),
			'fields'    => $fields
		);

		$settings = apply_filters( 'um_mycred_settings_extend', $settings, $key );
		return $settings;
	}


	/**
	 * Prepare data of the option "Chose points to show in account page"
	 *
	 * @param  array $settings
	 * @return array
	 */
	function settings_before_save( $settings ) {
		if ( isset( $settings['account_tab_points'] ) ) {
			if (isset($settings['mycred_point_types'])) {
				$settings['mycred_point_types'] = array_keys($settings['mycred_point_types']);
			} else {
				$settings['mycred_point_types'] = array();
			}
		}

		return $settings;
	}


	/**
	 * Creates options in Role page
	 *
	 * @param $roles_metaboxes
	 *
	 * @return array
	 */
	function add_role_metabox( $roles_metaboxes ) {

		$roles_metaboxes[] = array(
			'id'        => "um-admin-form-mycred{" . um_mycred_path . "}",
			'title'     => __( 'myCRED', 'um-mycred' ),
			'callback'  => array( UM()->metabox(), 'load_metabox_role' ),
			'screen'    => 'um_role_meta',
			'context'   => 'normal',
			'priority'  => 'default'
		);

		return $roles_metaboxes;
	}
}