<?php
namespace um_ext\um_messaging\core;

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) exit;

class Messaging_Enqueue {

	/**
	 * Should we print hidden login form or not
	 * @var boolean
	 */
	public $need_hidden_login = false;


	/**
	 * The class constructor
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts',  array( &$this, 'wp_enqueue_scripts' ), 9999 );
		add_action( 'wp_footer', array( &$this, 'footer_login_form' ), 5 );
	}


	/**
	 * Insert Login form to hidden block
	 */
	public function footer_login_form() {
		if ( !$this->need_hidden_login ) {
			return;
		}
		if ( is_user_logged_in() ) {
			return;
		}
		if ( empty( UM()->options()->get( 'show_pm_button' ) ) ) {
			return;
		}

		if ( ! empty( $_COOKIE['um_messaging_invite_login'] ) ) {
			$_POST = array_merge( json_decode( wp_unslash( $_COOKIE['um_messaging_invite_login'] ), true ), $_POST );
			UM()->form()->form_init();
		}

		add_filter( 'um_browser_url_redirect_to__filter', array( UM()->Messaging_API()->api(), 'set_redirect_to' ), 10, 1 );
		?>

		<div id="um_messaging_hidden_login" class="um_messaging_hidden_login">
			<div class="um-modal um-modal-hidden">
				<div class="um-message-header um-popup-header"></div>
				<div class="um-message-modal">
					<div class="um-message-body um-popup-autogrow2 um-message-autoheight" data-simplebar>
						<?php if ( version_compare( get_bloginfo( 'version' ),'5.4', '<' ) ) {
							echo do_shortcode( '[ultimatemember form_id="' . UM()->shortcodes()->core_login_form() . '" /]' );
						} else {
							echo apply_shortcodes( '[ultimatemember form_id="' . UM()->shortcodes()->core_login_form() . '" /]' );
						} ?>
					</div>
				</div>
			</div>
		</div>

		<?php
	}


	/**
	 * Frontend Scripts
	 */
	public function wp_enqueue_scripts() {
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || defined( 'UM_SCRIPT_DEBUG' ) ) ? '' : '.min';

		wp_register_script( 'um-messaging-moment', um_messaging_url . 'assets/js/moment-with-locales.min.js', array( 'jquery' ), um_messaging_version, true );
		wp_register_script( 'um-messaging-moment-timezone', um_messaging_url . 'assets/js/moment-timezone.min.js', array( 'jquery' ), um_messaging_version, true );
		wp_register_script( 'um-messaging-autosize', um_messaging_url . 'assets/js/autosize.min.js', array( 'jquery' ), um_messaging_version, true );

		wp_register_script( 'um-messaging', um_messaging_url . 'assets/js/um-messaging' . $suffix . '.js', array( 'jquery', 'wp-util', 'jquery-ui-datepicker', 'um-messaging-moment', 'um-messaging-moment-timezone', 'um-messaging-autosize', 'um_scripts', 'um_functions', 'um_modal', 'um_responsive', 'um_tipsy' ), um_messaging_version, true );

		// Localize the script with new data
		wp_localize_script( 'um-messaging', 'um_message_i18n', array(
			'no_chats_found' => __( 'No chats found here', 'um-messaging' ),
		) );

		// Localize time
		wp_localize_script( 'um-messaging', 'um_message_timezone', array(
			'string' => get_option( 'timezone_string' ),
			'offset' => get_option( 'gmt_offset' ),
		) );

		$interval = UM()->options()->get( 'pm_coversation_refresh_timer' );
		$interval = ( ! empty( $interval ) && is_numeric( $interval ) ) ? $interval * 1000 : 5000;

		$can_read = false;

		if ( is_user_logged_in() ) {
			um_fetch_user( get_current_user_id() );

			if ( um_user( 'can_read_pm' ) ) {
				$can_read = true;
			}

			um_reset_user();
		}

		wp_localize_script( 'um-messaging', 'um_messages', array(
			'can_read' => $can_read,
			'interval' => $interval
		) );

		wp_register_style( 'um-messaging', um_messaging_url . 'assets/css/um-messaging' . $suffix . '.css', array( 'um_scrollbar', 'um_modal', 'um_responsive' ), um_messaging_version );

		$color_hex = UM()->options()->get( 'pm_active_color' );
		$color_rgb = UM()->Messaging_API()->api()->hex_to_rgb( $color_hex );

		$css = '
			.um-message-item-content a { color:' . $color_hex . '; text-decoration: underline !important;}
			.um-message-item-content a:hover {color: rgba(' . $color_rgb . ', 0.9);}
			.um-message-item.left_m .um-message-item-content a {color: #fff}
			.um-message-send, .um-message-send.disabled:hover { background-color:' . $color_hex . '; }
			.um-message-send:hover { background-color: rgba(' . $color_rgb . ', 0.9) }
			.um-message-item.left_m .um-message-item-content { background-color: rgba(' . $color_rgb . ', 0.8);}
			.um-message-footer { background: rgba(' . $color_rgb . ', 0.03); border-top: 1px solid rgba(' . $color_rgb . ', 0.2);}
			.um-message-textarea textarea, div.um div.um-form .um-message-textarea textarea {border: 2px solid rgba(' . $color_rgb . ', 0.3) !important}
			.um-message-textarea textarea:focus,  div.um div.um-form .um-message-textarea textarea:focus {border: 2px solid rgba(' . $color_rgb . ', 0.6) !important}
			.um-message-emolist { border: 1px solid rgba(' . $color_rgb . ', 0.25);}
			.um-message-conv-item.active {color: ' . $color_hex . ';}
			.um-message-conv-view {border-left: 1px solid rgba(' . $color_rgb . ', 0.2);}
		';

		wp_add_inline_style( 'um-messaging', wp_strip_all_tags( $css ) );
	}

}