<?php
/**
 * Template for the single folder title
 *
 * Used:   Profile page > Bookmarks tab
 * Parent: profile/folder-view/folder.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/folder-view/folder/title.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  string $title
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h4 class="um-user-bookmarks-folder-name"><?php echo esc_html( $title ); ?></h4>