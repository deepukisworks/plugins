<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( function_exists( 'wcs_get_subscription' ) ) {
	// status => role meta_key
	$arr = array(
		'active'            => '_um_woo_product_activated_role',
		'pending'           => '_um_woo_product_downgrade_pending_role',
		'on-hold'           => '_um_woo_product_downgrade_onhold_role',
		'expired'           => '_um_woo_product_downgrade_expired_role',
		'cancelled'         => '_um_woo_product_downgrade_cancelled_role',
		'pending-cancel'    => '_um_woo_product_downgrade_pendingcancel_role',
	);

	$subscriptions = wcs_get_subscriptions( [ 'subscriptions_per_page' => -1 ] );

	foreach ( $subscriptions as $subscription ) {
		$items = $subscription->get_items();
		if ( empty( $items ) ) {
			continue;
		}

		$subscription_id = $subscription->get_id();
		$user_id = $subscription->get_user_id();

		foreach ( $items as $item_id => $item ) {
			$product_id = $item['product_id'];

			foreach ( $arr as $status => $meta_key ) {
				$usermeta = get_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . '_' . $status . '_role', true );
				if ( ! empty( $usermeta ) ) {
					continue;
				}

				$meta = get_post_meta( $product_id, $arr[ $status ], true );
				if ( empty( $meta ) ) {
					continue;
				}

				update_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . '_' . $status . '_role', $meta );
			}
		}
	}
}