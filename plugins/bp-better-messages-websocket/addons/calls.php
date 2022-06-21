<?php
defined( 'ABSPATH' ) || exit;

if ( !class_exists( 'BP_Better_Messages_Calls' ) ):

    class BP_Better_Messages_Calls
    {
        public $audio = false;

        public $video = false;

        public $revertIcons = false;

        public $fastCall    = false;

        public static function instance()
        {
            static $instance = null;

            if ( null === $instance ) {
                $instance = new BP_Better_Messages_Calls();
            }

            return $instance;
        }


        public function __construct()
        {
            $this->audio       = BP_Better_Messages()->settings['audioCalls'] === '1';
            $this->video       = BP_Better_Messages()->settings['videoCalls'] === '1';
            $this->revertIcons = BP_Better_Messages()->settings['callsRevertIcons'] === '0';

            add_action( 'bp_better_messages_thread_pre_header', array( $this, 'call_button' ), 10, 4 );
            add_action( 'bp_messages_thread_main_content',      array( $this, 'html_content' ), 10, 4 );

            add_action( 'wp_ajax_bp_better_messages_record_outgoing_call',  array( $this, 'record_outgoing_call' ) );
            add_action( 'wp_ajax_bp_better_messages_record_missed_call',  array( $this, 'record_missed_call' ) );
            add_action( 'wp_ajax_bp_better_messages_record_offline_call', array( $this, 'record_offline_call' ) );

            add_action( 'wp_ajax_bp_better_messages_register_started_call',  array( $this, 'register_started_call' ) );
            add_action( 'wp_ajax_bp_better_messages_register_call_usage',    array( $this, 'register_call_usage' ) );

            if( BP_Better_Messages()->settings['callsLimitFriends'] === '1' ){
                add_filter('bp_better_messages_can_audio_call', array( $this, 'restrict_non_friends_calls'), 10, 3 );
                add_filter('bp_better_messages_can_video_call', array( $this, 'restrict_non_friends_calls'), 10, 3 );
            }

            if( BP_Better_Messages()->settings['profileAudioCall'] === '1' || BP_Better_Messages()->settings['profileVideoCall'] === '1' ) {

                if ( function_exists('bp_get_theme_package_id') && bp_get_theme_package_id() == 'nouveau' ) {
                    add_action('bp_nouveau_get_members_buttons', array($this, 'profile_call_button'), 10, 3);
                } else {
                    add_action( 'bp_member_header_actions', array( $this, 'profile_call_button_legacy' ), 21 );
                }

                #add_action('youzify_social_buttons', array( $this, 'profile_call_button_legacy' ), 10, 1 );
            }

            if( isset(BP_Better_Messages()->settings['restrictCalls'])
                && is_array(BP_Better_Messages()->settings['restrictCalls'])
                && count(BP_Better_Messages()->settings['restrictCalls']) > 0
            ) {
                add_filter( 'bp_better_messages_script_variable', array( $this, 'disable_calls_for_restricted_role' ), 10, 1 );
            }


            add_action( 'template_redirect', array($this, 'catch_fast_call') );


            add_action( 'wp_ajax_bp_better_messages_get_thread_group_call_access_key',    array( $this, 'get_thread_group_call_access_key' ) );
            add_action( 'wp_ajax_bp_better_messages_thread_group_call_admin',    array( $this, 'thread_group_call_admin' ) );
            add_action( 'bp_better_messages_pinned_message',    array( $this, 'pinned_message' ), 10, 4 );


            /**
             * Grimlock profile call button
             */
            if( defined('GRIMLOCK_BUDDYPRESS_VERSION') ) {
                add_action('bp_member_header_actions', array($this, 'grimlock_profile_call_button'), 20);
            }

            if( defined('YOUZIFY_VERSION') ) {
                add_action('youzify_social_buttons', array($this, 'youzify_profile_call_button'), 20, 1);
            }

            add_filter('bp_nouveau_customizer_user_profile_actions', array($this, 'bp_nouveau_customizer_user_profile_actions'), 20, 1 );
        }

        public function disable_calls_for_restricted_role( $variables ){
            $user             = wp_get_current_user();
            $restricted_roles = (array) BP_Better_Messages()->settings['restrictCalls'];

            $is_restricted    = BP_Better_Messages()->functions->user_has_role( $user->ID, $restricted_roles );

            if( $is_restricted ) {
                $variables['callRestrict'] = BP_Better_Messages()->settings['restrictCallsMessage'];
            }

            return $variables;
        }


        public function bp_nouveau_customizer_user_profile_actions($buttons){
            $buttons['bpbm_audio_call'] = __( 'Audio Call', 'bp-better-messages' );
            $buttons['bpbm_video_call'] = __( 'Video Call', 'bp-better-messages' );
            return $buttons;
        }

        public function youzify_profile_call_button( $user_id ){

            $can_call = true;

            if( BP_Better_Messages()->settings['callsLimitFriends'] === '1' ){
                if( function_exists( 'friends_check_friendship' ) ){

                    if( current_user_can('manage_options') ){
                        /*
                         * Admin always can call
                         */
                        $can_call = true;
                    } else {
                        $can_call = friends_check_friendship(get_current_user_id(), $user_id);
                    }
                }
            }

            if( ! $can_call ){
                return false;
            }

            $base_link = BP_Better_Messages()->functions->get_link( get_current_user_id() );


            if( $this->audio && BP_Better_Messages()->settings['profileAudioCall'] === '1' ){
                $link = add_query_arg([
                    'fast-call' => '',
                    'to' => $user_id,
                    'type' => 'audio'
                ], $base_link);


                echo '<div class="bpbm-youzify-btn generic-button" id="bpbm-audio-call"><a href="' . $link . '" data-user-id="' . $user_id .'" class="audio-call grimlock-btn bpbm-audio-call"><i class="fas fa-phone"></i>' . __( 'Audio Call', 'bp-better-messages' ) . '</a></div>';
            }


            if( $this->video && BP_Better_Messages()->settings['profileVideoCall'] === '1' ) {
                $link = add_query_arg([
                    'fast-call' => '',
                    'to' => $user_id,
                    'type' => 'video'
                ], $base_link);

                echo '<div class="bpbm-youzify-btn generic-button" id="bpbm-video-call"><a href="' . $link . '" data-user-id="' . $user_id .'" class="video-call grimlock-btn bpbm-video-call"><i class="fas fa-video"></i>' . __( 'Video Call', 'bp-better-messages' ) . '</a></div>';

            }

        }

        public function grimlock_profile_call_button(){

            $can_call = true;

            $user_id = bp_displayed_user_id();
            if( BP_Better_Messages()->settings['callsLimitFriends'] === '1' ){
                if( function_exists( 'friends_check_friendship' ) ){

                    if( current_user_can('manage_options') ){
                        /*
                         * Admin always can call
                         */
                        $can_call = true;
                    } else {
                        $can_call = friends_check_friendship(get_current_user_id(), $user_id);
                    }
                }
            }

            if( ! $can_call ){
                return false;
            }

            $base_link = BP_Better_Messages()->functions->get_link( get_current_user_id() );


            if( $this->audio && BP_Better_Messages()->settings['profileAudioCall'] === '1' ){
                $link = add_query_arg([
                    'fast-call' => '',
                    'to' => $user_id,
                    'type' => 'audio'
                ], $base_link);


                echo '<div class="generic-button" id="bpbm-audio-call"><a href="' . $link . '" data-user-id="' . $user_id .'" class="audio-call grimlock-btn bpbm-audio-call">' . __( 'Audio Call', 'bp-better-messages' ) . '</a></div>';
            }


            if( $this->video && BP_Better_Messages()->settings['profileVideoCall'] === '1' ) {
                $link = add_query_arg([
                    'fast-call' => '',
                    'to' => $user_id,
                    'type' => 'video'
                ], $base_link);

                echo '<div class="generic-button" id="bpbm-video-call"><a href="' . $link . '" data-user-id="' . $user_id .'" class="video-call grimlock-btn bpbm-video-call">' . __( 'Video Call', 'bp-better-messages' ) . '</a></div>';

            }

        }


        public function catch_fast_call(){
            if( isset($_GET['fast-call'])
                && isset($_GET['to'])
                && isset($_GET['type'])
                && ! empty($_GET['to'])
                && ! empty($_GET['type'])
                && (rtrim(str_replace($_SERVER['QUERY_STRING'], '', $_SERVER['REQUEST_URI']), '?') === str_replace(site_url(''), '', BP_Better_Messages()->functions->get_link()))
            ){
                $type = $_GET['type'];

                if( $type !== 'audio' && $type !== 'video' ){
                    return false;
                }

                $to = get_userdata(intval($_GET['to']));
                if( ! $to ) return false;

                $thread_id = BP_Better_Messages()->functions->get_pm_thread_id($to->ID);

                $url = add_query_arg([
                    'thread_id' => $thread_id
                ], BP_Better_Messages()->functions->get_link() );

                if( $type === 'audio' ){
                    $url .= '&audioCall';
                }

                if( $type === 'video' ){
                    $url .= '&videoCall';
                }

                wp_redirect($url);
                exit;
            }
        }

        public function profile_call_button_legacy(){
            if ( bp_is_my_profile() || ! is_user_logged_in() ) {
                return false;
            }

            $user_id = bp_displayed_user_id();

            $can_call = true;

            if( BP_Better_Messages()->settings['callsLimitFriends'] === '1' ){
                if( function_exists( 'friends_check_friendship' ) ){
                    if( current_user_can('manage_options') ){
                        /*
                         * Admin always can call
                         */
                        $can_call = true;
                    } else {
                        $can_call = friends_check_friendship(get_current_user_id(), $user_id);
                    }
                }
            }


            if( ! $can_call ) {
                return false;
            }

            $base_link = BP_Better_Messages()->functions->get_link( get_current_user_id() );


            if( $this->audio && BP_Better_Messages()->settings['profileAudioCall'] === '1' ) {
                $link = add_query_arg([
                    'fast-call' => '',
                    'to' => $user_id,
                    'type' => 'audio'
                ], $base_link);
                echo bp_get_button(array(
                    'id' => 'bpbm_audio_call',
                    'component' => 'messages',
                    'must_be_logged_in' => true,
                    'block_self' => true,
                    'wrapper_id' => 'bpbm-audio-call',
                    'link_href' => $link,
                    'link_text' => __('Audio Call', 'bp-better-messages'),
                    'link_class' => 'bpbm-audio-call',
                    'button_attr' => [
                        'data-user-id' => $user_id
                    ]
                ));
            }

            if( $this->video && BP_Better_Messages()->settings['profileVideoCall'] === '1' ) {
                $link = add_query_arg([
                    'fast-call' => '',
                    'to' => $user_id,
                    'type' => 'video'
                ], $base_link);
                echo bp_get_button(array(
                    'id' => 'bpbm_video_call',
                    'component' => 'messages',
                    'must_be_logged_in' => true,
                    'block_self' => true,
                    'wrapper_id' => 'bpbm-video-call',
                    'link_href' => $link,
                    'link_text' => __('Video Call', 'bp-better-messages'),
                    'link_class' => 'bpbm-video-call',
                    'button_attr' => [
                        'data-user-id' => $user_id
                    ]
                ));
            }

        }

        public function profile_call_button( $buttons, $user_id, $type ){

            if ( ! is_user_logged_in() ) {
                return $buttons;
            }

            if( $type === 'profile' ){
                $can_call = true;

                if( BP_Better_Messages()->settings['callsLimitFriends'] === '1' ){
                    if( function_exists( 'friends_check_friendship' ) ){

                        if( current_user_can('manage_options') ){
                            /*
                             * Admin always can call
                             */
                            $can_call = true;
                        } else {
                            $can_call = friends_check_friendship(get_current_user_id(), $user_id);
                        }
                    }
                }

                if( ! $can_call ){
                    return $buttons;
                }

                $base_link = BP_Better_Messages()->functions->get_link( get_current_user_id() );

                $tag = 'li';

                if( defined('BP_PLATFORM_VERSION') ){
                    $tag = 'div';
                }

                if( $this->audio && BP_Better_Messages()->settings['profileAudioCall'] === '1' ){
                    $link = add_query_arg([
                        'fast-call' => '',
                        'to' => $user_id,
                        'type' => 'audio'
                    ], $base_link);

                    $buttons['audio_call'] = array(
                        'id'                => 'bpbm_audio_call',
                        'component'         => 'messages',
                        'must_be_logged_in' => true,
                        'block_self'        => true,
                        'parent_element'    => $tag,
                        'wrapper_id'        => 'bpbm-audio-call',
                        'link_href'         => $link,
                        'link_text'         => __( 'Audio Call', 'bp-better-messages' ),
                        'link_class'        => 'bpbm-audio-call',
                        'button_attr'       => [
                            'data-user-id' => $user_id
                        ]
                    );
                }


                if( $this->video && BP_Better_Messages()->settings['profileVideoCall'] === '1' ) {
                    $link = add_query_arg([
                        'fast-call' => '',
                        'to' => $user_id,
                        'type' => 'video'
                    ], $base_link);

                    $buttons['video_call'] = array(
                        'id' => 'bpbm_video_call',
                        'component' => 'messages',
                        'must_be_logged_in' => true,
                        'block_self' => true,
                        'parent_element' => $tag,
                        'wrapper_id' => 'bpbm-video-call',
                        'link_href' => $link,
                        'link_text' => __('Video Call', 'bp-better-messages'),
                        'link_class' => 'bpbm-video-call',
                        'button_attr' => [
                            'data-user-id' => $user_id
                        ]
                    );
                }
            }

            return $buttons;

        }

        public function restrict_non_friends_calls( $can_call, $user_id, $thread_id ){
            if( ! function_exists( 'friends_check_friendship' ) ) return $can_call;

            $participants = BP_Better_Messages()->functions->get_participants($thread_id);
            if(count($participants['users']) !== 2) return false;

            unset($participants['users'][$user_id]);
            reset($participants['users']);

            $friend_id = key($participants['users']);

            /**
             * Allow users reply to calls even if not friends
             */
            if( current_user_can('manage_options') || user_can( $friend_id, 'manage_options' ) ) {
                return $can_call;
            }

            return friends_check_friendship($user_id, $friend_id);
        }

        public function can_audio_call_in_thread( $thread_id, $user_id ){
            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Better_Messages()->functions->check_access( $thread_id ), $user_id, $thread_id );
            $can_send_message = apply_filters('bp_better_messages_can_start_call', $can_send_message, $user_id, $thread_id );
            if( ! $can_send_message  ) return false;

            return apply_filters('bp_better_messages_can_audio_call', $can_send_message, $user_id, $thread_id );
        }

        public function can_video_call_in_thread( $thread_id, $user_id ){
            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Better_Messages()->functions->check_access( $thread_id ), $user_id, $thread_id );
            $can_send_message = apply_filters('bp_better_messages_can_start_call', $can_send_message, $user_id, $thread_id );
            if( ! $can_send_message  ) return false;

            return apply_filters('bp_better_messages_can_video_call', $can_send_message, $user_id, $thread_id );
        }

        public function register_started_call()
        {
            global $call_data;

            $user_id = get_current_user_id();
            $thread_id = intval($_REQUEST['thread_id']);
            $message_id = intval( $_REQUEST['message_id'] );
            $type = sanitize_text_field($_REQUEST['type']);
            $duration   = 0;

            $mins       = floor($duration / 60 % 60);
            $secs       = floor($duration % 60);
            $seconds    = sprintf('%02d:%02d', $mins, $secs);

            $call_data = [
                'caller_id'    => $user_id,
                'thread_id'    => $thread_id,
                'type'         => $type,
                'call_started' => bp_core_current_time(),
                'mins'         => $mins,
                'secs'         => $secs,
                'duration'     => $seconds,
            ];

            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Better_Messages()->functions->check_access( $thread_id ), $user_id, $thread_id );
            if( ! apply_filters('bp_better_messages_can_start_call', $can_send_message, $user_id, $thread_id ) ) return false;

            if( $type === 'audio' ){
                $can_audio_call = $this->can_audio_call_in_thread( $thread_id, $user_id );

                if( ! $can_audio_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-audio call-accepted">' . sprintf( __( 'Audio call accepted <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds )  . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'return'      => 'message_id',
                    'send_push'   => false,
                    'meta'        => [
                        'type' => 'call_start'
                    ],
                    'date_sent'   => bp_core_current_time()
                );

                if( $message_id > 0 ) {
                    $args['message_id'] = $message_id;
                    BP_Better_Messages()->functions->update_message( $args );
                } else {
                    $message_id = BP_Better_Messages()->functions->new_message($args);
                }

                foreach( $call_data as $key => $value ){
                    bp_messages_update_meta( $message_id, $key, sanitize_text_field( $value ) );
                }
            }


            if( $type === 'video' ){
                $can_video_call = $this->can_video_call_in_thread( $thread_id, $user_id );

                if( ! $can_video_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-video call-accepted">' . sprintf( __( 'Video call accepted <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'return'      => 'message_id',
                    'send_push'   => false,
                    'meta'        => [
                        'type' => 'call_start'
                    ],
                    'date_sent'   => bp_core_current_time()
                );

                if( $message_id > 0 ) {
                    $args['message_id'] = $message_id;
                    BP_Better_Messages()->functions->update_message( $args );
                } else {
                    $message_id = BP_Better_Messages()->functions->new_message($args);
                }

                foreach( $call_data as $key => $value ){
                    bp_messages_update_meta( $message_id, $key, sanitize_text_field( $value ) );
                }
            }

            wp_send_json( $message_id );
        }

        public function register_call_usage(){
            global $call_data;

            $user_id    = get_current_user_id();
            $thread_id  = intval( $_REQUEST['thread_id'] );
            $message_id = intval( $_REQUEST['message_id'] );
            $message    = new BP_Messages_Message( $message_id );

            $duration   = intval( $_REQUEST['duration'] );

            $mins       = floor($duration / 60 % 60);
            $secs       = floor($duration % 60);
            $seconds    = sprintf('%02d:%02d', $mins, $secs);

            $call_data = [
                'mins'      => $mins,
                'secs'      => $secs,
                'duration'  => $seconds,
            ];

            if( $user_id !== $message->sender_id ) return false;

            $type = bp_messages_get_meta( $message_id, 'type', true );
            if( $type === 'video' ){
                $message    = '<span class="bpbm-call bpbm-call-video call-accepted">' . sprintf( __( 'Video call accepted <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>';
            } else if( $type === 'audio' ){
                $message    = '<span class="bpbm-call bpbm-call-audio call-accepted">' . sprintf( __( 'Audio call accepted <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>';
            } else {
                exit;
            }

            foreach( $call_data as $key => $value ){
                bp_messages_update_meta( $message_id, $key, sanitize_text_field( $value ) );
            }

            $args = array(
                'sender_id'   => $user_id,
                'thread_id'   => $thread_id,
                'content'     => $message,
                'message_id'  => $message_id,
                'send_push'   => false,
                'return'      => 'message_id',
                'date_sent'   => bp_core_current_time()
            );

            BP_Better_Messages()->functions->update_message( $args );

            wp_send_json( true );
        }

        public function record_offline_call(){
            global $call_data;

            $user_id   = get_current_user_id();
            $thread_id = intval( $_REQUEST['thread_id'] );
            $type      = sanitize_text_field( $_REQUEST['type'] );

            $call_data = [
                'caller_id' => $user_id,
                'thread_id' => $thread_id,
                'type'      => $type,
            ];

            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Better_Messages()->functions->check_access( $thread_id ), $user_id, $thread_id );
            if( ! $can_send_message  ) return false;

            if( $type === 'audio' ){
                $can_audio_call = $this->can_audio_call_in_thread($thread_id, $user_id);

                if( ! $can_audio_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-audio missed missed-offline">' . _x( 'I tried to make an audio call, but you were offline', 'Missed call message', 'bp-better-messages' )  . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'date_sent'  => bp_core_current_time()
                );

                add_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
                BP_Better_Messages()->functions->new_message( $args );
                remove_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
            }


            if( $type === 'video' ){
                $can_video_call = $this->can_video_call_in_thread($thread_id, $user_id);

                if( ! $can_video_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-video missed missed-offline">' . _x( 'I tried to make a video call, but you were offline', 'Missed call message', 'bp-better-messages' ) . '</span>';

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'content'     => $message,
                    'date_sent'   => bp_core_current_time()
                );

                add_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
                BP_Better_Messages()->functions->new_message( $args );
                remove_action( 'messages_message_sent', array( $this, 'record_missed_call_data' ) );
            }

            exit;
        }

        public function record_outgoing_call(){
            if( ! wp_verify_nonce( $_POST[ 'nonce' ], 'bpbm_edit_nonce' ) ){
                wp_send_json( false );
            }

            $user_id    = get_current_user_id();
            $thread_id  = intval( $_REQUEST['thread_id'] );

            $type      = sanitize_text_field( $_REQUEST['type'] );

            $duration  = intval( $_REQUEST['duration'] );
            $message_id = intval( $_REQUEST['message_id'] );

            $mins    = floor($duration / 60 % 60);
            $secs    = floor($duration % 60);
            $seconds = sprintf('%02d:%02d', $mins, $secs);

            $call_data = [
                'caller_id'      => $user_id,
                'thread_id'      => $thread_id,
                'type'           => $type,
                'mins'           => $mins,
                'secs'           => $secs,
                'duration'       => $seconds,
                'call_requested' => bp_core_current_time(),
            ];

            $participants   = BP_Better_Messages()->functions->get_participants( $thread_id );
            $target_user_id = $participants['recipients'][0];

            $url = add_query_arg([
                'thread_id' => $thread_id
            ], BP_Better_Messages()->functions->get_link($target_user_id) );

            if( $type === 'video' ){
                $message    = '<span class="bpbm-call bpbm-call-video call-incoming">' . sprintf( _x( 'Video Call', 'Private Call', 'bp-better-messages' ), $seconds ) . '</span>';

                $notification = array(
                    'title' => sprintf( _x('Incoming video call from %s', 'Private Call - Web Push', 'bp-better-messages'), bp_core_get_user_displayname( $user_id ) ),
                    'body'  => sprintf( _x('You have incoming video call from %s', 'Private Call - Web Push', 'bp-better-messages'), bp_core_get_user_displayname( $user_id ) ),
                    'icon'  => htmlspecialchars_decode(BP_Better_Messages_Functions()->get_avatar($user_id, 40, [ 'html' => false ])),
                    'tag'   => 'bp-better-messages-thread-' . $thread_id,
                    'data'  => array(
                        'url' => $url
                    )
                );
            } else if( $type === 'audio' ){
                $message    = '<span class="bpbm-call bpbm-call-audio call-incoming">' . sprintf( _x( 'Audio Call', 'Private Call', 'bp-better-messages' ), $seconds ) . '</span>';

                $notification = array(
                    'title' => sprintf( _x('Incoming audio call from %s', 'Private Call - Web Push', 'bp-better-messages'), bp_core_get_user_displayname( $user_id ) ),
                    'body'  => sprintf( _x('You have incoming audio call from %s', 'Private Call - Web Push', 'bp-better-messages'), bp_core_get_user_displayname( $user_id ) ),
                    'icon'  => htmlspecialchars_decode(BP_Better_Messages_Functions()->get_avatar($user_id, 40, [ 'html' => false ])),
                    'tag'   => 'bp-better-messages-thread-' . $thread_id,
                    'data'  => array(
                        'url' => $url
                    )
                );
            } else {
                wp_send_json( false );
                exit;
            }

            $args = array(
                'sender_id'    => $user_id,
                'thread_id'    => $thread_id,
                'content'      => $message,
                'send_push'    => false,
                'count_unread' => false,
                'show_on_site' => false,
                'meta'        => [
                    'type' => 'call_request',
                    'title' => $notification['title'],
                    'body'  => $notification['body'],
                ],
                'return'       => 'message_id',
                'date_sent'    => bp_core_current_time()
            );

            $return = [];

            if( $message_id > 0 ) {
                $args['message_id'] = $message_id;
                BP_Better_Messages()->functions->update_message( $args );
            } else {
                $message_id = BP_Better_Messages()->functions->new_message($args);

                $muted_threads = BP_Better_Messages()->functions->get_user_muted_threads( $target_user_id );

                if( ! isset($muted_threads[ $thread->thread_id ]) ){
                    $bulk_notifications = [ $target_user_id => $notification ];
                    BP_Better_Messages_Premium()->send_bulk_push_notification( $bulk_notifications );
                }
            }

            bp_messages_update_meta( $message_id, 'bpbm_call', true );
            bp_messages_update_meta( $message_id, 'bpbm_missed_call', false );
            foreach( $call_data as $key => $value ){
                bp_messages_update_meta( $message_id, $key, sanitize_text_field( $value ) );
            }

            $return['message_id'] = $message_id;

            wp_send_json( $return );
        }

        public function record_missed_call(){
            global $call_data;

            $user_id   = get_current_user_id();
            $thread_id = intval( $_REQUEST['thread_id'] );
            $type      = sanitize_text_field( $_REQUEST['type'] );
            $duration  = intval( $_REQUEST['duration'] );
            $message_id = intval( $_REQUEST['message_id'] );

            $mins    = floor($duration / 60 % 60);
            $secs    = floor($duration % 60);
            $seconds = sprintf('%02d:%02d', $mins, $secs);

            $call_data = [
                'caller_id' => $user_id,
                'thread_id' => $thread_id,
                'type'      => $type,
                'mins'      => $mins,
                'secs'      => $secs,
                'duration'  => $seconds,
            ];

            if( $message_id === 0 ){
                exit;
            }

            $notification   = false;

            $participants   = BP_Better_Messages()->functions->get_participants( $thread_id );
            $target_user_id = $participants['recipients'][0];

            $url = add_query_arg([
                'thread_id' => $thread_id
            ], BP_Better_Messages()->functions->get_link($target_user_id) );

            if( $type === 'audio' ){
                $can_audio_call = $this->can_audio_call_in_thread( $thread_id, $user_id );

                if( ! $can_audio_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-audio missed">' . sprintf( __( 'Missed audio call <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>';

                $notification = array(
                    'title' => sprintf( _x('Missed audio call from %s', 'Private Call - Web Push', 'bp-better-messages'), bp_core_get_user_displayname( $user_id ) ),
                    'body'  => sprintf( _x('You have missed audio call from %s', 'Private Call - Web Push', 'bp-better-messages'), bp_core_get_user_displayname( $user_id ) ),
                    'icon'  => htmlspecialchars_decode(BP_Better_Messages_Functions()->get_avatar($user_id, 40, [ 'html' => false ])),
                    'tag'   => 'bp-better-messages-thread-' . $thread_id,
                    'data'  => array(
                        'url' => $url
                    )
                );

                $args = array(
                    'sender_id'   => $user_id,
                    'thread_id'   => $thread_id,
                    'message_id'  => $message_id,
                    'content'     => $message,
                    'send_push'   => false,
                    'count_unread' => true,
                    'date_sent'   => bp_core_current_time()
                );

                BP_Better_Messages()->functions->update_message( $args );

                bp_messages_update_meta( $message_id, 'bpbm_call', true );
                bp_messages_update_meta( $message_id, 'bpbm_missed_call', true );
                foreach( $call_data as $key => $value ){
                    bp_messages_update_meta( $message_id, $key, sanitize_text_field( $value ) );
                }
            }

            if( $type === 'video' ){
                $can_video_call = $this->can_video_call_in_thread( $thread_id, $user_id );
                if( ! $can_video_call ) return false;
                $message = '<span class="bpbm-call bpbm-call-video missed">' . sprintf( __( 'Missed video call <span class="bpbm-call-duration">(%s)</span>', 'bp-better-messages' ), $seconds ) . '</span>';

                $notification = array(
                    'title' => sprintf( _x('Missed video call from %s', 'Private Call - Web Push', 'bp-better-messages'), bp_core_get_user_displayname( $user_id ) ),
                    'body'  => sprintf( _x('You have missed video call from %s', 'Private Call - Web Push', 'bp-better-messages'), bp_core_get_user_displayname( $user_id ) ),
                    'icon'  => htmlspecialchars_decode(BP_Better_Messages_Functions()->get_avatar($user_id, 40, [ 'html' => false ])),
                    'tag'   => 'bp-better-messages-thread-' . $thread_id,
                    'data'  => array(
                        'url' => $url
                    )
                );

                $args = array(
                    'sender_id'    => $user_id,
                    'thread_id'    => $thread_id,
                    'message_id'   => $message_id,
                    'content'      => $message,
                    'send_push'    => false,
                    'count_unread' => true,
                    'date_sent'    => bp_core_current_time()
                );

                BP_Better_Messages()->functions->update_message( $args );

                bp_messages_update_meta( $message_id, 'bpbm_call', true );
                bp_messages_update_meta( $message_id, 'bpbm_missed_call', true );
                foreach( $call_data as $key => $value ){
                    bp_messages_update_meta( $message_id, $key, sanitize_text_field( $value ) );
                }
            }

            if( $notification ){
                $muted_threads = BP_Better_Messages()->functions->get_user_muted_threads( $target_user_id );

                if( ! isset($muted_threads[ $thread->thread_id ]) ){
                    $bulk_notifications = [ $target_user_id => $notification ];
                    BP_Better_Messages_Premium()->send_bulk_push_notification( $bulk_notifications );
                }
            }

            wp_send_json( true );
            exit;
        }

        public function record_missed_call_data( $message ){
            global $call_data;

            $message_id = $message->id;

            bp_messages_add_meta( $message_id, 'bpbm_call', true );
            bp_messages_add_meta( $message_id, 'bpbm_missed_call', true );
            foreach( $call_data as $key => $value ){
                bp_messages_add_meta( $message_id, $key, sanitize_text_field( $value ) );
            }
        }

        public function is_group_call_active( $thread_type, $participants_count ){
            $groupsCallActive = false;

            if( $thread_type === 'thread' && $participants_count > 2 ){
                $groupsCallActive = BP_Better_Messages()->settings['groupCallsThreads'] === '1';
            }

            if( $thread_type === 'chat-room' ){
                $groupsCallActive = BP_Better_Messages()->settings['groupCallsChats'] === '1';
            }

            if( $thread_type === 'group' ){
                $groupsCallActive = BP_Better_Messages()->settings['groupCallsGroups'] === '1';
            }

            return $groupsCallActive;
        }

        public function is_audio_group_call_active( $thread_type, $participants_count ){
            $groupsCallActive = false;

            if( $thread_type === 'thread' && $participants_count > 2 ){
                $groupsCallActive = BP_Better_Messages()->settings['groupAudioCallsThreads'] === '1';
            }

            if( $thread_type === 'chat-room' ){
                $groupsCallActive = BP_Better_Messages()->settings['groupAudioCallsChats'] === '1';
            }

            if( $thread_type === 'group' ){
                $groupsCallActive = BP_Better_Messages()->settings['groupAudioCallsGroups'] === '1';
            }

            return $groupsCallActive;
        }

        public function call_button( $thread_id, $participants, $is_mini, $type = 'thread' ){
            #if( $type !== 'thread' ) return false;
            if( ! BP_Better_Messages()->functions->can_use_premium_code() ) return false;

            # if( ! bpbm_fs()->can_use_premium_code() ) return false;
            $can_send_message = apply_filters('bp_better_messages_can_send_message', BP_Better_Messages()->functions->check_access( $thread_id ), get_current_user_id(), $thread_id );
            if( ! apply_filters('bp_better_messages_can_start_call', $can_send_message, get_current_user_id(), $thread_id ) ) return false;

            $can_video_call = $this->can_video_call_in_thread( $thread_id, get_current_user_id() );
            $can_audio_call = $this->can_audio_call_in_thread( $thread_id, get_current_user_id() );

            $videoGroupCallActive = $this->is_group_call_active( $type, $participants['count']  );
            $audioGroupCallActive = $this->is_audio_group_call_active( $type, $participants['count']  );

            $groupsCallActive = $videoGroupCallActive || $audioGroupCallActive;

            $participants_count = count( $participants['recipients'] );
            if( $participants_count === 1 && $type === 'thread' ){
                if( $this->video && $can_video_call ){
                    if( $is_mini ){
                        if( BP_Better_Messages()->settings['miniChatVideoCall'] === '1' ) {
                            echo '<span class="video-call" data-user-id="' . $participants["recipients"][0] . '"></span>';
                        }
                    } else {
                        echo '<a href="#" class="video-call bpbm-can-be-hidden" data-user-id="' . $participants[ "recipients" ][0] . '"  title="' . __("Video Call", "bp-better-messages") . '"><i class="fas fa-video"></i></a>';
                    }
                }

                if( $this->audio && $can_audio_call ) {
                    if ($is_mini) {
                        if( BP_Better_Messages()->settings['miniChatAudioCall'] === '1' ) {
                            echo '<span class="audio-call" data-user-id="' . $participants["recipients"][0] . '"></span>';
                        }
                    } else {
                        echo '<a href="#" class="audio-call bpbm-can-be-hidden" data-user-id="' . $participants["recipients"][0] . '"  title="' . __("Audio Call", "bp-better-messages") . '"><i class="fas fa-phone"></i></a>';
                    }
                }
            } else if( $groupsCallActive ){
                if( $videoGroupCallActive ) {
                    echo '<a href="#" class="group-call bpbm-can-be-hidden" data-thread-id="' . $thread_id . '"  title="' . __("Video Chat", "bp-better-messages") . '"><i class="fas fa-video"></i></a>';
                }

                if( $audioGroupCallActive ) {
                    echo '<a href="#" class="group-audio-call bpbm-can-be-hidden" data-thread-id="' . $thread_id . '"  title="' . __("Audio Chat", "bp-better-messages") . '"><i class="fas fa-headset"></i></a>';
                }
            }
        }

        public function pinned_message( $thread_id, $participants, $is_mini, $type = 'thread' ){
            $videoGroupCallActive = $this->is_group_call_active( $type, $participants['count']  );
            $audioGroupCallActive = $this->is_audio_group_call_active( $type, $participants['count']  );

            $groupsCallActive = $videoGroupCallActive || $audioGroupCallActive;

            if( ! $groupsCallActive ) return false;

            if( $videoGroupCallActive ) {
            ?>
            <div class="bpbm-group-call-in-progress bpbm-group-call-type-video" style="display: none">
            <span class="bpbm-group-call-in-progress-info">
                    <i class="fas fa-video"></i> <span class="bpbm-group-call-participant-count">1</span> <?php _ex('participants are in group video chat', 'Group Video Chat', 'bp-better-messages'); ?>
                </span>
            <span class="bpbm-group-call-in-progress-join">
                    <button><?php _ex('Join Video Chat', 'Group Video Chat', 'bp-better-messages'); ?></button>
                </span>
            </div>
            <?php }
            if( $audioGroupCallActive ) { ?>
            <div class="bpbm-group-call-in-progress bpbm-group-call-type-audio" style="display: none">
            <span class="bpbm-group-call-in-progress-info">
                    <i class="fas fa-headset"></i> <span class="bpbm-group-call-participant-count">1</span> <?php _ex('participants are in group audio chat', 'Group Audio Chat', 'bp-better-messages'); ?>
                </span>
                <span class="bpbm-group-call-in-progress-join">
                    <button><?php _ex('Join Audio Chat', 'Group Audio Chat', 'bp-better-messages'); ?></button>
                </span>
            </div>
            <?php
            }
        }

        public function html_content( $thread_id, $participants, $is_mini, $type ){
            if( $is_mini ) return false;

            $disable_mic_icon = 'fas fa-microphone-slash';
            $enable_mic_icon  = 'fas fa-microphone';

            $disable_video_icon = 'fas fa-video-slash';
            $enable_video_icon  = 'fas fa-video';

            if( $this->revertIcons ){
                $disable_mic_icon = 'fas fa-microphone';
                $enable_mic_icon  = 'fas fa-microphone-slash';

                $disable_video_icon = 'fas fa-video';
                $enable_video_icon  = 'fas fa-video-slash';
            }

            $groupVideoActive = $this->is_group_call_active( $type, $participants['count'] );
            $groupAudioActive = $this->is_audio_group_call_active( $type, $participants['count'] );
            $groupsCallActive = $groupVideoActive || $groupAudioActive;

            if( $groupsCallActive ){
                ob_start();
                $isGroupCall = true;
                if( $groupAudioActive ) { ?>
                <div class="bp-messages-group-audio-call-container bp-messages-call-container">
                    <div class="bp-messages-group-call-audios-grid-wrapper">
                    <div class="bp-messages-group-call-audios-grid"></div>
                    </div>
                    <div class="bp-messages-group-call-audios-control">
                        <div class="bm-audio-chat-control">
                            <div class="microphone-control-selector">
                                <label><?php _ex('Microphone', 'Group Audio Chat', 'bp-better-messages'); ?></label>
                                <div class="bpbm-switch-mic-select-wrap">
                                    <select class="bpbm-switch-mic-select">
                                        <option value=""><?php _ex('Default', 'Group Audio Chat', 'bp-better-messages'); ?></option>
                                    </select>
                                </div>
                            </div>
                            <div class="speaker-control-selector">
                                <label><?php _ex('Speaker', 'Group Audio Chat', 'bp-better-messages'); ?></label>
                                <div class="bpbm-switch-audio-select-wrap">
                                    <select class="bpbm-switch-audio-select">
                                        <option value=""><?php _ex('Default', 'Group Audio Chat', 'bp-better-messages'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <div class="bm-audio-chat-control-group">
                                <div class="bm-connection-control">
                                    <span title="<?php _ex('Connection Status (Quality)', 'Group Audio Chat', 'bp-better-messages'); ?>" class="bm-connection-quality bm-tippy-title"><i></i><span></span></span>
                                </div>
                                <div class="bpbm-switch-mic-switcher">
                                    <span class="bpbm-disable-mic bm-tippy-title" title="<?php _e('Disable Microphone', 'bp-better-messages'); ?>" style="display: none"><i class="<?php echo $disable_mic_icon; ?>"></i></span>
                                    <span class="bpbm-enable-mic bm-tippy-title" title="<?php _e('Enable Microphone', 'bp-better-messages'); ?>"><i class="<?php echo $enable_mic_icon; ?>"></i></span>
                                    <span class="bm-end-call bm-tippy-title" title="<?php _ex('Leave chat', 'Group Audio Chat', 'bp-better-messages'); ?>"><i class="fas fa-sign-out-alt"></i></span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div> <?php }
                if( $groupVideoActive ) { ?>
                <div class="bp-messages-group-call-container bp-messages-call-container"></div>
                <?php } ?>
            <?php
                echo '<span class="bpbm-switch-chat bm-tippy-title" title="' . _x('Toggle Chat Screen', 'Calling Features - Toggle Chat Screen', 'bp-better-messages') . '"><i class="fas fa-caret-left"></i></span>';

                $html = ob_get_clean();
                $class = 'bp-messages-call-wrap bp-messages-call-wrap-group';
                echo '<div class="' . $class . '">' . $html . '</div>';
            } else {
                if( $this->video || $this->audio ){
                    $target_user_id = $participants[ 'recipients' ][0];
                    echo '<div class="bp-messages-call-wrap bp-messages-call-wrap-private" data-user-id="' . $target_user_id . '" data-avatar="' . BP_Better_Messages_Functions()->get_avatar($target_user_id, 100, ['html' => false]) . '"></div>';
                }
            }
        }

        public function thread_group_call_admin(){
            if( ! wp_verify_nonce( $_POST[ 'nonce' ], 'bpbm_edit_nonce' ) ){
                wp_send_json( false );
            }

            $user_id   = get_current_user_id();
            $thread_id = intval( $_POST['thread_id'] );
            $type      = sanitize_text_field( $_POST['type'] );
            $token     = sanitize_text_field( $_POST['token'] );

            $action = sanitize_text_field( $_POST['act'] );

            $video_cloud_server = apply_filters( 'bp_better_messages_video_cloud', 'https://video-cloud.bpbettermessages.com/' );
            $can_moderate = BP_Better_Messages()->functions->is_thread_super_moderator( $user_id, $thread_id );

            if( ! $can_moderate ) {
                wp_send_json( false );
            }

            $headers =  [
                'Content-Type'   => 'application/json',
                'Authorization'  => 'Bearer ' . $token,
            ];

            if( $action === 'remove_participant' ) {
                $identity = sanitize_text_field( $_POST['identity'] );

                $payload = json_encode([
                    'room'     => BP_Better_Messages()->premium->site_id . '_' . $thread_id . '_' . $type,
                    'identity' => $identity
                ]);

                $request = wp_remote_post($video_cloud_server . 'twirp/livekit.RoomService/RemoveParticipant', array(
                    'body'    => $payload,
                    'headers' => $headers
                ));

                if ( ! is_wp_error( $request ) ) {
                    wp_send_json( json_decode( $request['body'] ) );
                }
            }

            if( $action === 'mute_participant' ) {
                $identity = sanitize_text_field( $_POST['identity'] );

                $payload = json_encode([
                    'room'        => BP_Better_Messages()->premium->site_id . '_' . $thread_id . '_' . $type,
                    'identity'    => $identity,
                    'permission'  => [
                        'can_publish' => false,
                        'can_publish_data' => true,
                        'can_subscribe' => true
                    ]
                ]);

                $request = wp_remote_post($video_cloud_server . 'twirp/livekit.RoomService/UpdateParticipant', array(
                    'body'    => $payload,
                    'headers' => $headers
                ));

                if ( ! is_wp_error( $request ) ) {
                    wp_send_json( json_decode( $request['body'] ) );
                }
            }

            if( $action === 'unmute_participant' ) {
                $identity = sanitize_text_field( $_POST['identity'] );

                $payload = json_encode([
                    'room'        => BP_Better_Messages()->premium->site_id . '_' . $thread_id . '_' . $type,
                    'identity'    => $identity,
                    'permission'  => [
                        'can_publish' => true,
                        'can_publish_data' => true,
                        'can_subscribe' => true
                    ]
                ]);

                $request = wp_remote_post($video_cloud_server . 'twirp/livekit.RoomService/UpdateParticipant', array(
                    'body'    => $payload,
                    'headers' => $headers
                ));

                if ( ! is_wp_error( $request ) ) {
                    wp_send_json( json_decode( $request['body'] ) );
                }
            }



            wp_send_json( false );
        }

        public function get_thread_group_call_access_key(){
            if( ! wp_verify_nonce( $_POST[ 'nonce' ], 'bpbm_edit_nonce' ) ){
                wp_send_json(__( 'Security error', 'bp-better-messages' ));
                exit;
            }

            $user_id = get_current_user_id();
            $user = get_userdata( $user_id );
            $thread_id = intval( $_POST['thread_id'] );
            $type      = sanitize_text_field( $_POST['type'] );
            $video_management_server = apply_filters( 'bp_better_messages_video_management', 'https://realtime-cloud.bpbettermessages.com/' );

            $can_join = false;
            $can_moderate = BP_Better_Messages()->functions->is_thread_super_moderator( $user_id, $thread_id );
            if( $can_moderate ){
                $can_join = true;
            }

            if( ! $can_join ){
                $can_join = BP_Better_Messages()->functions->is_thread_participant( $user_id, $thread_id );
            }

            if( ! $can_join ){
                wp_send_json( false );
            }

            $request = [
                'site_id'    => BP_Better_Messages()->premium->site_id,
                'secret_key' => sha1( BP_Better_Messages()->premium->site_id . BP_Better_Messages()->premium->secret_key ),
                'user_id'    => $user_id,
                'thread_id'  => $thread_id,
                'meta'       => json_encode([
                    'name'   => ( ! empty( $user->display_name ) ) ? $user->display_name : $user->user_login,
                    'avatar' => BP_Better_Messages_Functions()->get_avatar($user->ID, 200, [ 'html' => false ] ),
                    'link'   => bp_core_get_userlink($user->ID, false, true)
                ]),
                'type'             => $type,
                'is_admin'         => ($can_moderate) ? '1' : '0',
                'can_publish'      => '1',
                'can_publish_data' => '1',
                'can_subscribe'    => '1',
                'is_hidden'        => '0',
            ];

            $request = wp_remote_post( $video_management_server . 'getCallAuthKey', array(
                'body' => $request,
                'blocking' => true
            ) );

            $token = $request['body'];

            wp_send_json( $token );
        }
    }

endif;


function BP_Better_Messages_Calls()
{
    return BP_Better_Messages_Calls::instance();
}
