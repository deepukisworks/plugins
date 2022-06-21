<?php
/**
 * Template that displays a bar with a number of followers and following
 *
 * Call: UM()->Followers_API()->shortcode()->ultimatemember_followers_bar( $args )
 * Shortcode: [ultimatemember_followers_bar]
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-followers/bar.php
 *
 * @var string  $active_tab  The current profile tab
 * @var boolean $can_follow  TRUE if current user can follow this user
 * @var boolean $can_view    TRUE if current user can view this user
 * @var int     $user_id     The user identification number
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-followers-links">
	<?php if ( $can_view ) { ?>
		<div class="um-followers-rc">
			<a href="<?php echo esc_url( UM()->Followers_API()->api()->followers_link( $user_id ) ); ?>" class="<?php if ( $active_tab == 'followers' ) { echo 'current'; } ?>"><?php _e( 'followers', 'um-followers' ); ?><?php echo UM()->Followers_API()->api()->count_followers( $user_id ); ?></a>
		</div>

		<div class="um-followers-rc">
			<a href="<?php echo esc_url( UM()->Followers_API()->api()->following_link( $user_id ) ); ?>" class="<?php if ( $active_tab == 'following' ) { echo 'current'; } ?>"><?php _e( 'following', 'um-followers' ); ?><?php echo UM()->Followers_API()->api()->count_following( $user_id ); ?></a>
		</div>
	<?php } ?>

	<?php if ( $can_follow ) { ?>
		<div class="um-followers-btn">
			<?php echo UM()->Followers_API()->api()->follow_button( $user_id, get_current_user_id() ); ?>
		</div>
	<?php } ?>
</div>