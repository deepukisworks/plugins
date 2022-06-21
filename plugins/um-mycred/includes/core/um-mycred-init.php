<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_myCRED
 */
class UM_myCRED {

	/**
	 * @deprecated since 2.1.8
	 *
	 * @var string
	 */
	public $action = '';


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_myCRED
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}


	/**
	 * UM_myCRED constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_mycred'] = $this;
		add_filter( 'um_call_object_myCRED', array( &$this, 'get_this' ) );
		add_filter( 'um_settings_default_values', array( &$this, 'default_settings' ), 10, 1 );

		if ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		}

		$this->enqueue();
		$this->account();
		$this->member_directory();
		$this->hooks();
		$this->rest();
		$this->profile();
		$this->notifications();
		$this->bbpress_integration();
		$this->fields();
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * @param array $defaults
	 *
	 * @return array
	 */
	function default_settings( $defaults ) {
		$defaults = array_merge( $defaults, $this->setup()->settings_defaults );
		return $defaults;
	}


	/**
	 * @return um_ext\um_mycred\core\Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_mycred_admin'] ) ) {
			UM()->classes['um_mycred_admin'] = new um_ext\um_mycred\core\Admin();
		}
		return UM()->classes['um_mycred_admin'];
	}


	/**
	 * @return um_ext\um_mycred\core\Hooks()
	 */
	function hooks() {
		if ( empty( UM()->classes['um_mycred_hooks'] ) ) {
			UM()->classes['um_mycred_hooks'] = new um_ext\um_mycred\core\Hooks();
		}
		return UM()->classes['um_mycred_hooks'];
	}


	/**
	 * @return um_ext\um_mycred\core\REST()
	 */
	function rest() {
		if ( empty( UM()->classes['um_mycred_rest'] ) ) {
			UM()->classes['um_mycred_rest'] = new um_ext\um_mycred\core\REST();
		}
		return UM()->classes['um_mycred_rest'];
	}


	/**
	 * @return um_ext\um_mycred\core\Points()
	 */
	function points() {
		if ( empty( UM()->classes['um_mycred_points'] ) ) {
			UM()->classes['um_mycred_points'] = new um_ext\um_mycred\core\Points();
		}
		return UM()->classes['um_mycred_points'];
	}


	/**
	 * @return um_ext\um_mycred\core\Profile()
	 */
	function profile() {
		if ( empty( UM()->classes['um_mycred_profile'] ) ) {
			UM()->classes['um_mycred_profile'] = new um_ext\um_mycred\core\Profile();
		}
		return UM()->classes['um_mycred_profile'];
	}


	/**
	 * @return um_ext\um_mycred\core\Badges()
	 */
	function badges() {
		if ( empty( UM()->classes['um_mycred_badges'] ) ) {
			UM()->classes['um_mycred_badges'] = new um_ext\um_mycred\core\Badges();
		}
		return UM()->classes['um_mycred_badges'];
	}


	/**
	 * @return um_ext\um_mycred\core\Ranks()
	 */
	function ranks() {
		if ( empty( UM()->classes['um_mycred_ranks'] ) ) {
			UM()->classes['um_mycred_ranks'] = new um_ext\um_mycred\core\Ranks();
		}
		return UM()->classes['um_mycred_ranks'];
	}


	/**
	 * @return um_ext\um_mycred\core\Notifications()
	 */
	function notifications() {
		if ( empty( UM()->classes['um_mycred_notifications'] ) ) {
			UM()->classes['um_mycred_notifications'] = new um_ext\um_mycred\core\Notifications();
		}
		return UM()->classes['um_mycred_notifications'];
	}


	/**
	 * @return um_ext\um_mycred\core\bbPress_Integration()
	 */
	function bbpress_integration() {
		if ( empty( UM()->classes['um_mycred_bbpress'] ) ) {
			UM()->classes['um_mycred_bbpress'] = new um_ext\um_mycred\core\bbPress_Integration();
		}
		return UM()->classes['um_mycred_bbpress'];
	}


	/**
	 * @return um_ext\um_mycred\core\Fields()
	 */
	function fields() {
		if ( empty( UM()->classes['um_mycred_fields'] ) ) {
			UM()->classes['um_mycred_fields'] = new um_ext\um_mycred\core\Fields();
		}
		return UM()->classes['um_mycred_fields'];
	}


	/**
	 * @return um_ext\um_mycred\core\Setup()
	 */
	function setup() {
		if ( empty( UM()->classes['um_mycred_setup'] ) ) {
			UM()->classes['um_mycred_setup'] = new um_ext\um_mycred\core\Setup();
		}
		return UM()->classes['um_mycred_setup'];
	}


	/**
	 * @return um_ext\um_mycred\core\Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_mycred_enqueue'] ) ) {
			UM()->classes['um_mycred_enqueue'] = new um_ext\um_mycred\core\Enqueue();
		}
		return UM()->classes['um_mycred_enqueue'];
	}


	/**
	 * @return um_ext\um_mycred\core\Account()
	 */
	function account() {
		if ( empty( UM()->classes['um_mycred_account'] ) ) {
			UM()->classes['um_mycred_account'] = new um_ext\um_mycred\core\Account();
		}
		return UM()->classes['um_mycred_account'];
	}


	/**
	 * @return um_ext\um_mycred\core\Member_Directory()
	 */
	function member_directory() {
		if ( empty( UM()->classes['um_mycred_member_directory'] ) ) {
			UM()->classes['um_mycred_member_directory'] = new um_ext\um_mycred\core\Member_Directory();
		}
		return UM()->classes['um_mycred_member_directory'];
	}











	/**
	 * @deprecated since 2.1.8
	 */
	function load_hooks() {
		$this->hooks()->load_hooks();
	}


	/**
	 * Init myCRED Custom Hooks
	 *
	 * @deprecated since 2.1.8
	 */
	function init_hooks() {
		$this->hooks()->init_hooks();
	}


	/**
	 * @deprecated since 2.1.8
	 *
	 * @param $hook_callback
	 */
	function build_hook_widget( $hook_callback ) {
		$this->hooks()->build_hook_widget( $hook_callback );
	}


	/**
	 * @deprecated since 2.1.8
	 *
	 * @param $data
	 * @param $hook_callback
	 *
	 * @return mixed
	 */
	function sanitise_preferences( $data, $hook_callback ) {
		return $this->hooks()->sanitise_preferences( $data, $hook_callback );
	}


	/**
	 * Extends REST API response attributes
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $response
	 * @param $field
	 * @param $user_id
	 *
	 * @return mixed
	 */
	function rest_get_auser( $response, $field, $user_id ) {
		return $this->rest()->rest_get_auser( $response, $field, $user_id );
	}


	/**
	 * Points formatting
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $formatted
	 * @param $number
	 * @param $decimals
	 *
	 * @return string
	 */
	function custom_number_format_i18n( $formatted, $number, $decimals ) {
		return $this->points()->number_format( $formatted, $number, $decimals );
	}


	/**
	 * Get points
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $user_id
	 * @param null $value
	 * @param string $point_type
	 *
	 * @return mixed|string|null
	 */
	function get_points( $user_id, $value = null, $point_type = null ) {
		return $this->points()->get_points( $user_id, $point_type );
	}


	/**
	 * Get points clean
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $user_id
	 * @param null $value
	 * @param null $point_type
	 *
	 * @return mixed|null
	 */
	function get_points_clean( $user_id, $value = null, $point_type = null ) {
		return $this->points()->get_points_clean( $user_id, $point_type );
	}


	/**
	 * Transfer points
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $from
	 * @param $to
	 * @param $amount
	 * @param string|null $point_type
	 */
	function transfer( $from, $to, $amount, $point_type = null  ) {
		$this->points()->transfer( $from, $to, $amount, $point_type );
	}


	/**
	 * Add points
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $user_id
	 * @param $add
	 * @param array $args
	 */
	function add( $user_id, $add, $args = array() ) {
		$this->points()->add( $user_id, $add, $args );
	}


	/**
	 * Add points (hold)
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $user_id
	 * @param $add
	 *
	 * @return mixed|string|void
	 */
	function add_pending( $user_id, $add ) {
		$this->points()->add_pending( $user_id, $add );
	}


	/**
	 * Deduct points
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $user_id
	 * @param $deduct
	 * @param array $args
	 */
	function deduct( $user_id, $deduct, $args = array() ) {
		$this->points()->deduct( $user_id, $deduct, $args );
	}


	/**
	 * Update user's balance
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param  integer $user_id
	 * @param  string  $action
	 * @param  string  $type
	 *
	 * @return bool
	 */
	function imply_limit( $user_id, $action, $type ) {
		return $this->points()->imply_limit( $user_id, $action, $type );
	}


	/**
	 * Show badges all
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param int $template
	 *
	 * @return string
	 */
	function show_badges_all( $template = 1 ) {
		return $this->badges()->show_all( $template );
	}


	/**
	 * Show badges of user
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $user_id
	 *
	 * @return string
	 */
	function show_badges( $user_id ) {
		return $this->badges()->show( $user_id );
	}


	/**
	 * Get user progress
	 *
	 * @deprecated since 2.1.8
	 *
	 * @param $user_id
	 *
	 * @return int|string
	 */
	function get_rank_progress( $user_id ) {
		return $this->ranks()->get_progress( $user_id );
	}

}


if ( ! function_exists( 'um_init_mycred' ) ) {


	/**
	 * Initialize myCRED running and create class variable
	 */
	function um_init_mycred() {
		if ( function_exists( 'UM' ) ) {
			UM()->set_class( 'myCRED', true );
		}
	}
}
add_action( 'plugins_loaded', 'um_init_mycred', -10, 1 );