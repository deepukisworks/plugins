<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('POP_front')) {

    class POP_front {

        protected static $instance;

        
        function cp_filter_wp_link_pages( $output, $args ) { 
           
            global $post;
            $id = get_the_ID();
            if( $post->post_type == "post") {
                if(is_singular()){

                }else{
                    return '<div class="cp_btn_div"><button class="cp_btn" data-id="'.$id.'" style="background-color:'.get_option('cp_btn_bg_clr').';font-size:'.get_option('cp_btn_ft_size').'px;color:'.get_option('cp_btn_ft_clr').';">'.get_option('cp_btn_txt').'</button></div>'.$output; 
                }
            }
        }


        function cp_pop_popup($atts, $content = null) {
            ob_start();  
            extract(shortcode_atts(array(
                'id' => '',
            ), $atts));
            // print_r($id);
            ?>
            <div class="cp_btn_div">
                <button class="cp_btn" data-id="<?php echo $id; ?>" style="background-color:<?php echo get_option('cp_btn_bg_clr') ?>;font-size:<?php echo get_option('cp_btn_ft_size')."px" ?>;color:<?php echo get_option('cp_btn_ft_clr') ?>;"><?php echo get_option('cp_btn_txt'); ?></button>
            </div>
            <?php
            return $var = ob_get_clean();
        }

      
        function cp_popup_div_footer(){
            ?>
            <div id="cp_popup_id" class="cp_popup_class">
                <div class="mailppss">
                </div>
            </div>
            <?php
        }


        function cp_popup_open() {
            $post_id = sanitize_text_field( $_REQUEST['popup_id'] );
            $post_info = get_post( $post_id );
            
            $comment_count = $post_info->comment_count;
            $link = $post_info->guid;

            $meta = get_post_meta($post_id); 
            $location = $meta['Address'][0];
            $type = $meta['Choose-Type'][0];

            $author = $post_info->post_author;
            $author_obj = get_user_by('id', $author);
            
            $featured_img_url = get_the_post_thumbnail_url($post_id, 'full'); 
            if(empty($featured_img_url)){
                $cp_image_url = POP_PLUGIN_DIR."/includes/images/background-image.jpg";
            }else{
                $cp_image_url = $featured_img_url;
            }
            $post_categories = get_the_category($post_id);
           	$fix_cat = array();

            foreach ($post_categories as $key => $value) {
            	
            	$category_link = get_category_link( $value->term_id );
            	$terms_name = '<a href="'.$category_link.'" target="_blank" style="color:'.get_option('cp_header_clr').';">'.$value->name.'</a>';
            	array_push($fix_cat,$terms_name);
            }

            ?> 
  <div class="cp_popup_content"> 
    <?php //var_dump($post_info); ?>
    <div class="again_inner_section">
        <span class="cp_close">&times;</span>
      <div class="popup-author-wrapper">
        <div class="author-block">
          <div class="author-name-block">
            <div div class="popup-author-img">
			  
              <?php echo get_avatar( get_the_author_meta( 'ID' ), 50, $post_id ); ?>
			  <?php //echo get_avatar( get_the_author_email(32), 50, $post_id ); ?>
            </div>
            <h3 class="popup-author-name">  
              <?php 
                if(get_option('cp_dis_autor') == "yes"){
                  echo "<a href='".get_author_posts_url(  $author  )."' target='_blank' >".$author_obj->display_name."</a>";
                  }
                ?>
            </h3>
          </div>
        </div>
        <div class="popup-type-of-post">
            <?php echo trim($type); ?>
        </div>
      </div>
          <!-- 2nd section -->
          <div id="custom-bg">
              <img src="<?php echo $cp_image_url; ?>">
          </div>
          <div class="description_block">
            <div class="popup-card-wrapper">
              <div class="honey-block">             
                <?php echo do_shortcode('[love_me]'); ?>
              </div>
              <div class="remains-block">
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
              <div class="right-side-block">
                <div class="bees-block">
                  <?php echo $comment_count; ?>
                  <img src="https://beesm.art/wp-content/uploads/2021/11/Bees4.png" alt="bees">
                </div>
              </div>
            </div>
              <!-- title block -->
            <div class="popup-title-section">
              <div class="title-block">
                <?php 
                  echo '<div class="cp_title_div">';
                      echo '<h3 style="color:'.get_option('cp_header_clr').';">'.$post_info->post_title.'</h3>';
                  echo '</div>';
                ?>
              </div>
              <div class="country-block">
                <span class="popup-address">
                  <?php echo $location ?>
                </span>
              </div>
            </div>
            <div class="content-block">
              <!-- unknown function that outputs the divi -->
              <?php echo '<div class="cp_popup_body" style="color:'.get_option('cp_discrption_clr').';font-size:'.get_option('cp_discrption_ft_size').'px;background-color: '.get_option('cp_popup_clr') .'">';
              echo $post_info->post_content;
              echo '</div>'; ?>
              </div>
              <div class="view-more-block">
                <a class="view-more-btn" href="<?php echo esc_url( home_url( '' ) ); ?>/?p=<?php echo $post_id; ?>">
                    VIEW MORE
                </a>
              </div>
          </div>
      </div>
    </div>
        <?php   exit();
        }


        function init() {
            add_filter( 'wp_link_pages', array($this,'cp_filter_wp_link_pages'), 10, 2 ); 
            add_shortcode( 'post_popup', array($this,'cp_pop_popup'));
            add_action( 'wp_footer', array( $this, 'cp_popup_div_footer' ));
            add_action( 'wp_ajax_post_popup', array( $this, 'cp_popup_open' ));
            add_action( 'wp_ajax_nopriv_post_popup', array( $this, 'cp_popup_open' ));
        }


        public static function instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
            }

            return self::$instance;
        }

    }

    POP_front::instance();
}



function oc_popup_post_button(){
   $id = get_the_ID();
   echo  '<div class="cp_btn_div custom 253455"><button class="cp_btn" data-id="'.$id.'" style="background-color:'.get_option('cp_btn_bg_clr').';font-size:'.get_option('cp_btn_ft_size').'px;color:'.get_option('cp_btn_ft_clr').';">'.get_option('cp_btn_txt').'</button></div>'.$output; 
}