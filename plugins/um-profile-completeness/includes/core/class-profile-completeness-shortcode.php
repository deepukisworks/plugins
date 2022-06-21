<?php
namespace um_ext\um_profile_completeness\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Profile_Completeness_Shortcode
 * @package um_ext\um_profile_completeness\core
 */
class Profile_Completeness_Shortcode {


	/**
	 * Profile_Completeness_Shortcode constructor.
	 */
	function __construct() {

		add_shortcode( 'ultimatemember_profile_completeness', array( &$this, 'ultimatemember_profile_completeness' ) );
		add_shortcode( 'ultimatemember_profile_progress_bar', array( &$this, 'ultimatemember_profile_progress_bar' ) );
		add_shortcode( 'um_profile_completeness_show_content', array( &$this, 'show_content' ) );
		add_shortcode( 'um_profile_completeness_related_text', array( &$this, 'completeness_related_text' ) );

	}


	/**
	 * Shortcode: Profile Completeness related text
	 *
	 * @param array $atts
	 *		$atts['logic']	string	'greater', 'less' or 'equals'
	 *		$atts['value']	int			from 0 to 99
	 * @param string $shortcode_content
	 *
	 * @return string
	 */
	function completeness_related_text( $atts, $shortcode_content = null ) {

		$args = shortcode_atts( array(
			'logic' => 'less',
			'value' => '50'
		), $atts );

		if ( ! defined( 'um_profile_completeness_version' ) ) {
			return '';
		}

		if ( ! is_user_logged_in() ) {
			return '';
		}

		$requested_user_id = um_get_requested_user() ? um_get_requested_user() : get_current_user_id();
		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $requested_user_id );
		if ( ! $result || $result['progress'] >= 100 ) {
			return '';
		}

		$show = false;
		switch ( $args['logic'] ) {
			case 'greater':
				if ( $result['progress'] > $args['value'] ) {
					$show = true;
				}
				break;

			case 'less':
				if ( $result['progress'] < $args['value'] ) {
					$show = true;
				}
				break;

			case 'equals':
				if ( $result['progress'] == $args['value'] ) {
					$show = true;
				}
				break;

			default:
				$show = false;
				break;
		}

		return $show ? $shortcode_content : '';
	}


	/**
	 * Bar only widget
	 *
	 * @param array $args
	 * @return string
	 */
	function ultimatemember_profile_progress_bar( $args = array() ) {
		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );

		$defaults = array(
			'user_id' => um_profile_id(),
			'who'     => 'loggedin',
		);
		$args = wp_parse_args( $args, $defaults );

		/**
		 * @var $user_id
		 * @var $who
		 */
		extract( $args );

		if ( empty( $user_id ) && $who == 'loggedin' ) {
			$user_id = um_profile_id();
		}

		if ( UM()->is_ajax() ) {
			if ( $user_id != get_current_user_id() && ! UM()->roles()->um_user_can('can_edit_everyone') ) {
				return '';
			}
		} else {
			if ( in_array( $who, array( 'loggedin', 'admin' ) ) && um_profile_id() && $user_id != um_profile_id() ) {
				return '';
			}
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $user_id );

		if ( ! $result || $result['progress'] >= 100 ) {
			return '';
		}

		return $result['bar'];
	}


	/**
	 * Completeness widget
	 *
	 * @param array $args
	 *
	 * @return string
	 */
	function ultimatemember_profile_completeness( $args = array() ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}
		if ( um_profile_id() != get_current_user_id() ) {
			return '';
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( um_profile_id() );
		if ( ! $result || $result['progress'] >= 100 ) {
			return '';
		}

		do_action( 'um_profile_completeness_shortcode_loaded' );

		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );

		$t_args = compact( 'args', 'result' );

		$output = UM()->get_template( 'js-widget.php', um_profile_completeness_plugin, $t_args );

		return $output;
	}


	/**
	 * re-order profile completion steps
	 *
	 * @param $steps
	 *
	 * @return mixed
	 */
	function reorder( $steps ) {
		if ( isset( $steps['profile_photo'] ) ) {
			$value = $steps['profile_photo'];
			unset( $steps['profile_photo'] );
			$steps['profile_photo'] = $value;
		}
		if ( isset( $steps['cover_photo'] ) ) {
			$value = $steps['cover_photo'];
			unset( $steps['cover_photo'] );
			$steps['cover_photo'] = $value;
		}

		return $steps;
	}


	/**
	 * Show content on specific progress
	 *
	 * @param array $atts
	 * @param string $content
	 *
	 * @return string
	 */
	function show_content( $atts = array(), $content = '' ) {
		if ( ! is_user_logged_in() ) {
			return '';
		}

		$a = shortcode_atts( array(
			'user_id'  => get_current_user_id(),
			'who'      => 'loggedin',
			'role'     => um_user( 'role' ),
			'progress' => 100,
			'not'      => false,
		), $atts );

		if ( $a['who'] == 'current_profile' ) {
			$a['user_id'] = um_profile_id();
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $a['user_id'] );

		if ( ! $result ) {
			return '';
		}

		wp_enqueue_script( 'um_profile_completeness' );
		wp_enqueue_style( 'um_profile_completeness' );

		if ( $a['not'] ) {
			if ( $a['role'] == um_user( 'role' ) && $result['progress'] != $a['progress'] ) {
				if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) {
					return do_shortcode( $content );
				} else {
					return apply_shortcodes( $content );
				}
			}
		} elseif ( $result['progress'] == $a['progress'] && $a['role'] == um_user( 'role' ) ) {
			if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) {
				return do_shortcode( $content );
			} else {
				return apply_shortcodes( $content );
			}
		}

		return '';
	}


	/**
	 * Get progress result
	 *
	 * @param  int $user_id  The user ID number
	 * @return bool|array
	 */
	function profile_progress( $user_id ) {
		static $data = [];
		if ( isset( $data[ $user_id ] ) ) {
			return $data[ $user_id ];
		}

		$progress = UM()->Profile_Completeness_API()->get_progress( $user_id );
		if ( $progress == -1 ) {
			return false;
		}

		$t_args = compact( 'progress', 'user_id' );
		$bar = UM()->get_template( 'completeness-bar.php', um_profile_completeness_plugin, $t_args );

		$profile_progress_defaults = apply_filters( 'um_profile_completeness_profile_progress_defaults', [
			'progress'                      => 0,
			'req_progress'                  => 100,
			'steps'                         => [],
			'completed'                     => [],
			'prevent_browse'                => 0,
			'prevent_browse_exclude_pages'  => '',
			'prevent_browse_redirect'       => 0,
			'prevent_browse_redirect_url'   => '',
			'prevent_profileview'           => 0,
			'prevent_comment'               => 0,
			'bar'                           => $bar,
		], $user_id );

		$output = array_merge( $profile_progress_defaults, $progress );

		$data[ $user_id ] = apply_filters( 'um_profile_completeness_progress_output', $output, $progress );

		return $data[ $user_id ];
	}
}