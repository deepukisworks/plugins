jQuery( document ).ready(function() {

	jQuery( document).on( 'click', '#um_options_private_content_generate', function(e){

		e.preventDefault();

		var obj = jQuery(this);
		obj.prop('disabled', true);

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'post',
			data: {
				action: 'um_generate_private_pages',
				nonce: um_admin_scripts.nonce
			},
			success: function( data ) {
				obj.siblings( '.um_setting_ajax_button_response' ).addClass('description complete').html( data.data.message );

				setTimeout( function() {
					obj.siblings( '.um_setting_ajax_button_response' ).removeClass('description complete').html( '' );
				}, 2000 );

				obj.prop('disabled', false);
			}
		});

		return false;
	});

	jQuery( document.body ).on( 'click', '#UM_fonticons a.um-admin-modal-back:not(.um-admin-modal-cancel)', function(e) {
		var icon_selected = jQuery(e.target).attr('data-code');
		if ( typeof icon_selected === 'string' && icon_selected !== '' ) {
			jQuery('#um_options_tab_private_content_icon').val( icon_selected ).parent().find('span.um-admin-icon-value').html('<i class="' + icon_selected + '"></i>');
		}
		return e;
	});
});