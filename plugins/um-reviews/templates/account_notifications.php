<?php
/**
 * Template for the UM User Reviews.
 * Used on the "Account" page, "Notifications" tab
 *
 * Caller: method Reviews_Account->account_tab()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-reviews/account_notifications.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>


<div class="um-field" data-key="">
	<div class="um-field-label"><strong><?php _e( 'User Reviews', 'um-reviews' ); ?></strong></div>

	<?php if ( $show_new_reviews ) { ?>
		<div class="um-field-area">
			<label class="um-field-checkbox<?php if ( ! empty( $_enable_new_reviews ) ) { ?> active<?php } ?>">
				<input type="checkbox" name="_enable_new_reviews" value="1" <?php checked( ! empty( $_enable_new_reviews ) ) ?> />
				<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-<?php if ( ! empty( $_enable_new_reviews ) ) { ?>outline<?php } else { ?>outline-blank<?php } ?>"></i></span>
				<span class="um-field-checkbox-option"><?php echo __( 'I have got a new review', 'um-reviews' ); ?></span>
			</label>
			<div class="um-clear"></div>
		</div>
	<?php }

	if ( $show_new_reviews_reply ) { ?>
		<div class="um-field-area">
			<label class="um-field-checkbox<?php if ( ! empty( $_enable_new_reviews_reply ) ) { ?> active<?php } ?>">
				<input type="checkbox" name="_enable_new_reviews_reply" value="1" <?php checked( ! empty( $_enable_new_reviews_reply ) ) ?> />
				<span class="um-field-checkbox-state"><i class="um-icon-android-checkbox-<?php if ( ! empty( $_enable_new_reviews_reply ) ) { ?>outline<?php } else { ?>outline-blank<?php } ?>"></i></span>
				<span class="um-field-checkbox-option"><?php echo __( 'I have got a new review reply', 'um-reviews' ); ?></span>
			</label>
			<div class="um-clear"></div>
		</div>
	<?php } ?>
</div>