<?php
/**
 * Template for the profile tab "Bookmarks"
 *
 * Used:  Profile page > Bookmarks tab
 * Call:  UM()->User_Bookmarks()->profile()->get_user_profile_bookmarks();
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/folder-view.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  bool   $include_private
 * @var  int    $profile_id
 * @var  array  $user_bookmarks
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>
<script type="text/javascript">
		$(document).ready(function() {
			$('.um-user-bookmarks-profile-add-folder-holder').each(function() {
					$(this).insertAfter($(this).parent().find('.book-empt-back'));
				});
		});
</script>		
<?php if ( is_user_logged_in() && get_current_user_id() == um_profile_id() ) {
	UM()->get_template( 'profile/folder-view/add-folder.php', um_user_bookmarks_plugin, array(
		'folder_text'   => UM()->User_Bookmarks()->get_folder_text(),
	), true );
}

if ( $user_bookmarks ) {

	if ( ! $profile_id ) {
		$profile_id = um_profile_id();
	}

	foreach ( $user_bookmarks as $key => $value ) {
		if ( ! $include_private && $value['type'] == 'private' ) {
			continue;
		}

		$count = 0;
		if ( isset( $value['bookmarks'] ) && count( $value['bookmarks'] ) ) {
			$count = count( $value['bookmarks'] );
		}

		UM()->get_template( 'profile/folder-view/folder.php', um_user_bookmarks_plugin, array(
			'profile_id'    => $profile_id,
			'key'           => $key,
			'folder'        => $value,
			'count'         => $count,
		), true );
	} ?>

	<div class="um-clear"></div>

<?php } else { ?>
    <div class="book-empt-back">
		<a href="javascript:void(0);" class="um-user-bookmarks-back-btn" data-profile="<?php echo esc_attr( $user ); ?>" data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_back' ); ?>" style="width:5%;float:left;display:inline-block;text-align:center;">
			<img src="/wp-content/uploads/2021/11/back.png">
		</a>
	</div>
	<div class="user-bookmar-empty">
		<img class="empty-img" src="/wp-content/uploads/2021/11/magic-box-4.png">
		<?php _e( 'Your bookmarks list is empty!', 'um-user-bookmarks' ); ?>
		<p>Explore <span class="book-empty-smart">BEE<b>SMART</b></span> and add post to bookmarks to show them here.</p>
	</div>	
<?php } ?>

