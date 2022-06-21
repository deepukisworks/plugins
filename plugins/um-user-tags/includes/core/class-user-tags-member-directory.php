<?php
namespace um_ext\um_user_tags\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class User_Tags_Member_Directory
 *
 * @package um_ext\um_user_tags\core
 */
class User_Tags_Member_Directory {


	/**
	 * User_Tags_Member_Directory constructor.
	 */
	function __construct() {
		add_action( 'um_pre_directory_shortcode', array( &$this, 'directory_enqueue_scripts' ), 10, 1 );

		add_filter( 'um_search_fields', array( &$this, 'user_tags_filter_dropdown' ), 10, 1 );

		add_filter( 'um_members_directory_custom_field_types_supported_filter', array( &$this, 'custom_field_types_supported_filter' ), 10, 1 );
		add_filter( 'um_member_directory_general_search_meta_query', array( &$this, 'extends_search_query' ), 10, 2 );
		add_action( 'um_member_directory_meta_general_search_meta_query', array( &$this, 'extends_search_query_meta' ), 10, 2 );
		add_filter( 'um_member_directory_filter_select_options', array( &$this, 'user_tags_filter_options' ), 10, 3 );
		add_filter( 'um_member_directory_filter_select_options_sorted', array( &$this, 'user_tags_filter_options_sort' ), 10, 2 );

	}


	/**
	 * Enqueue scripts on member directory
	 *
	 * @param $args
	 */
	function directory_enqueue_scripts( $args ) {
		wp_enqueue_style( 'um-user-tags' );
		wp_enqueue_script( 'um-user-tags' );
		wp_enqueue_script( 'um-user-tags-members' );
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function custom_field_types_supported_filter( $options ) {
		$options[] = 'user_tags';
		return $options;
	}



	/**
	 * @param $attrs
	 * @return bool
	 */
	function user_tags_filter_dropdown( $attrs ) {
		if ( isset( $attrs['type'] ) && 'user_tags' == $attrs['type'] ) {
			$attrs['options'] = apply_filters( 'um_multiselect_options_user_tags', array(), $attrs );
			$attrs['custom']  = 1;
		}

		return $attrs;
	}


	/**
	 * @param $query
	 * @param $search
	 *
	 * @return array
	 */
	function extends_search_query( $query, $search ) {

		$term = get_term_by( 'name', trim( $search ), 'um_user_tag' );

		if ( ! empty( $term->term_id ) ) {
			$query = array_merge( $query, array(
				array(
					'value'     => serialize( strval( $term->term_id ) ),
					'compare'   => 'LIKE',
				),
				array(
					'value'     => serialize( intval( $term->term_id ) ),
					'compare'   => 'LIKE',
				),
				array(
					'value'     => serialize( strval( $term->slug ) ),
					'compare'   => 'LIKE',
				),
				'relation' => 'OR',
			) );
		}

		return $query;
	}


	/**
	 * UM metadata compatibility
	 *
	 * @param string $query_string
	 * @param string $search
	 *
	 * @return string
	 */
	function extends_search_query_meta( $query_string, $search ) {
		$term = get_term_by( 'name', trim( $search ), 'um_user_tag' );

		if ( ! empty( $term->term_id ) ) {
			global $wpdb;
			$query_string .= $wpdb->prepare( ' OR umm_search.um_value LIKE %s OR umm_search.um_value LIKE %s OR umm_search.um_value LIKE %s', '%' . serialize( intval( $term->term_id ) ) . '%', '%' . serialize( strval( $term->term_id ) ) . '%', '%' . serialize( strval( $term->slug ) ) . '%' );
		}

		return $query_string;
	}


	/**
	 * @param $options
	 * @param $values_array
	 * @param $attrs
	 *
	 * @return mixed
	 */
	function user_tags_filter_options( $options, $values_array, $attrs ) {
		if ( $attrs['type'] !== 'user_tags' ) {
			return $options;
		}

		$fields = UM()->builtin()->all_user_fields;
		$attrs = $fields[ $attrs['metakey'] ];
		$attrs = apply_filters( 'um_search_fields', $attrs, $attrs['metakey'] );

		if ( ! empty( $values_array ) ) {
			$values_array = array_map( 'maybe_unserialize', $values_array );
			$temp_values = array();
			foreach ( $values_array as $values ) {
				if ( is_array( $values ) ) {
					$temp_values = array_merge( $temp_values, $values );
				} else {
					$temp_values[] = $values;
				}
			}
			$values_array = array_unique( $temp_values );
		}

		$options = array_intersect_key( array_map( 'trim', $attrs['options'] ), array_flip( $values_array ) );

		return $options;
	}


	/**
	 * @param $options
	 * @param $attrs
	 *
	 * @return mixed
	 */
	function user_tags_filter_options_sort( $options, $attrs ) {
		if ( $attrs['type'] !== 'user_tags' ) {
			return $options;
		}

		asort( $options );

		return $options;
	}

}