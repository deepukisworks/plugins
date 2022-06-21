<?php

if (!defined('ABSPATH'))
  exit;

if (!class_exists('POP_front')) {

  class POP_front
  {

    protected static $instance;


    function cp_filter_wp_link_pages($output, $args)
    {

      global $post;
      $id = get_the_ID();
      if ($post->post_type == "post") {
        if (is_singular()) {
        } else {
          return '<div class="cp_btn_div"><button class="cp_btn" data-id="' . $id . '" style="background-color:' . get_option('cp_btn_bg_clr') . ';font-size:' . get_option('cp_btn_ft_size') . 'px;color:' . get_option('cp_btn_ft_clr') . ';">' . get_option('cp_btn_txt') . '</button></div>' . $output;
        }
      }
    }


    function cp_pop_popup($atts, $content = null)
    {
      ob_start();
      extract(shortcode_atts(array(
        'id' => '',
      ), $atts));
      // print_r($id);
?>
      <div class="cp_btn_div">
        <button class="cp_btn" data-id="<?php echo $id; ?>" style="background-color:<?php echo get_option('cp_btn_bg_clr') ?>;font-size:<?php echo get_option('cp_btn_ft_size') . "px" ?>;color:<?php echo get_option('cp_btn_ft_clr') ?>;"><?php echo get_option('cp_btn_txt'); ?></button>
      </div>
    <?php
      return $var = ob_get_clean();
    }


    function cp_popup_div_footer()
    {
    ?>
      <div id="cp_popup_id" class="cp_popup_class">
        <div class="mailppss">
        </div>
      </div>
    <?php
    }

    // if (empty($featured_img_url)) {
    //   $cp_image_url = POP_PLUGIN_DIR . "/includes/images/background-image.jpg";
    // } else {
    //   $cp_image_url = $featured_img_url;
    // }
    // from old plugin developer
    function cp_popup_open()
    {
      // post 
      global $post, $wpdb;
      $id = get_the_ID();
      global $current_user;
      $current_user_id = $current_user->ID;
      $post_id = sanitize_text_field($_REQUEST['popup_id']);
      $post_info = get_post($post_id);
      $comment_count = $post_info->comment_count;
      $link = $post_info->guid;
      $meta = get_post_meta($post_id);
      $honeys = $meta['love_me_like'][0];
      $viewsCount = $wpdb->get_results("SELECT count FROM `wp_post_views` WHERE `id`=$post_id and `period`='total'");
      $viewsAmount = $viewsCount[0]->count;
      // $increaseTheAmount = $wpdb->query($wpdb->prepare("user_preview wp_save_feed SET other_id='$user_previewd_field' WHERE save_feed_id='$save_feed_id'"));
      $inm = $wpdb->query($wpdb->prepare("UPDATE wp_post_views SET `count`=$viewsAmount++ WHERE `id`=$post_id"));
      if ($inm) {
        $response['success'] = 'true';
        $response['message'] = "You have Succesfully updated amount of views";
      } else {
        $response['success'] = 'false';
        $response['message'] = "Oops something went wrong. Please try again later";
      }

      // user meta
      $author = $post_info->post_author; //user id
      $author_obj = get_user_by('id', $author);
      $author_meta = get_user_meta($author);

      $featured_img_url = get_the_post_thumbnail_url($post_id, 'full');

      $post_categories = get_the_category($post_id);
      $fix_cat = array();

      foreach ($post_categories as $key => $value) {

        $category_link = get_category_link($value->term_id);
        $terms_name = '<a href="' . $category_link . '" target="_blank" style="color:' . get_option('cp_header_clr') . ';">' . $value->name . '</a>';
        array_push($fix_cat, $terms_name);
      }

    ?>
      <style>
        .popup-add-secect {
          margin-top: -1rem;
        }

        .popup-type-of-post {
          display: block !important;
        }

        .cp_popup_content {
          border-radius: 0px !important;
        }

        .popup-author-img > a >div{
          position: relative;
          z-index: -1;
        }
      </style>
      <div class="cp_popup_content">
        <?php //debug($author_meta);
        ?>
        <div class="again_inner_section">
          <div class="popup-author-wrapper">
            <div class="addHive-block">
              <button data-type="addhive" type="button" class="click_animation hover_hue">
                <img data-type="addhive" src="<?php echo site_url(); ?>/wp-content/uploads/2022/05/Add-to-hive.png">
              </button>
            </div>
            <div class="author-block">
              <div class="author-name-block">
                <div div class="popup-author-img">
                  <?php
                  function curl3($cover_url)
                  {
                    $url = "https://iframe.ly/api/oembed?url=$cover_url&api_key=9aab64aeb3ff8937967473";
                    $curl = curl_init($url);
                    curl_setopt($curl, CURLOPT_URL, $url);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
                    //for debug only!
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

                    $resp = curl_exec($curl);
                    curl_close($curl);
                    $new_value = json_decode($resp, true);
                    echo htmlspecialchars_decode($new_value['html']);
                    // var_dump($new_value);
                    // return $new_value;
                  }


                  add_filter('previewRender2', 'previewRender2');
                  function previewRender2($field)
                  {
                    if (isset($field)) {
                      // $cover_url = $author_meta[$url][0];
                      // curl($cover_url);
                      curl3($field);
                    } else {
                      echo "<img src='https://media.istockphoto.com/photos/dark-blue-minimal-texture-banner-with-space-for-text-word-or-product-picture-id1224392306?b=1&k=20&m=1224392306&s=170667a&w=0&h=lpDpUrttCoFDMhGQ1GJyyxIHE78s3xoMUzkawP5n4Y4=' /> ";
                    }
                  }
                  ?>
                  <a href='<?php echo site_url(); ?>/user/<?php echo $author; ?>' target='_blank'><?php //echo $author_obj->display_name; ?>
                    <div class="response-block">
                      <?php apply_filters('previewRender2', $author_meta['user_avatar_url'][0]); ?>
                    </div>
                  </a>
                </div>
                <!--<h3 class="popup-author-name" data-id="<?php echo $author; ?>">
                  <?php
                  if (get_option('cp_dis_autor') == "yes") {
                    // echo "<a href='" . get_author_posts_url($author) . "' target='_blank' >" . $author_obj->display_name . "</a>";
                  ?>
                    <a href='<?php echo site_url(); ?>/user/<?php echo $author; ?>' target='_blank'><?php echo $author_obj->display_name; ?></a>
                  <?php
                  }
                  ?>
                </h3>-->
              </div>
            </div>
            <div class="popup-type-of-post">
              <div class="type-of-post">
                <!-- IS USER VERIFIED -->
                <?php
                $author_is_approved = $author_meta['account_status'][0]; // approved
                if ($author_is_approved == 'approved') {
                  echo '<img class="is-verified" src="https://beesmartdev.wpengine.com/wp-content/uploads/2022/01/Verified.png">';
                } else {
                  echo '<img class="is-verified" style="width:50px;height:50px;" src="https://beesmartstg.wpengine.com/wp-content/uploads/2022/05/not-verfied.jpg">';
                }
                ?>
                <!-- IS USER VERIFIED -->



                <!-- USER TYPE -->
                <?php
                $author_type = $author_meta['account_type'][0];
                if ($author_type == 'Business') {
                  echo  "<img src='https://beesmartstg.wpengine.com/wp-content/uploads/2021/11/Business-1.svg'>";
                } else if ($author_type == 'Career') {
                  echo  "<img src='https://beesmartstg.wpengine.com/wp-content/uploads/2021/11/Career.svg'>";
                } else if ($author_type == 'Personal') {
                  echo  "<img src='https://beesmartstg.wpengine.com/wp-content/uploads/2021/11/Personal.svg'>";
                } else if ($author_type == 'Hobby') {
                  echo  "<img src='https://beesmartstg.wpengine.com/wp-content/uploads/2021/11/Hobby.svg'>";
                } else if ($author_type == 'Location') {
                  echo  "<img src='https://beesmartstg.wpengine.com/wp-content/uploads/2021/11/Location.svg'>";
                } else if ($author_type == 'Community') {
                  echo  "<img src='https://beesmartstg.wpengine.com/wp-content/uploads/2021/11/Community.svg'>";
                } else {
                  echo  "<img src='https://beesmartstg.wpengine.com/wp-content/uploads/2022/02/dont.png'>";
                }
                ?>
                <!-- USER TYPE -->
                <!-- <img src="<?php echo site_url(); ?>//wp-content/uploads/2021/11/Hobby.svg"> -->
              </div>
              <?php // echo trim($type); 
              ?>
            </div>
          </div>
          <!-- 2nd section -->
          <!--<div class="popup-add-secect">
            <?php
            global $wpdb;
            $author_id = $author;
            $new_listing = $wpdb->get_results("SELECT * FROM wp_um_set_priority_by_follower where follower_id =$author_id and user_id=$current_user_id");
            $follower_cat_id = $new_listing[0]->follower_cat_id;
            if ($author_id != $current_user_id) {
              $author_category_listing = $wpdb->get_results("SELECT * FROM wp_category_created_by_author where author_id =$current_user_id");
              if (!empty($author_category_listing)) {
                $category = array();
                foreach ($author_category_listing as $category_listing) {
                  $category[] = $category_listing->category_value;
                } ?>
                <select class="add_select selected_category_value ">
                <?php
                $category_data = '<option value="0" data-u-id="' . $selected_id . '" selected>--Add category--</option>';
                foreach ($category as $category) {
                  if ($follower_cat_id == $category) {
                    $selected = 'selected';
                    $category_detail = get_cat_name($category);
                    $category_data .= '<option value="' . $category . '" data-id="' . $author_id . '" ' . $selected . '>' . $category_detail . '</option>';
                  } else {
                    $category_detail = get_cat_name($category);
                    $category_data .= '<option value="' . $category . '" data-u-id="' . $author_id . '">' . $category_detail . '</option>';
                  }
                }
              }
              echo $category_data;
                ?>
                </select>
              <?php
            }
              ?>
          </div>-->
          <div class="container-resposne-popup profile_single_post bg-white m-0" style="display:block">
            <div id="loading-popup" style="clear:both;" class="post-thumbnail <?php echo $id; ?>">
              <?php
              $is_explisit = get_field('is_explisit', $post_id);
              if ($is_explisit == 1) {
                $blur_img = "blur_img";
              } else {
                $blur_img = "";
              }
              $url = get_field('f_url', $post_id);
              $url_ = "https://iframe.ly/api/oembed?url=$url&api_key=9aab64aeb3ff8937967473";

              $curl = curl_init($url_);
              curl_setopt($curl, CURLOPT_URL, $url_);
              curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
              curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
              //for debug only!
              curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
              curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

              $resp = curl_exec($curl);
              curl_close($curl);
              $new_value = json_decode($resp, true);
              echo $new_value['html'];
              ?>

            </div>
          </div>
          <div id="custom-bg">

          </div>
          <script>
            $(document).ready(function() {
              console.log($('#cp_popup_id'))
            })
          </script>
          <div class="description_block">
            <div class="popup-card-wrapper">
              <div class="honey-block">
			  <button data-type="drophoney" class="love click_animation p-0"><input id="post_<?php echo $post_id;?>" type="checkbox" class="LoveCheck">
                <label for="post_<?php echo $post_id;?>" class="dashicons dashicons-heart LoveLabel" aria-label="like this" data-type="drophoney"></label><span class="LoveCount"> <?php echo intval(get_post_meta($post_id, 'love_me_like', true)); ?></span></button>
               
              </div>
              <div class="honey_pot">
                <img src="/wp-content/uploads/2022/03/Most-Honey.png" alt="">
                <div class="honey_pot_detail">
                  <ul>
                    <li>
                      <img src="/wp-content/uploads/2022/03/Most-Honey.png" alt="" data-id="<?php echo $current_user_id . 'current_user_id'; ?>">
                      <span class="total_honey_percentage">
                        <?php $coins_points = get_user_meta($current_user_id, 'mycred_default', true);
                        echo intval($coins_points);
                        ?></span>
                    </li>
                    <li class="select_item_background select_item">
                      <div class="select_group">
                        <div class="select">1%</div>
                        <input type="hidden" value="1" name="one_value">
                      </div>
                      <span class="one_percentage">
                        <?php $coins_points = get_user_meta($current_user_id, 'mycred_default', true);
                        $total = ($coins_points / 100) * 1;
                        echo intval($total); ?></span>
                    </li>
                    <li class="select_item_background select_item">
                      <div class="select_group">
                        <div class="select">10%</div>
                        <input type="hidden" value="10" name="ten_value">
                      </div>
                      <span class="tenth_percentage">
                        <?php $coins_points = get_user_meta($current_user_id, 'mycred_default', true);
                        $total = ($coins_points / 100) * 10;
                        echo intval($total); ?></span>
                    </li>
                    <li class="honey_input select_item_background">
                      <input type="number" class="form-control percentage_coins" data-id="<?php echo $post_id; ?>" id="" onkeypress="if(this.value.length==2) return false;">
                      <p class="inputlabel">%</p>
                      <span id="postt_<?php echo $post_id; ?>">0</span>
                    </li>
                    <li>
                      <a href="#" class="honey_check verify_percentage df" data-post="<?php echo $post_id; ?>">
                        <img src="/wp-content/uploads/2022/01/Check1.png" alt="">
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
              <div class="remains-block">
                <span class="red-time">
                  <div id="timer_<?php echo $post_id; ?>">
                    <span id="days1_<?php echo $post_id; ?>"></span>
                    <span id="hours1_<?php echo $post_id; ?>"></span>
                    <span id="minutes1_<?php echo $post_id; ?>"></span>
                  </div>
                  <span>
                    <?php $add_date =  get_the_date('F j, Y G:i:s',  $post_id); ?>
                  </span>
                  <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
                  <script type="text/javascript">
                    $(document).ready(function() {

                      function makeTimer() {
                        //alert('sss');
                        //alert('sss');
                        var endTime = new Date("<?php echo $add_date; ?>");
                        endTime.setDate(endTime.getDate() + 28);
                        var endTime = (Date.parse(endTime)) / 1000;
                        //console.log(endTime);
                        var now = new Date();
                        var now = (Date.parse(now) / 1000);

                        var timeLeft = endTime - now;

                        var days = Math.floor(timeLeft / 86400);
                        var hours = Math.floor((timeLeft - (days * 86400)) / 3600);
                        var minutes = Math.floor((timeLeft - (days * 86400) - (hours * 3600)) / 60);
                        var seconds = Math.floor((timeLeft - (days * 86400) - (hours * 3600) - (minutes * 60)));

                        if (hours < "10") {
                          hours = "0" + hours;
                        }
                        if (minutes < "10") {
                          minutes = "0" + minutes;
                        }
                        if (seconds < "10") {
                          seconds = "0" + seconds;
                        }
                        if (days > 0) {
                          $("#days1_<?php echo $post_id ?>").html(days + "<span>D</span>");
                        } else if (days == 0) {
                          $("#hours1_<?php echo $post_id; ?>").html(hours + "<span>H:</span>");
                          $("#minutes1_<?php echo $post_id; ?>").html(minutes + "<span>M</span>");
                        } else if (days == 0 && hours == 0) {
                          $("#minutes1_<?php echo $post_id; ?>").html(minutes + "<span>M</span>");
                        } else {
                          $('#timer_<?php echo $post_id; ?>').html('')
                        }


                      }

                      setInterval(function() {
                        makeTimer();
                      }, 1000);

                    });
                  </script>
                  <?php
                  echo "<div id='timer'>
								  <span id='days1'></span>
								  <span id='hours1'></span>
								  <span id='minutes1'></span>
							</div>";
                  ?>
                  <?php //echo do_shortcode('[post_cowndowns]'); 
                  ?>
                  <!-- <?php _e('in', 'ultimate-member'); ?>: <?php the_category(', ', '', $post_id); ?> -->
                </span>
              </div>
              <div class="right-side-block">
                <div class="bees-block">
                  <span class="post-views-count"><?php echo $viewsCount[0]->count; ?></span>
                  <img src="/wp-content/uploads/2022/02/Most-Views.png" alt="views count">
                </div>
              </div>
            </div>
            <!-- title block -->
            <div class="popup-title-section">
              <div class="title-block">
                <?php
                // echo '<div class="cp_title_div">';
                // echo '<h3 style="color:' . get_option('cp_header_clr') . ';">' . $post_info->post_title . '</h3>';
                // echo '</div>';
                ?>
                <div class="cp_title_div">
                  <h3>
                    <a href="<?php echo $link; ?>" style="color:#000;" class="mt-2">
                      <?php echo $post_info->post_title ?>
                    </a>
                  </h3>
                </div>
              </div>
              <div class="country-block">
                <span class="popup-address">
                  <?php echo $location; ?>
                </span>
              </div>
            </div>
            <div class="content-block">
              <!-- unknown function that outputs the divi -->
              <?php echo '<div class="cp_popup_body" style="color:' . get_option('cp_discrption_clr') . ';font-size:' . get_option('cp_discrption_ft_size') . 'px;background-color: ' . get_option('cp_popup_clr') . '">';
              echo $post_info->post_content;
              echo '</div>'; ?>
            </div>
          </div>
        </div>
      </div>
      <div class="view-more-block">
        <a class="view-more-btn" href="<?php echo esc_url(home_url('')); ?>/?p=<?php echo $post_id; ?>">
        </a>
      </div>
      <div class="cp_close_block">
        <span class="cp_close">
          <?php     define('imgPATH', get_stylesheet_directory_uri() . '/assets/images/'); ?>

          <img src="<?php echo imgPATH; ?>X.png"/>
        </span>
      </div>
<?php exit();
    }


    function init()
    {
      add_filter('wp_link_pages', array($this, 'cp_filter_wp_link_pages'), 10, 2);
      add_shortcode('post_popup', array($this, 'cp_pop_popup'));
      add_action('wp_footer', array($this, 'cp_popup_div_footer'));
      add_action('wp_ajax_post_popup', array($this, 'cp_popup_open'));
      add_action('wp_ajax_nopriv_post_popup', array($this, 'cp_popup_open'));
    }


    public static function instance()
    {
      if (!isset(self::$instance)) {
        self::$instance = new self();
        self::$instance->init();
      }

      return self::$instance;
    }
  }

  POP_front::instance();
}



function oc_popup_post_button()
{
  $id = get_the_ID();
  echo  '<div class="cp_btn_div custom">
            <button class="cp_btn" data-id="' . $id . '" style="background-color:' . get_option('cp_btn_bg_clr') . ';font-size:' . get_option('cp_btn_ft_size') . 'px;color:' . get_option('cp_btn_ft_clr') . ';">' . get_option('cp_btn_txt') . '
            </button>
          </div>' . $output;
}
