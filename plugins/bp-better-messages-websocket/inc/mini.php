<?php
defined( 'ABSPATH' ) || exit;

class BP_Better_Messages_Mini
{

    public static function instance()
    {

        // Store the instance locally to avoid private static replication
        static $instance = null;

        // Only run these methods if they haven't been run previously
        if ( null === $instance ) {
            $instance = new BP_Better_Messages_Mini;
            $instance->setup_actions();
        }

        // Always return the instance
        return $instance;

        // The last metroid is in captivity. The galaxy is at peace.
    }

    public function setup_actions()
    {
        add_action('wp_footer', array( $this, 'html' ), 200);
        add_action('bp_better_messages_thread_pre_header', array($this, 'thread_button'), 1, 4);
    }

    public function thread_button( $thread_id, $participants, $is_mini, $type = 'thread' )
    {
        if ($is_mini || ! in_array($type, ['thread', 'group'])) return false;

        if ($type === 'group' && function_exists('bp_is_current_component') && bm_bp_is_current_component('groups') && ! doing_action('wp_ajax_bp_messages_load_via_ajax')) {
            $url = bp_get_group_permalink();
            echo '<a href="' . $url . '" class="mini-chat group-redirect" title="' . __('Minimize thread', 'bp-better-messages') . '"><i class="fas fa-window-minimize"></i></a>';
        } else {
            echo '<a href="' . BP_Better_Messages()->functions->get_link() . '" class="mini-chat ajax" title="' . __('Minimize thread', 'bp-better-messages') . '"><i class="fas fa-window-minimize"></i></a>';
        }
    }

    public function html(){
        if( ! is_user_logged_in() ) return false;
        ?>
        <div class="bp-messages-wrap bp-better-messages-mini <?php BP_Better_Messages()->functions->messages_classes(); ?>">
            <div class="chats"></div>
        </div>
        <?php
    }
}

function BP_Better_Messages_Mini()
{
    return BP_Better_Messages_Mini::instance();
}