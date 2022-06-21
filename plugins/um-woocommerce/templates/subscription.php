<?php
/**
 * Template for the "View subscription" block
 * Used on the "Account" page, "Subscriptions" tab
 * Called from the WooCommerce_Main_API->ajax_get_subscription() method
 *
 * This template can be overridden by copying it to yourtheme/ultimate-member/um-woocommerce/subscription.php
 */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="um_account_subscription">
	<a href="javascript:void(0);" class="button back_to_subscriptions"><?php _e( 'All subscriptions', 'um-woocommerce' ); ?></a>
	<p class="um-subscription-id"><?php esc_html_e( 'Subscription', 'um-woocommerce' ); ?>: #<?php echo esc_html( $subscription->get_id() ); ?></p>

	<?php
	/**
	 * Gets subscription details table template
	 * @param WC_Subscription $subscription A subscription object
	 * @since 2.2.19
	 */
	do_action( 'woocommerce_subscription_details_table', $subscription );

	/**
	 * Gets subscription totals table template
	 * @param WC_Subscription $subscription A subscription object
	 * @since 2.2.19
	 */
	do_action( 'woocommerce_subscription_totals_table', $subscription );

	/** Related Orders */
	do_action( 'woocommerce_subscription_details_after_subscription_table', $subscription );

	/** Billing address */
	wc_get_template( 'order/order-details-customer.php', array( 'order' => $subscription ) );
	?>

</div>