<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Fields
 */
class Fields {


	/**
	 * Fields constructor.
	 */
	function __construct() {
		add_filter( 'um_core_fields_hook', array( &$this, 'add_balance_field' ) );
		add_filter( 'um_predefined_fields_hook', array( &$this, 'add_predefined_fields' ), 10 );
		add_action( 'um_admin_field_edit_hook_balance_metakey', array( &$this, 'balance_field_metakey_data_field' ), 10, 3 );

		add_filter( 'um_profile_field_filter_hook__mycred_balance', array( &$this, 'balance_field' ), 99, 2 );
		add_filter( 'um_profile_field_filter_hook__mycred_rank', array( &$this, 'rank_field' ), 99, 2 );
		add_filter( 'um_profile_field_filter_hook__mycred_progress', array( &$this, 'rank_bar_field' ), 99, 2 );
		add_filter( 'um_profile_field_filter_hook__mycred_badges', array( &$this, 'badges_field' ), 99, 2 );

		add_filter( 'um_profile_completeness_get_field_progress', array( &$this, 'customize_field_progress' ), 99, 3 );

		add_action( 'mycred_delete_point_type', array( &$this, 'on_delete_points_type' ), 10, 1 );
		add_action( 'updated_option', array( &$this, 'update_points_type' ), 10, 3 );
	}


	/**
	 * Delete point type process
	 *
	 * @param $key
	 */
	function delete_point_type( $key ) {
		$mycred_point_types = UM()->options()->get( 'mycred_point_types' );
		if ( false !== $index = array_search( $key, $mycred_point_types ) ) {
			unset( $mycred_point_types[ $index ] );
			$mycred_point_types = array_unique( $mycred_point_types );
			UM()->options()->update( 'mycred_point_types', $mycred_point_types );
		}

		global $wpdb;
		// update default sorting
		$wpdb->query(
			"UPDATE {$wpdb->postmeta}
			SET meta_value = IF( meta_value = 'most_{$key}', 'user_registered_desc', IF( meta_value = 'least_{$key}', 'user_registered_asc', meta_value ) )
			WHERE meta_key = '_um_sortby'"
		);


		// Update role_select and role_radio filters to role
		$postmeta = $wpdb->get_results( "SELECT * FROM {$wpdb->postmeta} WHERE meta_key='_um_sorting_fields' OR meta_key='_um_tagline_fields' OR meta_key='_um_reveal_fields'", ARRAY_A );
		if ( ! empty( $postmeta ) ) {
			foreach ( $postmeta as $row ) {
				$meta_value = maybe_unserialize( $row['meta_value'] );

				if ( is_array( $meta_value ) ) {
					$update = false;

					if ( $row['meta_key'] == '_um_sorting_fields' ) {
						if ( false !== ( $index = array_search( "most_{$key}", $meta_value ) ) ) {
							$meta_value[ $index ] = 'user_registered_desc';
							$update = true;
						}

						if ( false !== ( $index = array_search( "least_{$key}", $meta_value ) ) ) {
							$meta_value[ $index ] = 'user_registered_asc';
							$update = true;
						}
					} elseif ( $row['meta_key'] == '_um_tagline_fields' || $row['meta_key'] == '_um_reveal_fields' ) {
						if ( false !== ( $index = array_search( $key, $meta_value ) ) ) {
							unset( $meta_value[ $index ] );
							$update = true;
						}
					}

					if ( $update ) {
						$meta_value = array_unique( $meta_value );
						update_post_meta( $row['post_id'], $row['meta_key'], $meta_value );
					}
				}
			}
		}



		$custom_fields = get_option( 'um_fields', array() );

		$forms_query = new \WP_Query;
		$forms = $forms_query->query( array(
			'post_type'         => 'um_form',
			'posts_per_page'    => -1,
			'fields'            => 'ids'
		) );

		foreach ( $forms as $form_id ) {
			$forms_fields = get_post_meta( $form_id, '_um_custom_fields', true );

			foreach ( $forms_fields as $k => $field ) {
				if ( isset( $field['metakey'] ) && $key == $field['metakey'] ) {
					unset( $forms_fields[ $k ] );
				}
			}

			update_post_meta( $form_id, '_um_custom_fields', $forms_fields );
		}

		unset( $custom_fields[ $key ] );

		update_option( 'um_fields', $custom_fields );
	}


	/**
	 * When delete point type via MyCRED Settings
	 *
	 * @param $key
	 */
	function on_delete_points_type( $key ) {
		$this->delete_point_type( $key );
	}


	/**
	 * If meta keys were renamed, remove point type for UM:myCRED
	 *
	 * @param $option
	 * @param $old_value
	 * @param $value
	 */
	function update_points_type( $option, $old_value, $value ) {
		if ( $option == 'mycred_types' ) {
			$deleted = array_diff_key( $old_value, array_intersect_key( $old_value, $value ) );

			if ( ! empty( $deleted ) ) {
				foreach ( array_keys( $deleted ) as $key ) {
					$this->delete_point_type( $key );
				}
			}
		}
	}


	/**
	 * Add "Balance" field
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function add_balance_field( $fields ) {
		$fields['mycred_balance'] = array(
			'name' => __( 'myCRED Balance', 'um-mycred' ),
			'col1' => array( '_title', '_balance_metakey', '_help', '_default' ),
			'col2' => array( '_label', '_public', '_roles', '_visibility' ),
			'col3' => array( '_icon' ),
			'validate' => array(
				'_title' => array(
					'mode'  => 'required',
					'error' => __( 'You must provide a title', 'um-mycred' ),
				),
				'_metakey' => array(
					'mode' => 'unique',
				),
			)
		);

		return $fields;
	}


	/**
	 * Extend predefined core fields
	 *
	 * @param $fields
	 *
	 * @return mixed
	 */
	function add_predefined_fields( $fields ) {

		$fields['mycred_progress'] = array(
			'title'             => __( 'myCRED Progress', 'um-mycred' ),
			'metakey'           => 'mycred_progress',
			'type'              => 'text',
			'label'             => __( 'myCRED Progress', 'um-mycred' ),
			'required'          => 0,
			'public'            => 1,
			'editable'          => 0,
			'edit_forbidden'    => 1,
			'show_anyway'       => true,
			'custom'            => true,
		);

		$fields['mycred_badges'] = array(
			'title'             => __( 'myCRED Badges', 'um-mycred' ),
			'metakey'           => 'mycred_badges',
			'type'              => 'text',
			'label'             => __( 'myCRED Badges', 'um-mycred' ),
			'required'          => 0,
			'public'            => 1,
			'editable'          => 0,
			'edit_forbidden'    => 1,
			'show_anyway'       => true,
			'custom'            => true,
		);

		$fields['mycred_rank'] = array(
			'title'             => __( 'myCRED Rank', 'um-mycred' ),
			'metakey'           => 'mycred_rank',
			'type'              => 'select',
			'label'             => __( 'myCRED Rank', 'um-mycred' ),
			'required'          => 0,
			'public'            => 1,
			'editable'          => 0,
			'edit_forbidden'    => 1,
			'show_anyway'       => true,
			'custom'            => true,
			'options'           => array(),
		);

		return $fields;
	}


	/**
	 * The "_balance_metakey" input for the field "myCRED Balance"
	 * @since 2.1.8
	 *
	 * @hook "um_admin_field_edit_hook{$attribute}"
	 * @see  wp-content/plugins/ultimate-member/includes/admin/core/class-admin-metabox.php
	 *
	 * @param string $value
	 * @param int    $form_id
	 * @param array  $edit_array
	 */
	function balance_field_metakey_data_field( $value, $form_id, $edit_array ) {
		$mycred_types = mycred_get_types();

		if ( UM()->metabox()->in_edit == true ) {
			if ( isset( $edit_array['metakey'] ) ) {
				$value = $edit_array['metakey'];
			} ?>

			<p>
				<label for="_metakey_locked"><?php _e( 'Points Type', 'um-mycred' ) ?> <?php UM()->tooltip( __( 'You can add or delete point type on the [Points > Settings >Point Types] admin page.', 'um-mycred' ) ); ?></label>
				<input type="text" name="_metakey_locked" id="_metakey_locked" value="<?php echo esc_attr( $mycred_types[ $value ] ); ?>" disabled />
			</p>

		<?php } else { ?>

			<p>
				<label for="_metakey"><?php _e( 'Points Type', 'um-mycred' ) ?> <?php UM()->tooltip( __( 'You can add or delete point type on the [Points > Settings >Point Types] admin page.', 'um-mycred' ) ); ?></label>
				<select name="_metakey" id="_metakey" style="width: 100%">
					<?php foreach ( $mycred_types as $metakey => $label ) { ?>
						<option value="<?php echo esc_attr( $metakey ); ?>"><?php echo esc_html( $label ); ?></option>
					<?php } ?>
				</select>
			</p>

		<?php }
	}


	/**
	 * Number format for points
	 *
	 * @param $value
	 * @param $data
	 *
	 * @return mixed|null|string
	 */
	function balance_field( $value, $data ) {
		$user_id = um_is_core_page( 'user' ) ? um_profile_id() : um_user( 'ID' );
		return UM()->myCRED()->points()->get_points( $user_id, $data['metakey'] );
	}


	/**
	 * Show user rank
	 *
	 * @param $value
	 * @param $data
	 *
	 * @return null
	 */
	function rank_field( $value, $data ) {
		if ( ! function_exists( 'mycred_get_users_rank' ) ) {
			return null;
		}
		$user_id = um_is_core_page( 'user' ) ? um_profile_id() : um_user('ID');
		$rank = mycred_get_users_rank( $user_id );

		if ( is_object( $rank ) ) {
			$size = UM()->options()->get( 'mycred_badge_size' );
			$title = apply_filters( 'um_mycred_rank_title', $rank->title, $rank );

			$rank_logo = mycred_get_rank_logo( $rank->post_id, $size, array(
				'alt'   => esc_attr( $title ),
				'title' => esc_attr( $title )
			) );

			if ( $rank_logo ) {
				return '<span class="the-badge um-mycred-rank">' . $rank_logo . '</span>' . $title;
			}

			return $title;
		}

		return $value;
	}


	/**
	 * Show user progress
	 *
	 * @param $value
	 * @param $data
	 *
	 * @return null|string
	 */
	function rank_bar_field( $value, $data ) {
		if ( ! function_exists( 'mycred_get_users_rank' ) ) {
			return null;
		}

		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );


		$user_id = um_is_core_page( 'user' ) ? um_profile_id() : um_user('ID');

		$rank = mycred_get_users_rank( $user_id );
		if ( ! is_object( $rank ) ) {
			return;
		}

		$rank_progress = UM()->myCRED()->ranks()->get_progress( $user_id );
		$progress = '<span class="um-mycred-progress um-tip-n" title="'. $rank->title . ' ' . (int) $rank_progress . '%"><span class="um-mycred-progress-done" style="" data-pct="' . (int) $rank_progress . '"></span></span>';

		return $progress;
	}


	/**
	 * Show user balance
	 *
	 * @param $value
	 * @param $data
	 *
	 * @return string
	 */
	function badges_field( $value, $data ) {
		$user_id = um_is_core_page( 'user' ) ? um_profile_id() : um_user('ID');
		return UM()->myCRED()->badges()->show( $user_id );
	}


	/**
	 * Customize fields progress (UM: Profile Completeness)
	 *
	 * @param $is_custom
	 * @param $key
	 * @param $user_id
	 *
	 * @return string
	 */
	function customize_field_progress( $is_custom, $key, $user_id ) {
		if ( 'mycred_badges' !== $key ) {
			return $is_custom;
		}

		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return $is_custom;
		}

		$users_badges = mycred_get_users_badges( $user_id );
		if ( ! empty( $users_badges ) ) {
			$is_custom = true;
		}

		return $is_custom;
	}


}