<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class REST
 */
class REST {


	/**
	 * REST constructor.
	 */
	function __construct() {
		add_filter( 'um_rest_get_auser', array( &$this, 'rest_get_auser' ), 10, 3 );
	}


	/**
	 * Extends REST API response attributes
	 *
	 * @param $response
	 * @param $field
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function rest_get_auser( $response, $field, $user_id ) {
		if ( 'mycred_points' == $field ) {
			// compatibility with old rest API requests
			$point_type = defined( 'MYCRED_DEFAULT_TYPE_KEY' ) ? MYCRED_DEFAULT_TYPE_KEY : 'mycred_default';
			$response['mycred_points'] = number_format( (int)get_user_meta( $user_id, $point_type, true ), 2 );
		}

		$mycred_types = mycred_get_types();
		if ( ! empty( $mycred_types ) && in_array( $field, array_keys( $mycred_types ) ) ) {
			$response[ $field ] = number_format( (int)get_user_meta( $user_id, $field, true ), 2 );
		}

		return $response;
	}
}