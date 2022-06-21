<?php
/**
 * Template for the modal overlay
 *
 * Used:  Any page with the button "Bookmark"
 * Call:  UM()->User_Bookmarks()->common()->modal_area();
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/modal.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-user-bookmarks-modal">
	<div class="um-user-bookmarks-modal-content"><?php _e( 'Loading..', 'um-user-bookmarks' ) ?></div>
</div>