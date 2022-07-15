<?php
/**
 *
 * @wordpress-plugin
 * Plugin Name:       Love me
 * Description:       Display a very simple and customisable like button for your posts or any custom post type.
 * Version:           1.1.0
 * Author:            Agence Whodunit
 * Author URI:        https://www.whodunit.fr/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       love-me
 * Domain Path:       /languages
 */

if ( ! class_exists( 'Love_me' ) ) :

    class Love_me {
        private $options;

        protected static $instance = null;

        public function __construct(){
            load_plugin_textdomain(
                'love-me',
                false,
                basename( dirname( __FILE__ ) ) . '/languages/'
            );


            add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts_love_me'));



            add_action('wp_enqueue_scripts', array($this, 'love_me_scripts'));
            add_action('add_meta_boxes', array($this, 'love_me_boxes'));

            add_action('admin_enqueue_scripts', array($this, 'love_me_css'), 11, 1 );

            /**
             * Ajax
             */
            add_action('wp_ajax_love_me', array($this, 'ajax'));
            add_action('wp_ajax_nopriv_love_me', array($this, 'ajax'));


            /**
             * shortcode
             */
            add_shortcode('love_me',array($this, 'shortcode_love_me'));


            add_action( 'save_post', array($this, 'save_love_me'));


            if ( is_admin() ){

                add_action( 'admin_menu', array($this, 'love_me_add_admin_menu') );
                add_action( 'admin_init', array($this, 'love_me_settings_init') );

            }


            /**
             *
             */

            add_filter( 'the_content', array($this, 'love_me_filter_the_content') );
        }

        public function wp_enqueue_scripts_love_me() {
            $options = get_option( 'love_me_options' );


            if (!isset($options["show_love_active_dashicons"]) || $options["show_love_active_dashicons"] != true) {
                wp_enqueue_style( 'dashicons' );
            }

            if (!isset($options["show_love_active_css"]) || $options["show_love_active_css"] != true) {

                $plugins_url = plugin_dir_url( __FILE__ );
                wp_enqueue_style( 'love-me.css', $plugins_url . '/love-me.css' );
            }
        }

        public function love_me_css($hook) {

            global $current_screen, $typenow;

            if((isset($current_screen) && isset($current_screen->base) && $current_screen->base == 'settings_page_love_me_options') || ( $hook == 'post-new.php' || $hook == 'post.php' ) ){

                $plugins_url = plugin_dir_url( __FILE__ );

                wp_enqueue_style( 'love-me.css', $plugins_url . 'love-me.css' );

                wp_register_script('love-admin', $plugins_url . 'love-admin.js', array('jquery'));
                wp_enqueue_script('love-admin');

            }
        }

        public function love_me_settings_init() {
            register_setting( 'love_me_group', 'love_me_options' );

            add_settings_section(
                'love_me_options_page_section',
                __('Plugin settings', 'love-me'),
                array($this, 'love_me_options_page_settings_section_callback'),
                'love_me_settings'
            );

            add_settings_field(
                'love_me_options_active_css',
                __('Disable plugin’s CSS', 'love-me'),
                array($this, 'love_me_options_field_active_css_render'),
                'love_me_settings',
                'love_me_options_page_section'
            );

            add_settings_field(
                'love_me_options_active_dashicons',
                __('Disable plugin’s default dashicon', 'love-me'),
                array($this, 'love_me_options_field_active_dashicons_render'),
                'love_me_settings',
                'love_me_options_page_section'
            );

            add_settings_field(
                'love_me_options_field_show',
                __('Button location', 'love-me'),
                array($this, 'love_me_options_field_show_render'),
                'love_me_settings',
                'love_me_options_page_section'
            );

            add_settings_field(
                'love_me_options_field_show_on',
                __('Display like button on the following post types', 'love-me'),
                array($this, 'love_me_options_field_show_on_render'),
                'love_me_settings',
                'love_me_options_page_section'
            );
        }
        public function love_me_options_page_settings_section_callback() {
            echo '<p>' . __('This plugin displays a very simple and customisable like button for your posts or any custom post type.', 'love-me') . '</p>';
            echo '<p>' . __('One shortcode available:', 'love-me') . '</p>';
            echo '<ul><li>–';
            printf(
            /* translators: %s is a shortcode used by the plugin */
                __( '%s which displays the like button and the number of likes received', 'love-me' ),
                '<code>[love_me]</code>'
            );
            echo '</li></ul>';
        }

        public function love_me_options_field_active_css_render() {
            $options = get_option( 'love_me_options' );

            $checked = "";
            if (isset($options["show_love_active_css"]) && $options["show_love_active_css"] == true) {
                $checked = " checked='checked' ";
            }
            echo '<input id="show_love_active_css" type="checkbox" id="show_love_active_css" name="love_me_options[show_love_active_css]" value="true" '.$checked.' /><label for="show_love_active_css">' . __('Check this to disable our CSS and use your own custom CSS rules.', 'love-me') . '</label>';
        }

        public function love_me_options_field_active_dashicons_render() {
            $options = get_option( 'love_me_options' );

            $checked = "";
            if (isset($options["show_love_active_dashicons"]) && $options["show_love_active_dashicons"] == true) {
                $checked = " checked='checked' ";
            }
            echo '<input id="show_love_active_dashicons" type="checkbox" id="show_love_active_dashicons" name="love_me_options[show_love_active_dashicons]" value="true" '.$checked.' /><label for="show_love_active_dashicons">' . __('Check this to disable our heart dashicon and use your own custom button appearance.', 'love-me') . '</label>';

        }

        public function love_me_options_field_show_render() {
            $options = get_option( 'love_me_options' );

            $checked = "";
            if (isset($options["show_love_me_before"]) && $options["show_love_me_before"] == true) {
                $checked = " checked='checked' ";
            }
            echo '<label for="show_love_me_before"><input id="show_love_me_before" type="checkbox" name="love_me_options[show_love_me_before]" value="true" '.$checked.' />'.__('Before the content', 'love-me').'</label><br>';

            $checked = "";
            if (isset($options["show_love_me_after"]) && $options["show_love_me_after"] == true) {
                $checked = " checked='checked' ";
            }
            echo '<label for="show_love_me_after"><input id="show_love_me_after" type="checkbox" name="love_me_options[show_love_me_after]" value="true" '.$checked.' />'.__('After the content', 'love-me').'</label><br>';

            if ((!isset($options["show_love_me_before"]) || $options["show_love_me_before"] != true) && (!isset($options["show_love_me_after"]) || $options["show_love_me_after"] != true) ) {
                echo '<p class="alert alert-warning"><span class="dashicons dashicons-warning"></span> '.__('If nothing is checked, it will add the shortcode in the PHP code', 'love-me').'</p>';
            }
        }


        public function love_me_options_field_show_on_render() {
            $options = get_option( 'love_me_options' );

            $args = array(
                'public'   => true
            );
            $post_types = get_post_types( $args );

            foreach ($post_types as $key => $value) {
                $checked = " ";
                if (isset($options["cpt_".$key]) && $options["cpt_".$key] == true) {
                    $checked = " checked='checked' ";
                }
                echo '<label for="cpt_'.$key.'"><input id="cpt_'.$key.'" type="checkbox" name="love_me_options[cpt_'.$key.']" value="true" '.$checked.' />'.$value.'<br></label>';
            }
        }


        public function love_me_options_page() {

            echo '<div class="wrap love-me-options">
            <h1>'.__("Love me Settings", 'love-me').'</h1>
            <form method="post" action="options.php">';

            // This prints out all hidden setting fields
            settings_fields( 'love_me_group' );
            do_settings_sections( 'love_me_settings' );
            submit_button();

            echo'</form>
        </div>';

        }

        public function love_me_add_admin_menu() {
            add_options_page(__('Configuration Love me', 'love-me'), __('Love me', 'love-me'), 'manage_options', 'love_me_options', array($this, 'love_me_options_page') );
        }

        public function save_love_me($post_id) {

            $post_type = get_post_type($post_id);


            if ( !in_array($post_type, $this->get_all_cpt()) ) return;

            if(isset($_POST) && count($_POST) > 0) {
                if(isset($_POST['update-likes-yes']) && $_POST['update-likes-yes'] == 1) {

                    if (  ! isset( $_POST['love_me_metabox_nonce'] ) || ! wp_verify_nonce( $_POST['love_me_metabox_nonce'], 'love_me_nonce' )) {
                        print __('Sorry, your nonce did not verify.', 'love-me');
                        exit;
                    }

                    $love = get_post_meta( $post_id, 'love_me_like', true );

                    $likes_update = (isset($_POST['update-likes']) && is_numeric($_POST['update-likes'])) ? $_POST['update-likes'] : 0;

                    if(empty($love) || !is_numeric($love)) {
                        $love = $likes_update;
                        add_post_meta($post_id, 'love_me_like', $love, true);
                        update_post_meta($post_id, 'love_me_like', $love);
                    } else {
                        update_post_meta($post_id, 'love_me_like', $likes_update);
                    }
                }

                $love_hide = get_post_meta( $post_id, 'love_me_hide', true );
                if(empty($love_hide)) {
                    add_post_meta($post_id, 'love_me_hide', 0, true);
                }
                $count_hide = (isset($_POST['hide-like-button']) && is_numeric($_POST['hide-like-button'])) ? $_POST['hide-like-button'] : 0;

                update_post_meta($post_id, 'love_me_hide', $count_hide);
            }

        }

        public function love_me_filter_the_content($content) {

            if ( !in_array(get_post_type(), $this->get_all_cpt()) ) return $content;

            $options = get_option( 'love_me_options' );
            if (isset($options["show_love_me_before"]) && $options["show_love_me_before"] == true) {
                $content = do_shortcode('[love_me]') . $content;
            }
            if (isset($options["show_love_me_after"]) && $options["show_love_me_after"] == true) {
                $content = $content . do_shortcode('[love_me]');
            }

            return $content;

        }

        public function shortcode_love_me($atts, $content = null) {

            $id_post = get_the_ID();
            $love_hide = get_post_meta( $id_post, 'love_me_hide', true );
            if(isset($love_hide) && $love_hide == 1) {
                return;
            }
            $love    = get_post_meta( $id_post, 'love_me_like', true );

            if(empty($love) || !is_numeric($love)) {
                $love = 0;
            }
            $love_ip = get_post_meta( $id_post, 'love_me_ips', true );

            $love_ip = unserialize($love_ip);
            $class = '';
            $checked = '';
            if(!empty($love_ip)) {
                $ip_client = $_SERVER['REMOTE_ADDR'];
                if(in_array($ip_client, $love_ip)) {
                    $class = ' liked';
                    $checked = 'checked';
                }
            }
			$honey_like_count=intval(get_post_meta($id_post, 'love_me_like', true));
			 if($honey_like_count==0){
				 $class_="";
			 }else{
				 $class_="liked";
			 }
			$precision = 1;
			 // function number_format_short($n, $precision = 1 ) {
			  if ($love < 900) {
				// 0 - 900
				$n_format = number_format($love, $precision);
				$n_format=intval($n_format);
				$suffix = '';
			  } else if ($love < 900000) {
				// 0.9k-850k
				$n_format = number_format($love / 1000, $precision);
				$suffix = 'K';
			  } else if ($love < 900000000) {
				// 0.9m-850m
				$n_format = number_format($love / 1000000, $precision);
				$suffix = 'M';
			  } else if ($love < 900000000000) {
				// 0.9b-850b
				$n_format = number_format($love / 1000000000, $precision);
				$suffix = 'B';
			  } else {
				// 0.9t+
				$n_format = number_format($love / 1000000000000, $precision);
				$suffix = 'T';
			  }
			  if ( $precision > 0 ) {
				$dotzero = '.' . str_repeat( '0', $precision );
				$n_format = str_replace( $dotzero, '', $n_format );
			  }
			$get_new_data=$n_format . $suffix;
            return '<button data-type="drophoney" class="love click_animation p-0'.$class_.'"><input '.$checked.' id="post_'.$id_post.'" type="checkbox" class="LoveCheck"/>
                <label for="post_'.$id_post.'" class="dashicons dashicons-heart LoveLabel" aria-label="like this" data-type="drophoney"></label><span class="LoveCount" id="LoveCount1_'.$id_post.'">'.$get_new_data.'</span></button><!--/love-->';
        }

        public function ajax() {
            $nonce = $_POST['nonce'];

            if ( ! wp_verify_nonce( $nonce, 'love-me' ) )
                die ( 'Interdit !');


            $id_post = $_POST['post'];

            $id_post = str_replace('post_', '', $id_post);


            $love    = get_post_meta( $id_post, 'love_me_like', true );

            if(empty($love) || !is_numeric($love)) {
                $love = 0;
                add_post_meta($id_post, 'love_me_like', $love, true);
            }

            $love_ip = get_post_meta( $id_post, 'love_me_ips', true );
            if(empty($love_ip)) {
                add_post_meta($id_post, 'love_me_ips', '', true);
            }
            $ip_client = $_SERVER['REMOTE_ADDR'];

            $message = array();

            if(empty($love_ip)) {

                $love_ip = array($_SERVER['REMOTE_ADDR']);
				$author_id = get_post_field( 'post_author', $id_post);
				$goes_to_user_profile=(1.5/100)*25;
				$goes_to_post=(1.5/100)*75;
				$created_post_author = get_user_meta($author_id, 'mycred_default', true);
				$new_author_honey_count=$created_post_author+$goes_to_user_profile;
				$check = update_user_meta($author_id, 'mycred_default', $new_author_honey_count);
				 $love = $love + $goes_to_post;
                update_post_meta($id_post, 'love_me_like', $love);

                update_post_meta($id_post, 'love_me_ips', serialize($love_ip));
            } else {
               /* $love_ip = unserialize($love_ip);
                if(in_array($ip_client, $love_ip)) {

                    if (($key = array_search($ip_client, $love_ip)) !== false) {
                        unset($love_ip[$key]);
                    }
                    $love = $love - 1.5;
                    update_post_meta($id_post, 'love_me_like', $love);
                    update_post_meta($id_post, 'love_me_ips', serialize($love_ip));
                } else {
                    $love_ip[] = $_SERVER['REMOTE_ADDR'];

                    $love = $love + 1.5;
                    update_post_meta($id_post, 'love_me_like', $love);
                    update_post_meta($id_post, 'love_me_ips', serialize($love_ip));
                }*/
            }
            $message['likes'] = $love;




            echo json_encode($message);
            die();

        }

        public function love_me_scripts() {
            $plugins_url = plugin_dir_url( __FILE__ );

            wp_register_script('love', $plugins_url . 'love.js', array('jquery'));
            wp_enqueue_script('love');

            wp_localize_script('love', 'love_me', array(
                    'url'   => admin_url('admin-ajax.php'),
                    'nonce' => wp_create_nonce('love-me'),
                )
            );

        }

        public function love_me_boxes() {
            $post_types = $this->get_all_cpt();

            if(isset($post_types) && count($post_types) > 0) {
                add_meta_box( 'meta-box-love-me', __( 'Like button', 'love-me' ) . '  <span class="dashicons dashicons-heart"></span> ' . __( 'Love me Settings', 'love-me' ), array($this, 'love_me_metabox'), $post_types, 'side' );
            }
        }

        public function love_me_metabox($post) {

            $love = get_post_meta( $post->ID, 'love_me_like', true );


            if(empty($love) || !is_numeric($love)) {
                $love = 0;
                add_post_meta($post->ID, 'love_me_like', $love, true);
                update_post_meta($post->ID, 'love_me_like', $love);
            }

            wp_nonce_field( 'love_me_nonce', 'love_me_metabox_nonce');

            echo "<p><em>" . __('Current count:', 'love-me') . " <span class=\"badge badge-info\">" . $love . "</span></em></p>";

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

            echo '<p><input id="hide-like-button" name="hide-like-button" type="checkbox" value="1" '.$checked.' /> <label for="hide-like-button">'.__('Hide like button for this post', 'love-me').'</label></p>';

            echo '<p><input id="update-likes-yes" name="update-likes-yes" type="checkbox" value="1" /> <label for="update-likes-yes">'.__('Manual update', 'love-me').'</label></p>';

            echo '<div id="love-me-update-wrapper"><p><label for="update-likes">' . __('New total:', 'love-me') . '</label> <input class="small-text" id="update-likes" name="update-likes" type="number" value="'.$love.'" /><br /><small>' . __('You’ll need to update your post to save changes.', 'love-me') . '</small></p></div>';

        }

        private function get_all_cpt() {
            $args = array(
                'public'   => true
            );
            $post_types = get_post_types( $args );
            $options = get_option( 'love_me_options' );

            $cpts = array();
            foreach ($post_types as $key => $value) {
                if(isset($options['cpt_' . $key])) {
                    $cpts[$key] = $value;
                }
            }

            //unset($post_types['attachment']);

            return apply_filters( 'love_me_add_cpt', $cpts );
        }

        public static function get_instance() {

            if ( null == self::$instance ) {
                self::$instance = new self;
            }

            return self::$instance;
        }
    }



    Love_me::get_instance();


endif;

