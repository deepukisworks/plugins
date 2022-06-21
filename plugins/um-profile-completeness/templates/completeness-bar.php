<?php
/**
 * Template for the UM Profile Completeness, "Profile Progress" widget
 *
 * Called from the Profile_Completeness_Shortcode->profile_progress() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-profile-completeness/completeness-bar.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;
?>

<span class="um-completeness-bar um-tip-n" data-user_id="<?php echo esc_attr( $user_id ) ?>" title="<?php echo esc_attr( sprintf( __( '%s Complete', 'um-profile-completeness' ), $progress['progress'] . '%' ) ); ?>">
	<span class="um-completeness-done" style="width:<?php echo (int) $progress['progress']; ?>%;"></span>
	<?php for ( $i = 0; $i <= 9; $i++ ) { ?>
		<span class="um-completeness-i" style="left: <?php echo intval( $i * 10 ); ?>%;"></span>
	<?php } ?>
</span>