<?php if ( ! defined( 'ABSPATH' ) ) exit;


if ( function_exists( 'wcs_get_subscription' ) ) {
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

			$first_subscription_payment = get_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . 'first', true );
			$is_first_subscription_payment = empty( $first_subscription_payment );

			if ( $is_first_subscription_payment ) {
				update_user_meta( $user_id, '_um_woo_subscription_' . $subscription_id . '_product_' . $product_id . 'first', time() );
			}
		}
	}
}