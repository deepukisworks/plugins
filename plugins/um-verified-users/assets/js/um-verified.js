wp.hooks.addFilter( 'um_user_screen_block_hiding', 'um_verified_users', function( hide ) {
	hide = false;
	return hide;
}, 10 );

wp.hooks.addFilter( 'um_admin_blocks_condition_fields_default', 'um_verified_users', function( um_condition_fields ) {
	um_condition_fields['um_locked_to_verified'] = 'um_block_settings_hide';
	return um_condition_fields;
}, 10 );

wp.hooks.addFilter( 'um_admin_blocks_condition_fields', 'um_verified_users', function( um_condition_fields , props ) {
	if ( props.attributes.um_is_restrict !== true ) {
		um_condition_fields['um_locked_to_verified'] = 'um_block_settings_hide';
	} else {
		if ( parseInt( props.attributes.um_who_access ) === 0 || typeof props.attributes.um_who_access === 'undefined' ) {
			um_condition_fields['um_locked_to_verified'] = 'um_block_settings_hide';
		} else if ( parseInt( props.attributes.um_who_access ) === 1  ) {
			um_condition_fields['um_locked_to_verified'] = '';
		} else if ( parseInt( props.attributes.um_who_access ) === 2  ) {
			um_condition_fields['um_locked_to_verified'] = 'um_block_settings_hide';
		}
	}

	return um_condition_fields;
}, 10 );

wp.hooks.addFilter( 'um_admin_blocks_condition_fields_on_change', 'um_verified_users', function( um_condition_fields, key, value ) {
	if ( key === 'um_is_restrict' ) {
		if ( value === false ) {
			um_condition_fields['um_locked_to_verified'] = 'um_block_settings_hide';
		}
	} else if ( key === 'um_who_access' ) {
		if ( parseInt( value ) === 0 ) {
			um_condition_fields['um_locked_to_verified'] = 'um_block_settings_hide';
		} else if ( parseInt( value ) === 1 ) {
			um_condition_fields['um_locked_to_verified'] = '';
		} else  {
			um_condition_fields['um_locked_to_verified'] = 'um_block_settings_hide';
		}
	}

	return um_condition_fields;
}, 10 );

wp.hooks.addFilter( 'um_admin_blocks_custom_fields', 'um_verified_users', function( fields, um_condition_fields , props ) {
	fields.push( wp.element.createElement(
		wp.components.ToggleControl,
		{
			label: wp.i18n.__( 'Lock to verified accounts only', 'um-verified' ),
			className: um_condition_fields['um_locked_to_verified'],
			checked: props.attributes.um_locked_to_verified,
			onChange: function onChange( value ) {
				props.setAttributes({ um_locked_to_verified: value });
			}
		}
	) );
	return fields;
}, 10 );

wp.hooks.addFilter( 'um_admin_blocks_restrict_settings', 'um_verified_users', function( um_block_restrict_settings ) {
	um_block_restrict_settings.um_locked_to_verified = {
		type: "boolean"
	};

	return um_block_restrict_settings;
}, 10 );