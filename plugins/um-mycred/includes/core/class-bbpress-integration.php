<?php namespace um_ext\um_mycred\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class bbPress_Integration
 */
class bbPress_Integration {


	/**
	 * bbPress_Integration constructor.
	 */
	function __construct() {
		add_action( 'um_bbpress_theme_after_reply_author_details', array( &$this, 'hide_role' ) );
		add_action( 'um_bbpress_theme_after_reply_author_details', array( &$this, 'show_points' ) );
		add_action( 'um_bbpress_theme_after_reply_author_details', array( &$this, 'show_rank' ) );
		add_action( 'um_bbpress_theme_after_reply_author_details', array( &$this, 'show_rank_bar' ) );
	}


	/**
	 * Hide role
	 */
	function hide_role() {
		if ( ! UM()->options()->get( 'mycred_hide_role' ) ) {
			return;
		} ?>

		<style type="text/css">
			div.bbp-author-role {display: none !important}
		</style>

		<?php
	}


	/**
	 * Show points
	 */
	function show_points() {
		if ( ! UM()->options()->get( 'mycred_show_bb_points' ) ) {
			return;
		}

		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );

		$reply_author_id = get_post_field( 'post_author', bbp_get_reply_id() );

		$mycred_point_types = UM()->options()->get( 'mycred_point_types' );
		if ( ! empty( $mycred_point_types ) ) {
			foreach ( $mycred_point_types as $point_type ) { ?>
				<div class="um-mycred-bb-points">
					<?php echo UM()->myCRED()->points()->get_points( $reply_author_id, $point_type ); ?>
				</div>
			<?php }
		}
	}


	/**
	 * Show rank
	 */
	function show_rank() {
		if ( ! UM()->options()->get( 'mycred_show_bb_rank' ) ) {
			return;
		}
		if ( ! function_exists( 'mycred_get_users_rank' ) ) {
			return;
		}
		$reply_author_id = get_post_field( 'post_author', bbp_get_reply_id() );
		$rank = mycred_get_users_rank( $reply_author_id );

		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );

		// If the user has a rank, $rank will be an object
		if ( is_object( $rank ) ) { ?>
			<div class="um-mycred-bb-rank"><?php echo esc_html( $rank->title ) ?></div>
		<?php }
	}


	/**
	 * Show progress
	 */
	function show_rank_bar() {
		if ( ! UM()->options()->get( 'mycred_show_bb_progress' ) ) {
			return;
		}

		if ( ! function_exists('mycred_get_users_rank') ) {
			return;
		}

		wp_enqueue_script( 'um_mycred' );
		wp_enqueue_style( 'um_mycred' );

		$user_id = get_post_field( 'post_author', bbp_get_reply_id() );
		$rank = mycred_get_users_rank( $user_id );

		$t_args = compact( 'rank', 'user_id' );
		UM()->get_template( 'rank_bar.php', um_mycred_plugin, $t_args, true );
	}


}