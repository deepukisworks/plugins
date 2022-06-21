jQuery(document).ready(function (e) {

	/* Reviews */
	if (jQuery('.um-woo-review-avg').length) {
		jQuery('.um-woo-review-avg').um_raty({
			half: true,
			starType: 'i',
			number: function () {
				return jQuery(this).attr('data-number');
			},
			score: function () {
				return jQuery(this).attr('data-score');
			},
			hints: ['1 Star', '2 Star', '3 Star', '4 Star', '5 Star'],
			space: false,
			readOnly: true
		});
	}

	var billing_country = jQuery('.um-field #billing_country');
	var shipping_country = jQuery('.um-field #shipping_country');
	if ( billing_country.length ) {
		var country = billing_country.val();
		var type = billing_country.data('key');
		um_wc_refresh_address(country, type, billing_country);
	}
	if ( shipping_country.length ) {
		var country = shipping_country.val();
		var type = shipping_country.data('key');
		um_wc_refresh_address(country, type, shipping_country);
	}

	/* Country & State */
	jQuery(document.body).on('change', '.um-field #billing_country, .um-field #shipping_country', function () {
		var country = jQuery(this).val();
		var type = jQuery(this).data('key');
		um_wc_refresh_address(country, type, jQuery(this));
	});

	if (jQuery('.um-account-tab select.country_select, .um-account-tab select.state_select, .um-custom-shortcode-tab select').length) {
		jQuery('.um-account-tab select.country_select, .um-account-tab select.state_select, .um-custom-shortcode-tab select').select2({
			width: '100%'
		});
	}

	if (jQuery('.um-account-tab .um-field-state .um-field-error').length > 0) {
		var $field = jQuery('.um-account-tab .um-field-country:visible select');
		var country = $field.val();
		var type = $field.data('key');
		um_wc_refresh_address(country, type, $field);
	}


	/* Orders */
	jQuery(document.body).on('click', '.um-woo-orders .my_account_orders .um-woo-view-order, .um-woo-orders .my_account_orders a.view', um_wc_display_order);

	jQuery(document.body).on('click', '.um-woo-order-hide', function (e) {
		e.preventDefault();
		window.history.pushState("string", "Orders", window.location.pathname);
		remove_Modal();
		return false;
	});

	if (window.location.href.indexOf('/orders/') > -1) {
		if ( window.location.hash ) {
			var order_id = window.location.hash.replace( /\D/, '' );
			um_wc_display_order(e, order_id);
		}
	}


	/* Subscriptions */
	jQuery(document.body).on('click', '.um-woo-subscriptions .my_account_subscriptions a.view, .um-woo-subscriptions .my_account_subscriptions .subscription-id > a', um_wc_display_subscription);

	jQuery(document.body).on('click', '.back_to_subscriptions', function (e) {
		e.preventDefault();
		window.history.pushState("string", "Subscriptions", window.location.pathname);
		jQuery('.woocommerce_account_subscriptions').removeAttr('style').fadeIn().nextAll('.um_account_subscription').remove();
		return false;
	});

	if ( window.location.href.indexOf( '/subscription/' ) > -1 ) {
		if ( window.location.hash ) {
			var subscription_id = window.location.hash.replace( /\D/, '' );
			um_wc_display_subscription( e, subscription_id );
		}
	}


	/* Payment method */
	jQuery(document.body).on('click', 'a[href*="add-payment-method"]', function (e) {
		e.preventDefault();

		if (!/add-payment-method=1/.test(location.href)) {
			var link = location.href + (/\?/.test(location.href) ? '&' : '?') + 'add-payment-method=1';
			window.history.pushState('string', 'Add payment method', link);
		}

		jQuery('#um_add_payment_method_content .um-modal-overlay').remove();
		jQuery('#um_add_payment_method_content .um-modal').removeClass('um-modal-hidden').show().after('<div class="um-modal-overlay"></div>');
		jQuery(document.body).trigger('resize');
	});

	jQuery('#um_add_payment_method_content').on('click', '.um-modal-overlay', function (e) {
		e.stopPropagation();
		window.history.pushState("string", "Orders", window.location.pathname);

		var $self = jQuery(e.currentTarget);
		$self.siblings('.um-modal').addClass('um-modal-hidden').hide();
		$self.remove();
	});

	if (/add-payment-method=1/.test(location.href) && jQuery('a[href*="add-payment-method"]').length) {
		jQuery('a[href*="add-payment-method"]').trigger('click');
	}

});

function um_wc_display_order(e, order_id) {

	if (typeof (order_id) === 'undefined' && typeof (e.currentTarget) !== 'undefined') {
		e.preventDefault(e);
		order_id = jQuery(e.currentTarget).parents('tr').data('order_id');
		var a = jQuery( e.currentTarget ).get( 0 );
		if ( !order_id && typeof (a.hash) === 'string' ) {
			order_id = a.hash.replace( /\D/, '' );
		}
		window.history.pushState("string", "Orders", a.href);
	}

	return wp.ajax.send('um_woocommerce_get_order', {
		data: {
			order_id: order_id,
			nonce: um_scripts.nonce
		},
		beforeSend: prepare_Modal,
		success: function (data) {
			if (data) {
				show_Modal(data);
				responsive_Modal();
				jQuery('.um-popup:visible').addClass('um-popup-order').show();
			} else {
				remove_Modal();
			}
		},
		error: function (e) {
			remove_Modal();
			console.log('===UM Woocommerce error===', e);
		}
	});
}

function um_wc_display_subscription(e, subscription_id) {

	if ( typeof (subscription_id) === 'undefined' && typeof (e.currentTarget) !== 'undefined' ) {
		e.preventDefault( e );
		var a = jQuery( e.currentTarget ).get( 0 );
		if ( typeof (a.hash) === 'string' ) {
			subscription_id = a.hash.replace( /\D/, '' );
		} else {
			a = jQuery( e.currentTarget ).closest( 'tr' ).find( 'td.subscription-id a' );
			subscription_id = a.text().replace( /\D/, '' );
		}
		window.history.pushState( "string", "Subscriptions", a.href );
	}

	return wp.ajax.send('um_woocommerce_get_subscription', {
		data: {
			subscription_id: subscription_id,
			nonce: um_scripts.nonce
		},
		beforeSend: function () {
			jQuery('.woocommerce_account_subscriptions').css({cursor: 'wait', opacity: '0.7'});
		},
		success: function (data) {
			//console.dir(data);
			if( typeof( data.content ) === 'string' ){
				jQuery('.woocommerce_account_subscriptions').hide().after(data.content);
				jQuery('.um_account_subscription').fadeIn();
			}
			if( typeof( data.script_params ) === 'object' ){
				WCSViewSubscription = data.script_params;
			}
			if( typeof( data.script_ready ) === 'string' ){
				jQuery.globalEval( data.script_ready );
			}
		},
		error: function (e) {
			console.log('===UM Woocommerce error===', e);
		}
	});
}

function um_wc_refresh_address(country, type, field) {
	var error, form_id = 0;

	// Get the form ID
	if ( jQuery( field ).closest( '.um-field[id]' ).length ) {
		var match = jQuery( field ).closest( '.um-field[id]' ).attr( 'id' ).match( /um_field_(\d+)_(billing|shipping)_country/i );
		if ( typeof (match) === 'object' && match.length === 3 ) {
			form_id = parseInt( match[1] );
		}
	}
	if ( !form_id && jQuery( field ).closest( 'form' ).find( 'input[name="form_id"]' ).length ) {
		var form_id = parseInt( jQuery( field ).closest( 'form' ).find( 'input[name="form_id"]' ).val() );
	}

	wp.ajax.send('um_woocommerce_refresh_address', {
		data: {
			nonce: um_scripts.nonce,
			country: country,
			type: type,
			form_id: form_id
		},
		success: function (data) {

			var state_wrap = '.um-field-billing_state';
			if (type === 'shipping_country') {
				state_wrap = '.um-field-shipping_state';
			}
			if (jQuery(state_wrap + ' .um-field-error').length > 0) {
				error = jQuery(state_wrap + ' .um-field-error').clone();
			}

			jQuery(state_wrap).html(data).contents().unwrap();

			if (jQuery(state_wrap + ' select').length > 0) {
				jQuery(state_wrap + ' select').select2({
					width: '100%'
				});
			} else {
				if (jQuery(state_wrap + ' input[type = "hidden"]').length > 0) {
					jQuery(state_wrap).hide();
				} else {
					jQuery(state_wrap).show();
				}
			}
			if (error) {
				jQuery(state_wrap).append(error);
			}

		},
		error: function (e) {
			console.log('===UM Woocommerce error===', e);
		}
	});
}