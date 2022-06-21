<?php 
   function google_search_api_code() { /*shortcode function start in wp*/
   if ( is_user_logged_in() ) { /*user login condition check in wp start*/
       global $wpdb;
       $user_id = get_current_user_id();
       $plugins_file_url = plugins_url('/map-locations/public/partials/save.php'); 
       $user_info_get_wp = $wpdb->prefix . 'user_information_get';
       $api_parameters = $wpdb->get_row("SELECT * FROM $user_info_get_wp WHERE userid = $user_id");
       //print_r($api_parameters);
       $Latitude ='';
       $Longitude ='';
       $user_login_condition_data='';
   
       $Latitude = ($api_parameters->userlatitude);
       $Longitude = ($api_parameters->userlongitude);
   if (isset($Latitude) && isset($Longitude) ) {
   $user_login_condition_data ='<div class="main_cls_Section_map">
      <script src="https://polyfill.io/v3/polyfill.min.js?features=default"></script>
      <input id="pac-input" class="controls" type="text" placeholder="Search Box"/>
      <div id="map"></div>
      <div class="mydata" id="mydata"></div>
      <script
         src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCR_vV3_QFXkQwgt-HHGL0xgQmSxvzH3Tc&callback=initAutocomplete&libraries=places&v=weekly"
         async
         ></script>
   </div>
   <script>
      function initAutocomplete() {
        const map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: '.$Latitude.', lng: '.$Longitude.' },
            zoom: 13,
            mapTypeId: "roadmap",
        });
            const input = document.getElementById("pac-input");
            const searchBox = new google.maps.places.SearchBox(input);
            map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
        map.addListener("bounds_changed", () => {
            searchBox.setBounds(map.getBounds());
        });
        let markers = [];
      
            searchBox.addListener("places_changed", () => {
            const places = searchBox.getPlaces();
            var searchfound = []; 
               $.each(places, function(key, value){ 
            var formatted_address =  value["formatted_address"];
                searchfound.push(formatted_address);
            });
            var search_value =  $("#pac-input").val();
             var userid = '.$user_id.';
              $.ajax({
                url: "https://staging2.beesm.art/wp-content/plugins/map-locations/public/partials/save.php",
                type: "POST",
                data: {
                    userid: userid,
                    search_value: search_value,
                    searchfound: searchfound
                },
                dataType: "html",
                success: function (data, status, xhr) 
                {
                  $("#mydata").html(data);
                }                   
                });
      
        if (places.length == 0) {
            return;
        }
            markers.forEach((marker) => {
            marker.setMap(null);
        });
        markers = [];
            const bounds = new google.maps.LatLngBounds();
            places.forEach((place) => {
        if (!place.geometry || !place.geometry.location) {
            console.log("Returned place contains no geometry");
        return;
        }
        const icon = {
            url: place.icon,
            size: new google.maps.Size(71, 71),
            origin: new google.maps.Point(0, 0),
            anchor: new google.maps.Point(17, 34),
            scaledSize: new google.maps.Size(25, 25),
        };
        markers.push(
            new google.maps.Marker({
            map,
            icon,
            title: place.name,
            position: place.geometry.location,
        })
        );
        if (place.geometry.viewport) {
            bounds.union(place.geometry.viewport);
        } else {
            bounds.extend(place.geometry.location);
        }
        });
            map.fitBounds(bounds);
        });
      }
   </script>';  
   }else{
   $user_login_condition_data .= '<div class="warring_cls_latlong">
      <p>Sorry ... Please update Latitude and Longitude. </p>
   </div>'; 
   }
   } else {
   $user_login_condition_data .= '
   <div class="warring_cls">
      <p>Sorry , Please login.....</p>
   </div>
   '; 
   } 
   return $user_login_condition_data;
   }
   add_shortcode('search_api', 'google_search_api_code');
   
   
   /*user update information in wp*/
   function update_location_wp()
   {
   $user_id = get_current_user_id();
   echo do_shortcode('[gmw_current_location location_form_trigger="Get your current location" zoom_level="13"]');  
   $postid = $_GET['postid'];
   $id = $_GET['id'];
   $user_info = '';
   $user_info .= '<script>
      jQuery(document).ready(function($){
      var lat = $(".lat_custom_cls").attr("value");
      var lng = $(".lng_xutom_cls").attr("value");
      var address = $(".address_custom_get").attr("value");
      $(".mylat").val(lat);
      $(".mtlng").val(lng);
      $(".address_post").val(address);
      });
   </script>';
   $user_info .= '<div class="main_section_cls">
      <form method="GET" action="'.site_url('/wp-content/plugins/map-locations/public/partials/update-lat.php').'" >
         <input type="hidden" value="'.$postid.'" name="postid"> 
         <input type="hidden" value="'.$user_id.'" name="userid"> 
         <input type="hidden" value="'.$id.'" name="id"> 
         <label for="Latitude">Latitude:</label><br>
         <input type="text" class="mylat" value="" name="Latitude">
         <label for="Longitude">Longitude:</label>
         <input type="text" class="mtlng" value="" name="Longitude">
         <input type="text" class="address_post" value="" name="address">
         <input type="submit" name="location_update" value="Update">
      </form>
   </div>';
   return $user_info;
   }
   add_shortcode('location_update', 'update_location_wp');
   /*end code in wp*/
   
   
   /*code add in map-loaction plugin in wp */
   function wpdb_shortcode_user_postfilter(){
    
       $AllFilterPostData='';
       $user_id = get_current_user_id();
       $currentPage = get_query_var('paged');
       $posts = new WP_Query(array(
       'post_type' => 'post', 
       'posts_per_page' => 6,
       'post_status' => 'publish', 
       'paged' => $currentPage
       //'author' => $user_id
       ));  
   ?>
<?php 
$AllFilterPostData .='
<div class="main_section_cls_option">
   ';
   if ($posts->have_posts()) :
   while ($posts->have_posts()) :
   $posts->the_post(); 
   $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'single-post-thumbnail');
   $user = wp_get_current_user();  /*current user value get in wp*/
   $AllFilterPostData .='
   <div class="all_post_get_user_wise">
      ';
      $AllFilterPostData .='
      <div class="user_old_info">
         ';
         $AllFilterPostData .='<img class="user_image_clss" src="'.esc_url(get_avatar_url($user->ID)).'" />
         <h3 class="author_name">'.get_the_author().'</h3>
         ';
         $AllFilterPostData .='
      </div>
      ';
      $AllFilterPostData .= '<img src="'.$image[0].'">';
      $AllFilterPostData .='
      <a href="#">
         <h1>'.get_the_title().'</h1>
      </a>
      ';
      $AllFilterPostData .='
   </div>
   ';
   endwhile;
   endif;
   $AllFilterPostData .='
</div>
';
/*pagination  code start in wp*/
$AllFilterPostData .="
<div class='page-nav-container'>" 
   .paginate_links(array(
   'total' => $posts->max_num_pages,
   'prev_text' => __('<'),
   'next_text' => __('>')
   )) . "
</div>
";
/*End pagination  code in wp */
return $AllFilterPostData;            
}
add_shortcode('postfilters', 'wpdb_shortcode_user_postfilter');
/*end code in wp*/


function wpdp_user_info_get(){
$userinfo = wp_get_current_user();
$UserID = $userinfo->ID;

global $wpdb;
$user_location_get = $wpdb->prefix . 'user_information_get';
$results = $wpdb->get_row("SELECT * FROM $user_location_get WHERE userid = $UserID");

$UserInformationGet .='
<div class="userinfo_get">
    <img alt="user image" src="'.esc_url(get_avatar_url($userinfo->ID)).'" />
   <h4>'.$userinfo->display_name.'</h4>
   <div class="main_info_cl">
      <div class="right_cls"><label>Email address :</label></div>
      <div class="left_cls"><span>'.$userinfo->user_email.'</span></div>
   </div>
   <div class="main_info_cl">
      <div class="right_cls"><label>Birth date :</label></div>
      <div class="left_cls"><span>25-03-1995</span></div>
   </div>
   <div class="main_info_cl">
      <div class="right_cls"><label>Gender :</label></div>
      <div class="left_cls"><span>Gender</span></div>
   </div>
   <div class="main_info_cl">
      <div class="right_cls"><label>Location :</label></div>
      <div class="left_cls"><span>'.$results->user_country.' '.$results->user_city.'</span></div>
   </div>
   <div class="main_info_cl">
      <div class="right_cls"><label>Education :</label></div>
      <div class="left_cls"><span>Education</span></div>
   </div>
   <div class="main_info_cl">
      <div class="right_cls"><label>Language :</label></div>
      <div class="left_cls"><span>English</span></div>
   </div>
   <div class="main_info_cl">
      <div class="right_cls"><label>Services you offer :</label></div>
      <div class="left_cls"><span>English </span></div>
   </div>
</div>
';
return $UserInformationGet;  
}
add_shortcode('userinfo', 'wpdp_user_info_get');


/*header file start in wp*/
function wpdp_plugin_header_file(){
$PluginHeader='';
$PluginHeader .='
<div class="services_section_plugin_header">
   <div class="service_loop_cls">
      <a href="#">
         <img src="'.site_url().'/wp-content/uploads/2021/10/job.png" alt="image1">
         <h4> Job</h4>
      </a>
   </div>
   <div class="service_loop_cls active">
      <a href="#">
         <img src="'.site_url().'/wp-content/uploads/2021/10/handshake.png" alt="image2">
         <h4>Offer </h4>
      </a>
   </div>
   <div class="service_loop_cls">
      <a href="#">
         <img src="'.site_url().'/wp-content/uploads/2021/10/selling-1.png" alt="image3">
         <h4>Sale</h4>
      </a>
   </div>
   <div class="service_loop_cls">
      <a href="#">
         <img src="'.site_url().'/wp-content/uploads/2021/10/list-1.png" alt="image4">
         <h4>Activity</h4>
      </a>
   </div>
   <div class="service_loop_cls">
      <a href="#">
         <img src="'.site_url().'/wp-content/uploads/2021/10/Earth-1.png" alt="image5">
         <h4>Sharing</h4>
      </a>
   </div>
</div>';
return $PluginHeader;  
}
add_shortcode('pluginheader', 'wpdp_plugin_header_file');
/*end header shortcode in wp*/