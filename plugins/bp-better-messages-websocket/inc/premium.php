<?php
defined( 'ABSPATH' ) || exit;

/**
 * Class BP_Premium_Messages
 *
 * This used only when user using WebSocket version to communicate site with websocket server
 */
class BP_Better_Messages_Premium
{

    public $site_id;
    public $secret_key;

    public static function instance()
    {

        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been run previously
        if ( null === $instance ) {
            $instance = new BP_Better_Messages_Premium;
            $instance->setup_globals();
            $instance->setup_actions();
        }

        // Always return the instance
        return $instance;

        // The last metroid is in captivity. The galaxy is at peace.
    }

    public function setup_globals()
    {
        $site_url = site_url( '' );

        if( defined('BP_BETTER_MESSAGES_FORCE_DOMAIN') ){
            $site_url = BP_BETTER_MESSAGES_FORCE_DOMAIN;
        }

        $this->site_id = BP_Better_Messages()->functions->clean_site_url( $site_url );


        if( bpbm_fs()->is_trial() ){
            $secret_key = bpbm_fs()->get_site()->secret_key;
        } else {
            $license_key = bpbm_fs()->_get_license()->secret_key;
            if( defined('BP_BETTER_MESSAGES_FORCE_LICENSE_KEY') ){
                $license_key = BP_BETTER_MESSAGES_FORCE_LICENSE_KEY;
            }

            $secret_key = $license_key;
        }

        $this->secret_key = $secret_key;
    }

    public function setup_actions()
    {
        add_action( 'messages_message_sent', array( $this, 'on_message_sent' ) );
        add_action( 'init', array( $this, 'register_event' ) );
        add_action( 'bp_better_messages_sync_unread', array( $this, 'sync_unread' ) );
        add_action( 'bp_better_messages_sync_unread', array( $this, 'update_last_activity' ) );

        add_action( 'bp_better_messages_on_message_not_sent', array($this, 'on_message_not_sent'), 10, 2 );

        add_action( 'bp_better_messages_message_deleted', array( $this, 'on_message_deleted' ), 10, 2 );

        add_action( 'wp_ajax_bp_better_messages_save_user_push_subscription', array( $this, 'save_user_push_subscription' ) );
        add_action( 'wp_ajax_bp_better_messages_delete_user_push_subscription', array( $this, 'delete_user_push_subscription' ) );

        add_action( 'bp_better_chat_settings_updated', array( $this, 'install_push_workers_script' ) );

        add_action( 'bp_better_messages_thread_div', array( $this, 'add_thread_secret_key' ) );

        add_action( 'updated_user_meta', array( $this, 'on_usermeta_update'), 10, 4 );

        add_filter( 'bp_better_messages_avatar_extra_attr', array( $this, 'add_status_color_to_avatar'), 10, 2 );

        add_action( 'bp_notification_after_save', array( $this, 'buddypress_on_new_notification'), 10, 1 );

        add_action( 'bp_better_messages_thread_reaction', array( $this, 'thread_reaction' ), 10, 3 );
    }

    public function thread_reaction( $message_id, $thread_id, $new_reactions ){
        $request = [
            'site_id'    => $this->site_id,
            'secret_key' => sha1( $this->site_id . $this->secret_key ),
            'thread_id'  => $thread_id,
            'data'       => [
                'mid' => $message_id,
                'new' => $new_reactions
            ],
        ];

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');

        wp_remote_post( $socket_server . 'sendThreadEvent', array(
            'body' => $request,
            'blocking' => false
        ) );
    }

    public function buddypress_on_new_notification( &$notification ){
        global $bp;

        $action  = false;
        $user_id = false;

        $title = '';
        $image = '';
        $url   = '';

        if( $notification->component_name === 'groups' ){
            if( BP_Better_Messages()->settings['groupsOnSiteNotifications'] === '1' ) {
                $group_id = $notification->item_id;
                $group = groups_get_group( $group_id );
                $group_link = bp_get_group_permalink( $group );
                $title = $group->name;

                $image = bp_core_fetch_avatar( array(
                    'item_id'    => $group_id,
                    'avatar_dir' => 'group-avatars',
                    'object'     => 'group',
                    'type'       => 'thumb',
                    'width'      => 50,
                    'height'     => 50,
                ));

                $user_id = $notification->user_id;

                if ($notification->component_action === 'new_membership_request') {
                    $action = true;
                    $url = $group_link . 'admin/membership-requests/?n=1';
                }

                if ($notification->component_action === 'membership_request_rejected') {
                    $action = true;
                    $url = trailingslashit( bp_core_get_user_domain($user_id) . bp_get_groups_slug() ) . '?n=1';
                }

                if ($notification->component_action === 'membership_request_accepted') {
                    $action = true;
                    $url = $group_link . '?n=1';
                }

                if ($notification->component_action === 'member_promoted_to_admin') {
                    $action = true;
                    $url = trailingslashit( bp_core_get_user_domain($user_id) . bp_get_groups_slug() ) . '?n=1';
                }

                if ($notification->component_action === 'member_promoted_to_mod') {
                    $action = true;
                    $url = trailingslashit( bp_core_get_user_domain($user_id) . bp_get_groups_slug() ) . '?n=1';
                }

                if ($notification->component_action === 'group_invite') {
                    $action = true;
                    $url = trailingslashit( bp_core_get_user_domain($user_id) . bp_get_groups_slug() ) . '/invites/?n=1';
                }
            }
        }

        if( $notification->component_name === 'friends' ){

            if( BP_Better_Messages()->settings['friendsOnSiteNotifications'] === '1' ) {

                if ($notification->component_action === 'friendship_request') {
                    $action = true;

                    $title = __('New friendship request', 'bp-better-messages');

                    if (isset($notification->item_id)) {
                        $image = BP_Better_Messages()->functions->get_avatar($notification->item_id, 50);
                    }

                    if (isset($notification->user_id)) {
                        $user_id = $notification->user_id;
                        $url = bp_core_get_user_domain($user_id) . bp_get_friends_slug() . '/requests/?new';
                    }
                }

                if ($notification->component_action === 'friendship_accepted') {
                    $action = true;

                    $title = __('Friendship request accepted', 'bp-better-messages');

                    if (isset($notification->item_id)) {
                        $image = BP_Better_Messages()->functions->get_avatar($notification->item_id, 50);
                    }

                    if (isset($notification->user_id)) {
                        $user_id = $notification->user_id;
                        $url = bp_core_get_user_domain($user_id) . bp_get_friends_slug() . '/my-friends';
                    }
                }

            }
        }

        if( $action === false ){
            return;
        }

        if ( isset( $bp->{ $notification->component_name }->notification_callback ) && is_callable( $bp->{ $notification->component_name }->notification_callback ) ) {
            $description = call_user_func( $bp->{ $notification->component_name }->notification_callback, $notification->component_action, $notification->item_id, $notification->secondary_item_id, 1, 'string', $notification->id );
        } elseif ( isset( $bp->{ $notification->component_name }->format_notification_function ) && function_exists( $bp->{ $notification->component_name }->format_notification_function ) ) {
            $description = call_user_func( $bp->{ $notification->component_name }->format_notification_function, $notification->component_action, $notification->item_id, $notification->secondary_item_id, 1 );
        } else {
            $description = apply_filters_ref_array( 'bp_notifications_get_notifications_for_user', array( $notification->component_action, $notification->item_id, $notification->secondary_item_id, 1, 'string', $notification->component_action, $notification->component_name, $notification->id ) );
        }

        $url = apply_filters( 'better_messages_buddypress_notification_url', $url, $notification );

        $description = apply_filters( 'bp_get_the_notification_description', $description, $notification );

        $text = strip_tags( $description );

        $this->send_on_site_notification( $user_id, $title, $url, $image, $text );
    }

    public function send_on_site_notification( $user_id, $title, $url, $image, $text ){
        $request = [
            'site_id'    => $this->site_id,
            'secret_key' => sha1( $this->site_id . $this->secret_key ),
            'user_id'    => $user_id,
            'title'      => $this->encrypt_message_for_user($title, $user_id),
            'url'        => $this->encrypt_message_for_user($url, $user_id),
            'image'      => $this->encrypt_message_for_user($image, $user_id),
            'text'       => $this->encrypt_message_for_user($text, $user_id)
        ];

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');

        wp_remote_post( $socket_server . 'sendOnSiteNotification', array(
            'body' => $request,
            'blocking' => false
        ) );
    }

    public function endCall( $thread_id, $message = '' ) {
        $request = [
            'site_id'    => $this->site_id,
            'secret_key' => sha1( $this->site_id . $this->secret_key ),
            'thread_id'  => $thread_id,
            'user_ids'   => array_keys(BP_Messages_Thread::get_recipients_for_thread( $thread_id )),
            'message'    => $message
        ];

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');

        wp_remote_post( $socket_server . 'endCall', array(
            'body' => $request,
            'blocking' => false
        ) );
    }

    public function install_push_workers_script( $settings ){
        $file = BP_Better_Messages()->path . 'assets/js/bpbm-worker.min.js';
        $target_file = ABSPATH . 'bpbm-worker.js';

        if( $settings['enablePushNotifications'] === '1' ){
            file_put_contents( $target_file, file_get_contents( $file ) );
        } else {
            if( file_exists( $target_file ) ) {
                unlink($target_file);
            }
        }
    }

    public function save_user_push_subscription(){
        if ( ! wp_verify_nonce($_POST['nonce'], 'save_user_push_subscription' ) ) {
            wp_send_json_error();
        }

        $user_id      = get_current_user_id();
        $subscription = json_decode(wp_unslash($_POST['sub']));

        $user_push_subscriptions = get_user_meta( $user_id, 'bpbm_messages_push_subscriptions', true );
        if( empty( $user_push_subscriptions ) || ! is_array( $user_push_subscriptions ) ) $user_push_subscriptions = array();
        $user_push_subscriptions[ $subscription->endpoint ] = (array) $subscription->keys;
        update_user_meta( $user_id, 'bpbm_messages_push_subscriptions', $user_push_subscriptions );
        wp_send_json_success();
    }

    public function delete_user_push_subscription(){
        if ( ! wp_verify_nonce($_POST['nonce'], 'save_user_push_subscription' ) ) {
            wp_send_json_error();
        }

        $user_id      = get_current_user_id();
        $subscription = json_decode(wp_unslash($_POST['sub']));

        $user_push_subscriptions = get_user_meta( $user_id, 'bpbm_messages_push_subscriptions', true );
        if( empty( $user_push_subscriptions ) || ! is_array( $user_push_subscriptions ) ) $user_push_subscriptions = array();

        if( isset( $user_push_subscriptions[ $subscription->endpoint ] ) ){
            unset( $user_push_subscriptions[ $subscription->endpoint ] );
        } #(array) $subscription->keys;

        update_user_meta( $user_id, 'bpbm_messages_push_subscriptions', $user_push_subscriptions );

        wp_send_json_success();
    }

    public function send_bulk_push_notification( $notifications ){
        $prepare_bulk_data = [];

        foreach( $notifications as $user_id => $notification ){
            $user_push_subscriptions = get_user_meta( $user_id, 'bpbm_messages_push_subscriptions', true );
            if( empty( $user_push_subscriptions ) ) {
                continue;
            }

            $subs = [];
            foreach( $user_push_subscriptions as $endpoint => $keys ){
                $subs[] = [
                    'endpoint' => $endpoint,
                    'keys'     => $keys,
                ];
            }

            $prepare_bulk_data[] = [
                'subs'         => $subs,
                'user_id'      => $user_id,
                'notification' => $notification
            ];
        }

        if( empty( $prepare_bulk_data ) ) return false;

        $email = get_option('admin_email');

        $request = [
            'site_id'       => $this->site_id,
            'secret_key'    => sha1( $this->site_id . $this->secret_key ),
            'email'         => $email,
            'pushs'         => $prepare_bulk_data,
            'vapidKeys'     => $this->get_vapid_keys()
        ];

        $request = BP_Better_Messages()->functions->array_map_recursive('strval', $request);

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');

        wp_remote_post( $socket_server . 'sendPushNotifications', array(
            'headers'  => array('Content-Type' => 'application/json'),
            'body'     => json_encode($request),
            'blocking' => false
        ) );

        return true;
    }

    public function send_push_notification( $user_id, $notification ){
        $user_push_subscriptions = get_user_meta( $user_id, 'bpbm_messages_push_subscriptions', true );
        if( empty( $user_push_subscriptions ) ) return false;

        $subs = array();
        foreach( $user_push_subscriptions as $endpoint => $keys ){
            $subs[] = [
                'endpoint' => $endpoint,
                'keys'     => $keys
            ];
        }

        $email = get_option('admin_email');

        $request = [
            'site_id'       => $this->site_id,
            'secret_key'    => sha1( $this->site_id . $this->secret_key ),
            'user_id'       => $user_id,
            'email'         => $email,
            'notification'  => $notification,
            'subs'          => $subs,
            'vapidKeys'     => $this->get_vapid_keys()
        ];

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');

        wp_remote_post( $socket_server . 'sendPushNotification', array(
            'body' => $request,
            'blocking' => false
        ) );

        return true;
    }

    public function register_event()
    {
        if ( ! wp_next_scheduled( 'bp_better_messages_sync_unread' ) ) {
            wp_schedule_event( time(), 'one_minute', 'bp_better_messages_sync_unread' );
        }
    }

    public function get_vapid_keys(){
        $vapid_keys = get_option( 'bp_better_messages_vapid_keys', false );

        if( $vapid_keys !== false && ! empty( $vapid_keys ) ){
            return (array) $vapid_keys;
        }

        $data = array(
            'site_id'    => $this->site_id,
            'secret_key' => sha1( $this->site_id . $this->secret_key )
        );

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');
        $request = wp_remote_post( $socket_server . 'generateVAPIDKeys', array(
            'body'     => $data,
            'timeout'  => 120
        ) );

        if ( is_wp_error( $request ) ) {
            return false;
        }

        $vapid_keys = json_decode($request['body']);

        update_option('bp_better_messages_vapid_keys', $vapid_keys);

        return (array) $vapid_keys;
    }

    public function on_message_not_sent( $thread_id, $errors ){
        if( ! isset( $_REQUEST['tempID'] ) ) return false;
        $tempID = sanitize_text_field($_REQUEST['tempID']);
        $this->on_message_deleted( $tempID, array_keys(BP_Messages_Thread::get_recipients_for_thread($thread_id)) );
    }

    public function random_string($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));

        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }

        return $key;
    }

    public function add_thread_secret_key($thread_id){
        if( BP_Better_Messages()->settings['encryptionEnabled'] === '1') {
            $thread_key = BP_Better_Messages()->functions->get_thread_meta( $thread_id, 'secret_key' );
            if( empty($thread_key) ){
                $thread_key = $this->random_string(20);
                BP_Better_Messages()->functions->update_thread_meta( $thread_id, 'secret_key', $thread_key );
            }

            echo ' data-secret="' . $thread_key. '"';
        }
    }

    public function get_user_secret_key( $user_id ){
        $secret_key = get_user_meta( $user_id, 'bpbm_secret_key', true );

        if( empty($secret_key) ){
            $secret_key = $this->random_string(20);
            update_user_meta( $user_id, 'bpbm_secret_key', $secret_key );
        }

        return $secret_key;
    }

    public function get_site_secret_key(){
        $secret_key = get_option( 'bm_site_secret_key', '' );

        if( empty($secret_key) ){
            $secret_key = $this->random_string(20);
            update_option( 'bm_site_secret_key', $secret_key );
        }

        return $secret_key;
    }

    public function encrypt_message( $message, $thread_id ){
        if( BP_Better_Messages()->settings['encryptionEnabled'] !== '1') {
            return $message;
        }

        $thread_key = BP_Better_Messages()->functions->get_thread_meta( $thread_id, 'secret_key' );
        return BPBM_AES256::encrypt( $message, $thread_key );
    }

    public function encrypt_message_for_user( $message, $user_id ){
        if( BP_Better_Messages()->settings['encryptionEnabled'] !== '1') {
            return $message;
        }

        $secret_key = $this->get_user_secret_key( $user_id );
        return BPBM_AES256::encrypt( $message, $secret_key );
    }

    public function encrypt_message_for_website( $message ){
        if( BP_Better_Messages()->settings['encryptionEnabled'] !== '1') {
            return $message;
        }

        $secret_key = $this->get_site_secret_key();
        return BPBM_AES256::encrypt( $message, $secret_key );
    }

    public function on_message_sent( $message )
    {
        if( isset( $message->sender_id ) ) {
            $user_id = $message->sender_id;
        } else {
            $user_id = get_current_user_id();
        }

        $recipients = array();

        /**
         * Copy message so we can play with it
         */
        $message_copy = clone $message;

        $thread_id        = $message->thread_id;
        $message->message = convert_smilies( $message->message );

        if( isset( $message->count_unread ) ){
            $count_unread = $message->count_unread;
        } else {
            $count_unread = '1';
        }

        $send_global = true;
        $send_push   = $message->send_push;

        $chat_id = BP_Better_Messages()->functions->get_thread_meta( $thread_id, 'chat_id' );

        if( ! empty( $chat_id ) ) {
            $excluded_from_thread_list = BP_Better_Messages()->functions->get_thread_meta($thread_id, 'exclude_from_threads_list');
            if( ! empty( $excluded_from_thread_list ) ) {
                $count_unread = '0';
                $send_global  = false;
            } else {
                $notifications_enabled = BP_Better_Messages()->functions->get_thread_meta( $thread_id, 'enable_notifications' );
                if( $notifications_enabled !== '1' ){
                    $send_push = false;
                }
            }
        }

        // All recipients
        $dummy_recipients = array();

        foreach ( $message->recipients as $recipient ) {
            if ( is_object( $recipient ) ) {
                $dummy_recipients[ $recipient->user_id ] = $recipient->user_id;
            } else {
                $dummy_recipients[ $recipient ] = $recipient;
            }
        }

        $dummy_recipients[ $message->sender_id ] = $message->sender_id;

        $message_copy->user_id = $message_copy->sender_id;
        $message_copy->recipients = $dummy_recipients;

        unset( $message_copy->recipients[ $message->sender_id ] );

        $current_user_is_participant = false;

        if( $message->sender_id == $user_id || isset($message->recipients[ $user_id ] ) ){
            $current_user_is_participant = true;
        }

        if( count( $dummy_recipients ) === 1 && (int) array_shift($dummy_recipients) === (int) $message->sender_id){
            // Self Message
        } else if( $current_user_is_participant ) {
            $message_copy->unread_count = 0;
            $message_copy->message_id   = $message_copy->id;

            if( has_filter('bp_better_messages_thread_displayname', array( BP_Better_Messages_Hooks(), 'verified_member_badge' ) ) ) {
                remove_filter('bp_better_messages_thread_displayname', array( BP_Better_Messages_Hooks(), 'verified_member_badge' ) );
                $name = $this->encrypt_message_for_user(apply_filters( 'bp_better_messages_thread_displayname', bp_core_get_user_displayname( $message_copy->sender_id ), $message_copy->sender_id, $message_copy->thread_id ), $user_id);
                add_filter('bp_better_messages_thread_displayname', array( BP_Better_Messages_Hooks(), 'verified_member_badge' ), 10, 3 );
            } else {
                $name = $this->encrypt_message_for_user(apply_filters( 'bp_better_messages_thread_displayname', bp_core_get_user_displayname( $message_copy->sender_id ), $message_copy->sender_id, $message_copy->thread_id ), $user_id);
            }

            $thread_message_copy = clone $message_copy;
            $thread_message_copy->message = BP_Better_Messages()->functions->format_message( $thread_message_copy->message, $thread_message_copy->id, 'site', $user_id );

            $item = array(
                'user_id'      => $user_id,
                'total_unread' => BP_Messages_Thread::get_total_threads_for_user( $user_id, 'inbox', 'unread' ),
                'message'      => $this->encrypt_message( BP_Better_Messages()->functions->format_message( $message_copy->message, $message_copy->id, 'stack', $user_id), $thread_id),
                'avatar'       => $this->encrypt_message_for_user(BP_Better_Messages_Functions()->get_avatar( $message_copy->sender_id, 40 ), $user_id),
                'name'         => $name
            );

            if( $send_global ){
                $item['html']         = $this->encrypt_message_for_user( BP_Better_Messages()->functions->render_thread( $thread_message_copy, $user_id ), $user_id);
                $item['content_site'] = $this->encrypt_message_for_user(BP_Better_Messages()->functions->format_message( $message_copy->message, $message_copy->id, 'site', $user_id ), $user_id);
            }

            $recipients[] = $item;
        }

        $many_users_mode = false;

        if( count( $message->recipients ) > 5 ){
            $online_users = $this->get_online_users();
            $many_users_mode = true;
        }

        $site_name   = false;
        $site_avatar = false;

        $thread_type = BP_Better_Messages()->functions->get_thread_type( $thread_id );

        if( $thread_type === 'group' ){
            /**
             * BuddyPress Groups
             */
            if( class_exists('BP_Groups_Group') ) {
                $group_id = BP_Better_Messages()->functions->get_thread_meta($thread_id, 'group_id');
                $group = new BP_Groups_Group((int)$group_id);
                $site_name = bp_get_group_name($group);

                $avatar = bp_core_fetch_avatar(array(
                    'item_id' => $group_id,
                    'avatar_dir' => 'group-avatars',
                    'object' => 'group',
                    'type' => 'thumb',
                    'html' => true,
                ));

                if (!!$avatar) {
                    $site_avatar = $avatar;
                }
            }

            if( class_exists('PeepSoGroup') ){
                $group_id = BP_Better_Messages()->functions->get_thread_meta($thread_id, 'peepso_group_id');
                $group = new PeepSoGroup( (int) $group_id );

                $site_name = $group->name;
                $site_avatar = $group->get_avatar_url();
            }

            if( class_exists('UM_Groups') ){
                $group_id = BP_Better_Messages()->functions->get_thread_meta($thread_id, 'peepso_group_id');
                $group    = get_post( (int) $group_id );

                $site_name   = esc_html($group->post_title);
                $avatar = UM()->Groups()->api()->get_group_image( $group->ID, 'default', 50, 50, false );
                $site_avatar = $avatar;
            }

        }

        if( $thread_type === 'chat-room' ){
            $chat_id = BP_Better_Messages()->functions->get_thread_meta( $thread_id, 'chat_id' );
            $site_name = get_the_title( $chat_id );
        }

        foreach ( $message->recipients as $recipient ) {
            if ( is_object( $recipient ) ) {
                $_user_id = $recipient->user_id;
            } else {
                $_user_id = $recipient;
            }

            $message_copy->recipients = $dummy_recipients;
            unset( $message_copy->recipients[ $_user_id ] );

            //$message_copy->unread_count = BP_Better_Messages()->functions->get_thread_count( $message_copy->thread_id, $_user_id );
            $message_copy->message_id = $message_copy->id;

            $recipient = array(
                'user_id'      => $_user_id,
                //'total_unread' => BP_Messages_Thread::get_total_threads_for_user( $_user_id, 'inbox', 'unread' ),
            );

            if( $many_users_mode && ! isset( $online_users[$_user_id] ) ){
            } else {
                $message_copy->unread_count = 0;
                $recipient['message']       = $this->encrypt_message(BP_Better_Messages()->functions->format_message($message_copy->message, $message_copy->id, 'stack', $_user_id), $thread_id);

                if( $send_global ) {
                    $recipient['content_site'] = $this->encrypt_message_for_user(BP_Better_Messages()->functions->format_message($message_copy->message, $message_copy->id, 'site', $_user_id), $_user_id);
                    $recipient['html'] = $this->encrypt_message_for_user(BP_Better_Messages()->functions->render_thread($message_copy, $_user_id), $_user_id);
                }

                $recipient['avatar'] = $this->encrypt_message_for_user(BP_Better_Messages()->functions->get_avatar($message_copy->sender_id, 40), $_user_id);


                if (has_filter('bp_better_messages_thread_displayname', array(BP_Better_Messages_Hooks(), 'verified_member_badge'))) {
                    remove_filter('bp_better_messages_thread_displayname', array(BP_Better_Messages_Hooks(), 'verified_member_badge'));
                    $recipient['name'] = $this->encrypt_message_for_user(apply_filters('bp_better_messages_thread_displayname', bp_core_get_user_displayname($message_copy->sender_id), $message_copy->sender_id, $message_copy->thread_id), $_user_id);
                    add_filter('bp_better_messages_thread_displayname', array(BP_Better_Messages_Hooks(), 'verified_member_badge'), 10, 3);
                } else {
                    $recipient['name'] = $this->encrypt_message_for_user(apply_filters('bp_better_messages_thread_displayname', bp_core_get_user_displayname($message_copy->sender_id), $message_copy->sender_id, $message_copy->thread_id), $_user_id);
                }

            }

            $recipients[] = $recipient;
        }

        if ( BP_Better_Messages()->settings['enablePushNotifications'] === '1' && $send_global && $send_push ) {
            $bulk_notifications = [];

            foreach( $recipients as $recipient ) {
                if( (int) $recipient['user_id'] === (int) $message->sender_id ) continue;

                $muted_threads = BP_Better_Messages()->functions->get_user_muted_threads( $recipient['user_id'] );
                if( isset($muted_threads[ $thread_id ]) ){
                    continue;
                }

                $url = add_query_arg([
                    'thread_id' => $message->thread_id
                ], BP_Better_Messages()->functions->get_link($recipient['user_id']) );

                $notification = array(
                    'title' => sprintf( __('New message from %s', 'bp-better-messages'), bp_core_get_user_displayname( $message->sender_id ) ),
                    'body'  => sprintf( __('You have new message from %s', 'bp-better-messages'), bp_core_get_user_displayname( $message->sender_id ) ),
                    'icon'  => htmlspecialchars_decode(BP_Better_Messages_Functions()->get_avatar($message->sender_id, 40, [ 'html' => false ])),
                    'tag'   => 'bp-better-messages-thread-' . $message->thread_id,
                    'data'  => array(
                        'url' => $url
                    )
                );

                $bulk_notifications[ $recipient['user_id'] ] = $notification;
            }

            $this->send_bulk_push_notification( $bulk_notifications );

            do_action('bp_better_messages_bulk_push_notifications_sent', $bulk_notifications);
        }

        $name = apply_filters( 'bp_better_messages_thread_displayname', bp_core_get_user_displayname( $message->sender_id ), $message->sender_id, $message->thread_id );
        $main_message      = $this->encrypt_message(BP_Better_Messages()->functions->format_message( $message->message, $message->id, 'stack' ), $thread_id);
        $main_content_site = $this->encrypt_message(BP_Better_Messages()->functions->format_message( $message->message, $message->id, 'site' ), $thread_id);
        $main_avatar       = $this->encrypt_message(BP_Better_Messages_Functions()->get_avatar( $message->sender_id, 40 ), $thread_id);
        $main_name         = $this->encrypt_message($name, $thread_id);

        if( is_countable( $recipients ) && count( $recipients ) > 0 ){
            foreach( $recipients as $i => $recipient ){
                if( ! isset( $recipient['message'] ) ){
                    $recipients[ $i ] = $recipient['user_id'];
                } else {
                    if (isset($recipient['message']) && $recipient['message'] == $main_message) {
                        unset($recipients[$i]['message']);
                    }

                    if (isset($recipient['content_site']) && $recipient['content_site'] == $main_content_site) {
                        unset($recipients[$i]['content_site']);
                    }
                }
            }
        }

        $edit = '0';

        if(isset($_POST['edit']) && isset($_POST['message_id']) && ! empty($_POST['message_id'])){
            $edit = intval( $_POST['message_id'] ) > 0 ? '1' : '0';
        }

        $primary_message = array(
            'thread_id'    => $thread_id,
            'id'           => $message->id,
            'date'         => $message->date_sent,
            'message'      => $main_message,
            'avatar'       => $main_avatar,
            'name'         => $main_name,
            'link'         => $this->encrypt_message(bp_core_get_userlink( $message->sender_id, false, true ), $thread_id),
            'timestamp'    => strtotime( $message->date_sent ),
            'user_id'      => $message->sender_id
        );

        if( $send_global ){
            $primary_message['content_site'] = $main_content_site;
        }

        if( !! $site_name ){
            if( ! empty( $name ) ) $site_name = $site_name . ': ' . $name;
            $primary_message['site_name'] = $this->encrypt_message_for_website($site_name);
        }

        if( !! $site_avatar ){
            $primary_message['site_avatar'] = $this->encrypt_message_for_website($site_avatar);
        }

        $primary_message['show_on_site'] = $message->show_on_site;

        if( $message->meta ) {
            $primary_message['meta'] = $message->meta;
        }

        $data = array(
            'site_id'      => $this->site_id,
            'from'         => $user_id,
            'edit'         => $edit,
            'recipients'   => $recipients,
            'status'       => (BP_Better_Messages()->realtime && BP_Better_Messages()->settings['messagesStatus']) ? '1' : '0',
            'count_unread' => $count_unread,
            'message'      => $primary_message,
            'secret_key'   => sha1( $this->site_id . $this->secret_key )
        );

        $data = apply_filters( 'bp_better_messages_realtime_server_send_data', $data, $message );
        $data = BP_Better_Messages()->functions->array_map_recursive('strval', $data);

        if( isset($_POST['tempID']) ) $data['message']['temp_id'] = sanitize_text_field($_POST['tempID']);
        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');

        wp_remote_post( $socket_server . 'send', array(
            'headers' => array('Content-Type' => 'application/json'),
            'blocking' => false,
            'timeout'  => 30,
            'body'     => json_encode($data)
        ) );
    }

    public function get_online_users(){
        $data = array(
            'site_id'    => $this->site_id,
            'secret_key' => sha1( $this->site_id . $this->secret_key )
        );

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');
        $request = wp_remote_post( $socket_server . 'online_users', array(
            'body'     => $data
        ) );

        if ( is_wp_error( $request ) ) {
            return false;
        }

        $online_tabs = json_decode($request['body'], true);
        $online_users = [];

        if( is_array( $online_tabs ) ) {
            foreach ($online_tabs as $user_id => $online_tabs) {
                $online_users[$user_id] = $user_id;
            }
        }

        return $online_users;
    }

    public function update_last_activity(){
        $users = $this->get_online_users();

        foreach( $users as $user_id ){
            bp_update_user_last_activity( $user_id );
        }
    }

    public function sync_unread(){
        global $wpdb;

        $data = array(
            'site_id'    => $this->site_id,
            'secret_key' => sha1( $this->site_id . $this->secret_key )
        );

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');
        $request = wp_remote_post( $socket_server . 'sync', array(
            'timeout'  => 60,
            'body'     => $data
        ) );

        if ( is_wp_error( $request ) ) {
            return false;
        }

        if( $request['response']['code'] !== 200 ){
            return false;
        }

        $unread = json_decode($request['body']);

        if( isset( $unread->invalidEndpoints ) ){
            $invalidEndpoints = (array) $unread->invalidEndpoints;
            unset($unread->invalidEndpoints);

            if( count($invalidEndpoints) > 0 ) {
                foreach ($invalidEndpoints as $user_id => $invalidEndpoint) {
                    $user_endpoints = get_user_meta($user_id, 'bpbm_messages_push_subscriptions', true);
                    foreach ($invalidEndpoint as $item) {
                        if (isset($user_endpoints[$item])) {
                            unset($user_endpoints[$item]);
                        }
                    }

                    update_user_meta($user_id, 'bpbm_messages_push_subscriptions', $user_endpoints);
                }
            }
        }


        foreach($unread as $user_id => $threads){

            $updated_threads = [];

            foreach($threads as $thread_id => $_unread){
                $updated_threads[] = intval( $thread_id );

                $unread = (int) $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT unread_count FROM " . bpbm_get_table('recipients') . " WHERE `user_id` = %d AND `thread_id` = %d",
                        $user_id, $thread_id
                    )
                );

                if( $unread !== $_unread ){
                    $wpdb->update(
                        bpbm_get_table('recipients'),
                        array(
                            "unread_count" => $_unread,
                        ),
                        array(
                            'user_id' => $user_id,
                            'thread_id' => $thread_id
                        )
                    );

                    if(intval($_unread) == 0){
                        $last_seen = $wpdb->get_var($wpdb->prepare("SELECT date_sent FROM `" . bpbm_get_table('messages') . "` WHERE `thread_id` = %d ORDER BY `date_sent` DESC LIMIT %d,1", $thread_id, $_unread));
                        BP_Better_Messages()->functions->messages_mark_thread_read($thread_id, $user_id);
                    } else {
                        $last_seen = $wpdb->get_var($wpdb->prepare("SELECT date_sent FROM `" . bpbm_get_table('messages') . "` WHERE `thread_id` = %d AND `sender_id` != %d ORDER BY `date_sent` DESC LIMIT %d,1", $thread_id, $user_id, $_unread));
                    }

                    //update_user_meta($user_id, 'bpbm-last-seen-thread-' . $thread_id, strtotime($last_seen) );
                }
            }

            if( count( $updated_threads ) > 0 ) {
                $threads_in = 'thread_id NOT IN ("' . implode('","', $updated_threads) . '")';

                $uncleaned_threads = $wpdb->get_col($wpdb->prepare(
                    "SELECT thread_id FROM " . bpbm_get_table('recipients') . " WHERE `user_id` = %d AND unread_count > 0 AND $threads_in",
                    $user_id
                ));

                foreach ($uncleaned_threads as $thread_id) {
                    $wpdb->update(
                        bpbm_get_table('recipients'),
                        array(
                            "unread_count" => $_unread,
                        ),
                        array(
                            'user_id' => $user_id,
                            'thread_id' => $thread_id
                        )
                    );

                    $last_seen = $wpdb->get_var($wpdb->prepare("SELECT date_sent FROM `" . bpbm_get_table('messages') . "` WHERE `thread_id` = %d ORDER BY `date_sent` DESC LIMIT %d,1", $thread_id, $_unread));

                    BP_Better_Messages()->functions->messages_mark_thread_read( $thread_id, $user_id );

                    //update_user_meta($user_id, 'bpbm-last-seen-thread-' . $thread_id, strtotime($last_seen));
                }
            }

            if( function_exists('bp_notifications_mark_notification') ) {
                $unread_notifications_threads = $wpdb->get_results($wpdb->prepare("
                SELECT `messages`.`thread_id`, `notifications`.`id` as `notification_id`
                FROM `" . bpbm_get_table('notifications') . "` as `notifications`
                INNER JOIN `" . bpbm_get_table('messages') . "` as `messages`
                ON `notifications`.`item_id` = `messages`.`id`
                WHERE `notifications`.`user_id` = %d 
                AND   `notifications`.`is_new` = 1 
                AND   `notifications`.`component_name` = 'messages'", $user_id));

                $notifications_per_thread = [];
                foreach ($unread_notifications_threads as $item) {
                    $notifications_per_thread[$item->thread_id][] = $item->notification_id;
                }

                if (!empty($notifications_per_thread)) {

                    if (count($notifications_per_thread) > 0) {
                        $threads_in = '`thread_id` IN ("' . implode('","', array_keys($notifications_per_thread)) . '")';

                        $already_readed_threads = $wpdb->get_col($wpdb->prepare("SELECT `thread_id`  
                        FROM `" . bpbm_get_table('recipients') . "` 
                        WHERE $threads_in
                        AND `user_id` = %d
                        AND `unread_count` = 0", $user_id));

                        foreach ($already_readed_threads as $thread_id) {
                            if (isset($notifications_per_thread[$thread_id])) {
                                foreach( $notifications_per_thread[$thread_id] as $notification_id ){
                                    BP_Notifications_Notification::update(
                                        array( 'is_new' => false ),
                                        array( 'id'     => $notification_id )
                                    );
                                }
                            }
                        }
                    }
                }
            }

        }

        update_option( 'better_messages_last_sync', time() );

        return null;
    }

    public function mark_thread_read( $thread_id ){
        $thread = new BP_Messages_Thread();
        $recipients = $thread->get_recipients( $thread_id );

        $user_ids = [];
        foreach( $recipients as $recipient ){
            $user_ids[] = $recipient->user_id;
        }

        $data = array(
            'site_id'    => $this->site_id,
            'secret_key' => sha1( $this->site_id . $this->secret_key ),
            'thread_id'  => $thread_id,
            'user_ids'   => $user_ids
        );

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');
        $request = wp_remote_post( $socket_server . 'mark_thread_read', array(
            'body'     => $data,
            'blocking' => false
        ) );

        if ( is_wp_error( $request ) ) return false;

        return null;
    }

    public function on_message_deleted( $message_id, $user_ids = [] ){

        $data = array(
            'site_id'    => $this->site_id,
            'secret_key' => sha1( $this->site_id . $this->secret_key ),
            'message_id' => sanitize_text_field($message_id),
            'user_ids'   => $user_ids
        );

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');
        $request = wp_remote_post( $socket_server . 'message_deleted', array(
            'body'     => $data
        ) );

        if ( is_wp_error( $request ) ) return false;

        return null;
    }

    public function on_usermeta_update($meta_id, $user_id, $meta_key, $_meta_value){
        if( $meta_key !== 'bpbm_online_status' ) return $meta_id;

        $statuses = $this->get_all_statuses();
        $status = $this->get_user_status( $user_id );
        $newStatus = $statuses[$status];

        $newStatus['slug'] = $status;
        if( isset( $newStatus['icon'] ) ) unset( $newStatus['icon'] );
        if( isset( $newStatus['desc'] ) ) unset( $newStatus['desc'] );

        $request = [
            'site_id'       => $this->site_id,
            'secret_key'    => sha1( $this->site_id . $this->secret_key ),
            'user_id'       => $user_id,
            'status'        => $newStatus
        ];

        $socket_server = apply_filters('bp_better_messages_realtime_server', 'https://realtime-cloud.bpbettermessages.com/');

        wp_remote_post( $socket_server . 'setNewStatus', array(
            'body' => $request,
            'blocking' => false
        ) );

        BP_Better_Messages()->hooks->on_user_update( $user_id );
        return $meta_id;
    }

    public function add_status_color_to_avatar( $extraattr, $user_id ){
        $statuses = $this->get_all_statuses();
        $status = $this->get_user_status( $user_id );

        $color = $statuses[$status]['color'];
        $extraattr .= ' data-bpbm-status-color="' . $color . '" ';
        return $extraattr;
    }

    public function get_all_statuses(){
        return apply_filters('bp_better_messages_all_statuses', [
            'online' => [
                'name'  => _x('Online', 'User status', 'bp-better-messages'),
                'icon'  => '<i class="fas fa-circle" style="color:#3da512!important;"></i>',
                'color' => '#3da512'
            ],
            'away'   => [
                'name'  => _x('Away', 'User status', 'bp-better-messages'),
                'icon'  => '<i class="fas fa-moon" style="color:#ffbe00!important;"></i>',
                'color' => '#ffbe00'
            ],
            'dnd'    => [
                'name' => _x('Do not disturb', 'User status', 'bp-better-messages'),
                'desc' => _x('You will not receive sound notifications', 'User status description', 'bp-better-messages'),
                'icon' => '<i class="fas fa-stop" style="color:red!important;"></i>',
                'color' => 'red'
            ],
        ]);
    }

    public function get_user_full_status( $user_id ){
        $statuses = $this->get_all_statuses();
        $status = $this->get_user_status( $user_id );

        return $statuses[$status];
    }

    public function get_user_status( $user_id ){
        $status = get_user_meta($user_id, 'bpbm_online_status', true);

        $statuses = $this->get_all_statuses();

        if( empty($status) || ! isset( $statuses[$status]) ){
            $status = 'online';
        }

        return $status;
    }

    public function get_status_display_name( $status ){

        $statuses = $this->get_all_statuses();

        if( isset( $statuses[$status] ) ){
            return $statuses[$status]['name'];
        } else {
            return '';
        }
    }


}

function BP_Better_Messages_Premium()
{
    return BP_Better_Messages_Premium::instance();
}