<?php

/**
 * Fired during plugin activation
 *
 * @link       https://beesm.art/
 * @since      1.0.0
 *
 * @package    Map_Location
 * @subpackage Map_Location/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Map_Location
 * @subpackage Map_Location/includes
 * @author     Developer <test91171@gmail.com>
 */
class Map_Location_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */

    public static function activate() {

          global $wpdb;
          $location = 'user_information_get';
          $wp_track_code = $wpdb->prefix . "$location";
          $sql_location = "CREATE TABLE IF NOT EXISTS $wp_track_code ( ";
          $sql_location .= "  `id`  int(11)   NOT NULL auto_increment, ";
          $sql_location .= "  `userid`  varchar(1000)   NOT NULL, "; 
          $sql_location .= "  `user_country`  varchar(1000)   NOT NULL, ";
          $sql_location .= "  `user_state`  varchar(1000)   NOT NULL, ";
          $sql_location .= "  `user_city`  varchar(1000)   NOT NULL, ";
          $sql_location .= "  `userlatitude`  varchar(1000)   NOT NULL, ";
          $sql_location .= "  `userlongitude`  varchar(1000)   NOT NULL, ";
          $sql_location .= "  PRIMARY KEY `id` (`id`) "; 
          $sql_location .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
          require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
          dbDelta($sql_location);

          /*user search data in wp */
          $usearch = 'user_search_data';
          $wp_search_code = $wpdb->prefix . "$usearch";
          $sql_search = "CREATE TABLE IF NOT EXISTS $wp_search_code ( ";
          $sql_search .= "  `id`  int(11)   NOT NULL auto_increment, ";
          $sql_search .= "  `userid`  varchar(1000)   NOT NULL, "; 
          $sql_search .= "  `search_value`  varchar(1000)   NOT NULL, ";
          $sql_search .= "  `searchfound`  varchar(1000)   NOT NULL, ";
          $sql_search .= "  PRIMARY KEY `id` (`id`) "; 
          $sql_search .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
          require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
          dbDelta($sql_search);
          
           /*user search data in wp */
          $vaddress = 'user_search_address';
          $wp_address_code = $wpdb->prefix . "$vaddress";
          $sql_address = "CREATE TABLE IF NOT EXISTS $wp_address_code ( ";
          $sql_address .= "  `id`  int(11)   NOT NULL auto_increment, ";
          $sql_address .= "  `userid`  varchar(1000)   NOT NULL, "; 
          $sql_address .= "  `search_value`  varchar(1000)   NOT NULL, ";
          $sql_address .= "  `searchfound`  varchar(1000)   NOT NULL, ";
          $sql_address .= "  `latitude`  varchar(1000)   NOT NULL, ";
          $sql_address .= "  `longitude`  varchar(1000)   NOT NULL, ";
          $sql_address .= "  PRIMARY KEY `id` (`id`) "; 
          $sql_address .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
          require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
          dbDelta($sql_address);

          /*user submit the post form then save all data in this table*/
          $postdata = 'userpostdata';
          $wp_post_track = $wpdb->prefix . "$postdata";
          $sql_post = "CREATE TABLE IF NOT EXISTS $wp_post_track ( ";
          $sql_post .= "  `id`  int(11)   NOT NULL auto_increment, ";
          $sql_post .= "  `userid`  varchar(1000)   NOT NULL, "; 
          $sql_post .= "  `userlatitude`  varchar(1000)   NOT NULL, ";
          $sql_post .= "  `userlongitude`  varchar(1000)   NOT NULL, ";
          $sql_post .= "  `post_title`  varchar(1000)   NOT NULL, ";
          $sql_post .= "  `post_status`  varchar(1000)   NOT NULL, ";
          $sql_post .= "  `post_id`  varchar(1000)   NOT NULL, ";
          $sql_post .= "  PRIMARY KEY `id` (`id`) "; 
          $sql_post .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
          require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
          dbDelta($sql_post);


          }

}
