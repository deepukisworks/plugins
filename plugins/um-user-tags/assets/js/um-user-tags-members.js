wp.hooks.addFilter( 'um_member_directory_filter_request', 'um_user_tags', function( request ) {
	if ( typeof um_user_tags !== 'undefined' ) {
		request.user_tag = um_user_tags.user_tag;
		request.user_tag_field = um_user_tags.user_tag_field;
	}

	return request;
}, 10 );

// ignore show after search/filtering option if we display individual User tag page
wp.hooks.addFilter( 'um_member_directory_ignore_after_search', 'um_user_tags', function( ignore, directory ) {
	if ( typeof um_user_tags !== 'undefined' ) {
		ignore = true;
	}

	return ignore;
}, 10 );


wp.hooks.addFilter( 'um_member_directory_url_attrs', 'um_user_tags', function( query_strings ) {
	if ( typeof um_user_tags !== 'undefined' ) {
		query_strings.push( 'tag_field=' + um_user_tags.user_tag_field );
	}

	return query_strings;
}, 10 );