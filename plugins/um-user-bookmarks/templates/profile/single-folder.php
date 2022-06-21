<?php
/**
 * Template for the single folder view
 *
 * Used:  Profile page > Bookmarks tab > folder
 * Call:  UM()->User_Bookmarks()->profile()->get_user_profile_bookmarks();
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/single-folder.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  string $key
 * @var  string $title
 * @var  int    $user
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$bookmarks = array();
$user_bookmarks = get_user_meta( $user, '_um_user_bookmarks', true );
if ( $user_bookmarks && isset( $user_bookmarks[ $key ] ) && ! empty( $user_bookmarks[ $key ]['bookmarks'] ) ) {
	$bookmarks = array_keys( $user_bookmarks[ $key ]['bookmarks'] );
}
?>

<header style="width:100%;display:block;position:relative;">
    <div class="location-folder-opened">
	<a href="javascript:void(0);" class="um-user-bookmarks-back-btn" data-profile="<?php echo esc_attr( $user ); ?>" data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_back' ); ?>" style="width:5%;float:left;display:inline-block;text-align:center;">
		<img src="/wp-content/uploads/2021/11/back.png">
	</a>
    <h1>Bookmarks</h1><span>/</span><h3 style="width:89%;float:none;text-align:center;margin:0;display:inline-block;"><?php echo esc_html( $title ); ?></h3>
    </div>
	<?php if ( empty( $bookmarks ) ) { ?>
	        <div class="um-user-bookmarks-profile-add-folder-holder">
				<div class="um-clear" style="text-align:center;">
					<br />
					<a href="javascript:void(0);" id="um-bookmarks-profile-add-folder" class="um-modal-btn alt">
						+ <?php echo esc_html( sprintf( __( 'Add Folder', 'um-user-bookmarks' ), $folder_text ) ); ?>
					</a>
				</div>

				<form id="um-user-bookmarks-profile-add-folder-form">
					<div class="um_bookmarks_table new-um-user-bookmarks-folder-tbl">
						<div class="um_bookmarks_tr">
							<div class="um_bookmarks_td">
								<input type="text" name="_um_user_bookmarks_folder" placeholder="<?php echo esc_attr( sprintf( __( '%s name', 'um-user-bookmarks' ), $folder_text ) ); ?>" required />
								<small class="error-message"><?php echo esc_html( sprintf( __( '%s name is required.', 'um-user-bookmarks' ), $folder_text ) ); ?></small>
							</div>

							<div class="um_bookmarks_td" style="vertical-align: middle;max-width:115px;">
								<input id="um_user_bookmarks_access_type_checkbox" type="checkbox" name="is_private" value="1" />
								<label for="um_user_bookmarks_access_type_checkbox"><?php _e( 'Private', 'um-user-bookmarks' ); ?></label>
							</div>

							<div class="um_bookmarks_td" style="max-width:115px">
								<button class="um_user_bookmarks_profile_create_folder_btn um-modal-btn" type="button" style="height:40px;"><?php _e('Create','um-user-bookmarks'); ?></button>
							</div>
						</div>
					</div>

					<?php wp_nonce_field( 'um_user_bookmarks_new_bookmark_folder' ); ?>
					<input type="hidden" name="user_id" value="<?php echo esc_attr( get_current_user_id() ); ?>" />
					<input type="hidden" name="action" value="um_bookmarks_folder_add" />

					<div class="form-response" style="text-align:center;color:#ab1313;"></div>
				</form>
			</div>
			
	<?php } else {  ?>
	<?php if ( is_user_logged_in() && $user == get_current_user_id() ) {

		UM()->get_template( 'profile/single-folder/dropdown.php', um_user_bookmarks_plugin, array(
			'key'       => $key,
			'user_id'   => $user,
		), true );

	} ?>
	<?php } ?>
</header>

<section class="um-user-bookmarks <?php if ( empty( $bookmarks ) ) { ?> user-bookmar-empty <?php } ?>">
	<?php if ( empty( $bookmarks ) ) { ?>
	     <img class="empty-img" src="/wp-content/uploads/2021/11/magic-box-4.png">
		<?php _e( 'Your folder is empty!', 'um-user-bookmarks' ); ?>
		<p>Explore <span class="book-empty-smart">BEE<b>SMART</b></span> and add post to bookmarks to show them here.</p>
	<?php } else {
		UM()->get_template( 'profile/bookmarks.php', um_user_bookmarks_plugin, array(
			'bookmarks' => $bookmarks,
			'user_id'   => $user,
		), true );
	} ?>
</section>
<div class="um-clear"></div>
<?php if ( empty( $bookmarks ) ) {  

} else { ?>
<div class="delete-colllection-bookmark">
      <a href="javascript:void(0);" class="um-user-bookmarks-folder-delete" data-folder_key="<?php echo esc_attr( $key ); ?>" data-alert_text="<?php esc_attr_e( 'Delete folder and its content?', 'um-user-bookmarks' ); ?>" data-profile="<?php echo esc_attr( $user_id ) ?>" data-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_folder_delete' ); ?>" data-callback-nonce="<?php echo wp_create_nonce( 'um_user_bookmarks_back' ); ?>"><img src="/wp-content/uploads/2021/11/delete-1.png"><?php _e( 'Remove Collection', 'um-user-bookmarks' ) ?></a>
</div>
<?php } ?>