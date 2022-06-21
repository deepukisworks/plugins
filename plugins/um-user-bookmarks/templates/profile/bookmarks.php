<?php
/**
 * Template for displaying a bookmarks list
 *
 * Used:  Profile page > Bookmarks tab > All
 * Call:  UM()->User_Bookmarks()->profile()->get_user_profile_bookmarks_all();
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/bookmarks.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  array  $bookmarks
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-user-bookmarked-list">
	<?php
	foreach ( $bookmarks as $post_id ) {
		$bookmarked_post = get_post( $post_id );
		if ( empty( $bookmarked_post ) || is_wp_error( $bookmarked_post ) ) {
			continue;
		}

		$has_image       = false;
		$has_image_class = 'no-image';
		$image           = [];
		$image_url       = '';
		if ( has_post_thumbnail( $post_id ) ) {
			$has_image       = true;
			$has_image_class = 'has-image';
			$image           = wp_get_attachment_image_src( get_post_thumbnail_id( $post_id ), 'thumbnail' );
			$image_url       = $image[0];
		}

		$content = '';
		if ( $bookmarked_post->post_content ) {
			$content .= strip_shortcodes( $bookmarked_post->post_content );
			$content = str_replace( ']]>', ']]&gt;', apply_filters( 'the_content', $content ) );
		}

		UM()->get_template( 'profile/single-folder/bookmark-item.php', um_user_bookmarks_plugin, array(
			'excerpt'           => trim( substr( strip_tags( $content ), 0, 130 ) ),
			'has_image'         => $has_image,
			'has_image_class'   => $has_image_class,
			'id'                => $post_id,
			'image'             => $image,
			'image_url'         => $image_url,
			'post_link'         => get_the_permalink( $post_id ),
			'post_title'        => get_the_title( $post_id ),
			'user_id'           => $user_id,
		), true );
	}
	?>
	<div class="um-clear"></div>
</div>