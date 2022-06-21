<?php
/**
 * Template for the single folder view dropdown
 *
 * Used:   Profile page > Bookmarks tab > folder
 * Parent: profile/single-folder.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/single-folder/dropdown.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  string $key
 * @var  int    $user
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-trigger-menu-on-click" style="float:right; position:relative;">
    <a href="javascript:void(0);" class="um-user-bookmarks-folder-edit" data-folder_key="<?php echo esc_attr( $key ); ?>" data-profile="<?php echo esc_attr( $user_id ) ?>" data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_folder_edit' ); ?>">
						<img src="/wp-content/uploads/2021/11/image-38.png">
					</a>
	<!--<a href="javascript:void(0);" class="um-profile-edit-folder-a">
		<img src="/wp-content/uploads/2021/11/image-38.png">
	</a>-->

	<div class="um-clear"></div>

	<div class="um-dropdown um-user-bookmarks-dropdown" data-element="a.um-profile-edit-folder-a" style="top:30px; width: 200px; right:0; left: auto; text-align: center; display: none;">
		<div class="um-dropdown-b">
			<div class="um-dropdown-arr" style="top:-17px;right:0;left: auto;">
				<i class="um-icon-arrow-up-b"></i>
			</div>
			<ul>
				<li>
					<a href="javascript:void(0);" class="um-user-bookmarks-folder-edit" data-folder_key="<?php echo esc_attr( $key ); ?>" data-profile="<?php echo esc_attr( $user_id ) ?>" data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_folder_edit' ); ?>">
						<?php _e( 'Edit', 'um-user-bookmarks' ) ?>
					</a>
				</li>
				<li>
					<a href="javascript:void(0);" class="um-user-bookmarks-folder-delete" data-folder_key="<?php echo esc_attr( $key ); ?>" data-alert_text="<?php esc_attr_e( 'Delete folder and its content?', 'um-user-bookmarks' ); ?>" data-profile="<?php echo esc_attr( $user_id ) ?>" data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_folder_delete' ); ?>" data-callback-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_back' ); ?>">
						<?php _e( 'Delete', 'um-user-bookmarks' ) ?>
					</a>
				</li>
				<li>
					<a href="javascript:void(0);" class="um-user-bookmarks-dropdown-hide">
						<?php _e( 'Cancel', 'um-user-bookmarks' ); ?>
					</a>
				</li>
			</ul>
		</div>
	</div>
</div>