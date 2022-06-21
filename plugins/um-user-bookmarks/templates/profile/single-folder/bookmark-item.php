<?php
/**
 * Template for the single bookmark
 *
 * Used:   Profile page > Bookmarks tab > folder
 * Parent: profile/bookmarks.php
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-user-bookmarks/profile/single-folder/bookmark-item.php
 *
 * @see      https://docs.ultimatemember.com/article/1516-templates-map
 * @package  um_ext\um_user_bookmarks\templates
 * @version  2.0.7
 *
 * @var  string $excerpt
 * @var  bool   $has_image
 * @var  string $has_image_class
 * @var  int    $id
 * @var  array  $image
 * @var  string $image_url
 * @var  string $post_link
 * @var  string $post_title
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$all_meta_for_users = get_user_meta( $post_id );
$country = $all_meta_for_users['country'][0];
?>

<div class="um-user-bookmarked-item <?php echo esc_attr( $has_image_class ); ?>">
	<div class="um-user-bookmarkss-list" href="<?php echo esc_url( $post_link ); ?>">
	
	    <div class="uper-header-folder-bookmark">
		    <div class="post-book-list">
				<?php echo get_avatar( get_the_author_meta( 'ID' ), $post_id ); ?>
				<h3><?php echo esc_attr( um_user( 'display_name' ) ); ?></h3>
			</div>
			<div class="user_postright">
					    <?php $chosetype = get_post_meta( $post->ID, 'Choose-Type', true ); ?>
						<?php if ( $chosetype == 'Bussiness' ) { ?>
							<img src="/wp-content/uploads/2021/11/Business-7.png">
						<?php } else if ( $chosetype == 'Hobby' ) { ?>
						<img src="/wp-content/uploads/2021/09/Hobby.png">
						<?php } else if ( $chosetype == 'Personal' ) { ?>
						<img src="/wp-content/uploads/2021/09/MicrosoftTeams-image-54.png">
						<?php } else if ( $chosetype == 'Community' ) { ?>
						<img src="/wp-content/uploads/2021/09/Community.png">
						<?php } else if ( $chosetype == 'Career' ) { ?>
						<img src="/wp-content/uploads/2021/09/Career-1.png">
						<?php } else if ( $chosetype == 'Location' ) { ?>
						<img src="/wp-content/uploads/2021/09/Location.png">
						<?php } else { ?>
						   <img src="/wp-content/uploads/2021/11/Business-7.png">
						<?php } ?>
					</div>
		</div>
		<div class="bookmark-post-image">
		<?php if ( $has_image ) { ?>
			<a href="<?php echo esc_url( $post_link ); ?>" target="_blank">
				<img class="um-user-bookmarked-post-image" src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $post_title ); ?>" />
			</a>
		<?php } ?>
        </div>
		<div class="um-user-bookmarks-post-content">
			<h3>
				<a href="<?php echo esc_url( $post_link ); ?>" target="_blank">
					<?php echo esc_html( $post_title ); ?>
				</a>
			</h3>
			<div class='cont-flg'>
			    <span><img src='/wp-content/uploads/2021/11/Flag_of_Ukraine_pantone_colors-1.png'></span>Lviv<?php echo $country ?>
			</div>
			<?php
			//if ( is_user_logged_in() && $user_id == get_current_user_id() && $id ) { ?>
				<!--<a href="javascript:void(0);" data-nonce="<?php //echo wp_create_nonce( 'um_user_bookmarks_remove_' . $id ); ?>" data-remove_element="true" class="um-user-bookmarks-profile-remove-link" data-id="<?php //echo esc_attr( $id ); ?>">
					<?php // _e( 'Remove', 'um-user-bookmarks' ); ?>
				</a>-->
			<?php //} ?>
		</div>
		<div class="bookmark-post-content">
		   <?php if ( ! empty( $excerpt ) && ! UM()->options()->get( 'um_user_bookmarks_page_builder' ) ) { ?>
				<p style="margin-bottom:0;"><?php echo strip_shortcodes( $excerpt ); ?>...</p>
			<?php } ?>
		</div>
		<div class="bottam-bookmark-post">
			<div class="love-me-post">
				<?php echo do_shortcode('[love_me]'); ?>
			</div>
			<div class="count-timer-post-bookmark">
				<span class="red-time">
					<div id="timer_<?php echo $post->ID; ?>">
					  <span id="days_<?php echo $post->ID; ?>"></span>
					  <span id="hours_<?php echo $post->ID; ?>"></span>
					  <span id="minutes_<?php echo $post->ID; ?>"></span>
					</div>
					<span>
						<?php $add_date =  get_the_date('F j, Y G:i:s',  $post->ID ); ?>
					</span>
					<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
					 <script type="text/javascript">
						$(document).ready(function(){  

						function makeTimer() {

						var endTime = new Date("<?php echo $add_date; ?>");
									endTime.setDate(endTime.getDate() + 28);
						var endTime = (Date.parse(endTime)) / 1000;
						console.log(endTime);
						var now = new Date();
						var now = (Date.parse(now) / 1000);

						var timeLeft = endTime - now;

						var days = Math.floor(timeLeft / 86400); 
						var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
						var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600 )) / 60);
						var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));

						if (hours < "10") { hours = "0" + hours; }
						if (minutes < "10") { minutes = "0" + minutes; }
						if (seconds < "10") { seconds = "0" + seconds; }    

						$("#days_<?php echo $post->ID; ?>").html(days + "<span>d:</span>");
						$("#hours_<?php echo $post->ID; ?>").html(hours + "<span>h:</span>");
						$("#minutes_<?php echo $post->ID; ?>").html(minutes + "<span>m</span>");

						}  

						setInterval(function() { makeTimer(); }, 1000);

						});  

					   </script>
					<?php 
					 echo "<div id='timer'>
								  <span id='days'></span>
								  <span id='hours'></span>
								  <span id='minutes'></span>
							</div>";
					?>
					<?php //echo do_shortcode('[post_cowndowns]'); ?>
					<!-- <?php  _e( 'in', 'ultimate-member' ); ?>: <?php the_category( ', ', '', $post->ID ); ?> -->
				</span> 
			</div>
			<div class="comment-post-bookmark">
				<span class="post-comments-cont">
					<?php $num_comments = get_comments_number( $post->ID );

					if ( $num_comments == 0 ) {
						$comments = __( '0 <span class="comment-img-icon"><img src="/wp-content/uploads/2021/09/MicrosoftTeams-image-52.png"></span>', 'ultimate-member' );
					} elseif ( $num_comments > 1 ) {
						$comments = sprintf( __( '%s <span class="comment-img-icon"><img src="/wp-content/uploads/2021/09/MicrosoftTeams-image-52.png"></span>', 'ultimate-member' ), $num_comments );
					} else {
						$comments = __( '1 <span class="comment-img-icon"><img src="/wp-content/uploads/2021/09/MicrosoftTeams-image-52.png"></span>', 'ultimate-member' );
					} ?>

					<a href="<?php echo esc_url( get_comments_link( $post->ID ) ); ?>"><?php echo $comments; ?></a>
				</span>
			</div>
		</div>
	</div>

	<div class="um-clear"></div>
	<hr/>
</div>
