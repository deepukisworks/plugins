<?php
/**
 * Template for the UM myCRED.
 * Used on Account page, My Points tab
 *
 * Caller: method myCRED_Account->points_tab_content()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-mycred/account_points.php
 */
if ( ! defined( 'ABSPATH' ) ) exit;

$mycred_types = mycred_get_types();
$mycred_point_types = UM()->options()->get( 'mycred_point_types' );

if ( ! empty( $mycred_point_types ) ) { ?>

	<div class="um-field um-mycred-account-col" data-key="">
		<div class="um-field-label"><strong><?php _e( 'My Balance', 'um-mycred' ); ?></strong></div>

		<?php foreach ( $mycred_point_types as $point_type ) { ?>
			<div class="um-field-area">
				<label class="um-points-label"><?php echo UM()->myCRED()->points()->get_points( $user_id, $point_type ); ?></label>
			</div>
		<?php } ?>
	</div>

<?php }

if ( um_user( 'can_transfer_mycred' ) ) {

	add_filter( 'number_format_i18n', array( UM()->myCRED()->points(), 'number_format' ), 10, 3 ); ?>

	<div class="um-field um-mycred-account-col" data-key="">
		<div class="um-field-label"><strong><?php _e( 'Transfer Balance', 'um-mycred' ); ?></strong></div>
		<div class="um-field-area">
			<p><?php _e( 'Enter the user to which you want to transfer', 'um-mycred' ); ?></p>
			<div class="um-field-area">
				<input type="text" name="mycred_transfer_uid" placeholder="<?php esc_attr_e( 'Username, e-mail, or ID', 'um-mycred' ); ?>" class="um-mycred-input" />
			</div>

			<p>
				<?php
				if ( count( $mycred_point_types ) > 1 ) {
					_e( 'You can transfer points to another user. ', 'um-mycred' );
					_e( 'Select a point type:', 'um-mycred' );
				} elseif ( count( $mycred_point_types ) === 1 ) {
					$point_type = current( $mycred_point_types );
					printf( __( 'You can transfer %s to another user. ', 'um-mycred' ), mycred_get_point_type_name( $point_type, false ) );
					printf( __( 'You have %s now.', 'um-mycred' ), UM()->myCRED()->points()->get_points( $user_id, $point_type ) );
				} else {
					_e( 'You can not transfer points.', 'um-mycred' );
				}
				?>
			</p>

			<?php if ( count( $mycred_point_types ) > 1 ) { ?>
				<div class="um-field-area">
					<select id="mycred_transfer_type" name="mycred_transfer_type" class="um-form-field required um-s2 um-mycred-select" placeholder="<?php esc_attr_e( 'Point type', 'um-mycred' ); ?>" style="width: 100%;">

						<?php foreach ( $mycred_point_types as $point_type ) { ?>
							<option value="<?php echo esc_attr( $point_type ); ?>"><?php printf( __( '%s (up to %s)', 'um-mycred' ), $mycred_types[ $point_type ], UM()->myCRED()->points()->get_points( $user_id, $point_type, 'suffix' ) ); ?></option>
						<?php } ?>

					</select>
				</div>
			<?php } elseif ( count( $mycred_point_types ) === 1 ) { ?>
				<input type="hidden" name="mycred_transfer_type" value="<?php echo esc_attr( current( $mycred_point_types ) ); ?>" />
			<?php } ?>

			<p><?php _e( 'Enter amount below:', 'um-mycred' ); ?></p>
			<div class="um-field-area">
				<input type="text" name="mycred_transfer_amount" placeholder="<?php echo number_format_i18n( 0, UM()->options()->get( 'mycred_decimals' ) ) ?>" class="um-mycred-amount" />

				<input type="hidden" name="um_account_nonce_points" value="<?php echo esc_attr( wp_create_nonce( 'um_account_nonce_points' ) ); ?>" />
				<input type="submit" name="um_account_submit" id="um_account_submit_mycred_transfer" value="<?php esc_attr_e( 'Confirm Transfer', 'um-mycred' ); ?>" class="um-mycred-send-points um-button" />
			</div>
			<p><?php _e( 'This is not reversible once you click confirm transfer.', 'um-mycred' ); ?></p>

		</div>
	</div>

	<?php remove_filter( 'number_format_i18n', array( UM()->myCRED()->points(), 'number_format' ) );
}

if ( UM()->options()->get( 'mycred_refer' ) && $mycred_referral_link && function_exists( 'mycred_render_affiliate_link' ) ) { ?>

	<div class="um-field um-mycred-account-col" data-key="">
		<div class="um-field-label"><strong><?php _e( 'My Referral Link', 'um-mycred' ); ?></strong></div>
		<div class="um-field-area">
			<?php if ( version_compare( get_bloginfo('version'),'5.4', '<' ) ) { ?>
                <a href="<?php echo do_shortcode( '[mycred_affiliate_link url="' . get_bloginfo( 'url' ) . '"]' ); ?>" target="_blank"><?php echo do_shortcode( '[mycred_affiliate_link url="' . get_bloginfo( 'url' ) . '"]' ); ?></a>
			<?php } else { ?>
                <a href="<?php echo apply_shortcodes( '[mycred_affiliate_link url="' . get_bloginfo( 'url' ) . '"]' ); ?>" target="_blank"><?php echo apply_shortcodes( '[mycred_affiliate_link url="' . get_bloginfo( 'url' ) . '"]' ); ?></a>
			<?php } ?>
		</div>
	</div>

	<?php
}