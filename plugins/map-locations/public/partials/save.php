<?php
   require_once(rtrim($_SERVER['DOCUMENT_ROOT'], '/') . '/wp-load.php');
   
   $table_name = $wpdb->prefix . 'user_search_address';
   $wp_post_track = $wpdb->prefix . "userpostdata";
   $wp_user_information_get = $wpdb->prefix . "user_information_get";
   
   $userid = $_POST['userid'];
   $searchfound = $_POST['searchfound'];
   
   $stringsfsdf = stripslashes($_POST['search_value']);
   $stringdata = preg_replace("/[^A-Za-z0-9 ]/", '', $stringsfsdf);
   
   /*user information get query start in wp*/
   $SelectPostData = $wpdb->get_results( "SELECT * FROM $wp_user_information_get ");

   $selectdata = $wpdb->get_results( "SELECT * FROM $table_name WHERE search_value = '$stringdata'");
   $allcount = count($selectdata); 

   $SelectPostData = $wpdb->get_results( "SELECT * FROM $wp_post_track WHERE post_title  LIKE '%$stringdata%'");
   $postcount = count($SelectPostData);
      
   ?>
<div class="dataTables_wrapper main_section_wp">
   <?php  if($allcount > 0 ){  ?>
   <div class="right_section_cls">
      <table class="spotify-table album-table">
         <tr>
            <th><b>Sl No.</b></th>
            <th><b>Address</b></th>
         </tr>
         <?php 
            $counter = 1;
            foreach($selectdata as $data) { ?>
         <tr>
            <td><b><?php echo $counter++; ?></b></td>
            <td> <?php echo $data->searchfound; ?></td>
         </tr>
         <?php } ?>
      </table>
      <a href="#" class="load_more" id="album-load">Load more</a>
      <script type="text/javascript">
         $( document ).ready(function() {
         $('table tr:nth-child(n+1):nth-child(-n+8)').addClass('active');
            $('#album-load').on('click', function(e) {
              e.preventDefault();  
              var $rows = $('.album-table tr');
              var lastActiveIndex = $rows.filter('.active:last').index();
              $rows.filter(':lt(' + (lastActiveIndex + 5) + ')').addClass('active');
            });
         });
      </script>
   </div>
   <?php }else{
      foreach ($searchfound as $value) {
      $UserData = array(
      'userid' => $userid,
      'search_value' => $stringdata,
      'searchfound' => $value
      );
         $result = $wpdb->insert($table_name, $UserData);
      }
      } 
      ?>
   <?php  if($postcount > 0 ){  ?>
   <div class="left_section_cls">
      <table border="1">
         <tr>
            <th><b>Sl No.</b></th>
            <th><b>Title</b></th>
            <th><b>Location</b></th>
         </tr>
         <?php 
            $counters = 1;
            foreach($SelectPostData as $posts) { ?>
         <tr>
            <td><b><?php echo $counters++; ?></b></td>
            <td><a target="_blank" href="<?php echo $posts->post_url; ?>"><?php echo $posts->post_title; ?></a></td>
            <td>
               <form method="get" action="<?php echo site_url(); ?>/update-post-location">
                  <input type="hidden" name="postid" value="<?php echo $posts->post_id; ?>">
                  <input type="hidden" name="id" value="<?php echo $posts->id; ?>">
                  <div class="count_cls"><span> <b>Address:</b></span> <?php echo $posts->address; ?></div>
                  <div class="count_cls"><span> <b>Latitude & Longitude:</b></span> 
                  <?php $latitude = $posts->userlatitude; echo $removedigitslatitude = round($latitude, 2);?> 
                  , 
                  <?php  $userlongitude =  $posts->userlongitude; echo $removedigitslongitude = round($userlongitude, 2);?> 
                  </div>
                  <input type="submit" name="submit_btn" value="Update Location">
               </form>
            </td>
         </tr>
         <?php } ?>
      </table>
   </div>
   <?php } ?>
</div>