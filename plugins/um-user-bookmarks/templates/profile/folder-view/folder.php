<?php
/**
 * Template for the single folder
 *
 * Used:   Profile page > Bookmarks tab
 * Parent: profile/folder-view.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/folder-view/folder.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  int    $count
 * @var  array  $folder
 * @var  string $key
 * @var  int    $profile_id
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um-user-bookmarks-folder1">

	<div class="um-user-bookmarks-folder-container">
		<div class="um-user-bookmarks-save-view">
			<?php
			UM()->get_template( 'profile/folder-view/folder/title.php', um_user_bookmarks_plugin, array(
				'title' => $folder['title'],
			), true );

			UM()->get_template( 'profile/folder-view/folder/folder-info.php', um_user_bookmarks_plugin, array(
				'count'         => $count,
				'text'          => __( 'saved', 'um-user-bookmarks' ),
				'access_type'   => $folder['type'],
			), true );
			?>
		</div>
		<!--<div class="view-all-folder-bookmark"><p>View All</p></div>-->
	</div>
</div>