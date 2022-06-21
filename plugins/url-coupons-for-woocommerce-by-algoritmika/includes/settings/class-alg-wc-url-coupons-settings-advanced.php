<?php
/**
 * URL Coupons for WooCommerce - Advanced Section Settings
 *
 * @version 1.6.2
 * @since   1.6.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_URL_Coupons_Settings_Advanced' ) ) :

class Alg_WC_URL_Coupons_Settings_Advanced extends Alg_WC_URL_Coupons_Settings_Section {

	/**
	 * Constructor.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 */
	function __construct() {
		$this->id   = 'advanced';
		$this->desc = __( 'Advanced', 'url-coupons-for-woocommerce-by-algoritmika' );
		parent::__construct();
	}

	/**
	 * get_settings.
	 *
	 * @version 1.6.2
	 * @since   1.6.0
	 *
	 * @todo    [next] (feature) add "Action" option (defaults to `wp_loaded`)
	 * @todo    [next] (desc) `alg_wc_url_coupons_payment_request_product_data`: better naming and/or description
	 * @todo    [next] (desc) Force coupon redirect: better naming and/or description
	 */
	function get_settings() {
		return array(
			array(
				'title'    => __( 'Advanced', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'type'     => 'title',
				'id'       => 'alg_wc_url_coupons_advanced_options',
			),
			array(
				'title'    => __( 'Save on empty cart', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc'     => __( 'Enable', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc_tip' => __( 'Save coupons when cart is emptied. Coupons will be reapplied when some product is added to the cart.', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'id'       => 'alg_wc_url_coupons_save_empty_cart',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Hook priority', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc_tip' => __( 'Priority for the main plugin hook.', 'url-coupons-for-woocommerce-by-algoritmika' ) . ' ' .
					 __( 'Leave empty for the default priority.', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'id'       => 'alg_wc_url_coupons_priority',
				'default'  => '',
				'type'     => 'number',
			),
			array(
				'title'    => __( 'Remove "add to cart" key', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc'     => __( 'Enable', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc_tip' => sprintf( __( 'Will remove %s key on "%s" option.', 'url-coupons-for-woocommerce-by-algoritmika' ),
					'<code>add-to-cart</code>',
					__( 'Redirect URL', 'url-coupons-for-woocommerce-by-algoritmika' ) . ' > ' . __( 'No redirect', 'url-coupons-for-woocommerce-by-algoritmika' ) ),
				'id'       => 'alg_wc_url_coupons_remove_add_to_cart_key',
				'default'  => 'yes',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Force coupon redirect', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc'     => __( 'Enable', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc_tip' => sprintf( __( 'Force coupon redirect after %s action.', 'url-coupons-for-woocommerce-by-algoritmika' ),
					'<code>add-to-cart</code>' ),
				'id'       => 'alg_wc_url_coupons_add_to_cart_action_force_coupon_redirect',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Extra cookie', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc'     => __( 'Enable', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc_tip' => __( 'Enable this if you want to set cookie when URL coupon has been applied.', 'url-coupons-for-woocommerce-by-algoritmika' ) . ' ' .
					sprintf( __( 'Cookie name will be %s.', 'url-coupons-for-woocommerce-by-algoritmika' ), '<code>alg_wc_url_coupons</code>' ),
				'id'       => 'alg_wc_url_coupons_cookie_enabled',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'desc'     => __( 'The time the cookie expires.', 'url-coupons-for-woocommerce-by-algoritmika' ) . ' ' .
					__( 'In seconds.', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'id'       => 'alg_wc_url_coupons_cookie_sec',
				'default'  => 1209600,
				'type'     => 'number',
				'custom_attributes' => array( 'min' => 1 ),
			),
			array(
				'title'    => __( 'WP Rocket', 'url-coupons-for-woocommerce-by-algoritmika' ) . ': ' . __( 'Disable empty cart caching', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc'     => __( 'Disable', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc_tip' => __( 'Check this if you have "WP Rocket" plugin installed, and having issues with cart being empty after you apply URL coupon and add a product.', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'id'       => 'alg_wc_url_coupons_wp_rocket_disable_cache_wc_empty_cart',
				'default'  => 'no',
				'type'     => 'checkbox',
			),
			array(
				'title'    => __( 'Payment request buttons: Apply coupons on single product pages', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'desc'     => __( 'WooCommerce Stripe Gateway', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'id'       => 'alg_wc_url_coupons_payment_request_product_data[wc_stripe]',
				'default'  => 'no',
				'type'     => 'checkbox',
				'checkboxgroup' => 'start',
			),
			array(
				'desc'     => __( 'WooCommerce Payments', 'url-coupons-for-woocommerce-by-algoritmika' ),
				'id'       => 'alg_wc_url_coupons_payment_request_product_data[wcpay]',
				'default'  => 'no',
				'type'     => 'checkbox',
				'checkboxgroup' => 'end',
			),
			array(
				'type'     => 'sectionend',
				'id'       => 'alg_wc_url_coupons_advanced_options',
			),
		);
	}

}

endif;

return new Alg_WC_URL_Coupons_Settings_Advanced();
