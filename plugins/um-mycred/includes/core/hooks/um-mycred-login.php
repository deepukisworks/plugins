<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_myCRED_Login_Hooks
 */
class UM_myCRED_Login_Hooks extends myCRED_Hook {


	/**
	 * @var array
	 */
	var $um_hooks = array();


	/**
	 * UM_myCRED_Login_Hooks constructor.
	 *
	 * @param $hook_prefs
	 * @param $type
	 */
	function __construct( $hook_prefs, $type ) {

		$this->um_hooks = array(
			'um_user_login' => array(
				'title'  => __( 'Logging via Ultimate Member Login Form', 'um-mycred' ),
				'action' => __( 'logging in', 'um-mycred' ),
			)
		);

		$arr_defaults = array();

		foreach ( $this->um_hooks as $hook => $k ) {

			$arr_defaults[ $hook ] = array(
				'creds'     => 1,
				'log'       => sprintf( __( '%%plural%% for %s.', 'um-mycred' ), $k['action'] ),
				'limit'     => '0/x',
				'um_hook'   => $hook,
			);

			$arr_defaults[ $hook ] = apply_filters( 'um_mycred_hook_defaults', $arr_defaults[ $hook ], $hook, $k, $this->um_hooks );

		}

		parent::__construct( array(
			'id'       => 'um-user-login',
			'defaults' => $arr_defaults
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {
		if ( $this->prefs['um_user_login']['creds'] != 0 ) {
			add_action( 'um_on_login_before_redirect', array( $this, 'award_points_login' ), 20, 1 );
		}
	}


	/**
	 * Check if the user qualifies for points
	 */
	public function award_points_login( $user_id ) {
		// Check for exclusion

		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( 'um_user_login', 'um-user-login', $user_id ) ) {
			return;
		}

		$ref = 'um_user_login';
		$data = apply_filters( 'um_mycred_hooks_data', '', $ref, $this->prefs );

		// Execute
		$this->core->add_creds(
			'um-user-login',
			$user_id,
			$this->prefs[ $ref ]['creds'],
			$this->prefs[ $ref ]['log'],
			0,
			$data,
			$this->mycred_type
		);
	}


	/**
	 * Add Settings
	 */
	public function preferences() {
		UM()->myCRED()->hooks()->build_hook_widget( $this );
	}


	/**
	 * Sanitize Preferences
	 *
	 * @param $data
	 *
	 * @return mixed
	 */
	public function sanitise_preferences( $data ) {
		return UM()->myCRED()->hooks()->sanitise_preferences( $data, $this );
	}
}
