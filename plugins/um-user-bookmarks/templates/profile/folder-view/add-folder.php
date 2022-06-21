<?php
/**
 * Template for the form "Add folder"
 *
 * Used:   Profile page > Bookmarks tab
 * Parent: profile/folder-view.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/folder-view/add-folder.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  string $folder_text
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="um-user-bookmarks-profile-add-folder-holder">
    <div class="bookmark-main-set">
        <div class="bookmarks-name">
		   <h1>Bookmarks</h1> 
		</div>
		<div class="um-clear" style="text-align:center;">
			<br />
			<a href="javascript:void(0);" id="um-bookmarks-profile-add-folder" class="um-modal-btn alt">
				+ <?php echo esc_html( sprintf( __( 'Add %s', 'um-user-bookmarks' ), $folder_text ) ); ?>
			</a>
		</div>
    </div>
	<form id="um-user-bookmarks-profile-add-folder-form">
		<div class="um_bookmarks_table new-um-user-bookmarks-folder-tbl">
			<div class="um_bookmarks_tr bookmarks_add_folder">
           
				<div class="um_bookmarks_td">
					<input type="text" class="form-control" placeholder="..." name="_um_user_bookmarks_folder" required />
					<small class="error-message"><?php echo esc_html( sprintf( __( '%s name is required.', 'um-user-bookmarks' ), $folder_text ) ); ?></small>
				</div>

				<div class="um_bookmarks_td">
                <a class="ml-3 d-block m-0"><img width="40" src="/wp-content/uploads/2022/05/Visibility.png" class="disable"></a>
					<input id="um_user_bookmarks_access_type_checkbox" type="checkbox" name="is_private" value="1" />
					<label for="um_user_bookmarks_access_type_checkbox"><?php _e( 'Private', 'um-user-bookmarks' ); ?></label>
				</div>

				<div class="um_bookmarks_td">
					<button class="um_user_bookmarks_profile_create_folder_btn um-modal-btn" type="button"> </button>
				</div>
			</div>
		</div>

		<?php wp_nonce_field( 'um_user_bookmarks_new_bookmark_folder' ); ?>
		<input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>" />
		<input type="hidden" name="action" value="um_bookmarks_folder_add" />

		<div class="form-response" style="text-align:center;color:#ab1313;"></div>
	</form>
	
</div>