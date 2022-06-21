<?php
namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Account
 *
 * @package um_ext\um_mycred\core
 */
class Account {


	/**
	 * Account constructor.
	 */
	function __construct() {

		add_filter( 'um_account_page_default_tabs_hook', array( &$this, 'account_tab' ), 100 );
		add_filter( 'um_account_content_hook_points', array( &$this, 'points_tab_content' ), 10, 1 );

		add_action( 'um_submit_account_points_tab_errors_hook', array( &$this, 'transfer_errors' ), 10, 1 );
		add_filter( 'um_custom_error_message_handler', array( &$this, 'custom_error' ), 30, 2 );
		add_filter( 'um_custom_success_message_handler', array( &$this, 'custom_success' ), 10, 2 );

		add_action( 'mycred_update_user_balance', array( &$this, 'reset_cache' ), 9999, 4 );
	}


	/**
	 * Add tab to account page
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	function account_tab( $tabs ) {
		$mycred_point_types = UM()->options()->get( 'mycred_point_types' );

		if ( empty( $mycred_point_types ) ) {
			return $tabs;
		}

		if ( count( $mycred_point_types ) > 1 ) {
			$label = __( 'My points', 'um-mycred' );
		} else {
			$mycred = mycred( $mycred_point_types[0] );
			$label = $mycred->template_tags_general( __( 'My %_plural%', 'um-mycred' ) );
		}

		$tabs[1000]['points']['icon'] = 'um-faicon-trophy';
		$tabs[1000]['points']['title'] = $label;
		$tabs[1000]['points']['submit_title'] = $label;
		$tabs[1000]['points']['show_button'] = false;

		return $tabs;
	}


	/**
	 * Content to account tab
	 *
	 * @param $output
	 *
	 * @return string
	 */
	function points_tab_content( $output ) {
		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );

		$mycred_referral_link = apply_filters( 'um_mycred_enable_referral_link', true );
		$user_id = get_current_user_id();

		$t_args = compact( 'mycred_referral_link', 'user_id' );
		$output = UM()->get_template( 'account_points.php', um_mycred_plugin, $t_args );

		return $output;
	}


	/**
	 * Errors/Success for transferring points
	 *
	 * @param $args
	 */
	function transfer_errors( $args ) {
		if ( ! empty( $_POST['mycred_transfer_uid'] ) && ! empty( $_POST['mycred_transfer_amount'] ) && ! empty( $_POST['mycred_transfer_type'] ) ) {

			$user = sanitize_text_field( $_POST['mycred_transfer_uid'] );
			$amount = sanitize_text_field( $_POST['mycred_transfer_amount'] );
			$point_type = sanitize_key( $_POST['mycred_transfer_type'] );

			// check if the current user can make points transfer
			if ( ! um_user( 'can_transfer_mycred' ) ) {
				$r = add_query_arg( array( 'err' => 'mycred_unauthorized', 'points' => $point_type ), UM()->account()->tab_link( 'points' ) );
				exit( wp_redirect( $r ) );
			}

			//convert amount here to support , and . as decimal separators
			$dotPos = strrpos( $amount, '.' );
			$commaPos = strrpos( $amount, ',' );
			$sep = ( ( $dotPos > $commaPos ) && $dotPos ) ? $dotPos :
				( ( ( $commaPos > $dotPos ) && $commaPos ) ? $commaPos : false );

			$string_array = str_split( $amount );

			if ( empty( $string_array[ $sep ] ) ) {
				$r = add_query_arg( 'err', 'mycred_invalid_amount', UM()->account()->tab_link( 'points' ) );
				exit( wp_redirect( $r ) );
			}

			if ( $sep ) {
				$sep = $string_array[ $sep ];

				if ( $sep === ',' ) {
					$amount = str_replace( '.', '', $amount );
					$amount = str_replace( ',', '.', $amount );
				} elseif ( $sep === '.' ) {
					$amount = str_replace( ',', '', $amount );
				}
			}

			$amount = floatval( $amount );

			if ( ! is_numeric( $amount ) ) {
				$r = add_query_arg( 'err', 'mycred_invalid_amount', UM()->account()->tab_link( 'points' ) );
				exit( wp_redirect( $r ) );
			}

			if ( $amount > UM()->myCRED()->points()->get_points_clean( get_current_user_id(), $point_type ) ) {
				$r = add_query_arg( 'err', 'mycred_not_enough_balance', UM()->account()->tab_link( 'points' ) );
				exit( wp_redirect( $r ) );
			}

			if ( is_numeric( $user ) ) {
				if ( $user == get_current_user_id() ) {
					$r = add_query_arg( array( 'err' => 'mycred_myself', 'points' => $point_type ), UM()->account()->tab_link( 'points' ) );
					exit( wp_redirect( $r ) );
				}
				if ( ! UM()->user()->user_exists_by_id( $user ) ) {
					$r = add_query_arg( 'err', 'mycred_invalid_user', UM()->account()->tab_link( 'points' ) );
					exit( wp_redirect( $r ) );
				}

				$user_id = $user;
			} else {
				if ( ! username_exists( $user ) && ! email_exists( $user ) ) {
					$r = add_query_arg( 'err', 'mycred_invalid_user', UM()->account()->tab_link( 'points' ) );
					exit( wp_redirect( $r ) );
				}

				if ( is_email( $user ) ) {
					$user_id = email_exists( $user );
				} else {
					$user_id = username_exists( $user );
				}
			}

			// check if user can receive points
			um_fetch_user( $user_id );
			if ( um_user( 'cannot_receive_mycred' ) ) {
				$r = add_query_arg( array( 'err' => 'mycred_cant_receive', 'points' => $point_type ), UM()->account()->tab_link( 'points' ) );
				exit( wp_redirect( $r ) );
			}

			UM()->myCRED()->points()->transfer( get_current_user_id(), $user_id, $amount, $point_type );
			$r = add_query_arg( array( 'updated' => 'mycred_transfer_done', 'points' => $point_type ), UM()->account()->tab_link( 'points' ) );
			exit( wp_redirect( $r ) );

		}
	}


	/**
	 * Custom Error Message on upgrade account page
	 *
	 * @param string $msg
	 * @param string $err
	 *
	 * @return string
	 */
	function custom_error( $msg, $err ) {
		if ( $err == 'mycred_invalid_amount' ) {
			$msg = __( 'Invalid amount.', 'um-mycred' );
		}

		if ( $err == 'mycred_invalid_user' ) {
			$msg = __( 'The user does not exist.', 'um-mycred' );
		}

		if ( $err == 'mycred_not_enough_balance' ) {
			$msg = __( 'You do not have enough balance.', 'um-mycred' );
		}

		if ( in_array( $err, array( 'mycred_cant_receive', 'mycred_myself', 'mycred_unauthorized' ) ) ) {
			$point_type_default = defined( 'MYCRED_DEFAULT_TYPE_KEY' ) ? MYCRED_DEFAULT_TYPE_KEY : 'mycred_default';
			$mycred = mycred( $point_type_default );

			if ( ! empty( $_GET['points'] ) ) {
				$mycred_point_types = UM()->options()->get( 'mycred_point_types' );

				if ( in_array( $point_type = sanitize_key( $_GET['points'] ), $mycred_point_types ) ) {
					$mycred = mycred( $point_type );
				}
			}

			if ( $err == 'mycred_cant_receive' ) {
				$msg = $mycred->template_tags_general( __( 'That user can not receive <span class="um-points-wrap">%_plural%</span>.', 'um-mycred' ) );
			}

			if ( $err == 'mycred_myself' ) {
				$msg = $mycred->template_tags_general( __( 'You can not transfer <span class="um-points-wrap">%_plural%</span> to yourself.', 'um-mycred' ) );
			}

			if ( $err == 'mycred_unauthorized' ) {
				$msg = $mycred->template_tags_general( __( 'You are not allowed to transfer <span class="um-points-wrap">%_plural%</span>.', 'um-mycred' ) );
			}
		}

		return $msg;
	}


	/**
	 * Custom Success Message on upgrade account page
	 *
	 * @param string $msg
	 * @param string $success
	 *
	 * @return string
	 */
	function custom_success( $msg, $success ) {
		if ( $success == 'mycred_transfer_done' ) {
			$point_type_default = defined( 'MYCRED_DEFAULT_TYPE_KEY' ) ? MYCRED_DEFAULT_TYPE_KEY : 'mycred_default';
			$mycred = mycred( $point_type_default );

			if ( ! empty( $_GET['points'] ) ) {
				$mycred_point_types = UM()->options()->get( 'mycred_point_types' );

				if ( in_array( $point_type = sanitize_key( $_GET['points'] ), $mycred_point_types ) ) {
					$mycred = mycred( $point_type );
				}
			}

			$msg = $mycred->template_tags_general( __( '%plural% transferred successfully', 'um-mycred' ) );
		}

		return $msg;
	}


	/**
	 * Reset user cached balance
	 *
	 * @param int $user_id
	 * @param $current_balance
	 * @param $amount
	 * @param $type
	 */
	function reset_cache( $user_id, $current_balance, $amount, $type ) {
		delete_option( "um_cache_userdata_{$user_id}" );
	}

}