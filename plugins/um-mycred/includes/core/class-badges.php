<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Badges
 */
class Badges {


	/**
	 * Badges constructor.
	 */
	function __construct() {
	}


	/**
	 * Show all badges
	 *
	 * @param int $template
	 *
	 * @return string
	 */
	function show_all( $template = 1 ) {
		global $mycred;

		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return '';
		}

		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );

		$size = UM()->options()->get( 'mycred_badge_size' );

		if ( $template == 1 ) {

			if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) {
				return do_shortcode( '[mycred_badges title="0" requires="0" show="main" width="' . $size . '" height="' . $size . '"]' );
			} else {
				return apply_shortcodes( '[mycred_badges title="0" requires="0" show="main" width="' . $size . '" height="' . $size . '"]' );
			}

		} elseif ( $template == 2 ) {
			$output = '';

			$all_badges = mycred_get_badge_ids();
			$point_types = mycred_get_types( true );
			$references = mycred_get_all_references();

			if ( ! empty( $all_badges ) ) {
				$output = UM()->get_template( 'badges.php', um_mycred_plugin, compact(
					'mycred',
					'size',
					'all_badges',
					'point_types',
					'references'
				));
			}

			return $output;
		}

		return '';
	}


	/**
	 * Show badges of user
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	function show( $user_id ) {

		if ( ! function_exists( 'mycred_get_users_badges' ) ) {
			return '';
		}

		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );

		$output = '';
		$size = UM()->options()->get( 'mycred_badge_size' );

		$users_badges = mycred_get_users_badges( $user_id );
		$all_badges = mycred_get_badge_ids();

		if ( ! empty( $all_badges  ) ) {

			$output .= '<span class="um-badges">';
			$current_assigned_badges = 0;
			foreach ( $all_badges as $badge_id ) {
				$image_identification = false;

				if ( array_key_exists( $badge_id, $users_badges ) ) {
					$level = $users_badges[ $badge_id ];
					$badge = mycred_get_badge( $badge_id, $level );
					$image_identification = $badge->levels[ $level ]['image_url'];

					if ( $badge->levels[ $level ]['attachment_id'] > 0 ) {
						$image_identification = $badge->levels[ $level ]['attachment_id'];
						$level_title = $badge->levels[ $level ]['label'];

						if ( ! empty( $level_title  ) ) {
							$badge->title = "{$badge->title} - {$level_title}";
						} else {
							$level++;
							$badge->title = "{$badge->title} - Level {$level}";
						}
					}
				}

				if ( $image_identification ) {
					$image_url = wp_get_attachment_url( $image_identification );
					$title = apply_filters( 'um_mycred_badge_loop_title', $badge->title, $badge );

					$output .= '<span class="the-badge">';
					$output .= '<img src="' . esc_url( $image_url ) . '" title="' . esc_attr( $title ) . '" alt="' . esc_attr( $title ) . '" width="' . esc_attr( $size ) . '" height="' . esc_attr( $size ) . '" class="mycred-badge earned um-tip-n" />';
					$output .= '</span>';
					$current_assigned_badges++;
				}

			} // endforeach

			$output .= '</span>';

			if ( $current_assigned_badges <= 0 ) {
				$output = '';
			}
		}

		return $output;
	}
}