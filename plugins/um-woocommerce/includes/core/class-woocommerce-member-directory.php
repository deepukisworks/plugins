<?php
namespace um_ext\um_woocommerce\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class WooCommerce_Member_Directory
 *
 * @package um_ext\um_woocommerce\core
 */
class WooCommerce_Member_Directory {


	/**
	 * WooCommerce_Member_Directory constructor.
	 */
	function __construct() {
		add_filter( 'um_members_directory_filter_fields', array( &$this, 'members_directory_filter_fields' ), 10, 1 );
		add_filter( 'um_members_directory_filter_types', array( &$this, 'directory_filter_types' ), 10, 1 );
		add_filter( 'um_member_directory_filter_woo_order_count_slider', array( &$this, 'um_woocommerce_directory_filter_woo_order_count_slider' ), 10, 1 );
		add_filter( 'um_member_directory_filter_woo_total_spent_slider', array( &$this, 'um_woocommerce_directory_filter_woo_total_spent_slider' ), 10, 1 );
		add_filter( 'um_member_directory_filter_slider_range_placeholder', array( &$this, 'slider_range_placeholder' ), 10, 2 );

		add_filter( 'um_search_fields',  array( $this, 'country_dropdown' ), 10, 1 );

		add_filter( 'um_query_args_woo_order_count__filter', array( $this, 'filter_by_orders_count' ), 10, 4 );
		add_filter( 'um_query_args_woo_order_count__filter_meta',  array( $this, 'filter_by_orders_count_meta' ), 10, 6 );
		add_filter( 'um_query_args_woo_total_spent__filter', array( $this, 'filter_by_total_spent' ), 10, 4 );
		add_filter( 'um_query_args_woo_total_spent__filter_meta',  array( $this, 'filter_by_total_spent_meta' ), 10, 6 );

		add_filter( 'um_search_fields', array( &$this, 'change_filter_label' ), 10, 2 );

		add_filter( 'um_ajax_get_members_data', array( &$this, 'get_members_data' ), 50, 2 );

		add_filter( 'um_member_directory_filter_select_options', array( &$this, 'intersect_filter_options' ), 10, 3 );
	}


	/**
	 * @param $options
	 * @param $values_array
	 * @param $attrs
	 *
	 * @return array
	 */
	function intersect_filter_options( $options, $values_array, $attrs ) {
		if ( ! ( $attrs['metakey'] == 'billing_country' || $attrs['metakey'] == 'shipping_country' ) ) {
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
	 * @param $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 *
	 * @return bool
	 */
	function filter_by_orders_count( $query, $field, $value, $filter_type ) {
		global $wpdb;

		$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );

		$min = min( $value );
		$max = max( $value );

		if ( $min == $max ) {
			if ( $min == 0 ) {
				$user_ids = $wpdb->get_col(
					"SELECT u.ID
					FROM {$wpdb->users} as u
					LEFT JOIN {$wpdb->postmeta} AS pm ON pm.meta_key = '_customer_user' AND pm.meta_value = u.ID
					LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
					WHERE ( pm.meta_id IS NULL AND p.ID IS NULL ) OR 
						  ( p.post_status NOT IN ( 'wc-" . implode( "','wc-", $statuses ) . "' ) AND 
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') )
					GROUP BY u.ID"
				);
			} else {
				$user_ids = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm.meta_value AS user_id
					FROM $wpdb->posts as p 
					LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
					WHERE pm.meta_key = '_customer_user' AND     
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
						  p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
					GROUP BY pm.meta_value
					HAVING COUNT( p.ID ) = %d",
					$min
				) );
			}
		} else {
			if ( $min == 0 ) {
				$user_ids = $wpdb->get_col(
					"SELECT u.ID
					FROM {$wpdb->users} as u
					LEFT JOIN {$wpdb->postmeta} AS pm ON pm.meta_key = '_customer_user' AND pm.meta_value = u.ID
					LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
					WHERE ( pm.meta_id IS NULL AND p.ID IS NULL ) OR 
						  ( p.post_status NOT IN ( 'wc-" . implode( "','wc-", $statuses ) . "' ) AND 
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') )
					GROUP BY u.ID"
				);

				$user_ids2 = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm.meta_value AS user_id
					FROM $wpdb->posts as p 
					LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
					WHERE pm.meta_key = '_customer_user' AND     
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
						  p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
					GROUP BY pm.meta_value
					HAVING COUNT( p.ID ) BETWEEN %d AND %d",
					$min,
					$max
				) );

				$user_ids = array_merge( $user_ids, $user_ids2 );
			} else {
				$user_ids = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm.meta_value AS user_id
					FROM $wpdb->posts as p 
					LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
					WHERE pm.meta_key = '_customer_user' AND     
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
						  p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
					GROUP BY pm.meta_value
					HAVING COUNT( p.ID ) BETWEEN %d AND %d",
					$min,
					$max
				) );
			}
		}

		if ( ! empty( $user_ids ) ) {
			UM()->member_directory()->query_args['include'] = $user_ids;
		} else {
			UM()->member_directory()->query_args['include'] = array('0');
		}

		UM()->member_directory()->custom_filters_in_query[ $field ] = $value;

		return true;
	}


	/**
	 * @param $skip
	 * @param $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 * @param bool $is_default
	 *
	 * @return bool
	 */
	function filter_by_orders_count_meta( $skip, $query, $field, $value, $filter_type, $is_default ) {
		global $wpdb;

		$skip = true;

		$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );

		$min = min( $value );
		$max = max( $value );

		if ( $min == $max ) {
			if ( $min == 0 ) {
				$user_ids = $wpdb->get_col(
					"SELECT u.ID
					FROM {$wpdb->users} as u
					LEFT JOIN {$wpdb->postmeta} AS pm ON pm.meta_key = '_customer_user' AND pm.meta_value = u.ID
					LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
					WHERE ( pm.meta_id IS NULL AND p.ID IS NULL ) OR 
						  ( p.post_status NOT IN ( 'wc-" . implode( "','wc-", $statuses ) . "' ) AND 
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') )
					GROUP BY u.ID"
				);
			} else {
				$user_ids = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm.meta_value AS user_id
					FROM $wpdb->posts as p 
					LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
					WHERE pm.meta_key = '_customer_user' AND     
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
						  p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
					GROUP BY pm.meta_value
					HAVING COUNT( p.ID ) = %d",
					$min
				) );
			}
		} else {
			if ( $min == 0 ) {
				$user_ids = $wpdb->get_col(
					"SELECT u.ID
					FROM {$wpdb->users} as u
					LEFT JOIN {$wpdb->postmeta} AS pm ON pm.meta_key = '_customer_user' AND pm.meta_value = u.ID
					LEFT JOIN {$wpdb->posts} AS p ON p.ID = pm.post_id
					WHERE ( pm.meta_id IS NULL AND p.ID IS NULL ) OR 
						  ( p.post_status NOT IN ( 'wc-" . implode( "','wc-", $statuses ) . "' ) AND 
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') )
					GROUP BY u.ID"
				);

				$user_ids2 = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm.meta_value AS user_id
					FROM $wpdb->posts as p 
					LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
					WHERE pm.meta_key = '_customer_user' AND     
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
						  p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
					GROUP BY pm.meta_value
					HAVING COUNT( p.ID ) BETWEEN %d AND %d",
					$min,
					$max
				) );

				$user_ids = array_merge( $user_ids, $user_ids2 );
			} else {
				$user_ids = $wpdb->get_col( $wpdb->prepare(
					"SELECT pm.meta_value AS user_id
					FROM $wpdb->posts as p 
					LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
					WHERE pm.meta_key = '_customer_user' AND     
						  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
						  p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
					GROUP BY pm.meta_value
					HAVING COUNT( p.ID ) BETWEEN %d AND %d",
					$min,
					$max
				) );
			}
		}

		if ( ! empty( $user_ids ) ) {
			$query->where_clauses[] = "u.ID IN ('" . implode( "','", $user_ids ) . "')";
		} else {
			$query->where_clauses[] = "u.ID IN ('0')";
		}

		if ( ! $is_default ) {
			$query->custom_filters_in_query[ $field ] = $value;
		}

		return $skip;
	}


	/**
	 * @param $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 *
	 * @return array
	 */
	function filter_by_total_spent( $query, $field, $value, $filter_type ) {
		$min = min( $value );
		$max = max( $value );

		if ( $min == $max ) {
			if ( $min == 0 ) {
				$query = array(
					'relation' => 'OR',
					array(
						'key'       => '_money_spent',
						'value'     => $min,
					),
					array(
						'key'       => '_money_spent',
						'compare'   => 'NOT EXISTS',
					)
				);
			} else {
				$query = array(
					'key'       => '_money_spent',
					'value'     => $min,
				);
			}
		} else {
			if ( $min == 0 ) {
				$query = array(
					'relation' => 'OR',
					array(
						'key'       => '_money_spent',
						'value'     => array_map( 'absint', $value ),
						'compare'   => 'BETWEEN',
						'type'      => 'NUMERIC',
						'inclusive' => true,
					),
					array(
						'key'       => '_money_spent',
						'compare'   => 'NOT EXISTS',
					)
				);
			} else {
				$query = array(
					'key'       => '_money_spent',
					'value'     => array_map( 'absint', $value ),
					'compare'   => 'BETWEEN',
					'type'      => 'NUMERIC',
					'inclusive' => true,
				);
			}
		}

		UM()->member_directory()->custom_filters_in_query[ $field ] = $value;

		return $query;
	}


	/**
	 * @param $skip
	 * @param $query
	 * @param $field
	 * @param $value
	 * @param $filter_type
	 * @param bool $is_default
	 *
	 * @return bool
	 */
	function filter_by_total_spent_meta( $skip, $query, $field, $value, $filter_type, $is_default ) {
		global $wpdb;

		$skip = true;

		$min = min( $value );
		$max = max( $value );

		$query->joins[] = "LEFT JOIN {$wpdb->prefix}um_metadata ummwoo ON ( ummwoo.user_id = u.ID AND ummwoo.um_key = '_money_spent' )";

		if ( $min == $max ) {
			if ( $min == 0 ) {
				$query->where_clauses[] = "ummwoo.um_value IS NULL";
			} else {
				$query->where_clauses[] = $wpdb->prepare( "CAST( ummwoo.um_value AS UNSIGNED ) = %d", $min );
			}
		} else {
			if ( $min == 0 ) {
				$query->where_clauses[] = $wpdb->prepare( "( ummwoo.um_value IS NULL OR CAST( ummwoo.um_value AS UNSIGNED ) BETWEEN %d AND %d )", $min, $max );
			} else {
				$query->where_clauses[] = $wpdb->prepare( "CAST( ummwoo.um_value AS UNSIGNED ) BETWEEN %d AND %d", $min, $max );
			}
		}

		if ( ! $is_default ) {
			$query->custom_filters_in_query[ $field ] = $value;
		}

		return $skip;
	}


	/**
	 * @param $options
	 *
	 * @return mixed
	 */
	function members_directory_filter_fields( $options ) {
		$options['billing_city']    = __( 'WC Billing city', 'um-woocommerce' );
		$options['billing_country'] = __( 'WC Billing country', 'um-woocommerce' );
		$options['shipping_city']    = __( 'WC Shipping city', 'um-woocommerce' );
		$options['shipping_country'] = __( 'WC Shipping country', 'um-woocommerce' );
		$options['woo_order_count'] = __( 'Total Orders', 'um-woocommerce' );
		$options['woo_total_spent'] = __( 'Total Spent', 'um-woocommerce' );

		return $options;
	}


	/**
	 * @param $filters
	 *
	 * @return mixed
	 */
	function directory_filter_types( $filters ) {
		$filters['billing_country'] = 'select';
		$filters['shipping_country'] = 'select';
		$filters['woo_order_count'] = 'slider';
		$filters['woo_total_spent'] = 'slider';

		return $filters;
	}


	/**
	 * @param $range
	 *
	 * @return array|bool
	 */
	function um_woocommerce_directory_filter_woo_order_count_slider( $range ) {
		global $wpdb;

		$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );

		$counts = $wpdb->get_col(
			"SELECT COUNT( p.ID ) as woo_orders
			FROM $wpdb->posts as p 
			LEFT JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id 
			WHERE pm.meta_key = '_customer_user' AND     
				  p.post_type IN ('" . implode( "','", wc_get_order_types( 'order-count' ) ) . "') AND     
				  p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
			GROUP BY pm.meta_value
			ORDER BY woo_orders"
		);

		if ( empty( $counts ) ) {
			$range = false;
		} else {
			$range = array( 0, max( $counts ) );
		}

		return $range;
	}


	/**
	 * @param $range
	 *
	 * @return array|bool
	 */
	function um_woocommerce_directory_filter_woo_total_spent_slider( $range ) {
		global $wpdb;

		$meta = $wpdb->get_row(
			"SELECT MAX( meta_value ) as max_meta, 
			COUNT( DISTINCT meta_value ) as amount 
			FROM {$wpdb->usermeta} 
			WHERE meta_key = '_money_spent'",
		ARRAY_A );

		$range = false;
		if ( ! empty( $meta['max_meta'] ) ) {
			$range = array( 0, $meta['max_meta'] );
		}

		return $range;
	}


	/**
	 * @param $placeholder
	 * @param $filter
	 *
	 * @return array
	 */
	function slider_range_placeholder( $placeholder, $filter ) {
		if ( $filter == 'woo_order_count' ) {
			$placeholder = array(
				__( '<strong>Total Orders:</strong>&nbsp;{value}', 'um-woocommerce' ),
				__( '<strong>Total Orders:</strong>&nbsp;{min_range} - {max_range}', 'um-woocommerce' ),
			);
		} elseif ( $filter == 'woo_total_spent' ) {
			$placeholder = array(
				__( '<strong>Total Spent (' . get_woocommerce_currency_symbol() . '):</strong>&nbsp;{value}', 'um-woocommerce' ),
				__( '<strong>Total Spent (' . get_woocommerce_currency_symbol() . '):</strong>&nbsp;{min_range} - {max_range}', 'um-woocommerce' ),
			);
		}

		return $placeholder;
	}


	/**
	 * @param $attrs
	 *
	 * @return mixed
	 */
	function country_dropdown( $attrs ) {
		if ( isset( $attrs['metakey'] ) && ( 'billing_country' == $attrs['metakey'] || 'shipping_country' == $attrs['metakey'] ) ) {
			$countries_obj   = new \WC_Countries();
			$countries   = $countries_obj->__get( 'countries' );

			$attrs['options'] = $countries;
			$attrs['custom'] = true;
		}

		return $attrs;
	}


	/**
	 * Remove "WC " from Woo address fields labels
	 *
	 * @param array $attrs
	 *
	 * @return array
	 */
	function change_filter_label( $attrs, $field_key ) {
		$address_field_keys = UM()->WooCommerce_API()->api()->get_wc_address_fields( true );

		if ( in_array( $field_key, $address_field_keys ) ) {
			$attrs['label'] = substr( $attrs['label'], 3 );
		}
		return $attrs;
	}


	/**
	 * Expand AJAX member directory data
	 *
	 * @param $data_array
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function get_members_data( $data_array, $user_id ) {
		if ( isset( $data_array['billing_country'] ) || isset( $data_array['shipping_country'] ) ) {
			$countries = UM()->builtin()->get( 'countries' );

			if ( isset( $data_array['billing_country'] ) && strlen( $data_array['billing_country'] ) == 2 ) {
				$lang_code = $data_array['billing_country'];
				$data_array['billing_country'] = $countries[ $lang_code ];
			}
			if ( isset( $data_array['shipping_country'] ) && strlen( $data_array['shipping_country'] ) == 2 ) {
				$lang_code = $data_array['shipping_country'];
				$data_array['shipping_country'] = $countries[ $lang_code ];
			}
		}

		return $data_array;
	}
}