<?php
namespace um_ext\um_mycred\core;


use um\core\Member_Directory_Meta;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Member_Directory
 *
 * @package um_ext\um_mycred\core
 */
class Member_Directory {


	/**
	 * @var array
	 */
	var $joined = array();


	/**
	 * Member_Directory constructor.
	 */
	function __construct() {
		add_action( 'um_pre_directory_shortcode', array( &$this, 'directory_enqueue_scripts' ), 10, 1 );

		add_filter( 'um_members_directory_sort_fields', array( &$this, 'sort_dropdown_options' ), 10, 1 );

		add_filter( 'um_members_directory_custom_field_types_supported_filter', array( &$this, 'add_mycred_balance' ), 10, 1 );
		add_filter( 'um_custom_field_filter_type', array( &$this, 'add_mycred_balance_filter_type' ), 10, 2 );

		add_filter( 'um_members_directory_filter_fields', array( &$this, 'um_mycred_members_directory_filter_fields' ), 10, 1 );
		add_filter( 'um_members_directory_filter_types', array( &$this, 'um_mycred_directory_filter_types' ), 10, 1 );
		add_filter( 'um_member_directory_filter_slider_common', array( &$this, 'mycred_slider_range' ), 10, 3 );
		add_filter( 'um_member_directory_filter_slider_range_placeholder', array( &$this, 'mycred_slider_range_placeholder' ), 10, 2 );

		add_filter( 'um_search_fields',  array( $this, 'mycred_rank_dropdown' ), 10, 1 );
		add_filter( 'um_query_args_filter_global',  array( $this, 'mycred_filter_query' ), 10, 4 );

		add_filter( 'um_admin_extend_directory_options_profile', array( &$this, 'admin_directory_options_profile' ), 11, 1 );

		//for grid
		add_action( 'um_members_just_after_name_tmpl', array( &$this, 'badges_tmpl' ), 1, 1 );

		//for list
		add_action( 'um_members_list_after_user_name_tmpl', array( &$this, 'badges_tmpl' ), 1, 1 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'extend_ajax_members_data' ), 50, 2 );

		add_filter( 'um_search_fields', array( &$this, 'change_filter_label' ), 10, 2 );

		add_filter( 'um_modify_sortby_parameter', array( &$this, 'sortby_points' ), 100, 2 );


		//UM metadata
		add_action( 'um_pre_users_query', array( &$this, 'add_sortby_custom' ), 10, 3 );
		add_filter( 'um_query_args_filter_global_meta',  array( $this, 'mycred_filter_query_meta' ), 10, 6 );
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function add_mycred_balance( $options ) {
		$options[] = 'mycred_balance';
		return $options;
	}

	/**
	 * @param $type
	 * @param $field_data
	 *
	 * @return string
	 */
	function add_mycred_balance_filter_type( $type, $field_data ) {
		if ( isset( $field_data['type'] ) && $field_data['type'] == 'mycred_balance' ) {
			$type = 'slider';
		}

		return $type;
	}


	/**
	 * @param $query_args
	 * @param $sortby
	 *
	 * @uses mycred_get_types
	 *
	 * @return mixed
	 */
	function sortby_points( $query_args, $sortby ) {
		$mycred_types = mycred_get_types();

		$order = ( 0 === strpos( $sortby, 'most_' ) ) ? 'DESC' : 'ASC';
		$sortby = preg_replace( '/^(most_|least_)(.*?)/s', "$3", $sortby );

		if ( ! in_array( $sortby, array_keys( $mycred_types ) ) ) {
			return $query_args;
		}

		if ( empty( $query_args['meta_query'] ) ) {
			$query_args['meta_query'] = array();
		}

		$query_args['meta_query'][] = array(
			'relation'      => 'OR',
			array(
				'key'       => $sortby,
				'compare'   => 'EXISTS',
				'type'      => 'NUMERIC',
			),
			'_mycred_points' => array(
				'key'       => $sortby,
				'compare'   => 'NOT EXISTS',
				'type'      => 'NUMERIC',
			)
		);

		$query_args['orderby'] = array( '_mycred_points' => $order, 'user_registered' => 'DESC' );
		unset( $query_args['order'] );

		return $query_args;
	}


	/**
	 * Enqueue scripts
	 *
	 */
	function directory_enqueue_scripts() {
		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );
	}


	/**
	 * @param $options
	 *
	 * @uses mycred_get_types
	 *
	 * @return mixed
	 */
	function sort_dropdown_options( $options ) {
		$mycred_types = mycred_get_types();

		if ( ! empty( $mycred_types ) ) {
			foreach ( array_keys( $mycred_types ) as $point_type ) {
				$options[ 'most_' . $point_type ] = sprintf( __( 'Most %s', 'um-mycred' ), mycred_get_point_type_name( $point_type, false ) );
				$options[ 'least_' . $point_type ] = sprintf( __( 'Least %s', 'um-mycred' ), mycred_get_point_type_name( $point_type, false ) );
			}
		}

		return $options;
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function um_mycred_members_directory_filter_fields( $options ) {
		if ( function_exists( 'mycred_have_ranks' ) ) {
			$options['mycred_rank'] = __( 'myCRED Rank', 'um-mycred' );
		}

		return $options;
	}


	/**
	 * @param array $attrs
	 *
	 * @return array
	 */
	function change_filter_label( $attrs, $field_key ) {
		if ( $field_key === 'mycred_rank' ) {
			$attrs['label'] = __( 'Rank', 'um-mycred' );
		}
		return $attrs;
	}


	/**
	 * @param $filters
	 *
	 * @return mixed
	 */
	function um_mycred_directory_filter_types( $filters ) {
		if ( function_exists( 'mycred_have_ranks' ) ) {
			$filters['mycred_rank'] = 'select';
		}

		return $filters;
	}


	function mycred_slider_range( $range, $directory_data, $filter ) {
		$mycred_types = mycred_get_types();
		if ( ! empty( $mycred_types ) && in_array( $filter, array_keys( $mycred_types ) ) ) {
			if ( ! empty( $range ) ) {
				$range[0] = 0;

				if ( $range[0] == $range[1] ) {
					return false;
				}
			}
		}

		return $range;
	}


	/**
	 * @param bool|array $placeholder
	 * @param string $filter
	 *
	 * @uses mycred_get_point_type_name
	 * @uses mycred_get_types
	 *
	 * @return bool|array
	 */
	function mycred_slider_range_placeholder( $placeholder, $filter ) {
		$mycred_types = mycred_get_types();
		if ( ! empty( $mycred_types ) && in_array( $filter, array_keys( $mycred_types ) ) ) {
			return array(
				'<strong>' . __( 'Balance', 'um-mycred' ) . ':</strong>&nbsp;{value}&nbsp;<span class="um-points-wrap">' . esc_html( lcfirst( mycred_get_point_type_name( $filter, false ) ) ) .'</span>',
				'<strong>' . __( 'Balance', 'um-mycred' ) . ':</strong>&nbsp;{min_range} - {max_range}&nbsp;<span class="um-points-wrap">' . esc_html( lcfirst( mycred_get_point_type_name( $filter, false ) ) ) . '</span>',
			);
		}

		return $placeholder;
	}


	/**
	 * @param $attrs
	 *
	 * @return mixed
	 */
	function mycred_rank_dropdown( $attrs ) {
		if ( isset( $attrs['metakey'] ) && 'mycred_rank' == $attrs['metakey'] ) {
			$all_ranks = mycred_get_ranks( 'publish', '-1' );

			$options = array();
			if ( ! empty( $all_ranks ) ) {
				foreach ( $all_ranks as $rank ) {
					$options[ $rank->post_id ] = $rank->post->post_title;
				}
			}

			$attrs['options'] = $options;
			$attrs['custom'] = true;
		}

		return $attrs;
	}


	/**
	 * @param $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 *
	 * @return array
	 */
	function mycred_filter_query( $query, $field, $value, $filter_type ) {
		$mycred_types = mycred_get_types();

		if ( ! empty( $mycred_types ) && in_array( $field, array_keys( $mycred_types ) ) ) {
			$query = array(
				'key'       => $field,
				'value'     => array_map( 'absint', $value ),
				'compare'   => 'BETWEEN',
				'type'      => 'NUMERIC',
				'inclusive' => true,
			);

			UM()->member_directory()->custom_filters_in_query[ $field ] = $value;
		}

		return $query;
	}


	/**
	 * Admin options for directory filtering
	 *
	 * @param $fields
	 *
	 * @return array
	 */
	function admin_directory_options_profile( $fields ) {

		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return $fields;
		}

		$fields = array_merge( array_slice( $fields, 0, 3 ), array(
			array(
				'id'    => '_um_mycred_hide_badges',
				'type'  => 'checkbox',
				'label' => __( 'Hide myCRED badges', 'um-mycred' ),
				'value' => UM()->query()->get_meta_value( '_um_mycred_hide_badges', null, 'na' ),
			),
		), array_slice( $fields, 3, count( $fields ) - 1 ) );

		return $fields;
	}


	/**
	 * Display badges in Member Directories
	 *
	 * @param $args
	 */
	function badges_tmpl( $args ) {
		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return;
		}

		$hide_badges = ! empty( $args['mycred_hide_badges'] ) ? $args['mycred_hide_badges'] : ! UM()->options()->get( 'mycred_show_badges_in_members' );
		if ( empty( $hide_badges ) ) { ?>
			<# if ( typeof user.badges !== 'undefined' && user.badges !== '' ) { #>
				<div class="um-header" style="border:none;margin:initial;padding:initial;min-height:initial;">{{{user.badges}}}</div>
			<# } #>
		<?php }
	}


	/**
	 * Extends AJAX member directory data
	 *
	 * @param $data_array
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function extend_ajax_members_data( $data_array, $user_id ) {
		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return $data_array;
		}

		$data_array['badges'] = UM()->myCRED()->badges()->show( $user_id );
		return $data_array;
	}


	/**
	 * @param Member_Directory_Meta $query
	 * @param array $directory_data
	 * @param string $sortby
	 *
	 * @uses mycred_get_types
	 */
	function add_sortby_custom( $query, $directory_data, $sortby ) {
		$mycred_types = mycred_get_types();

		$order = ( 0 === strpos( $sortby, 'most_' ) ) ? 'DESC' : 'ASC';
		$sortby = preg_replace( '/^(most_|least_)(.*?)/s', "$3", $sortby );

		if ( empty( $mycred_types ) || ! in_array( $sortby, array_keys( $mycred_types ) ) ) {
			return;
		}

		global $wpdb;

		if ( empty( $this->joined[ $sortby ] ) ) {
			$this->joined[ $sortby ] = true;
			$indexes = array_flip( array_keys( $this->joined ) );
			$index = $indexes[ $sortby ];

			$query->joins[] = $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata ummmycred{$index} ON ( ummmycred{$index}.user_id = u.ID AND ummmycred{$index}.um_key = %s )", $sortby );
		} else {
			$indexes = array_flip( array_keys( $this->joined ) );
			$index = $indexes[ $sortby ];
		}

		$query->sql_order = " ORDER BY CAST( ummmycred{$index}.um_value AS SIGNED ) {$order}, u.user_registered DESC";
	}


	/**
	 * @param bool $skip
	 * @param Member_Directory_Meta $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 * @param bool $is_default
	 *
	 * @uses mycred_get_types
	 *
	 * @return bool
	 */
	function mycred_filter_query_meta( $skip, $query, $field, $value, $filter_type, $is_default ) {
		global $wpdb;

		$mycred_types = mycred_get_types();

		if ( ! empty( $mycred_types ) && in_array( $field, array_keys( $mycred_types ) ) ) {

			$skip = true;

			$min = min( $value );
			$max = max( $value );

			if ( empty( $this->joined[ $field ] ) ) {
				$this->joined[ $field ] = true;
				$indexes = array_flip( array_keys( $this->joined ) );
				$index = $indexes[ $field ];

				$query->joins[] = $wpdb->prepare( "LEFT JOIN {$wpdb->prefix}um_metadata ummmycred{$index} ON ( ummmycred{$index}.user_id = u.ID AND ummmycred{$index}.um_key = %s )", $field );
			} else {
				$indexes = array_flip( array_keys( $this->joined ) );
				$index = $indexes[ $field ];
			}

			$query->where_clauses[] = $wpdb->prepare( "CAST( ummmycred{$index}.um_value AS SIGNED ) BETWEEN %d AND %d", $min, $max );

			if ( ! $is_default ) {
				$query->custom_filters_in_query[ $field ] = $value;
			}

		}

		return $skip;
	}
}