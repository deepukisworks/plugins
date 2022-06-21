jQuery(document).ready(function() {

	var id = jQuery('#um_edit_role input[name="role[id]"]').val();
	if ( id !== undefined ) {
		wp.ajax.send( 'um_profile_completeness_get_fields_data', {
			data: {
				id: id,
				nonce: um_admin_scripts.nonce
			},
			success: function( result ) {
				if ( result.fields_data ) {
					var template = wp.template('um_completeness_fields');
					var fields_data = template({
						fields: result.fields_data,
						select: result.select_fields
					});
					jQuery('.compl-fields').html( fields_data );
				}
			},
			error: function( e ) {
				console.log( e )
			}
		});
	}

	um_profilec_check();

	jQuery(document).on( 'keyup keydown change', '#role__um_profilec_pct', function(e) {
		um_profilec_check();
	});

	/**
	 Check a total percentage
	 **/
	function um_profilec_check() {
		var total = jQuery('#role__um_profilec_pct').val();
		if ( total > 100 ) {
			jQuery('#role__um_profilec_pct').val(100);
		} else if ( total < 0 ) {
			jQuery('#role__um_profilec_pct').val(0);
		}
	}

	/**
	 * Show / Hide a add button new fields
	 *
	 * @return void;
	 */
	function um_profilec_add_button() {
		jQuery('.profilec-add').show();
		jQuery('#create_role').removeAttr('disabled');
		var current_progress = jQuery('#role_um_allocated_progress').val();
		if ( current_progress*1 >= 100 ) {
			jQuery('.profilec-add').hide();
		}
	}

	/**
	 Add a profile field to completion
	 **/
	jQuery(document).on( 'click', '.profilec-add', function(e) {
		e.preventDefault();
		jQuery(this).hide();
		jQuery('.profilec-field').show();
		jQuery('#progress_field, #progress_value').prop('disabled', false).prop('readonly', false);
	});

	/**
	 Cancel add
	 **/
	jQuery(document).on( 'click', '.profilec-cancel', function(e) {
		e.preventDefault();
		jQuery('.profilec-add').parent().show();
		jQuery('.profilec-field, .profilec-field .profilec-new-field-percent-validation, .profilec-field .profilec-new-field-validation, .profilec-field .profilec-empty-field-validation').hide();
		jQuery('#progress_field, #progress_value').prop('disabled', true).prop('readonly', true).val('');
		um_profilec_add_button();
		return false;
	});


	/**
	 Save a profile field to completion
	 **/
	jQuery(document).on( 'click', '.profilec-save', function(e) {
		e.preventDefault();

		var current_progress = jQuery( '#role_um_allocated_progress' ).val();
		if ( current_progress*1 == 100 ) {
			return false;
		}

		var progress_value = jQuery( '#progress_value' ).val();
		if(!(/^[0-9]+$/gm.test(progress_value)))
			return false;

		if ( progress_value*1 <= 0 )
			return false;

		var new_progress = current_progress*1 + progress_value*1;
		if ( new_progress > 100 ) {
			jQuery('.profilec-field .profilec-new-field-percent-validation').show();
			return false;
		}
		jQuery('.profilec-field .profilec-new-field-percent-validation').hide();

		var progress_field = jQuery( '#progress_field' ).val();
		var progress_name = jQuery( '#progress_field option:selected' ).text();

		if ( ! progress_field || progress_field == '0' ) {
			jQuery('.profilec-field .profilec-empty-field-validation').show();
			return false;
		}
		jQuery('.profilec-field .profilec-empty-field-validation').hide();

		var profilec_data_keys = [];

		jQuery( '.profilec-data p' ).each( function() {
			profilec_data_keys.push( jQuery( this ).attr('data-key').replace('_um_progress_', '') );
		});

		if( jQuery.inArray( progress_field, profilec_data_keys ) !== -1 ){
			jQuery('.profilec-field .profilec-new-field-validation').show();
			return false;
		}
		jQuery('.profilec-field .profilec-new-field-validation').hide();

		var label = jQuery('.profilec-field-form label').text();
		var percent_wrapper = '<p data-key="_um_progress_' + progress_field + '"><span class="profilec-key alignleft">' + progress_name + '</span><span class="profilec-progress alignright"><strong><ins>' + progress_value + '</ins>%</strong> <span class="profilec-edit" data-id="_um_progress_' + progress_field + '"><i class="um-faicon-pencil"></i></span><span class="profilec-remove" data-id="_um_progress_' + progress_field + '"><i class="um-faicon-remove"></i></span></span><span class="profilec-field-form role_um_progress_' + progress_field + '" style="display: none;"><label for="role_um_progress_' + progress_field + '">' + label + '</label><input type="number" min="0" max="100" id="role_um_progress_' + progress_field + '" name="role[_um_progress_' + progress_field + ']" value="' + progress_value + '" data-prev="' + progress_value + '" /><span class="profilec-field-form-buttons"></span></span></p><div class="clear"></div>';
		jQuery('.profilec-data').append( percent_wrapper );

		jQuery('#role_um_allocated_progress').val( new_progress );
		jQuery('.profilec-ajax').html( 100 - new_progress );

		jQuery('.profilec-cancel').trigger('click');
		um_profilec_add_button();
		return false;
	});


	/**
		Make inline edit
	**/
	jQuery(document).on('click', '.profilec-edit',function(e){
		e.preventDefault();

		var id = jQuery(this).attr('data-id');
		var el = jQuery('.profilec-field-form.role' + id);
		var value = el.find('input').val();
		el.find('input').attr('data-prev', value);
		jQuery('.profilec-data .profilec-field-form').each( function () {
			var prev = jQuery(this).find('input').attr('data-prev');
			jQuery(this).find('input').val(prev);
			jQuery(this).hide();
		});
		el.show();
		var buttons = jQuery('.profilec-field-form-hidden').html();
		el.find('.profilec-field-form-buttons').html('');
		el.find('.profilec-field-form-buttons').append(buttons);
		el.find('.profilec-update, .profilec-cancel-edit').attr('data-id', id);
		jQuery('.profilec-add, .profilec-field').hide();
		jQuery('#create_role').attr('disabled','disabled');
		return false;
	});


	/**
	 Cancel edit field
	 **/
	jQuery(document).on( 'click', '.profilec-cancel-edit', function(e) {
		e.preventDefault();

		var id = jQuery(this).attr('data-id');
		var el = jQuery('.profilec-field-form.role' + id);
		var prev = el.find('input').attr('data-prev');
		el.hide();
		el.find('input').val(prev);
		el.find('.profilec-field-form-buttons').html('');
		um_profilec_add_button();
		return false;
	});


	/**
		Remove a profile field
	**/
	jQuery(document).on('click', '.profilec-remove',function(e){
		e.preventDefault();

		var id = jQuery(this).attr('data-id');
		var el = jQuery('.profilec-field-form.role' + id);
		var prev = el.find('input').attr('data-prev');
		el.find('input').val(prev);
		var progress_value = jQuery('#role' + id ).val();
		var current_progress = jQuery('#role_um_allocated_progress').val();
		var new_progress = current_progress*1 - progress_value*1;
		jQuery('#role_um_allocated_progress').val( new_progress );
		jQuery('.profilec-ajax').html( 100 - new_progress );
		jQuery('p[data-key="' + id + '"]').remove();
		jQuery('.profilec-add').parent().show();
		um_profilec_add_button();
		return false;
	});


	/**
		Update a profile field to completion
	**/
	jQuery(document).on('click', '.profilec-update',function(e){
		e.preventDefault();

		var id = jQuery(this).attr('data-id');
		var el = jQuery('.profilec-field-form.role' + id);
		var progress_value = el.find('input').val();
		var current_progress = parseInt( jQuery('#role_um_allocated_progress').val() );
		var prev_progress_value = parseInt( el.find('input').attr('data-prev') );
		var diff_progress = prev_progress_value - progress_value;
		var new_progress = current_progress*1 - diff_progress;

		if ( current_progress <= 100 && new_progress*1 > 100 ) {
			el.find('.profilec-field-validation').show();
			return false;
		}

		el.find('input').attr('data-prev', progress_value);
		el.hide();
		jQuery('.profilec-ajax').html( 100 - new_progress );
		jQuery('p[data-key="' + id + '"] ins').html( progress_value );
		jQuery('#role_um_allocated_progress').val( new_progress );
		el.find('.profilec-field-form-buttons').html('');
		jQuery('.profilec-add').parent().show();
		um_profilec_add_button();
		return false;
	});
});