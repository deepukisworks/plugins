jQuery( document ).ready( function($) {

	$( document.body ).on( 'change', '#product-type', function() {
		if ( $(this).val() === 'subscription' || $(this).val() === 'variable-subscription' ) {
			$('.um-woo-subscription-settings').parents('.um-forms-line').show();
			$('.um-woo-not-subscription-settings').parents('.um-forms-line').hide();
		} else {
			$('.um-woo-subscription-settings').parents('.um-forms-line').hide();
			$('.um-woo-not-subscription-settings').parents('.um-forms-line').show();
		}
	});

	$('#product-type').trigger('change');

});