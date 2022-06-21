<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_myCRED_Register_Hooks
 */
class UM_myCRED_Register_Hooks extends myCRED_Hook {


	/**
	 * @var array
	 */
	var $um_hooks = array();


	/**
	 * UM_myCRED_Register_Hooks constructor.
	 *
	 * @param $hook_prefs
	 * @param $type
	 */
	function __construct( $hook_prefs, $type ) {

		$this->um_hooks = array(
			'signup' => array(
				'title'  => __( 'Completing Registration', 'um-mycred' ),
				'action' => __( 'signing up', 'um-mycred' ),
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
			'id'       => 'um-user-register',
			'defaults' => $arr_defaults
		), $hook_prefs, $type );
	}


	/**
	 * Hook into WordPress
	 */
	public function run() {

		if ( $this->prefs['signup']['creds'] != 0 ) {
			add_action( 'um_after_user_is_approved', array( $this, 'award_points_user_is_approved' ), 1 );
		}
	
	}


	/**
	 * Check if the user qualifies for points
	 *
	 * @param $user_id
	 */
	public function award_points_user_is_approved( $user_id ) {
		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( 'signup', 'um-user-register', $user_id ) ) {
			return;
		}

		$ref = 'signup';
		$data = apply_filters( 'um_mycred_hooks_data', '', $ref, $this->prefs );

		// Execute
		$this->core->add_creds(
			'um-user-register',
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
