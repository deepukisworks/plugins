<?php
/**
 * Template for the UM Followers. The list of user following
 *
 * Shortcode: [ultimatemember_following]
 * Caller: method Followers_Shortcode->ultimatemember_following()
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-followers/following-mini.php
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} ?>

<script type="text/template" id="tmpl-um-following-mini">
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
			<?php echo ( $user_id == get_current_user_id() ) ? __( 'You did not follow anybody yet.', 'um-followers' ) : __( 'This user did not follow anybody yet.', 'um-followers' ); ?>
		</p>
	<# } #>
</script>

<div class="um-followers-widget um-followers-m" data-max="<?php echo esc_attr( $max ); ?>"
     data-user_id="<?php echo esc_attr( $user_id ); ?>" data-type="following">

	<div class="um-followers-widget-overlay"><div class="um-ajax-loading"></div></div>
	<div class="um-followers-widget-wrapper"></div>

</div>
<div class="um-clear"></div>