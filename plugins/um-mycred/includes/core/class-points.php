<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Points
 */
class Points {


	/**
	 * Points constructor.
	 */
	function __construct() {

	}


	/**
	 * Get formatted points
	 *
	 * @param  integer     $user_id
	 * @param  string|null $point_type
	 * @param  string|null $show       Allowed values: 'label', 'suffix', 'default'
	 *
	 * @return mixed|string|null
	 */
	function get_points( $user_id, $point_type = null, $show = 'default' ) {
		if ( !$point_type ) {
			$point_type = defined( 'MYCRED_DEFAULT_TYPE_KEY' ) ? MYCRED_DEFAULT_TYPE_KEY : 'mycred_default';
		}

		$mycred = mycred( $point_type );
		$balance = $mycred->get_users_balance( $user_id, $point_type );

		if ( empty( $mycred->core['after'] ) ) {
			$format = _n( '%1$s <span class="um-points-wrap">%%singular%%</span>', '%1$s <span class="um-points-wrap">%%plural%%</span>', $balance, 'um-mycred' );
		} else {
			switch ( $show  ) {
				case 'label':
					$format = _n( '%1$s <span class="um-points-wrap">%2$s</span>', '%1$s <span class="um-points-wrap">%3$s</span>', $balance, 'um-mycred' );
					break;

				case 'suffix':
					$format = __( '%1$s <span class="um-points-wrap">%4$s</span>', 'um-mycred' );
					break;

				case 'default':
				default:
					$format = _n( '%1$s <span class="um-points-wrap">%%singular%%</span>', '%1$s <span class="um-points-wrap">%%plural%%</span>', $balance, 'um-mycred' );
					break;
			}
		}

		$points = $mycred->template_tags_general( sprintf( $format,
			/* 1 */ $balance,
			/* 2 */ $mycred->singular(),
			/* 3 */ $mycred->plural(),
			/* 4 */ $mycred->core['after']
		) );

		$pointsf = apply_filters( 'um_mycred_get_points_content', $points, $point_type, $user_id );
		return $pointsf;
	}


	/**
	 * Get points clean
	 *
	 * @param $user_id
	 * @param null $point_type
	 *
	 * @return mixed|null
	 */
	function get_points_clean( $user_id, $point_type = null ) {
		if ( ! $point_type ) {
			$point_type = defined( 'MYCRED_DEFAULT_TYPE_KEY' ) ? MYCRED_DEFAULT_TYPE_KEY : 'mycred_default';
		}

		$clean_points = mycred_get_users_balance( $user_id, $point_type );
		return $clean_points;
	}


	/**
	 * Transfer points
	 *
	 * @param $from
	 * @param $to
	 * @param $amount
	 * @param string|null $point_type
	 */
	function transfer( $from, $to, $amount, $point_type = null  ) {
		if ( ! $point_type ) {
			$point_type = defined( 'MYCRED_DEFAULT_TYPE_KEY' ) ? MYCRED_DEFAULT_TYPE_KEY : 'mycred_default';
		}

		do_action('um_mycred_credit_balance_transfer', $to, $amount, $from, $point_type );

		mycred_add( 'um-transfer-credit', $to, $amount, __( '%plural% received!', 'um-mycred' ), '', '', $point_type );
		mycred_subtract( 'um-transfer-charge', $from, $amount, __( '%plural% sent!', 'um-mycred' ), '', '', $point_type );

		delete_option( "um_cache_userdata_{$to}" );
		delete_option( "um_cache_userdata_{$from}" );
	}


	/**
	 * Add points
	 *
	 * @param $user_id
	 * @param $add
	 * @param array $args
	 */
	function add( $user_id, $add, $args = array() ) {

		if ( $add == 'mycred_um_social_connect' ) {
			$add = 'mycred_' . $args['provider'];
		}
		$mycred = UM()->options()->get( $add );

		if ( ! $mycred ) {
			return;
		}

		// imply limits
		if ( $this->imply_limit( $user_id, $add, 'awarded' ) ) {
			return;
		}

		$action = $add;
		UM()->myCRED()->notifications()->action = $add;
		$type = 'reward';
		$amount = UM()->options()->get( $action . '_points' );

		$default_handler = apply_filters( 'um_mycred_add_func', true );
		if ( $default_handler ) {
			$task = UM()->options()->get( $action . '_task' );
			$log_template = UM()->options()->get( $action . '_log_template' );
			$log_text = str_replace( '%task%', __( $task, 'um-mycred' ), __( $log_template, 'um-mycred' ) );

			mycred_add( $action, $user_id, $amount, $log_text );
		}

		do_action('um_mycred_credit_balance_user', $user_id, $add, $action, $args, $type );

		$description = sprintf(
			__( 'Earned %s via Ultimate Member( %s )', 'um-mycred' ),
			'%plural%',
			$action
		);

		$description = apply_filters( 'um_mycred_add_point_description', $description, $amount, $action );

		mycred_add( $action, $user_id, $add, $description );
		delete_option( "um_cache_userdata_{$user_id}" );

	}


	/**
	 * Add points (hold)
	 *
	 * @param $user_id
	 * @param $add
	 *
	 * @return mixed|string|void
	 */
	function add_pending( $user_id, $add ) {
		$mycred = UM()->options()->get( $add );
		if ( ! $mycred ) {
			return;
		}

		// imply limits
		if ( $this->imply_limit( $user_id, $add, 'awarded' ) ) {
			return;
		}

		$add = UM()->options()->get( $add . '_points' );
		return $add;
	}


	/**
	 * Deduct points
	 *
	 * @param $user_id
	 * @param $deduct
	 * @param array $args
	 */
	function deduct( $user_id, $deduct, $args = array() ) {

		if ( $deduct == 'mycred_um_social_disconnect' ) {
			$deduct = 'mycred_d_'.$args['provider'];
		}

		$mycred = UM()->options()->get( $deduct );

		if ( ! $mycred ) {
			return;
		}

		// imply limits
		if ( $this->imply_limit( $user_id, $deduct,'deducted' ) ) {
			return;
		}

		$action = $deduct;
		UM()->myCRED()->notifications()->action = $deduct;
		$type = 'deduct';

		$default_handler = apply_filters( 'um_mycred_deduct_func', true );
		if ( $default_handler ) {
			$amount = UM()->options()->get( $action . '_points' );
			$task = UM()->options()->get( $action . '_task' );
			$log_template = UM()->options()->get( $action . '_log_template' );
			$log_text = str_replace('%task%', __( $task, 'um-mycred' ) , __( $log_template, 'um-mycred' ) );

			mycred_subtract( $action, $user_id, $amount, $log_text );
		}

		do_action('um_mycred_credit_balance_user', $user_id, $deduct, $action, $args, $type );

		delete_option( "um_cache_userdata_{$user_id}" );
	}


	/**
	 * Update user's balance
	 *
	 * @param  integer $user_id
	 * @param  string  $action
	 * @param  string  $type
	 *
	 * @return bool
	 */
	function imply_limit( $user_id, $action, $type ) {
		if ( UM()->options()->get( $action . '_limit' ) ) {

			$last_update 	= get_user_meta( $user_id, '_mycred_'.$type.'_last_time_update', true);
			$limit   	 	= UM()->options()->get( $action . '_limit' );
			$limit_by 	 	= UM()->options()->get( $action . '_limit_duration');
			$a_limit 	 	= get_user_meta( $user_id, '_mycred_'.$type.'_lmt', true);
			$user_limit_by_value = get_user_meta( $user_id, '_mycred_'.$type.'_'.$limit_by.'_lmt', true);

			$current_time 	= current_time('timestamp');

			if( ! $user_limit_by_value ){
				$user_limit_by_value = 0;
			}

			$user_limit_by_value++;

			switch ( $limit_by ) {
				case 'in_total': // if within total limit
					if ( isset( $a_limit[ $action ] ) && $a_limit[ $action ] >= $limit ) {
						return true;
					}
					break;
				case 'per_day': // if within the day and exceeds limit, return;
					if ( strtotime( '+1 day', $last_update ) >= $current_time && $user_limit_by_value >= $limit ) {
						return true;
					}
					break;
				case 'per_week': // if within the week and exceeds limit, return;
					if ( strtotime( '+1 week', $last_update ) >=  $current_time && $user_limit_by_value >= $limit ) {
						return true;
					}
					break;
				case 'per_month': // if within the month and exceeds limit, return;
					if ( strtotime( '+1 month', $last_update ) >=  $current_time && $user_limit_by_value >= $limit ) {
						return true;
					}
					break;
				default: // no limit

					break;
			}

			if ( ! isset( $a_limit[ $action ] ) ) {
				$a_limit[ $action ] = 1;
			} else {
				$a_limit[ $action ] = $a_limit[ $action ] + 1;
			}



			update_user_meta( $user_id, '_mycred_' . $type . '_lmt', $a_limit );
			update_user_meta( $user_id, '_mycred_' . $type . '_' . $limit_by . '_lmt', $user_limit_by_value );
			update_user_meta( $user_id, '_mycred_' . $type . '_last_time_update', current_time( 'timestamp' ) );

			return false;

		}

		return false;
	}


	/**
	 * @param $formatted
	 * @param $number
	 * @param $decimals
	 *
	 * @return string
	 */
	function number_format( $formatted, $number, $decimals ) {
		global $mycred;

		$mycred_settings = $mycred->core;

		$formatted = number_format( $number, absint( $mycred_settings['format']['decimals'] ), $mycred_settings['format']['separators']['decimal'], $mycred_settings['format']['separators']['thousand'] );

		return $formatted;
	}

}