<?php
/**
 * URL Coupons for WooCommerce - Core Class
 *
 * @version 1.6.2
 * @since   1.0.0
 *
 * @author  Algoritmika Ltd.
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'Alg_WC_URL_Coupons_Core' ) ) :

class Alg_WC_URL_Coupons_Core {

	/**
	 * Constructor.
	 *
	 * @version 1.6.2
	 * @since   1.0.0
	 *
	 * @todo    [next] (feature) multiple keys, e.g. `apply_coupon,coupon`
	 * @todo    [maybe] (dev) hide coupons: maybe it's safer to hide coupons with CSS instead of using filter
	 */
	function __construct() {
		if ( 'yes' === get_option( 'alg_wc_url_coupons_enabled', 'yes' ) ) {
			// Apply URL coupon
			add_action( 'wp_loaded', array( $this, 'apply_url_coupon' ), ( '' !== ( $priority = get_option( 'alg_wc_url_coupons_priority', '' ) ) ? $priority : PHP_INT_MAX ) );
			add_action( 'alg_wc_url_coupons_before_coupon_applied', array( $this, 'maybe_force_start_session' ), 10 );
			add_action( 'alg_wc_url_coupons_before_coupon_applied', array( $this, 'maybe_set_additional_cookie' ), 11 );
			// Delay coupon
			if ( 'yes' === get_option( 'alg_wc_url_coupons_delay_coupon', 'no' ) ) {
				add_action( 'woocommerce_add_to_cart', array( $this, 'apply_delayed_coupon' ), PHP_INT_MAX, 6 );
			}
			// Delay notice
			if ( 'yes' === get_option( 'alg_wc_url_coupons_delay_notice', 'no' ) ) {
				add_action( 'alg_wc_url_coupons_coupon_applied', array( $this, 'delay_notice' ), 10, 3 );
				add_action( 'wp_head', array( $this, 'display_delayed_notice' ) );
			}
			add_action( 'alg_wc_url_coupons_after_coupon_applied', array( $this, 'redirect' ), PHP_INT_MAX, 3 );
			// Hide coupons
			if ( 'yes' === get_option( 'alg_wc_url_coupons_cart_hide_coupon', 'no' ) ) {
				add_filter( 'woocommerce_coupons_enabled', array( $this, 'hide_coupon_field_on_cart' ), PHP_INT_MAX );
			}
			if ( 'yes' === get_option( 'alg_wc_url_coupons_checkout_hide_coupon', 'no' ) ) {
				add_filter( 'woocommerce_coupons_enabled', array( $this, 'hide_coupon_field_on_checkout' ), PHP_INT_MAX );
			}
			// Force coupon redirect
			if ( 'yes' === get_option( 'alg_wc_url_coupons_add_to_cart_action_force_coupon_redirect', 'no' ) ) {
				add_filter( 'woocommerce_add_to_cart_redirect', array( $this, 'add_to_cart_action_force_coupon_redirect' ), PHP_INT_MAX, 2 );
			}
			// WP Rocket: Disable empty cart caching
			if ( 'yes' === get_option( 'alg_wc_url_coupons_wp_rocket_disable_cache_wc_empty_cart', 'no' ) ) {
				add_filter( 'rocket_cache_wc_empty_cart', '__return_false', PHP_INT_MAX );
			}
			// Save coupons on empty cart
			if ( 'yes' === get_option( 'alg_wc_url_coupons_save_empty_cart', 'no' ) ) {
				add_action( 'woocommerce_before_cart_emptied', array( $this, 'save_empty_cart_coupons' ) );
				add_action( 'woocommerce_add_to_cart',         array( $this, 'apply_empty_cart_coupons' ), PHP_INT_MAX, 6 );
			}
			// Payment request product data: WooCommerce Stripe Gateway, WooCommerce Payments
			$payment_request_product_data_options = get_option( 'alg_wc_url_coupons_payment_request_product_data', array() );
			foreach ( array( 'wc_stripe', 'wcpay' ) as $gateway ) {
				if ( isset( $payment_request_product_data_options[ $gateway ] ) && 'yes' === $payment_request_product_data_options[ $gateway ] ) {
					add_filter( $gateway . '_payment_request_product_data', array( $this, 'payment_request_product_data' ), PHP_INT_MAX, 2 );
				}
			}
			// Shortcodes
			add_shortcode( 'alg_wc_url_coupons_translate', array( $this, 'translate_shortcode' ) );
		}
	}

	/**
	 * payment_request_product_data.
	 *
	 * @version 1.6.2
	 * @since   1.6.2
	 *
	 * @see     https://github.com/woocommerce/woocommerce-gateway-stripe/blob/5.8.1/includes/payment-methods/class-wc-stripe-payment-request.php#L451
	 * @see     https://github.com/Automattic/woocommerce-payments/blob/3.4.0/includes/class-wc-payments-payment-request-button-handler.php#L289
	 */
	function payment_request_product_data( $data, $product ) {
		if ( ! empty( $data['total']['amount'] ) && $data['total']['amount'] > 0 ) {
			$applied_coupons = WC()->cart->get_applied_coupons();
			if ( ! empty( $applied_coupons ) ) {
				$total_discounts = 0;
				foreach ( $applied_coupons as $coupon_code ) {
					$coupon = new WC_Coupon( $coupon_code );
					if ( $coupon && $coupon->is_valid_for_product( $product ) ) {
						$total_discounts += $coupon->get_discount_amount( $product->get_price() * 100 );
					}
				}
				if ( 0 != $total_discounts ) {
					$data['total']['amount'] -= $total_discounts;
					$data['displayItems'][] = array(
						'label'  => esc_html( ( 'wc_stripe_payment_request_product_data' === current_filter() ?
							__( 'Discount', 'woocommerce-gateway-stripe' ) :
							__( 'Discount', 'woocommerce-payments' ) ) ),
						'amount' => $total_discounts,
					);
				}
			}
		}
		return $data;
	}

	/**
	 * save_empty_cart_coupons.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 *
	 * @todo    [next] (dev) merge this with `WC()->session->set( 'alg_wc_url_coupons', ... )`?
	 */
	function save_empty_cart_coupons( $clear_persistent_cart ) {
		$coupons = WC()->session->get( 'alg_wc_url_coupons_empty_cart', array() );
		WC()->session->set( 'alg_wc_url_coupons_empty_cart', array_unique( array_merge( $coupons, WC()->cart->applied_coupons ) ) );
	}

	/**
	 * apply_empty_cart_coupons.
	 *
	 * @version 1.6.1
	 * @since   1.6.1
	 *
	 * @todo    [next] (dev) use `$this->apply_coupon()`?
	 * @todo    [next] (feature) apply only applicable coupons, e.g. `fixed_product`, etc.
	 */
	function apply_empty_cart_coupons( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$coupons = WC()->session->get( 'alg_wc_url_coupons_empty_cart', array() );
		if ( ! empty( $coupons ) ) {
			WC()->session->set( 'alg_wc_url_coupons_empty_cart', null );
			foreach ( $coupons as $coupon ) {
				WC()->cart->add_discount( $coupon );
			}
		}
	}

	/**
	 * translate_shortcode.
	 *
	 * @version 1.5.4
	 * @since   1.5.4
	 */
	function translate_shortcode( $atts, $content = '' ) {
		// E.g.: `[alg_wc_url_coupons_translate lang="EN,DE" lang_text="Text for EN & DE" not_lang_text="Text for other languages"]`
		if ( isset( $atts['lang_text'] ) && isset( $atts['not_lang_text'] ) && ! empty( $atts['lang'] ) ) {
			return ( ! defined( 'ICL_LANGUAGE_CODE' ) || ! in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ?
				$atts['not_lang_text'] : $atts['lang_text'];
		}
		// E.g.: `[alg_wc_url_coupons_translate lang="EN,DE"]Text for EN & DE[/alg_wc_url_coupons_translate][alg_wc_url_coupons_translate not_lang="EN,DE"]Text for other languages[/alg_wc_url_coupons_translate]`
		return (
			( ! empty( $atts['lang'] )     && ( ! defined( 'ICL_LANGUAGE_CODE' ) || ! in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['lang'] ) ) ) ) ) ) ||
			( ! empty( $atts['not_lang'] ) &&     defined( 'ICL_LANGUAGE_CODE' ) &&   in_array( strtolower( ICL_LANGUAGE_CODE ), array_map( 'trim', explode( ',', strtolower( $atts['not_lang'] ) ) ) ) )
		) ? '' : $content;
	}

	/**
	 * apply_delayed_coupon.
	 *
	 * @version 1.5.3
	 * @since   1.5.0
	 *
	 * @todo    [now] (dev) `$skip_coupons`: `fixed_cart` type?
	 * @todo    [next] (dev) `$skip_coupons`: `percent` type?
	 * @todo    [maybe] (dev) `$skip_coupons`: `$coupon->is_valid_for_product()`: 2nd param?
	 * @todo    [now] (dev) `$skip_coupons`: `$coupon->is_valid_for_cart()`?
	 */
	function apply_delayed_coupon( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		$coupons = WC()->session->get( 'alg_wc_url_coupons', array() );
		if ( ! empty( $coupons ) ) {
			WC()->session->set( 'alg_wc_url_coupons', null );
			$skip_coupons = array();
			$key          = get_option( 'alg_wc_url_coupons_key', 'alg_apply_coupon' );
			foreach ( $coupons as $coupon_code ) {
				if (
					'yes' === get_option( 'alg_wc_url_coupons_delay_coupon_check_product', 'no' ) &&
					( $product = wc_get_product( $variation_id ? $variation_id : $product_id ) ) &&
					( $coupon_id = wc_get_coupon_id_by_code( $coupon_code ) ) && ( $coupon = new WC_Coupon( $coupon_id ) ) &&
					$coupon->is_type( 'fixed_product' ) && ! $coupon->is_valid_for_product( $product )
				) {
					$skip_coupons[] = $coupon_code;
				} else {
					$result = $this->apply_coupon( $coupon_code, $key );
				}
			}
			if ( ! empty( $skip_coupons ) ) {
				WC()->session->set( 'alg_wc_url_coupons', $skip_coupons );
			}
		}
	}

	/**
	 * add_to_cart_action_force_coupon_redirect.
	 *
	 * @version 1.3.2
	 * @since   1.3.2
	 */
	function add_to_cart_action_force_coupon_redirect( $url, $adding_to_cart ) {
		$key = get_option( 'alg_wc_url_coupons_key', 'alg_apply_coupon' );
		return ( isset( $_GET[ $key ] ) ? remove_query_arg( 'add-to-cart' ) : $url );
	}

	/**
	 * hide_coupon_field_on_checkout.
	 *
	 * @version 1.2.1
	 * @since   1.2.1
	 */
	function hide_coupon_field_on_checkout( $enabled ) {
		return ( is_checkout() ? false : $enabled );
	}

	/**
	 * hide_coupon_field_on_cart.
	 *
	 * @version 1.0.0
	 * @since   1.0.0
	 */
	function hide_coupon_field_on_cart( $enabled ) {
		return ( is_cart() ? false : $enabled );
	}

	/**
	 * maybe_force_start_session.
	 *
	 * @version 1.6.0
	 * @since   1.3.0
	 */
	function maybe_force_start_session( $coupon_code ) {
		if ( 'yes' === get_option( 'alg_wc_url_coupons_force_start_session', 'yes' ) && WC()->session && ! WC()->session->has_session() ) {
			WC()->session->set_customer_session_cookie( true );
		}
	}

	/**
	 * maybe_set_additional_cookie.
	 *
	 * @version 1.3.0
	 * @since   1.3.0
	 */
	function maybe_set_additional_cookie( $coupon_code ) {
		if ( 'yes' === get_option( 'alg_wc_url_coupons_cookie_enabled', 'no' ) ) {
			setcookie( 'alg_wc_url_coupons', $coupon_code, ( time() + get_option( 'alg_wc_url_coupons_cookie_sec', 1209600 ) ), '/', $_SERVER['SERVER_NAME'], false );
		}
	}

	/**
	 * delay_notice.
	 *
	 * @version 1.4.0
	 * @since   1.3.0
	 *
	 * @todo    [maybe] (dev) still delay notice on `! $result`?
	 */
	function delay_notice( $coupon_code, $key, $result ) {
		if ( ! $result ) {
			return;
		}
		if ( WC()->cart->is_empty() ) {
			$all_notices = WC()->session->get( 'wc_notices', array() );
			wc_clear_notices();
			WC()->session->set( 'alg_wc_url_coupons_notices', $all_notices );
		}
	}

	/**
	 * display_delayed_notice.
	 *
	 * @version 1.5.5
	 * @since   1.2.5
	 */
	function display_delayed_notice() {
		if ( function_exists( 'WC' ) && isset( WC()->cart ) && ! WC()->cart->is_empty() && ( $notices = WC()->session->get( 'alg_wc_url_coupons_notices', array() ) ) && ! empty( $notices ) ) {
			WC()->session->set( 'alg_wc_url_coupons_notices', null );
			WC()->session->set( 'wc_notices', $notices );
		}
	}

	/**
	 * redirect.
	 *
	 * @version 1.5.2
	 * @since   1.3.0
	 *
	 * @todo    [now] [!] (dev) different/same redirect on `! $result` (e.g. when coupon is applied twice)?
	 */
	function redirect( $coupon_code, $key, $result ) {
		if ( ! $result ) {
			return;
		}
		$keys_to_remove = array( $key );
		if ( 'yes' === get_option( 'alg_wc_url_coupons_remove_add_to_cart_key', 'yes' ) ) {
			$keys_to_remove[] = 'add-to-cart';
		}
		$redirect_url = apply_filters( 'alg_wc_url_coupons_redirect_url', remove_query_arg( $keys_to_remove ), $coupon_code, $key );
		wp_safe_redirect( $redirect_url );
		exit;
	}

	/**
	 * do_delay_coupon.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 *
	 * @todo    [now] (dev) `fixed_cart`
	 * @todo    [maybe] (dev) `$coupon->is_valid_for_product()`: 2nd param?
	 */
	function do_delay_coupon( $coupon_code ) {
		if ( 'yes' === get_option( 'alg_wc_url_coupons_delay_coupon', 'no' ) ) {
			if ( 'yes' !== ( $delay_on_non_empty_cart = get_option( 'alg_wc_url_coupons_delay_coupon_non_empty_cart', 'yes' ) ) && ! WC()->cart->is_empty() ) {
				if ( 'no' === $delay_on_non_empty_cart ) {
					return false;
				} elseif ( 'check_product' === $delay_on_non_empty_cart ) {
					if ( ( $coupon_id = wc_get_coupon_id_by_code( $coupon_code ) ) && ( $coupon = new WC_Coupon( $coupon_id ) ) && $coupon->is_type( 'fixed_product' ) ) {
						foreach ( WC()->cart->get_cart() as $item ) {
							if ( ( $product = wc_get_product( $item['variation_id'] ? $item['variation_id'] : $item['product_id'] ) ) && $coupon->is_valid_for_product( $product ) ) {
								return false;
							}
						}
					}
				}
			}
			return true;
		}
		return false;
	}

	/**
	 * delay_coupon.
	 *
	 * @version 1.6.0
	 * @since   1.6.0
	 *
	 * @todo    [next] (dev) `alg_wc_url_coupons_delay_coupon`: require force session start?
	 * @todo    [maybe] (feature) `alg_wc_url_coupons_delay_coupon`: notices: customizable notice types?
	 */
	function delay_coupon( $coupon_code, $key ) {
		$result  = false;
		$notices = get_option( 'alg_wc_url_coupons_delay_coupon_notice', array() );
		$notices = array_map( 'do_shortcode', $notices );
		if ( ! WC()->cart->has_discount( $coupon_code ) ) {
			if ( wc_get_coupon_id_by_code( $coupon_code ) ) {
				$coupons = WC()->session->get( 'alg_wc_url_coupons', array() );
				$coupons[] = $coupon_code;
				WC()->session->set( 'alg_wc_url_coupons', array_unique( $coupons ) );
				$notice = ( isset( $notices['success'] ) ? $notices['success'] : __( 'Coupon code applied successfully.', 'url-coupons-for-woocommerce-by-algoritmika' ) );
				if ( '' != $notice ) {
					wc_add_notice( str_replace( '%coupon_code%', $coupon_code, $notice ) );
				}
				$result = true;
				do_action( 'alg_wc_url_coupons_coupon_delayed', $coupon_code, $key, $result );
			} else {
				$notice = ( isset( $notices['error_not_found'] ) ? $notices['error_not_found'] : __( 'Coupon "%coupon_code%" does not exist!', 'url-coupons-for-woocommerce-by-algoritmika' ) );
				if ( '' != $notice ) {
					wc_add_notice( str_replace( '%coupon_code%', $coupon_code, $notice ), 'error' );
				}
			}
		} else {
			$notice = ( isset( $notices['error_applied'] ) ? $notices['error_applied'] : __( 'Coupon code already applied!', 'url-coupons-for-woocommerce-by-algoritmika' ) );
			if ( '' != $notice ) {
				wc_add_notice( str_replace( '%coupon_code%', $coupon_code, $notice ), 'error' );
			}
		}
		do_action( 'alg_wc_url_coupons_after_coupon_delayed', $coupon_code, $key, $result );
		return $result;
	}

	/**
	 * apply_url_coupon.
	 *
	 * e.g. http://example.com/?alg_apply_coupon=test
	 *
	 * @version 1.6.0
	 * @since   1.0.0
	 *
	 * @todo    [maybe] (feature) options to add products to cart with query arg?
	 * @todo    [maybe] (dev) `if ( ! WC()->cart->has_discount( $coupon_code ) ) {}`?
	 */
	function apply_url_coupon() {
		$key = get_option( 'alg_wc_url_coupons_key', 'alg_apply_coupon' );
		if ( isset( $_GET[ $key ] ) && '' !== $_GET[ $key ] && function_exists( 'WC' ) ) {
			$coupon_code = sanitize_text_field( $_GET[ $key ] );
			do_action( 'alg_wc_url_coupons_before_coupon_applied', $coupon_code, $key );
			if ( $this->do_delay_coupon( $coupon_code ) ) {
				// Delay coupon
				$result = $this->delay_coupon( $coupon_code, $key );
			} else {
				// Apply coupon
				$result = $this->apply_coupon( $coupon_code, $key );
			}
			do_action( 'alg_wc_url_coupons_after_coupon_applied', $coupon_code, $key, $result );
		}
	}

	/**
	 * apply_coupon.
	 *
	 * @version 1.5.2
	 * @since   1.5.2
	 *
	 * @todo    [next] (dev) use `WC()->cart->apply_coupon()` instead of `WC()->cart->add_discount()`
	 */
	function apply_coupon( $coupon_code, $key ) {
		$result = WC()->cart->add_discount( $coupon_code );
		do_action( 'alg_wc_url_coupons_coupon_applied', $coupon_code, $key, $result );
		return $result;
	}

}

endif;

return new Alg_WC_URL_Coupons_Core();
