<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Ranks
 */
class Ranks {


	/**
	 * Ranks constructor.
	 */
	function __construct() {
	}


	/**
	 * Get user progress
	 *
	 * @param $user_id
	 *
	 * @return int|string
	 */
	function get_progress( $user_id ) {

		$mycred = mycred();
		$key = $mycred->get_point_type_key();
		$users_balance = $mycred->get_users_balance( $user_id, $key );

		$max = $min = 0;

		$users_rank = mycred_get_users_rank( $user_id );
		if ( is_object( $users_rank ) ) {
			$max = (float) $users_rank->maximum;
			$min = (float) $users_rank->minimum;
		}

		if ( empty( $users_balance ) || empty( $max ) || $users_balance < $min ) {
			return 0;
		} elseif ( $users_balance > $max ) {
			return 100;
		}

		$progress = ( $users_balance - $min ) / ( $max - $min ) * 100;

		return number_format( $progress, 1 );
	}
}