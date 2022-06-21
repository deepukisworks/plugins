<?php
namespace um_ext\um_woocommerce\core;


if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class WooCommerce_Main_API
 * @package um_ext\um_woocommerce\core
 */
class WooCommerce_Main_API {


	/**
	 * WooCommerce_Main_API constructor.
	 */
	function __construct() {

	}


	/**
	 * @param bool $keys_only
	 *
	 * @return array
	 */
	function get_wc_address_fields( $keys_only = false ) {
		$fields = array();

		// billing
		$fields['billing_first_name'] = array(
			'title'     => __( 'WC Billing First name', 'um-woocommerce' ),
			'metakey'   => 'billing_first_name',
			'type'      => 'text',
			'label'     => __( 'WC Billing First name', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['billing_last_name'] = array(
			'title'     => __( 'WC Billing Last name', 'um-woocommerce' ),
			'metakey'   => 'billing_last_name',
			'type'      => 'text',
			'label'     => __( 'WC Billing Last name', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['billing_company'] = array(
			'title'     => __( 'WC Billing Company', 'um-woocommerce' ),
			'metakey'   => 'billing_company',
			'type'      => 'text',
			'label'     => __( 'WC Billing Company', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['billing_address_1'] = array(
			'title'     => __( 'WC Billing Address 1', 'um-woocommerce' ),
			'metakey'   => 'billing_address_1',
			'type'      => 'text',
			'label'     => __( 'WC Billing Address 1', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_address_2'] = array(
			'title'     => __( 'WC Billing Address 2', 'um-woocommerce' ),
			'metakey'   => 'billing_address_2',
			'type'      => 'text',
			'label'     => __( 'WC Billing Address 2', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_city'] = array(
			'title'     => __( 'WC Billing city', 'um-woocommerce' ),
			'metakey'   => 'billing_city',
			'type'      => 'text',
			'label'     => __( 'WC Billing city', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_postcode'] = array(
			'title'     => __( 'WC Billing postcode', 'um-woocommerce' ),
			'metakey'   => 'billing_postcode',
			'type'      => 'text',
			'label'     => __( 'WC Billing postcode', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_country'] = array(
			'title'     => __( 'WC Billing country', 'um-woocommerce' ),
			'metakey'   => 'billing_country',
			'type'      => 'select',
			'label'     => __( 'WC Billing country', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker',
			'options'   => array_flip ( UM()->builtin()->get( 'countries' ) )
		);

		$fields['billing_state'] = array(
			'title'     => __( 'WC Billing state', 'um-woocommerce' ),
			'metakey'   => 'billing_state',
			'type'      => 'text',
			'label'     => __( 'WC Billing state', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['billing_phone'] = array(
			'title'     => __( 'WC Billing phone', 'um-woocommerce' ),
			'metakey'   => 'billing_phone',
			'type'      => 'text',
			'label'     => __( 'WC Billing phone', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-phone'
		);

		$fields['billing_email'] = array(
			'title'     => __( 'WC Billing email', 'um-woocommerce' ),
			'metakey'   => 'billing_email',
			'type'      => 'text',
			'label'     => __( 'WC Billing email', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-envelope'
		);


		// Shipping
		$fields['shipping_first_name'] = array(
			'title'     => __( 'WC Shipping First name', 'um-woocommerce' ),
			'metakey'   => 'shipping_first_name',
			'type'      => 'text',
			'label'     => __( 'WC Shipping First name', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['shipping_last_name'] = array(
			'title'     => __( 'WC Shipping Last name', 'um-woocommerce' ),
			'metakey'   => 'shipping_last_name',
			'type'      => 'text',
			'label'     => __( 'WC Shipping Last name', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['shipping_company'] = array(
			'title'     => __( 'WC Shipping Company', 'um-woocommerce' ),
			'metakey'   => 'shipping_company',
			'type'      => 'text',
			'label'     => __( 'WC Shipping Company', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-user'
		);

		$fields['shipping_address_1'] = array(
			'title'     => __( 'WC Shipping Address 1', 'um-woocommerce' ),
			'metakey'   => 'shipping_address_1',
			'type'      => 'text',
			'label'     => __( 'WC Shipping Address 1', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_address_2'] = array(
			'title'     => __( 'WC Shipping Address 2', 'um-woocommerce' ),
			'metakey'   => 'shipping_address_2',
			'type'      => 'text',
			'label'     => __( 'WC Shipping Address 2', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_city'] = array(
			'title'     => __( 'WC Shipping city', 'um-woocommerce' ),
			'metakey'   => 'shipping_city',
			'type'      => 'text',
			'label'     => __( 'WC Shipping city', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_postcode'] = array(
			'title'     => __( 'WC Shipping postcode', 'um-woocommerce' ),
			'metakey'   => 'shipping_postcode',
			'type'      => 'text',
			'label'     => __( 'WC Shipping postcode', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_country'] = array(
			'title'     => __( 'WC Shipping country', 'um-woocommerce' ),
			'metakey'   => 'shipping_country',
			'type'      => 'select',
			'label'     => __( 'WC Shipping country', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker',
			'options'   => array_flip ( UM()->builtin()->get( 'countries' ) )
		);

		$fields['shipping_state'] = array(
			'title'     => __( 'WC Shipping state', 'um-woocommerce' ),
			'metakey'   => 'shipping_state',
			'type'      => 'text',
			'label'     => __( 'WC Shipping state', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-map-marker'
		);

		$fields['shipping_phone'] = array(
			'title'     => __( 'WC Shipping phone', 'um-woocommerce' ),
			'metakey'   => 'shipping_phone',
			'type'      => 'text',
			'label'     => __( 'WC Shipping phone', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-phone'
		);

		$fields['shipping_email'] = array(
			'title'     => __( 'WC Shipping email', 'um-woocommerce' ),
			'metakey'   => 'shipping_email',
			'type'      => 'text',
			'label'     => __( 'WC Shipping email', 'um-woocommerce' ),
			'public'    => 1,
			'editable'  => 1,
			'icon'      => 'um-faicon-envelope'
		);

		if ( $keys_only ) {
			return array_keys( $fields );
		} else {
			return $fields;
		}
	}


	/**
	 * Check if Woo Subscriptions plugin is active
	 *
	 * @return bool
	 */
	function is_wc_subscription_plugin_active() {
		return function_exists( 'wcs_get_subscription' );
	}


	function maybe_skip_user_by_role( $user_id ) {
		$skip = false;

		$excludes = UM()->options()->get( 'woo_oncomplete_except_roles' );
		$excludes = empty( $excludes ) ? array() : $excludes;

		foreach ( $excludes as $role ) {
			if ( user_can( $user_id, $role ) ) {
				$skip = true;
				break;
			}
		}

		return $skip;
	}


	/**
	 * @param $user_id
	 */
	function maybe_auto_approve( $user_id ) {
		um_fetch_user( $user_id );

		$auto_approve = apply_filters( 'um_woocommerce_auto_approve_on_completed', true );
		if ( $auto_approve ) {
			UM()->user()->approve( false );
		}

		um_reset_user();
	}


	/**
	 * Check single product order need or not need to change user role
	 *
	 * @param int $order_id
	 * @return array|bool
	 *
	 * @throws \Exception
	 */
	function change_role_data_single( $order_id ) {
		$order = new \WC_Order( $order_id );
		$user_id = $order->get_user_id();

		$skip_by_role = $this->maybe_skip_user_by_role( $user_id );
		if ( $skip_by_role ) {
			return false;
		}

		$return = false;
		$data = array();
		//items have more priority
		$items = $order->get_items();
		foreach ( $items as $item_key => $item ) {
			if ( $this->is_wc_subscription_plugin_active() ) {
				$is_subscription = \WC_Subscriptions_Product::is_subscription( $item['product_id'] );

				if ( $is_subscription ) {
					$return = true;
					continue;
				}
			}

			$role_item = get_post_meta( $item['product_id'], '_um_woo_product_role', true );
			if ( ! empty( $role_item ) ) {
				$data['roles'][] = array(
					'role' => $role_item,
					'type' => 'item'
				);
				wc_update_order_item_meta( $item_key, '_um_woo_item_assign_role', $role_item );
			}
		}

		$role_order = UM()->options()->get( 'woo_oncomplete_role' );
		if ( ! empty( $role_order ) ) {
			$data['roles'][] = array(
				'role' => $role_order,
				'type' => 'order'
			);

			$order->update_meta_data( '_um_woo_order_assign_role', $role_order );
			$order->save();
		}

		if ( empty( $data ) ) {
			return $return;
		}

		$data['user_id'] = $user_id;

		return $data;
	}


	/**
	 * Check single product order need or not need to change user role
	 *
	 * @param int $order_id
	 *
	 * @return array|bool
	 */
	function change_role_data_single_refund( $order_id ) {
		$order = new \WC_Order( $order_id );
		$user_id = $order->get_user_id();

		$role = UM()->options()->get( 'woo_onrefund_role' );
		if ( ! empty( $role ) && ! user_can( $user_id, $role ) ) {
			return $role;
		}

		return false;
	}


	/**
	 * Get Order Data via AJAX
	 */
	function ajax_get_order() {
		UM()->check_ajax_nonce();

		if ( ! isset( $_POST['order_id'] ) || ! is_user_logged_in() ) {
			wp_send_json_error();
		}

		$is_customer = get_post_meta( sanitize_key( $_POST['order_id'] ), '_customer_user', true );

		if ( $is_customer != get_current_user_id() ) {
			wp_send_json_error();
		}
		um_fetch_user( get_current_user_id() );

		$order_id = sanitize_key( $_POST['order_id'] );
		$order = wc_get_order( $order_id );
		$notes = $order->get_customer_order_notes();

		$t_args = compact( 'order', 'order_id', 'notes' );
		$output = UM()->get_template( 'order-popup.php', um_woocommerce_plugin, $t_args );

		wp_send_json_success( $output );
	}


	/**
	 * Get Subscription Data via AJAX
	 * @see file /wp-content/plugins/woocommerce-subscriptions-master/woocommerce-subscriptions.php method enqueue_frontend_scripts()
	 */
	function ajax_get_subscription() {
		UM()->check_ajax_nonce();

		$subscription_id = filter_input( INPUT_POST, 'subscription_id', FILTER_SANITIZE_NUMBER_INT );
		$subscription = wcs_get_subscription( sanitize_key( $subscription_id ) );

		if ( $subscription && current_user_can( 'view_order', $subscription->get_id() ) ) {
			set_query_var( 'view-subscription', $subscription_id );

			$t_args = compact( 'subscription', 'subscription_id' );
			$html = UM()->get_template( 'subscription.php', um_woocommerce_plugin, $t_args );


			$script_params = array(
				'ajax_url'							 => esc_url( WC()->ajax_url() ),
				'subscription_id'				 => $subscription->get_id(),
				'add_payment_method_msg' => __( 'To enable automatic renewals for this subscription, you will first need to add a payment method.', 'um-woocommerce' ) . "\n\n" . __( 'Would you like to add a payment method now?', 'um-woocommerce' ),
				'auto_renew_nonce'			 => wp_create_nonce( "toggle-auto-renew-{$subscription->get_id()}" ),
				'add_payment_method_url' => esc_url( $subscription->get_change_payment_method_url() ),
				'has_payment_gateway'		 => $subscription->has_payment_gateway() && wc_get_payment_gateway_by_order( $subscription )->supports( 'subscriptions' ),
			);

			$script_filepath = plugin_dir_path( \WC_Subscriptions::$plugin_file ) . 'assets/js/frontend/view-subscription.js';
			if ( is_file( $script_filepath ) ) {
				$script_file_content = file_get_contents( $script_filepath, true );
				$script_ready = preg_replace( array( '/^[^\{]+\{/', '/\}[^\}]+$/', '/\$\(/', '/\$\./' ), array( '', '', 'jQuery(', 'jQuery.' ), $script_file_content );
			}

			$output = array(
				'content'				 => preg_replace( array( '/\t+/m', '/^\s+/m' ), ' ', $html ),
				'script_ready'	 => preg_replace( array( '/\t+/m', '/^\s+/m' ), ' ', $script_ready ),
				'script_params'	 => apply_filters( 'woocommerce_subscriptions_frontend_view_subscription_script_parameters', $script_params )
			);

			wp_send_json_success( $output );
		}
	}


	/**
	 * Refresh address via AJAX
	 *
	 * @version 2.2.3
	 */
	function ajax_refresh_address() {
		UM()->check_ajax_nonce();

		$country = sanitize_text_field( $_POST['country'] );
		$type = sanitize_key( $_POST['type'] );
		$key = ( $type == 'billing_country' ) ? 'billing_state' : 'shipping_state';

		// set WooCommerce label for the state field
		add_filter( "um_get_field__{$key}", function( $array ) use( $country ) {
			$locale = WC()->countries->get_country_locale();
			if ( isset( $locale[$country]['state']['label'] ) ) {
				$array['label'] = $locale[$country]['state']['label'];
			}
			return $array;
		}, 20 );

		// alphabetical order for states
		add_filter( "um_select_dropdown_dynamic_options_{$key}", function( $options ) {
			asort( $options );
			return $options;
		}, 20 );

		// get fields
		$form_id = absint( $_POST['form_id'] );
		if ( $form_id ) {
			UM()->fields()->set_id = $form_id;
			UM()->fields()->set_mode = get_post_meta( $form_id, '_um_mode', true );
			$fields = UM()->query()->get_attr( 'custom_fields', $form_id ); // get state field from the form
		} else {
			UM()->fields()->set_id = 0;
			UM()->fields()->set_mode = 'profile';
			$fields = UM()->builtin()->get_specific_fields( $key );         // get predefined state field
		}
		if ( empty( $fields[ $key ] ) ) {
			wp_send_json_error( __( 'Can not find state field in this form.', 'um-woocommerce' ) );
		}

		UM()->fields()->editing = true;
		$html = UM()->fields()->edit_field( $key, $fields[ $key ] );
		wp_send_json_success( preg_replace( array( '/\r\n/m', '/\t/m', '/\s+/m' ), ' ', $html ) );
	}


	/**
	 * Check if current user has subscriptions and return subscription IDs
	 * @param  integer			$user_id
	 * @param  string				$product_id
	 * @param  string				$status
	 * @param  array|int		$except_subscriptions
	 * @return array|bool		subscription products ids
	 */
	function user_has_subscription( $user_id = 0, $product_id = '', $status = 'any', $except_subscriptions = array() ) {
		if ( ! function_exists( 'wcs_get_users_subscriptions' ) ) {
			return '';
		}

		$subscriptions = wcs_get_users_subscriptions( $user_id );
		$has_subscription = false;
		$arr_product_ids = array();
		if ( empty( $product_id ) ) { // Any subscription
			if ( ! empty( $status ) && 'any' != $status ) { // We need to check for a specific status
				foreach ( $subscriptions as $subscription ) {
					if ( in_array( $subscription->get_id(), (array) $except_subscriptions ) ) {
						continue;
					}
					if ( $subscription->has_status( $status ) ) {
						$order_items  = $subscription->get_items();
						foreach ( $order_items as $order ) {
							$arr_product_ids[] = wcs_get_canonical_product_id( $order );
						}
					}
				}

				return $arr_product_ids;

			} elseif ( ! empty( $subscriptions ) ) {
				$has_subscription = true;
			}
		} else {
			foreach ( $subscriptions as $subscription ) {
				if ( in_array( $subscription->get_id(), (array) $except_subscriptions ) ) {
					continue;
				}
				if ( $subscription->has_product( $product_id ) && ( empty( $status ) || 'any' == $status || $subscription->has_status( $status ) ) ) {
					$has_subscription = true;
					break;
				}
			}
		}
		return $has_subscription;
	}

}
