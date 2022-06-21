<?php

function my_admin_menu() {

                add_menu_page(
                'Users Location',
                'Users Location',
                'manage_options',
                'maintenace-user',
                'my_admin_page_contents',
               'dashicons-schedule',3);
        
              
    }
    add_action( 'admin_menu', 'my_admin_menu' );