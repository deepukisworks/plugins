<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class UM_Profile_Completeness_API
 */
class UM_Profile_Completeness_API {


	/**
	 * @var
	 */
	private static $instance;


	/**
	 * @return UM_Profile_Completeness_API
	 */
	static public function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}


	/**
	 * UM_Profile_Completeness_API constructor.
	 */
	function __construct() {
		// Global for backwards compatibility.
		$GLOBALS['um_profile_completeness'] = $this;
		add_filter( 'um_call_object_Profile_Completeness_API', array( &$this, 'get_this' ) );

		if ( UM()->is_request( 'admin' ) ) {
			$this->admin();
		}

		$this->enqueue();
		$this->shortcode();
		$this->restrict();
		$this->member_directory();

		add_action( 'plugins_loaded', array( &$this, 'init' ), 0 );

		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-widget.php';
		add_action( 'widgets_init', array( &$this, 'widgets_init' ) );

		add_action( 'wp_ajax_um_profile_completeness_save_popup', array( $this, 'ajax_save_popup' ) );
		add_action( 'wp_ajax_um_profile_completeness_edit_popup', array( $this, 'ajax_edit_popup' ) );
		add_action( 'wp_ajax_um_profile_completeness_get_widget', array( $this, 'ajax_get_widget' ) );
		add_action( 'wp_ajax_um_profile_completeness_get_fields_data', array( $this, 'ajax_get_fields_data' ) );
	}


	/**
	 * @return $this
	 */
	function get_this() {
		return $this;
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Enqueue()
	 */
	function enqueue() {
		if ( empty( UM()->classes['um_profile_completeness_enqueue'] ) ) {
			UM()->classes['um_profile_completeness_enqueue'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Enqueue();
		}

		return UM()->classes['um_profile_completeness_enqueue'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Shortcode()
	 */
	function shortcode() {
		if ( empty( UM()->classes['um_profile_completeness_shortcode'] ) ) {
			UM()->classes['um_profile_completeness_shortcode'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Shortcode();
		}

		return UM()->classes['um_profile_completeness_shortcode'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Admin()
	 */
	function admin() {
		if ( empty( UM()->classes['um_profile_completeness_admin'] ) ) {
			UM()->classes['um_profile_completeness_admin'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Admin();
		}

		return UM()->classes['um_profile_completeness_admin'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Restrict()
	 */
	function restrict() {
		if ( empty( UM()->classes['um_profile_completeness_restrict'] ) ) {
			UM()->classes['um_profile_completeness_restrict'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Restrict();
		}

		return UM()->classes['um_profile_completeness_restrict'];
	}


	/**
	 * @return um_ext\um_profile_completeness\core\Profile_Completeness_Member_Directory()
	 */
	function member_directory() {
		if ( empty( UM()->classes['um_profile_completeness_member_directory'] ) ) {
			UM()->classes['um_profile_completeness_member_directory'] = new um_ext\um_profile_completeness\core\Profile_Completeness_Member_Directory();
		}

		return UM()->classes['um_profile_completeness_member_directory'];
	}


	/**
	 * Init
	 */
	function init() {
		delete_user_meta( 1, 'birthdate' );

		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-profile.php';
		require_once um_profile_completeness_path . 'includes/core/um-profile-completeness-fields.php';
	}


	/**
	 * Get factors that increase completion
	 *
	 * @param $role_data
	 *
	 * @return array|bool
	 */
	function get_metrics( $role_data ) {
		$array = array();
		$meta = $role_data;
		foreach ( $meta as $k => $v ) {
			if ( strstr( $k, 'progress_' ) ) {
				$k = str_replace( 'progress_', '', $k );
				if ( $k == 'profile_photo' ) {

					if ( um_user( 'profile_photo' ) ) {
						$array['profile_photo'] = $v;
					} elseif ( um_user( 'synced_profile_photo' ) ) {
						$array['synced_profile_photo'] = $v;
					} elseif ( UM()->options()->get( 'use_gravatars' ) ) {
						$array['synced_gravatar_hashed_id'] = $v;
					}
					continue;
				}
				$array[ $k ] = $v;
			}
		}

		return ! empty( $array ) ? $array : false;
	}


	/**
	 * Get user profile progress
	 *
	 * @param $user_id
	 *
	 * @return array|int
	 */
	function get_progress( $user_id ) {

		//get priority role here
		$role_data = UM()->roles()->role_data( UM()->roles()->get_priority_user_role( $user_id ) );
		if ( empty( $role_data['profilec'] ) ) {
			return -1;
		}

		// get factors
		$array = $this->get_metrics( $role_data );
		if ( ! $array ) {
			$result = array(
				'req_progress'                  => $role_data['profilec_pct'],
				'progress'                      => 100,
				'steps'                         => '',
				'prevent_browse'                => $role_data['profilec_prevent_browse'],
				'prevent_browse_exclude_pages'  => empty( $role_data['profilec_prevent_browse_exclude_pages'] ) ? '' : $role_data['profilec_prevent_browse_exclude_pages'],
				'prevent_browse_redirect'       => empty( $role_data['profilec_prevent_browse_redirect'] ) ? 0 : $role_data['profilec_prevent_browse_redirect'],
				'prevent_browse_redirect_url'   => empty( $role_data['profilec_prevent_browse_redirect_url'] ) ? '' : $role_data['profilec_prevent_browse_redirect_url'],
				'prevent_profileview'           => $role_data['profilec_prevent_profileview'],
				'prevent_comment'               => $role_data['profilec_prevent_comment'],
			);

			$result = apply_filters( 'um_profile_completeness_get_progress_result', $result, $role_data );

			$result['raw'] = $result;

			update_user_meta( $user_id, '_profile_progress', $result );
			update_user_meta( $user_id, '_completed', 100 );

			return $result;
		}

		// see what user has completed
		$profile_progress = 0;
		$completed = array();
		foreach ( $array as $key => $value ) {
			$custom = apply_filters( 'um_profile_completeness_get_field_progress', false, $key, $user_id );
			if ( $custom ) {
				$profile_progress = $profile_progress + (int)$value;
				$completed[] = $key;
			} else {

				$field_type = UM()->fields()->get_field_type( $key );
				$user_meta = get_user_meta( $user_id, $key, true );

				switch( $field_type ) {
					case 'checkbox':
					case 'multiselect':

						if ( ! empty( $user_meta ) ) {
							$profile_progress = $profile_progress + (int)$value;
							$completed[] = $key;
						}

						break;
					default:

						$check_hook = apply_filters( 'um_profile_completeness_get_progress', false, $field_type, $key, $user_meta, $value );

						if ( false === $check_hook ) {
							if ( ! empty( $user_meta ) ) {
								$profile_progress = $profile_progress + (int)$value;
								$completed[] = $key;
							} elseif ( in_array( $key, array( 'user_email' ) ) ) {
								$user = get_user_by( 'ID', $user_id );
								if ( ! empty( $user ) && ! empty( $user->user_email ) ) {
									$profile_progress = $profile_progress + (int)$value;
									$completed[] = $key;
								}
							} elseif ( in_array( $key, array( 'user_url' ) ) ) {
								$user = get_user_by( 'ID', $user_id );
								if ( ! empty( $user ) && ! empty( $user->user_url ) ) {
									$profile_progress = $profile_progress + (int)$value;
									$completed[] = $key;
								}
							} elseif ( in_array( $key, array( 'profile_photo' ) ) ) {
								$user_photo = get_user_meta( $user_id, 'profile_photo', true );
								if ( ! $user_photo ) {
									$user_photo = get_user_meta( $user_id, '_save_synced_profile_photo', true );
								}
								if ( $user_photo ) {
									$profile_progress = $profile_progress + (int) $value;
									$completed[] = $key;
								}
							}
						} elseif ( $check_hook != '' ) {
							$profile_progress = $profile_progress + (int) $check_hook;
							$completed[] = $key;
						}

						break;
				}
			}
		}

		$result = array(
			'req_progress'                  => $role_data['profilec_pct'],
			'progress'                      => $profile_progress,
			'steps'                         => $array,
			'completed'                     => $completed,
			'prevent_browse'                => ( empty( $role_data['profilec_prevent_browse'] ) ? 0 : 1 ),
			'prevent_browse_exclude_pages'  => empty( $role_data['profilec_prevent_browse_exclude_pages'] ) ? '' : $role_data['profilec_prevent_browse_exclude_pages'],
			'prevent_browse_redirect'       => empty( $role_data['profilec_prevent_browse_redirect'] ) ? 0 : $role_data['profilec_prevent_browse_redirect'],
			'prevent_browse_redirect_url'   => empty( $role_data['profilec_prevent_browse_redirect_url'] ) ? '' : $role_data['profilec_prevent_browse_redirect_url'],
			'prevent_profileview'           => ( empty( $role_data['profilec_prevent_profileview'] ) ? 0 : 1 ),
			'prevent_comment'               => ( empty( $role_data['profilec_prevent_comment'] ) ? 0 : 1 ),
			'prevent_bb'                    => ( empty( $role_data['profilec_prevent_bb'] ) ? 0 : 1 ),
		);

		$result = apply_filters( 'um_profile_completeness_get_progress_result', $result, $role_data );

		update_user_meta( $user_id, '_profile_progress', $result );
		update_user_meta( $user_id, '_completed', $profile_progress );

		$profile_percentage = $role_data['profilec_pct'];

		if ( empty( $profile_percentage ) ) {
			$profile_percentage = 100;
		}

		if ( $profile_progress >= $profile_percentage && $role_data['profilec_upgrade_role'] ) {
			$userdata = get_userdata( $user_id );
			$old_roles = $userdata->roles;
			foreach ( $old_roles as $_role ) {
				UM()->roles()->remove_role( $user_id, $_role );
			}

			$new_role = $role_data['profilec_upgrade_role'];
			UM()->roles()->set_role( $user_id, $new_role );

			do_action( 'um_after_member_role_upgrade', array( $new_role ), $old_roles, $user_id );
		}

		$result['raw'] = $result;
		return $result;
	}


	/**
	 * Field validation
	 * @param  string       $key    The key of the field
	 * @param  string|array $value  Submitted value
	 * @return string							  Error message
	 */
	public function validate_field( $key, $value ) {
		$error = '';

		$can_edit = false;
		$current_user_roles = array();
		if ( is_user_logged_in() ) {
			$can_edit = UM()->roles()->um_current_user_can( 'edit', get_current_user_id() );

			um_fetch_user( get_current_user_id() );
			$current_user_roles = um_user( 'roles' );
			um_reset_user();
		}

		$field = UM()->fields()->get_field( $key );

		$mode = 'profile';
		$user_id = get_current_user_id();

		$restricted_fields = UM()->fields()->get_restricted_fields_for_edit();
		if ( is_array( $restricted_fields ) && in_array( $key, $restricted_fields ) ) {
			return __( 'You can not edit this field. The field is restricted.', 'ultimate-member' );
		}

		$can_view = true;
		if ( isset( $field['public'] ) && $mode != 'register' ) {

			switch ( $field['public'] ) {
				case '1': // Everyone
					break;
				case '2': // Members
					if ( ! is_user_logged_in() ) {
						$can_view = false;
					}
					break;
				case '-1': // Only visible to profile owner and admins
					if ( ! is_user_logged_in() ) {
						$can_view = false;
					} elseif ( $user_id != get_current_user_id() && ! $can_edit ) {
						$can_view = false;
					}
					break;
				case '-2': // Only specific member roles
					if ( ! is_user_logged_in() ) {
						$can_view = false;
					} elseif ( ! empty( $field['roles'] ) && count( array_intersect( $current_user_roles, $field['roles'] ) ) <= 0 ) {
						$can_view = false;
					}
					break;
				case '-3': // Only visible to profile owner and specific roles
					if ( ! is_user_logged_in() ) {
						$can_view = false;
					} elseif ( $user_id != get_current_user_id() && ! empty( $field['roles'] ) && count( array_intersect( $current_user_roles, $field['roles'] ) ) <= 0 ) {
						$can_view = false;
					}
					break;
				default:
					$can_view = apply_filters( 'um_can_view_field_custom', $can_view, $field );
					break;
			}

		}

		if ( ! apply_filters( 'um_can_view_field', $can_view, $field ) ) {
			return __( 'You can not edit this field. The field is hidden.', 'ultimate-member' );
		}


		if ( isset( $field['type'] ) && $field['type'] == 'checkbox' && isset( $field['required'] ) && $field['required'] == 1 && ! isset( $value ) ) {
			$error = sprintf( __( '%s is required.', 'ultimate-member' ), $field['title'] );
		}

		if ( isset( $field['type'] ) && $field['type'] == 'radio' && isset( $field['required'] ) && $field['required'] == 1 && ! isset( $value ) && ! in_array( $key, array( 'role_radio', 'role_select' ) ) ) {
			$error = sprintf( __( '%s is required.', 'ultimate-member'), $field['title'] );
		}

		if ( isset( $field['type'] ) && $field['type'] == 'multiselect' && isset( $field['required'] ) && $field['required'] == 1 && ! isset( $value ) && ! in_array( $key, array( 'role_radio', 'role_select' ) ) ) {
			$error = sprintf( __( '%s is required.', 'ultimate-member' ), $field['title'] );
		}

		if ( ! empty( $field['required'] ) ) {
			if ( ! isset( $value ) || $value == '' || $value == 'empty_file' ) {
				if ( empty( $field['label'] ) ) {
					$error = __( 'This field is required', 'ultimate-member' );
				} else {
					$error = sprintf( __( '%s is required', 'ultimate-member' ), $field['label'] );
				}
			}
		}

		if ( empty( $error ) && isset( $value ) ) {

			if ( isset( $field['max_words'] ) && $field['max_words'] > 0 ) {
				if ( str_word_count( $value, 0, "éèàôù" ) > $field['max_words'] ) {
					$error = sprintf( __( 'You are only allowed to enter a maximum of %s words', 'ultimate-member' ), $field['max_words'] );
				}
			}

			if ( isset( $field['min_chars'] ) && $field['min_chars'] > 0 ) {
				if ( $value && strlen( utf8_decode( $value ) ) < $field['min_chars'] ) {
					if ( empty( $field['label'] ) ) {
						$error = sprintf( __( 'This field must contain at least %s characters', 'ultimate-member' ), $field['min_chars'] );
					} else {
						$error = sprintf( __( 'Your %s must contain at least %s characters', 'ultimate-member' ), $field['label'], $field['min_chars'] );
					}
				}
			}

			if ( isset( $field['max_chars'] ) && $field['max_chars'] > 0 ) {
				if ( $value && strlen( utf8_decode( $value ) ) > $field['max_chars'] ) {
					if ( empty( $field['label'] ) ) {
						$error = sprintf( __( 'This field must contain less than %s characters', 'ultimate-member' ), $field['max_chars'] );
					} else {
						$error = sprintf( __( 'Your %s must contain less than %s characters', 'ultimate-member' ), $field['label'], $field['max_chars'] );
					}
				}
			}

			if ( isset( $field['type'] ) && $field['type'] == 'textarea' && UM()->profile()->get_show_bio_key( [] ) !== $key ) {
				if ( ! isset( $field['html'] ) || $field['html'] == 0 ) {
					if ( wp_strip_all_tags( $value ) !== trim( $value ) ) {
						$error = __( 'You can not use HTML tags here', 'ultimate-member' );
					}
				}
			}

			if ( isset( $field['force_good_pass'] ) && $field['force_good_pass'] == 1 ) {
				if ( ! UM()->validation()->strong_pass( $value ) ) {
					$error = __( 'Your password must contain at least one lowercase letter, one capital letter and one number', 'ultimate-member' );
				}
			}

			if ( isset( $field['min_selections'] ) && $field['min_selections'] > 0 ) {
				if ( ( ! isset( $value ) ) || ( isset( $value ) && is_array( $value ) && count( $value ) < $field['min_selections'] ) ) {
					$error = sprintf( __( 'Please select at least %s choices', 'ultimate-member' ), $field['min_selections'] );
				}
			}

			if ( isset( $field['max_selections'] ) && $field['max_selections'] > 0 ) {
				if ( isset( $value ) && is_array( $value ) && count( $value ) > $field['max_selections'] ) {
					$error = sprintf( __( 'You can only select up to %s choices', 'ultimate-member' ), $field['max_selections'] );
				}
			}

			if ( isset( $field['min'] ) && is_numeric( $value ) ) {
				if ( isset( $value )  && $value < $field['min'] ) {
					$error = sprintf( __( 'Minimum number limit is %s', 'ultimate-member' ), $field['min'] );
				}
			}

			if ( isset( $field['max'] ) && is_numeric( $value )  ) {
				if ( isset( $value ) && $value > $field['max'] ) {
					$error = sprintf( __( 'Maximum number limit is %s', 'ultimate-member' ), $field['max'] );
				}
			}

			if ( empty( $error ) && ! empty( $field['validate'] ) ) {

				switch ( $field['validate'] ) {

					case 'custom':
						$custom = $field['custom_validate'];
						do_action( "um_custom_field_validation_{$custom}", $key, $field, [] );
						break;

					case 'numeric':
						if ( $value && ! is_numeric( $value ) ) {
							$error = __( 'Please enter numbers only in this field', 'ultimate-member' );
						}
						break;

					case 'phone_number':
						if ( ! UM()->validation()->is_phone_number( $value ) ) {
							$error = __( 'Please enter a valid phone number', 'ultimate-member' );
						}
						break;

					case 'youtube_url':
						if ( ! UM()->validation()->is_url( $value, 'youtube.com' ) ) {
							$error = sprintf( __( 'Please enter a valid %s username or profile URL', 'ultimate-member' ), $field['label'] );
						}
						break;

					case 'soundcloud_url':
						if ( ! UM()->validation()->is_url( $value, 'soundcloud.com' ) ) {
							$error = sprintf( __( 'Please enter a valid %s username or profile URL','ultimate-member'), $field['label'] );
						}
						break;

					case 'facebook_url':
						if ( ! UM()->validation()->is_url( $value, 'facebook.com' ) ) {
							$error = sprintf( __( 'Please enter a valid %s username or profile URL', 'ultimate-member' ), $field['label'] );
						}
						break;

					case 'twitter_url':
						if ( ! UM()->validation()->is_url( $value, 'twitter.com' ) ) {
							$error = sprintf( __( 'Please enter a valid %s username or profile URL', 'ultimate-member' ), $field['label'] );
						}
						break;

					case 'instagram_url':
						if ( ! UM()->validation()->is_url( $value, 'instagram.com' ) ) {
							$error = sprintf( __( 'Please enter a valid %s profile URL', 'ultimate-member' ), $field['label'] );
						}
						break;

					case 'google_url':
						if ( ! UM()->validation()->is_url( $value, 'plus.google.com' ) ) {
							$error = sprintf( __( 'Please enter a valid %s username or profile URL', 'ultimate-member' ), $field['label'] );
						}
						break;

					case 'linkedin_url':
						if ( ! UM()->validation()->is_url( $value, 'linkedin.com' ) ) {
							$error = sprintf( __( 'Please enter a valid %s username or profile URL', 'ultimate-member' ), $field['label'] );
						}
						break;

					case 'vk_url':
						if ( ! UM()->validation()->is_url( $value, 'vk.com' ) ) {
							$error = sprintf( __( 'Please enter a valid %s username or profile URL', 'ultimate-member' ), $field['label'] );
						}
						break;

					case 'url':
						if ( ! UM()->validation()->is_url( $value ) ) {
							$error = __( 'Please enter a valid URL', 'ultimate-member' );
						}
						break;

					case 'unique_username':

						if ( $value == '' ) {
							$error = __( 'You must provide a username', 'ultimate-member' );
						} elseif ( $mode == 'register' && username_exists( sanitize_user( $value ) ) ) {
							$error = __( 'Your username is already taken', 'ultimate-member' );
						} elseif ( is_email( $value ) ) {
							$error = __( 'Username cannot be an email', 'ultimate-member' );
						} elseif ( ! UM()->validation()->safe_username( $value ) ) {
							$error = __( 'Your username contains invalid characters', 'ultimate-member' );
						}

						break;

					case 'unique_username_or_email':

						if ( $value == '' ) {
							$error = __( 'You must provide a username', 'ultimate-member' );
						} elseif ( $mode == 'register' && username_exists( sanitize_user( $value ) ) ) {
							$error = __( 'Your username is already taken', 'ultimate-member' );
						} elseif ( $mode == 'register' && email_exists( $value ) ) {
							$error = __( 'This email is already linked to an existing account', 'ultimate-member' );
						} elseif ( ! UM()->validation()->safe_username( $value ) ) {
							$error = __( 'Your username contains invalid characters', 'ultimate-member' );
						}

						break;

					case 'unique_email':

						$value = trim( $value );

						if ( in_array( $key, array( 'user_email' ) ) ) {

							if ( ! isset( $user_id ) ){
								$user_id = um_get_requested_user();
							}

							$email_exists =  email_exists( $value );

							if ( $value == '' && in_array( $key, array( 'user_email' ) ) ) {
								$error = __( 'You must provide your email', 'ultimate-member' );
							} elseif ( in_array( $mode, array( 'register' ) ) && $email_exists  ) {
								$error = __( 'This email is already linked to an existing account', 'ultimate-member' );
							} elseif ( in_array( $mode, array( 'profile' ) ) && $email_exists && $email_exists != $user_id  ) {
								$error = __( 'This email is already linked to an existing account', 'ultimate-member' );
							} elseif ( !is_email( $value ) ) {
								$error = __( 'This is not a valid email', 'ultimate-member');
							} elseif ( ! UM()->validation()->safe_username( $value ) ) {
								$error =  __( 'Your email contains invalid characters', 'ultimate-member' );
							}

						} else {

							if ( $value != '' && ! is_email( $value ) ) {
								$error = __( 'This is not a valid email', 'ultimate-member' );
							} elseif ( $value != '' && email_exists( $value ) ) {
								$error = __( 'This email is already linked to an existing account', 'ultimate-member' );
							} elseif ( $value != '' ) {

								$users = get_users( 'meta_value=' . $value );

								foreach ( $users as $user ) {
									if ( $user->ID != $user_id ) {
										$error = __( 'This email is already linked to an existing account', 'ultimate-member' );
									}
								}
							}
						}

						break;

					case 'is_email':

						$value = trim( $value );
						if ( $value != '' && ! is_email( $value ) ) {
							$error = __( 'This is not a valid email', 'ultimate-member' );
						}

						break;

					case 'unique_value':

						if ( $value != '' ) {

							$args_unique_meta = array(
								'meta_key'      => $key,
								'meta_value'    => $value,
								'compare'       => '=',
								'exclude'       => array( $user_id ),
							);

							$meta_key_exists = get_users( $args_unique_meta );

							if ( $meta_key_exists ) {
								UM()->form()->add_error( $key , __( 'You must provide a unique value', 'ultimate-member' ) );
							}
						}
						break;

					case 'alphabetic':

						if ( $value != '' ) {
							if ( ! preg_match( '/^\p{L}+$/u', str_replace( ' ', '', $value ) ) ) {
								$error = __( 'You must provide alphabetic letters', 'ultimate-member' );
							}
						}

						break;

					case 'lowercase':

						if ( $value != '' ) {
							if ( ! ctype_lower( str_replace(' ', '', $value ) ) ) {
								$error = __( 'You must provide lowercase letters.', 'ultimate-member' );
							}
						}

						break;

				}

			}

		}

		return $error;
	}


	/**
	 *
	 */
	function widgets_init() {
		register_widget( 'um_profile_completeness' );
		register_widget( 'um_profile_progress_bar' );
	}


	/**
	 * @param string $key
	 *
	 * @return string
	 */
	function get_field_title( $key = '' ) {
		$fields_without_metakey = UM()->builtin()->get_fields_without_metakey();
		$fields_without_metakey = apply_filters( 'um_profile_completeness_fields_without_metakey', $fields_without_metakey );

		UM()->builtin()->fields_dropdown = array( 'image', 'file', 'password', 'rating' );
		UM()->builtin()->fields_dropdown = array_merge( UM()->builtin()->fields_dropdown, $fields_without_metakey );

		$custom = UM()->builtin()->custom_fields;
		$predefined = UM()->builtin()->predefined_fields;

		$all = array( 0 => '' );

		if ( is_array( $custom ) ) {
			$all = $all + array_merge( $predefined, $custom );
		} else {
			$all = $all + $predefined;
		}

		$fields = array( 0 => '' ) + $all;

		$fields = apply_filters( 'um_profile_completeness_fields_array_for_titles', $fields, $key );

		if ( ! empty( $fields[ $key ]['label'] ) ) {
			return sprintf( __( '%s', 'um-profile-completeness' ), $fields[ $key ]['label'] );
		}

		if ( ! empty( $fields[ $key ]['title'] ) ) {
			return sprintf( __( '%s', 'um-profile-completeness' ), $fields[ $key ]['title'] );
		}

		return __( 'Custom Field', 'um-profile-completeness' );
	}


	/**
	 * Save field over popup
	 */
	function ajax_save_popup() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['key'] ) || ! isset( $_POST['value'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$user_id = get_current_user_id();
		$key = sanitize_text_field( $_POST['key'] );

		$progress = UM()->Profile_Completeness_API()->get_progress( $user_id );

		if ( isset( $progress['completed'] ) && is_array( $progress['completed'] ) && in_array( $key, $progress['completed'] ) && ! in_array( $key, array( 'profile_photo', 'cover_photo', 'synced_profile_photo' ) ) ) {
			wp_send_json_error();
		}

		if ( is_array( $_POST['value'] ) ) {
			$value = $_POST['value'];
		} else {
			$value = sanitize_text_field( $_POST['value'] );
		}

		$field_type = UM()->fields()->get_field_type( $key );
		$fields_for_explode = apply_filters( 'um_profile_completeness_save_progress_fields_explode', array( 'checkbox', 'radio', 'multiselect' ) );
		if ( in_array( $field_type, $fields_for_explode ) && strstr( $value, ', ' ) ) {
			$value = explode( ', ', $value );
		}

		if ( is_array( $value ) ) {
			$value = array_map( 'sanitize_text_field', $value );
		}

		// Field validation
		$error = $this->validate_field( $key, $value );
		if ( $error ) {
			wp_send_json_error( array( 'error_message' => $error, ) );
		}

		// for fields that use not 1 metakey for saving the data (e.g. User Location field)
		$multisave_meta = apply_filters( 'um_profile_completeness_multisave_meta', false, $key );
		if ( is_array( $value ) && $multisave_meta ) {
			foreach ( $value as $k => $v ) {
				update_user_meta( $user_id, $k, $v );
			}
		} else {
			update_user_meta( $user_id, $key, $value );
		}

		/* Move uploaded file to the user's folder */
		if ( in_array( $field_type, [ 'file', 'image' ] ) ) {
			UM()->uploader()->move_temporary_files( $user_id, [
				"$key" => $value
			] );
		}

		delete_option( "um_cache_userdata_{$user_id}" );

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( $user_id );
		$output['percent'] = $result['progress'];
		$output['raw'] = $result['raw'];
		$output['user_id'] = $user_id;
		$output['redirect'] = apply_filters( 'um_profile_completeness_complete_profile_redirect', '', $user_id, $result );

		wp_send_json_success( $output );
	}


	/**
	 * Edit field over popup
	 *
	 * @throws Exception
	 */
	function ajax_edit_popup() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['key'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$key = sanitize_text_field( $_POST['key'] );

		um_fetch_user( get_current_user_id() );

		$progress = UM()->Profile_Completeness_API()->get_progress( get_current_user_id() );

		if ( isset( $progress['completed'] ) && is_array( $progress['completed'] ) && in_array( $key, $progress['completed'] ) ) {
			wp_send_json_error();
		}

		$result = UM()->Profile_Completeness_API()->shortcode()->profile_progress( get_current_user_id() );

		$data = UM()->builtin()->get_a_field( $key );

		UM()->fields()->disable_tooltips = true;

		$args['profile_completeness'] = true;

		$t_args = compact( 'args', 'data', 'result', 'key' );
		$output = UM()->get_template( 'completeness-popup.php', um_profile_completeness_plugin, $t_args );

		wp_send_json_success( $output );
	}


	/**
	 * Get widget data
	 */
	function ajax_get_widget() {
		UM()->check_ajax_nonce();

		if ( empty( $_POST['user_id'] ) ) {
			wp_send_json_error( __( 'Wrong User ID', 'um-profile-completeness' ) );
		}

		$user_id = absint( $_POST['user_id'] );

		$is_profile = ! empty( $_POST['is_profile'] );

		$result = $this->shortcode()->profile_progress( $user_id );

		if ( is_array( $result['steps'] ) ) {
			$result['steps'] = $this->shortcode()->reorder( $result['steps'] );
		}

		$result['isProfile'] = intval( $is_profile || um_is_core_page( 'user' ) );
		$result['profileEditURL'] = um_edit_profile_url();

		$bullet = 0;
		$result['fields'] = array();
		foreach ( $result['steps'] as $key => $pct ) {
			if ( $key == 'synced_profile_photo' || $key == 'synced_gravatar_hashed_id' ) {
				continue;
			}
			if ( in_array( $key, $result['completed'] ) ) {
				continue;
			}
			if ( apply_filters( 'um_profile_completeness_skip_field', false, $key, $result ) ) {
				continue;
			}
			if ( $key == 'profile_photo' && um_user( 'synced_gravatar_hashed_id' ) && UM()->options()->get( 'use_gravatars' ) ) {
				continue;
			}

			$result['fields'][ $key ] = array(
				'bullet'    => ++$bullet,
				'class'     => in_array( $key, $result['completed'] ) ? 'completed' : '',
				'label'     => UM()->Profile_Completeness_API()->get_field_title( $key ),
				'pct'       => $pct,
			);
		}

		$output = array_intersect_key( $result, array(
			'bar'               => '',
			'fields'            => '',
			'isProfile'         => '',
			'profileEditURL'    => '',
			'progress'          => '',
		) );

		wp_send_json_success( apply_filters( 'um_profile_completeness_ajax_get_widget', $output, $result, $user_id ) );
	}


	/**
	 * Get fields data
	 */
	function ajax_get_fields_data() {
		$data = array();
		global $wp_roles;

		if ( ! empty( $_POST['id'] ) ) {
			$id = sanitize_key( $_POST['id'] );
			$data = get_option( "um_role_{$id}_meta" );

			if ( empty( $data['_um_is_custom'] ) ) {
				$data['name'] = $wp_roles->roles[ $id ]['name'];
			}
		}

		if ( ! empty( $_POST['role'] ) ) {
			$data = $_POST['role'];
		}
		$_um_allocated_progress = 0;
		$fields_data = array();
		foreach ( $data as $k => $v ) {
			if ( strstr( $k, '_um_progress_' ) ) {
				$k = sanitize_text_field( $k ); // Don't use the function sanitize_key(), it changes all letters to lowercase.
				$fields_data[$k] = $v;

				$_um_allocated_progress += $v;
			}
		}

		// get fields
		$fields = UM()->builtin()->all_user_fields( null, true );

		// remove unused fields
		$exclude_fields = [ 'role_select', 'role_radio', 'user_password', 'completeness_bar', 'mycred_badges', 'mycred_progress', 'mycred_rank', '_um_last_login', 'woo_total_spent', 'woo_order_count' ];
		$ignore_fields = [ 'cover_photo', 'profile_photo' ];
		foreach ( $fields as $key => $arr ) {
			if ( in_array( $key, $exclude_fields ) ) {
				unset( $fields[ $key ] );
				continue;
			}
			if ( in_array( $key, $ignore_fields ) ) {
				continue;
			}

			$field = UM()->fields()->get_field( $key );
			if ( isset( $field['account_only'] ) || isset( $field['private_use'] ) ) {
				unset( $fields[ $key ] );
			}
		}

		$fields = apply_filters( 'um_profile_completeness_progress_fields', $fields );

		wp_send_json_success( array( 'fields_data' => $fields_data, 'select_fields' => $fields ) );
	}
}

//create class var
add_action( 'plugins_loaded', 'um_init_profile_completeness', -10, 1 );
function um_init_profile_completeness() {
	if ( function_exists( 'UM' ) ) {
		UM()->set_class( 'Profile_Completeness_API', true );
	}
}