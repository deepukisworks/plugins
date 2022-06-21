<?php
/**
 * Template for the single folder info
 *
 * Used:   Profile page > Bookmarks tab
 * Parent: profile/folder-view/folder.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/folder-view/folder/folder-info.php
 *
 * @see       https://docs.ultimatemember.com/article/1516-templates-map
 * @package   um_ext\um_user_bookmarks\templates
 * @version   2.0.7
 *
 * @var  string $access_type
 * @var  int    $count
 * @var  string $text
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<p class="folder-info">
	<?php echo esc_html( $count ); ?> <?php echo esc_html( $text ); ?> &nbsp; | &nbsp;
	<?php if ( $access_type == 'private' ) {
		_e( 'Private', 'um-user-bookmarks' );
	} else {
		_e( 'Public' , 'um-user-bookmarks' );
	} ?>
</p>