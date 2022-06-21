// User Tags field getting default value for conditional logic
wp.hooks.addFilter( 'um_conditional_logic_default_value', 'um_user_tags', function( default_value, type, $dom ) {
	if ( type === 'user_tags' ) {
		default_value = $dom.find('select').val();
	}

	return default_value;
}, 10 );

wp.hooks.addFilter( 'um_conditional_logic_field_element', 'um_user_tags', function( field_element, type, $dom ) {
	if ( type === 'user_tags' ) {
		field_element = $dom.find( 'select' );
	}

	return field_element;
}, 10 );

wp.hooks.addFilter( 'um_conditional_logic_contains_operator_owners', 'um_user_tags', function( $owners, field_type, live_field_value, condition, index ) {
	if ( field_type === 'user_tags' ) {
		if ( live_field_value && live_field_value.indexOf( condition.value ) >= 0 && um_in_array( condition.value, live_field_value ) ) {
			$owners[ condition.owner ][ index ] = true;
		} else {
			$owners[ condition.owner ][ index ] = false;
		}
	}

	return $owners;
}, 10 );


wp.hooks.addAction( 'um_conditional_logic_restore_default_value', 'um_user_tags', function( type, $dom, field ) {
	if ( type === 'user_tags' ) {
		$dom.find('select').find('option').prop( 'selected', false );
		jQuery.each( field.value, function ( i, value ) {
			$dom.find('select').find('option[value="' + value + '"]').attr('selected', true);
		});
		$dom.find('select').trigger( 'change' );
	}
}, 10 );


jQuery(document).ready(function() {
	/* Tooltip for tag */
	if( typeof tipsy !== 'undefined' ){
		jQuery('.um-user-tag-desc').tipsy({
			gravity: 'n',
			opacity: 0.95,
			offset: 5,
			fade: false
		});
	}
	
	/* Show more tags */
	jQuery( document.body ).on('click', '.um-user-tag-more', function() {
		jQuery(this).hide();
		jQuery(this).parents('.um-user-tags').find('.um-user-hidden-tag').show();
		return false;
	});


	if ( typeof select2 !== 'undefined' ) {
		jQuery('.um-field-user_tags select').select2('destroy');
		jQuery(".um-field-user_tags select").each(function(){
			var $this = jQuery(this);
			$this.select2({
				allowClear: true,
				minimumResultsForSearch: 10,
				maximumSelectionSize: parseInt( $this.attr('data-maxsize') )
			});
		});
	}

});