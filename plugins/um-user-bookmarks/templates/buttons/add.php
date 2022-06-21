<?php
/**
 * Template for displaying the button "Bookmark"
 *
 * Used:  Any page with the button "Bookmark"
 * Call:  UM()->User_Bookmarks()->get_button( $button_type );
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/buttons/add.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  string $icon
 * @var  int    $post_id
 * @var  string $text
 * @var  int    $user_id
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-clear">
	<a href="javascript:void(0);" class="um-user-bookmarks-button um-user-bookmarks-add-button"
		 data-post="<?php echo esc_attr( $post_id ); ?>"
		 data-um_user_bookmarks_id="<?php echo esc_attr( $post_id ); ?>" 
		 data-user="<?php echo esc_attr( $user_id ); ?>">
		<i class="<?php echo esc_attr( $icon ); ?>"></i>
		<?php if ( ! empty( $text ) ) { ?>
			<span class="text"><?php echo esc_html( $text ); ?></span>
		<?php } ?>
	</a>
</div>