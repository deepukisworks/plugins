<?php if ( ! defined( 'ABSPATH' ) ) exit; 
$post_id = get_current_user_id();
global $wpdb;
$all_meta_for_users = get_user_meta( $post_id );
$country = $all_meta_for_users['country'][0];
$location = $all_meta_for_users['location'][0];
$diname = esc_attr( um_user( 'display_name' ) );
$UserPostMeta  = $wpdb->prefix . 'userpostdata';
$SelectPostMeta = $wpdb->get_row("SELECT * FROM $UserPostMeta WHERE post_id = '$post_id' ");
$UserCurrentLocation = $SelectPostMeta->address;


?>
<style>
.countryy {
    margin: 10px;
    display: inline-block;
    font-family: tahoma;
    font-size: 12px
}
.countryy i {
    background: url(https://dl.dropboxusercontent.com/s/izcyieh1iatr4n5/flags.png) no-repeat;
    display: inline-block;
    width: 16px;
    height: 11px;
}
</style>
<div class="um-item">
 
	<div class="media-profile-section">
		<div class="media-left-right">
            <div class="desktop-media-img">
			<?php if ( has_post_thumbnail( $post->ID ) ) { 
				$image_id = get_post_thumbnail_id( $post->ID );
				$image_url = wp_get_attachment_image_src( $image_id, 'full', true ); ?>
			
				<div class="um-item-img">
					<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
						<?php echo get_the_post_thumbnail( $post->ID, 'full' ); ?>
					</a>
				</div>
				<div class="um-item-tags">
							<?php
							  //$id = get_the_ID();
							  $tags = wp_get_post_tags($post->ID); //this is the adjustment, all the rest is bhlarsen
							  $html = '<div class="post_tags">';

							  foreach ($tags as $tag) {
								 $tag_link = get_tag_link($tag->term_id);

								 $html .= "<a href='{$tag_link}' title='{$tag->name} Tag' class='tag_item {$tag->slug}'>";
								 $html .= "# {$tag->name}</a> ";
							  }
							  $html .= '</div>';
							  echo $html;
							  ?>
						</div>
			<?php } else { ?>
				<div class="um-item-img">
					<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
						<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/11/Featured-img.png">
					</a>
				</div>
			<?php } ?>
		    </div>
			<div class="posts-des-title">
			    <div class="user-profile-post">
					<div class="user_postleft">
					    <div class="max-profile-left">
						    <a href="https://beesm.art/user/<?php echo esc_attr( um_user( 'display_name' ) ); ?>" class="um-profile-photo-img" title="<?php echo esc_attr( um_user( 'display_name' ) ); ?>">
									<?php if ( ! $default_size || $default_size == 'original' ) {
										$profile_photo = UM()->uploader()->get_upload_base_url() . um_user( 'ID' ) . "/" . um_profile( 'profile_photo' );

										$data = um_get_user_avatar_data( um_user( 'ID' ) );
										echo $overlay . sprintf( '<img src="%s" class="%s" alt="%s" data-default="%s" onerror="%s" />',
											esc_url( $profile_photo ),
											esc_attr( $data['class'] ),
											esc_attr( $data['alt'] ),
											esc_attr( $data['default'] ),
											'if ( ! this.getAttribute(\'data-load-error\') ){ this.setAttribute(\'data-load-error\', \'1\');this.setAttribute(\'src\', this.getAttribute(\'data-default\'));}'
										);
									} else {
										echo $overlay . get_avatar( um_user( 'ID' ), $default_size );
									} ?>
									<?php if ( is_user_logged_in() ) { 
									if ( um_is_on_edit_profile() || UM()->user()->preview ) { 
									
									} else { ?>
									 <span class="active-icon"></span> 
									
									<?php } } ?>
								</a>
						</div>
						<div class="max-profile-right">
							<a href="https://beesm.art/user/<?php echo esc_attr( um_user( 'display_name' ) ); ?>"><h1><?php echo esc_attr( um_user( 'display_name' ) ); ?></h1></a>
							<!--<p>Sport Trainer</p>-->
							<p><?php echo $chosetype = get_post_meta( $post->ID, 'Choose-Type', true ); ?></p>
								
						</div>
						<div class="edit-posts" style="display:flex;">
							<?php  echo '<a href="/user/'.$diname.'?profiletab=buddyformssubmissionspage&amp;bf_um_action=edit&amp;subnav=form-buddyform-post&amp;bf_post_id='.$post->ID.'"> <img src="/wp-content/uploads/2021/11/image-38.png"></a>';  ?>
						<?php  $current_user = wp_get_current_user();
						if ($post->post_author == $current_user->ID) { ?> 
							<p><a class="delete-post-btn" onclick="return confirm('Are you sure ?')" href="<?php echo get_delete_post_link( $post->ID ) ?>"><img src="https://beesm.art/wp-content/uploads/2021/11/close.png"></a></p> 
						<?php } ?> 
						</div>
					</div>
					<div class="user_postright">
					    <?php $chosetype = get_post_meta( $post->ID, 'Choose-Type', true ); ?>
						<?php if ( $chosetype == 'Bussiness' ) { ?>
							<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/11/Business-7.png">
						<?php } else if ( $chosetype == 'Hobby' ) { ?>
						<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/09/Hobby.png">
						<?php } else if ( $chosetype == 'Personal' ) { ?>
						<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/09/MicrosoftTeams-image-54.png">
						<?php } else if ( $chosetype == 'Community' ) { ?>
						<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/09/Community.png">
						<?php } else if ( $chosetype == 'Career' ) { ?>
						<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/09/Career-1.png">
						<?php } else if ( $chosetype == 'Location' ) { ?>
						<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/09/Location.png">
						<?php } else { ?>
						   <img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/11/Business-7.png">
						<?php } ?>
					</div>
				</div>
				<div class="mobile-media-img">
						<?php if ( has_post_thumbnail( $post->ID ) ) {
						$image_id = get_post_thumbnail_id( $post->ID );
						$image_url = wp_get_attachment_image_src( $image_id, 'full', true ); ?>
					
						<div class="um-item-img">
							<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
								<?php echo get_the_post_thumbnail( $post->ID, 'full' ); ?>
							</a>
						</div>
						<div class="um-item-tags">
							<?php
							  //$id = get_the_ID();
							  $tags = wp_get_post_tags($post->ID); //this is the adjustment, all the rest is bhlarsen
							  $html = '<div class="post_tags">';

							  foreach ($tags as $tag) {
								 $tag_link = get_tag_link($tag->term_id);

								 $html .= "<a href='{$tag_link}' title='{$tag->name} Tag' class='tag_item {$tag->slug}'>";
								 $html .= "# {$tag->name}</a> ";
							  }
							  $html .= '</div>';
							  echo $html;
							  ?>
						</div>
					<?php } else { ?>
						<div class="um-item-img">
							<a href="<?php echo esc_url( get_permalink( $post ) ); ?>">
								<img src="<?php echo esc_url( home_url( '' ) ); ?>/wp-content/uploads/2021/11/Featured-img.png">
							</a>
						</div>
					<?php } ?>
				</div>
				
				<div class='country-left'>
					<div class="um-item-link">
						<a href="<?php echo esc_url( get_permalink( $post ) ); ?>"><?php echo get_the_title( $post ); ?></a>
					</div>
					<div class='country-right'>
						<div class='country-flags'>
						    <div class="post-location">
							    <p> 
									<?php $key_1_value = get_post_meta( $post->ID, 'Eventaddress', true );
									// Check if the custom field has a value.
									if ( ! empty( $key_1_value ) ) { ?>
									    <a href="https://www.google.com/maps?q=<?php echo $key_1_value;?>">
										   <?php echo $key_1_value; ?>
										</a>
									<?php } ?>
						        </p>
							       
							</div>
							<?php echo do_shortcode('[currency-icons]'); ?>
						</div>
					</div>
					<div class="um-item-description">
						<?php $postcontent =  get_post_field('post_content', $post); 
							  //echo $postcontent;
							  echo wp_trim_words( $postcontent, 20 );
						?>
						<?php if( $postcontent ){ ?>
						<a class="more" href="<?php echo esc_url( get_permalink( $post ) ); ?>">more</a>
						<?php } ?>
					</div>
				</div>
			</div>
		</div>
		<div class="um-item-meta">
			<span class="lovepost-cont">
				<?php //printf( __( '%s ago', 'ultimate-member' ), human_time_diff( get_the_time( 'U', $post->ID ), current_time( 'timestamp' ) ) ); ?>

				<?php $love = get_post_meta( $post->ID, 'love_me_like', true );

					if(empty($love) || !is_numeric($love)) {
						$love = 0;
						add_post_meta($post->ID, 'love_me_like', $love, true);
						update_post_meta($post->ID, 'love_me_like', $love);
					}

					wp_nonce_field( 'love_me_nonce', 'love_me_metabox_nonce');
					
					

					echo '<div class="honey-block">'; 
					echo '<input checked="" id="post_'.$post->ID.'" type="checkbox" class="LoveCheck">'; 
					echo '<label for="post_'.$post->ID.'" class="dashicons dashicons-heart LoveLabel" aria-label="like this"></label>';
					echo "<span class='LoveCount'>" . $love . "</span>";
					echo "</div>";

					// Hide like button for this post
					$love_hide = get_post_meta( $post->ID, 'love_me_hide', true );
					if(empty($love_hide) || !is_numeric($love_hide)) {
						add_post_meta($post->ID, 'love_me_hide', 0, true);
						update_post_meta($post->ID, 'love_me_hide', 0);
					}
					if (isset($love_hide) && $love_hide == 1) {
						$checked = " checked='checked' ";
					} else {
						$checked = "";
					}
					?>

			</span>
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
			<span class="post-comments-cont">
			
				<?php $num_comments = get_comments_number( $post->ID );

				if ( $num_comments == 0 ) {
					$comments = __( '0 <span class="comment-img-icon"><img src="https://beesm.art/wp-content/uploads/2021/09/MicrosoftTeams-image-52.png"></span>', 'ultimate-member' );
				} elseif ( $num_comments > 1 ) {
					$comments = sprintf( __( '%s <span class="comment-img-icon"><img src="https://beesm.art/wp-content/uploads/2021/09/MicrosoftTeams-image-52.png"></span>', 'ultimate-member' ), $num_comments );
				} else {
					$comments = __( '1 <span class="comment-img-icon"><img src="https://beesm.art/wp-content/uploads/2021/09/MicrosoftTeams-image-52.png"></span>', 'ultimate-member' );
				} ?>

				<a href="<?php echo esc_url( get_comments_link( $post->ID ) ); ?>"><?php echo $comments; ?></a>
				
			</span>
		</div>
	</div>
</div>

