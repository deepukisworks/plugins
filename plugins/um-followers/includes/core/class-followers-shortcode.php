<?php
namespace um_ext\um_followers\core;


// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Followers_Shortcode
 * @package um_ext\um_followers\core
 */
class Followers_Shortcode {


	/**
	 * Followers_Shortcode constructor.
	 */
	function __construct() {
		add_shortcode( 'ultimatemember_followers', array( &$this, 'ultimatemember_followers' ) );
		add_shortcode( 'ultimatemember_following', array( &$this, 'ultimatemember_following' ) );

		add_shortcode( 'ultimatemember_followers_bar', array( &$this, 'ultimatemember_followers_bar' ) );
	}


	/**
	 * Follow bar Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_followers_bar( $args = array() ) {

		$defaults = array(
			'user_id' => um_profile_id()
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $user_id
		 */
		extract( $args );

		$active_tab = UM()->profile()->active_tab();

		$can_follow = UM()->Followers_API()->api()->can_follow( $user_id, get_current_user_id() );

		$can_view = true;
		if ( ! is_user_logged_in() || get_current_user_id() != $user_id ) {
			$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Followers', 'um-followers' ) );
			$is_private_case = UM()->user()->is_private_case( $user_id, 'follower' );
			if ( $is_private_case || $is_private_case_old ) { // only followers can view my profile
				$can_view = false;
			}
			$is_private_case_old = UM()->user()->is_private_case( $user_id, __( 'Only people I follow can view my profile', 'um-followers' ) );
			$is_private_case = UM()->user()->is_private_case( $user_id, 'followed' );
			if ( $is_private_case || $is_private_case_old ) { // only people i follow can view my profile
				$can_view = false;
			}
		}

		$output = '';
		if ( $can_follow || $can_view ) {
			$t_args = compact( 'active_tab', 'can_follow', 'can_view', 'user_id' );
			$output .= UM()->get_template( 'bar.php', um_followers_plugin, $t_args );

			wp_enqueue_style( 'um_followers' );
			wp_enqueue_script( 'um_followers' );
		}

		return $output;
	}


	/**
	 * Followers Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_followers( $args = array() ) {
		$defaults = array(
			'user_id' => um_profile_id(),
			'style'   => 'default',
			'max'     => '',
		);

		$args = shortcode_atts( $defaults, $args );

		/**
		 * @var $user_id;
		 * @var $style;
		 * @var $max;
		 */
		extract( $args );

		wp_enqueue_style( 'um_followers' );
		wp_enqueue_script( 'um_followers' );

		$tpl = 'followers';
		if ( $style == 'avatars' ) {
			$t_args = array(
				'user_id' => $args['user_id'],
				'max'     => $args['max'],
			);
			$tpl .= '-mini';
		} else {
			$followers = UM()->Followers_API()->api()->followers( um_user( 'ID' ), $args );

			$t_args = array(
				'user_id'   => $args['user_id'],
				'followers' => $followers,
			);
		}

		$output = UM()->get_template( $tpl . '.php', um_followers_plugin, $t_args );

		return $output;
	}


	/**
	 * Following Shortcode
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_following( $args = array() ) {
		$defaults = array(
			'user_id' => um_profile_id(),
			'style'   => 'default',
			'max'     => '',
		);
		$args = shortcode_atts( $defaults, $args );

		/**
		 * @var $user_id;
		 * @var $style;
		 * @var $max;
		 */
		extract( $args );

		wp_enqueue_style( 'um_followers' );
		wp_enqueue_script( 'um_followers' );

		$tpl = 'following';
		if ( $style == 'avatars' ) {
			$t_args = array(
				'user_id' => $args['user_id'],
				'max'     => $args['max'],
			);
			$tpl .= '-mini';
		} else {
			$following = UM()->Followers_API()->api()->following( um_user( 'ID' ), $args );
			$t_args = array(
				'user_id'   => $args['user_id'],
				'following' => $following,
			);
		}

		$output = UM()->get_template( $tpl . '.php', um_followers_plugin, $t_args );

		return $output;
	}
}