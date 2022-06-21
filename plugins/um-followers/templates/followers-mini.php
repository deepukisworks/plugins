<?php
/**
 * Template for the UM Followers. The list of user followers
 *
 * Shortcode: [ultimatemember_followers]
 * Caller: method Followers_Shortcode->ultimatemember_followers()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-followers/followers-mini.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<script type="text/template" id="tmpl-um-followers-mini">
	<# if ( data.users.length > 0 ) { #>
		<# _.each( data.users, function( user, key, list ) { #>
			<div class="um-followers-m-user">
				<div class="um-followers-m-pic">
					<a href="{{{user.url}}}" class="um-tip-n" title="{{{user.name}}}">
						{{{user.avatar}}}
					</a>
				</div>
			</div>
		<# }) #>
		<# if ( data.all && data.all > 0 ) { #>
			<div class="um-follow-show-all um-followers-m-user">
				+{{{data.all}}}
			</div>
		<# } #>
	<# } else { #>
		<p>
			<?php echo ( $user_id == get_current_user_id() ) ? __( 'You do not have any followers yet.', 'um-followers' ) : __( 'This user do not have any followers yet.', 'um-followers' ); ?>
		</p>
	<# } #>
</script>

<div class="um-followers-widget um-followers-m" data-max="<?php echo esc_attr( $max ); ?>"
     data-user_id="<?php echo esc_attr( $user_id ); ?>" data-type="followers">

	<div class="um-followers-widget-overlay"><div class="um-ajax-loading"></div></div>
	<div class="um-followers-widget-wrapper"></div>

</div>
<div class="um-clear"></div>