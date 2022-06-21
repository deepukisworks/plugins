<?php if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * When any product is on-hold purchase
 *
 * @param int $order_id
 *
 * @throws Exception
 */
function um_woocommerce_sync_role_onhold( $order_id ) {
	$change = UM()->options()->get( 'woo_onhold_change_roles' );
	if ( ! $change ) {
		return;
	}

	$change_roles = UM()->WooCommerce_API()->api()->change_role_data_single( $order_id );
	if ( false === $change_roles ) {
		return;
	}

	$order = new WC_Order( $order_id );
	$user_id = $order->get_user_id();
	$userdata = get_userdata( $user_id );

	if ( empty( $userdata ) || is_wp_error( $userdata ) ) {
		return;
	}

	$old_roles = $userdata->roles;
	update_user_meta( $user_id, 'um_woo_change_role_' . $order_id, $old_roles );

	// Disable social activity 'joined site' post
	remove_action( 'um_after_user_is_approved', 'um_activity_new_user', 90 );
	// Disable welcome email
	add_filter( 'um_get_option_filter__welcome_email_on', '__return_false', 999 );

	$remove_previous = UM()->options()->get( 'woo_remove_roles' );
	if ( $remove_previous ) {
		foreach ( $old_roles as $_role ) {
			$userdata->remove_role( $_role );
		}
	}

	$hook_roles = array();
	if ( isset( $change_roles['roles'] ) ) {
		foreach ( $change_roles['roles'] as $change_role ) {
			$role = $change_role['role'];
			$userdata->add_role( $role );
			$hook_roles[] = $role;
		}
	}

	do_action( 'um_after_member_role_upgrade', $hook_roles, $old_roles, $user_id );

	UM()->WooCommerce_API()->api()->maybe_auto_approve( $user_id );

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );
}
add_action( 'woocommerce_order_status_on-hold', 'um_woocommerce_sync_role_onhold' );


/**
 * @param int $order_id
 */
function um_woocommerce_sync_role_failed( $order_id ) {
	$order = new WC_Order( $order_id );
	$user_id = $order->get_user_id();

	$previous_roles = get_user_meta( $user_id, 'um_woo_change_role_' . $order_id );
	delete_user_meta( $user_id, 'um_woo_change_role_' . $order_id );
	if ( empty( $previous_roles ) ) {
		return;
	}

	// Disable social activity 'joined site' post
	remove_action( 'um_after_user_is_approved', 'um_activity_new_user', 90 );
	// Disable welcome email
	add_filter( 'um_get_option_filter__welcome_email_on', '__return_false', 999 );

	$userdata = get_userdata( $user_id );

	if ( empty( $userdata ) || is_wp_error( $userdata ) ) {
		return;
	}

	$old_roles = $userdata->roles;
	foreach ( $old_roles as $_role ) {
		UM()->roles()->remove_role( $user_id, $_role );
	}

	foreach ( $previous_roles as $_role ) {
		UM()->roles()->set_role_wp( $user_id, $_role );
	}

	do_action( 'um_after_member_role_upgrade', $previous_roles, $old_roles, $user_id );

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );
}
add_action( 'woocommerce_order_status_failed', 'um_woocommerce_sync_role_failed' );
add_action( 'woocommerce_order_status_canceled', 'um_woocommerce_sync_role_failed' );


/**
 * When any product is bought
 *
 * @param int $order_id
 *
 * @throws Exception
 */
function um_woocommerce_sync_role_completed( $order_id ) {
	$order = new WC_Order( $order_id );
	$user_id = $order->get_user_id();
	$user = get_userdata( $user_id );

	if ( empty( $user ) || is_wp_error( $user ) ) {
		return;
	}

	$change = UM()->options()->get( 'woo_onhold_change_roles' );
	$previous_role = get_user_meta( $user_id, 'um_woo_change_role_' . $order_id );
	if ( ! empty( $previous_role ) && $change ) {
		delete_user_meta( $user_id, 'um_woo_change_role_' . $order_id );
		return;
	}

	$status = $order->get_status();
	if ( $status == 'processing' ) {
		$order->update_meta_data( '_um_woo_order_was_processing', true );
		$order->save();
	} elseif ( $status == 'completed' ) {
		$was_processing = $order->get_meta( '_um_woo_order_was_processing' );
		if ( ! empty( $was_processing ) ) {
			$order->delete_meta_data( '_um_woo_order_was_processing' );
			$order->save();
			return;
		}
	}

	if ( ( UM()->WooCommerce_API()->api()->is_wc_subscription_plugin_active() && ! wcs_order_contains_renewal( $order_id ) ) ||
	     ! UM()->WooCommerce_API()->api()->is_wc_subscription_plugin_active() ) {

		$change_roles = UM()->WooCommerce_API()->api()->change_role_data_single( $order_id );
		if ( false === $change_roles ) {
			return;
		}

		// Disable social activity 'joined site' post
		remove_action( 'um_after_user_is_approved', 'um_activity_new_user', 90 );
		// Disable welcome email
		add_filter( 'um_get_option_filter__welcome_email_on', '__return_false', 999 );

		$userdata = get_userdata( $user_id );
		$old_roles = $userdata->roles;

		$remove_previous = UM()->options()->get( 'woo_remove_roles' );
		if ( $remove_previous ) {
			foreach ( $old_roles as $_role ) {
				$user->remove_role( $_role );
			}
		}

		$hook_roles = array();
		if ( isset( $change_roles['roles'] ) ) {
			foreach ( $change_roles['roles'] as $change_role ) {
				$role = $change_role['role'];
				$user->add_role( $role );
				$hook_roles[] = $role;
			}
		}

		do_action( 'um_after_member_role_upgrade', $hook_roles, $old_roles, $user_id );

		UM()->WooCommerce_API()->api()->maybe_auto_approve( $user_id );

		// forcefully flush the cache
		UM()->user()->remove_cache( $user_id );

	}
}
add_action( 'woocommerce_order_status_completed', 'um_woocommerce_sync_role_completed' );
add_action( 'woocommerce_order_status_processing', 'um_woocommerce_sync_role_completed' );


/**
 * @param int $order_id
 */
function um_woocommerce_sync_role_onrefund( $order_id ) {
	$order = new WC_Order( $order_id );
	$user_id = $order->get_user_id();

	$skip_by_role = UM()->WooCommerce_API()->api()->maybe_skip_user_by_role( $user_id );
	if ( $skip_by_role ) {
		return;
	}

	$userdata = get_userdata( $user_id );

	if ( empty( $userdata ) || is_wp_error( $userdata ) ) {
		return;
	}

	$old_roles = $userdata->roles;

	$role_assigned = $order->get_meta( '_um_woo_order_assign_role' );
	if ( ! empty( $role_assigned ) ) {
		UM()->roles()->remove_role( $user_id, $role_assigned );
	}

	$change_role = UM()->WooCommerce_API()->api()->change_role_data_single_refund( $order_id );
	if ( ! empty( $change_role ) ) {
		UM()->roles()->set_role_wp( $user_id, $change_role );
	}

	if ( UM()->options()->get( 'woo_remove_roles' ) ) {
		foreach ( $old_roles as $_role ) {
			if ( $change_role == $_role ) {
				continue;
			}

			UM()->roles()->remove_role( $user_id, $_role );
		}
	}

	$userdata = get_userdata( $user_id );
	$new_roles = $userdata->roles;
	do_action( 'um_after_member_role_upgrade', $new_roles, $old_roles, $user_id );

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );
}
add_action( 'woocommerce_order_status_refunded', 'um_woocommerce_sync_role_onrefund' );


/**
 * @param int $order_id
 * @param int $refund_id
 *
 * @throws Exception
 */
function um_woocommerce_sync_role_onrefund_items( $order_id, $refund_id ) {
	$order = new WC_Order( $order_id );
	$user_id = $order->get_user_id();

	$skip_by_role = UM()->WooCommerce_API()->api()->maybe_skip_user_by_role( $user_id );
	if ( $skip_by_role ) {
		return;
	}

	$userdata = get_userdata( $user_id );
	if ( empty( $userdata ) || is_wp_error( $userdata ) ) {
		return;
	}

	$old_roles = $userdata->roles;

	$removed = false;

	$items = $order->get_items();
	foreach ( $items as $item_key => $item ) {
		if ( UM()->WooCommerce_API()->api()->is_wc_subscription_plugin_active() ) {
			$is_subscription = \WC_Subscriptions_Product::is_subscription( $item['product_id'] );

			if ( $is_subscription ) {
				continue;
			}
		}

		$order_qty    = $item->get_quantity();
		$refunded_qty = $order->get_qty_refunded_for_item( $item_key );
		if ( abs( $refunded_qty ) === $order_qty ) {
			$remove_role = wc_get_order_item_meta( $item_key, '_um_woo_item_assign_role', true );

			if ( ! empty( $remove_role ) ) {
				UM()->roles()->remove_role( $user_id, $remove_role );
				$removed = true;
			}
		}
	}

	if ( $removed ) {
		$userdata = get_userdata( $user_id );
		$new_roles = $userdata->roles;
		do_action( 'um_after_member_role_upgrade', $new_roles, $old_roles, $user_id );
	}

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );
}
add_action( 'woocommerce_order_refunded', 'um_woocommerce_sync_role_onrefund_items', 10, 2 );


// WooCommerce Subscriptions integration below


/**
 * Subscription change status
 *
 * @param $subscription_id
 * @param $old_status
 * @param $new_status
 */
function um_woocommerce_subscription_status_changed( $subscription_id, $old_status, $new_status ) {
	if ( ! UM()->WooCommerce_API()->api()->is_wc_subscription_plugin_active() ) {
		return;
	}

	$subscription = wcs_get_subscription( $subscription_id );
	$user_id = $subscription->get_user_id();

	$skip_by_role = UM()->WooCommerce_API()->api()->maybe_skip_user_by_role( $user_id );
	if ( $skip_by_role ) {
		return;
	}

	// status => role meta_key
	$arr = array(
		'active'            => '_um_woo_product_activated_role',
		'pending'           => '_um_woo_product_downgrade_pending_role',
		'on-hold'           => '_um_woo_product_downgrade_onhold_role',
		'expired'           => '_um_woo_product_downgrade_expired_role',
		'cancelled'         => '_um_woo_product_downgrade_cancelled_role',
		'pending-cancel'    => '_um_woo_product_downgrade_pendingcancel_role',
	);

	// Disable social activity 'joined site' post
	remove_action( 'um_after_user_is_approved', 'um_activity_new_user', 90 );
	// Disable welcome email
	add_filter( 'um_get_option_filter__welcome_email_on', '__return_false', 999 );

	um_fetch_user( $user_id );
	$userdata = $subscription->get_user();

	if ( empty( $userdata ) || is_wp_error( $userdata ) ) {
		return;
	}

	$old_roles = $userdata->roles;

	// Check all products in the subscription and change a role if necessary
	$items = $subscription->get_items();

	$changed = false;
	foreach ( $items as $item_id => $item ) {
		$product_id = $item['product_id'];

		// empty meta means the first subscription payment
		$first_subscription_payment = get_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . 'first', true );
		$is_first_subscription_payment = empty( $first_subscription_payment );

		if ( $old_status == 'pending' && $is_first_subscription_payment ) {
			foreach ( $arr as $status => $meta_key ) {
				$meta = get_post_meta( $product_id, $meta_key, true );
				if ( empty( $meta ) ) {
					continue;
				}

				update_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . '_' . $status . '_role', $meta );
			}

			update_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . 'first', time() );
		}

		$new_single_role = $old_single_role = '';

		if ( isset( $arr[ $new_status ] ) ) {
			$new_single_role = get_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . '_' . $new_status . '_role', true );
		}
		if ( isset( $arr[ $old_status ] ) ) {
			$old_single_role = get_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . '_' . $old_status . '_role', true );
		}

		// skip continue if there is the first change the pending status to some other with the same role.
		// there isn't the change status trigger for the insert subscription with pending status
		if ( $old_status != 'pending' && $new_single_role === $old_single_role ) {
			continue;
		}

		// remove role for old status
		if ( ! empty( $old_single_role ) && user_can( $user_id, $old_single_role ) ) {
			$userdata->remove_role( $old_single_role );
			$changed = true;
		}

		// set role for new status
		if ( ! empty( $new_single_role ) && ! user_can( $user_id, $new_single_role ) ) {
			$userdata->add_role( $new_single_role );
			$changed = true;
		}
	}

	// Possible statuses: pending, active, on-hold, pending-cancel, cancelled, switched or expired
	switch ( $new_status ) {
		case 'active':

			UM()->WooCommerce_API()->api()->maybe_auto_approve( $user_id );
			break;

		case 'on-hold':
		case 'expired':
		case 'pending':
		case 'pending-cancel':
		case 'cancelled':

			do_action( 'um_wooocommerce_after_subscription_status_changed_to_' . $new_status, $new_status, $old_status, $user_id );
			break;
	}

	if ( $changed ) {
		$userdata = get_userdata( $user_id );
		$new_roles = $userdata->roles;
		do_action( 'um_after_member_role_upgrade', $new_roles, $old_roles, $user_id );
	}

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );
}
add_action( 'woocommerce_subscription_status_changed', 'um_woocommerce_subscription_status_changed', 10, 3 );


/**
 * Disable 'WooCommerce Subscriptions' role switcher
 * @see /wp-content/plugins/woocommerce-subscriptions/includes/wcs-user-functions.php
 * @see WC_Subscription::update_status()
 */
add_filter( 'woocommerce_subscriptions_update_users_role', function( $enabled, $user, $role_new ) {
	if ( UM()->options()->get( 'woo_disable_subscriptions_switcher' ) ) {
		$enabled = false;
	}

	return $enabled;
}, 20, 3 );


/**
 * @param \WC_Order $order
 */
function um_woocommerce_subscription_switch_completed( $order ) {
	$user_id = $order->get_user_id();
	$user = get_userdata( $user_id );

	if ( empty( $user ) || is_wp_error( $user ) ) {
		return;
	}

	$skip_by_role = UM()->WooCommerce_API()->api()->maybe_skip_user_by_role( $user_id );
	if ( $skip_by_role ) {
		return;
	}

	$old_roles = $user->roles;

	// status => role meta_key
	$arr = array(
		'active'            => '_um_woo_product_activated_role',
		'pending'           => '_um_woo_product_downgrade_pending_role',
		'on-hold'           => '_um_woo_product_downgrade_onhold_role',
		'expired'           => '_um_woo_product_downgrade_expired_role',
		'cancelled'         => '_um_woo_product_downgrade_cancelled_role',
		'pending-cancel'    => '_um_woo_product_downgrade_pendingcancel_role',
	);

	// Disable social activity 'joined site' post
	remove_action( 'um_after_user_is_approved', 'um_activity_new_user', 90 );
	// Disable welcome email
	add_filter( 'um_get_option_filter__welcome_email_on', '__return_false', 999 );

	$changed = false;

	$subscriptions = wcs_get_subscriptions_for_order( $order );
	if ( ! empty( $subscriptions ) ) {
		foreach ( $subscriptions as $subscription_id => $subscription ) {
			$items = $subscription->get_items();

			foreach ( $items as $item_id => $item ) {
				$product_id = $item['product_id'];

				foreach ( $arr as $status => $meta_key ) {
					$meta = get_post_meta( $product_id, $arr[ $status ], true );
					if ( empty( $meta ) ) {
						continue;
					}

					update_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . '_' . $status . '_role', $meta );
				}

				$new_single_role = '';

				if ( isset( $arr['active'] ) ) {
					$new_single_role = get_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . '_active_role', true );
				}

				// set role for new status
				if ( ! empty( $new_single_role ) && ! user_can( $user_id, $new_single_role ) ) {
					$user->add_role( $new_single_role );
					$changed = true;
				}
			}
		}
	}

	if ( $changed ) {
		$user = get_userdata( $user_id );
		$new_roles = $user->roles;
		do_action( 'um_after_member_role_upgrade', $new_roles, $old_roles, $user_id );
	}

	// forcefully flush the cache
	UM()->user()->remove_cache( $user_id );
}
add_action( 'woocommerce_subscriptions_switch_completed', 'um_woocommerce_subscription_switch_completed', 10, 1 );