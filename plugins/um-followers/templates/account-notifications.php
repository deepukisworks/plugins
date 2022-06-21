<?php
/**
 * Template for the UM Followers.
 *
 * Page: Account > Notifications
 * Call: um_followers_account_tab()
 * Hook: 'um_account_content_hook_notifications'
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-followers/account-notifications.php
 */
if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div class="um-field" data-key="">
	<div class="um-field-label"><strong><?php _e( 'Followers', 'um-followers' ); ?></strong></div>
	<div class="um-field-area">
		<label class="um-field-checkbox<?php if ( ! empty( $_enable_new_follow ) ) { ?> active<?php } ?>">
			<input type="checkbox" name="_enable_new_follow" value="1" <?php checked( ! empty( $_enable_new_follow ) ) ?> />
			<span class="um-field-checkbox-state">
				<i class="um-icon-android-checkbox-<?php if ( ! empty( $_enable_new_follow ) ) { ?>outline<?php } else { ?>outline-blank<?php } ?>"></i>
			</span>
			<span class="um-field-checkbox-option"><?php _e( 'I\'m followed by someone new', 'um-followers' ); ?></span>
		</label>
		<div class="um-clear"></div>
	</div>
</div>