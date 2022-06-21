<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_myCRED_Member_Directory_Hooks
 */
class UM_myCRED_Member_Directory_Hooks extends myCRED_Hook {


	/**
	 * @var array
	 */
	var $um_hooks = array();


	/**
	 * UM_myCRED_Member_Directory_Hooks constructor.
	 *
	 * @param $hook_prefs
	 * @param $type
	 */
	function __construct( $hook_prefs, $type ) {

		$this->um_hooks = array(
			'member_search' => array(
				'title'  => __( 'Use Search', 'um-mycred' ),
				'action' => __( 'using search member form', 'um-mycred' ),
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
			'id'       => 'um-member-directory',
			'defaults' => $arr_defaults
		), $hook_prefs, $type );

	}

	/**
	 * Hook into WordPress
	 */
	public function run() {
		if ( $this->prefs['member_search']['creds'] != 0 ) {
			add_action( 'um_user_before_query', array( $this, 'award_points_directory_search' ), 10, 2 );
		}
	}

	/**
	 * Check if the user qualifies for points
	 *
	 * @param array $query_args
	 * @param um\core\Member_Directory $md_class
	 */
	public function award_points_directory_search( $query_args, $md_class ) {
		if ( ! isset( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		// Check for exclusion
		if ( $this->core->exclude_user( $user_id ) ) {
			return;
		}

		// Limit
		if ( $this->over_hook_limit( 'member_search', 'member_search', $user_id ) ) {
			return;
		}

		if ( $md_class->is_search ) {

			$ref = 'member_search';
			$data = apply_filters( 'um_mycred_hooks_data', '', $ref, $this->prefs );

			// Execute
			$this->core->add_creds(
				$ref,
				$user_id,
				$this->prefs[ $ref ]['creds'],
				$this->prefs[ $ref ]['log'],
				0,
				$data,
				$this->mycred_type
			);
		}
	}


	/**
	 * Add Settings
	 */
	public function preferences() {
		UM()->myCRED()->hooks()->build_hook_widget( $this );
	}


	/**
	 * Sanitize Preferences
	 */
	public function sanitise_preferences( $data ) {
		return UM()->myCRED()->hooks()->sanitise_preferences( $data, $this );
	}
}