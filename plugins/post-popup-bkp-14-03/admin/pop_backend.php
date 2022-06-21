<?php

if (!defined('ABSPATH'))
    exit;

if (!class_exists('OCCP_menu')) {

    class OCCP_menu {

        protected static $instance;

        function myplugin_register_settings() {
            add_option('cp_btn_txt','Quick View');
            add_option('cp_dis_autor','yes');
            add_option('cp_dis_cat','yes');
            add_option('cp_dis_date','yes');
            add_option('cp_header_clr','#ffffff');
            register_setting( 'myplugin_options_group', 'cp_btn_txt' );
            register_setting( 'myplugin_options_group', 'cp_btn_bg_clr' );
            register_setting( 'myplugin_options_group', 'cp_btn_ft_size' );
            register_setting( 'myplugin_options_group', 'cp_btn_ft_clr' );

            register_setting( 'myplugin_options_group', 'cp_dis_autor' );
            register_setting( 'myplugin_options_group', 'cp_dis_cat' );
            register_setting( 'myplugin_options_group', 'cp_dis_date' );

            register_setting( 'myplugin_options_group', 'cp_popup_clr' );
            register_setting( 'myplugin_options_group', 'cp_discrption_clr' );
            register_setting( 'myplugin_options_group', 'cp_discrption_ft_size' );
            register_setting( 'myplugin_options_group', 'cp_header_clr' );

        }
        

        function myplugin_register_options_page() {
          add_options_page('Post Popup', 'Post Popup', 'manage_options', 'post-popup', array($this, 'myplugin_options_page'));
        }


        function myplugin_options_page()
        { ?>
            <div>
                <?php screen_icon(); ?>
                <div class="custom_part">
                    <form method="post" action="options.php">
                        <?php settings_fields( 'myplugin_options_group' ); ?>
                        <h3>Post Popup Setting</h3>
                        
                        <table class="cp_table">
                            <tr class="cp_top">
                                <td colspan="2">
                                    <h4>Post Button Setting</h4>
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_btn_txt">Button Text</label>
                                </th>
                                <td>
                                    <input type="text" id="cp_btn_txt" name="cp_btn_txt" value="<?php echo get_option('cp_btn_txt'); ?>" />
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_btn_bg_clr">Button Background Color</label>
                                </th>
                                <td>
                                    <input type="color" id="cp_btn_bg_clr" name="cp_btn_bg_clr" value="<?php echo get_option('cp_btn_bg_clr'); ?>"/>
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th scope="row">
                                    <label for="cp_btn_ft_size">Button Font Size</label>
                                </th>
                                <td>
                                    <input type="number" id="cp_btn_ft_size" name="cp_btn_ft_size" value="<?php echo get_option('cp_btn_ft_size'); ?>"/>
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_btn_ft_clr">Button Font Color</label>
                                </th>
                                <td>
                                    <input type="color" id="cp_btn_ft_clr" name="cp_btn_ft_clr" value="<?php echo get_option('cp_btn_ft_clr'); ?>"/>
                                </td>
                            </tr>
                            
                            <tr class="cp_top">
                                <td colspan="2">
                                    <h4>Popup Contain Setting</h4>
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_dis_autor">Display Post Author</label>
                                </th>
                                <td>
                                    <input type="checkbox" id="cp_dis_autor" name="cp_dis_autor" value="yes" <?php if(get_option('cp_dis_autor') == "yes" ){ echo "checked"; } ?>/>
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_dis_cat">Display Post Category</label>
                                </th>
                                <td>
                                    <input type="checkbox" id="cp_dis_cat" name="cp_dis_cat" value="yes" <?php if(get_option('cp_dis_cat') == "yes" ){ echo "checked"; } ?>/>
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_dis_date">Display Created Date</label>
                                </th>
                                <td>
                                    <input type="checkbox" id="cp_dis_date" name="cp_dis_date" value="yes" <?php if(get_option('cp_dis_date') == "yes" ){ echo "checked"; } ?> />
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <td colspan="2">
                                    <h4>Popup Design Setting</h4>
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_popup_clr"> Popup Body Color</label>
                                </th>
                                <td>
                                    <input type="color" id="cp_popup_clr" name="cp_popup_clr" value="<?php echo get_option('cp_popup_clr'); ?>" />
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_discrption_clr">Discription Font Color</label>
                                </th>
                                <td>
                                    <input type="color" id="cp_discrption_clr" name="cp_discrption_clr" value="<?php echo get_option('cp_discrption_clr'); ?>" />
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_discrption_ft_size">Discription Font Size</label>
                                </th>
                                <td>
                                    <input type="number" id="cp_discrption_ft_size" name="cp_discrption_ft_size" value="<?php echo get_option('cp_discrption_ft_size'); ?>"/>
                                </td>
                            </tr>
                            <tr class="cp_top">
                                <th>
                                    <label for="cp_header_clr">Header Font Color</label>
                                </th>
                                <td>
                                    <input type="color" id="cp_header_clr" name="cp_header_clr" value="<?php echo get_option('cp_header_clr'); ?>"/>
                                </td>
                            </tr>

                        </table>
                        <?php submit_button(); ?>
                    </form>
                </div>
                <div class="custom_part">
                    <h3>Note</h3>
                    <p>If this plugin can not support your theme then put this function <input type="text" value="oc_popup_post_button()" id="function" onclick="cp_select_data(this.id)" readonly> in your file which you want to apply popup box button.</p>
                    <img src="<?php echo POP_PLUGIN_DIR ?>/includes/images/function_img.png">
                    <p>If you want to add quick view button use this shortcode anywhere <input type="text" value='[post_popup id="93"]' id="shortcode" onclick="cp_select_data(this.id)" readonly> just need to pass <strong>post id</strong></p>
                </div>
            </div>
        <?php
        } 


        function init() {
            add_action('admin_menu', array($this,'myplugin_register_options_page'));
            add_action( 'admin_init', array($this,'myplugin_register_settings' ));
        }


        public static function instance() {
            if (!isset(self::$instance)) {
                self::$instance = new self();
                self::$instance->init();
            }
            return self::$instance;
        }
    }
    OCCP_menu::instance();
}
