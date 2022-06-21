var um_followers_ajax = false;

jQuery(document).ready(function() {

	/* Mouse over of following button */
	jQuery( document.body ).on( 'mouseenter', '.um-unfollow-btn', function() {
		if ( ! jQuery(this).hasClass('um_followers_ajax') ) {
			jQuery(this).addClass('um-unfollow-btn2');
			jQuery(this).html( jQuery(this).attr('data-unfollow') );
		}
	});

	/* Mouse out of following button */
	jQuery(document.body).on('mouseleave', '.um-unfollow-btn2', function() {
		if ( ! jQuery(this).hasClass('um_followers_ajax') ) {
			jQuery(this).removeClass('um-unfollow-btn2');
			jQuery(this).html( jQuery(this).attr('data-following') );
		}
	});

	/* Following user */
	jQuery(document.body).on('click', '.um-follow-btn', function(e) {
		e.preventDefault();
		if ( um_followers_ajax === true ) {
			return false;
		}
		um_followers_ajax = true;

		var btn = jQuery(this);
		var member_directory = btn.parents( '.um-members-wrapper' ).length > 0;

		btn.addClass('um_followers_ajax');
		var user_id = btn.data('user_id');

		wp.ajax.send( 'um_followers_follow', {
			data: {
				user_id: user_id,
				nonce: um_scripts.nonce
			},
			success: function( response ) {
				if ( typeof ( response.count ) !== 'undefined' ) {
					if ( member_directory ) {
						btn.parents( '.um-member' ).find('span.um-ajax-count-followers').text( response.count );
					} else {
						jQuery('span.um-ajax-count-followers').text( response.count );
					}
				}

				btn.replaceWith( response.btn );
				um_followers_ajax = false;
			},
			error: function( e ) {
				console.log( e );
				um_followers_ajax = false;
			}
		});
		return false;
	});

	/* Unfollowing user */
	jQuery(document.body).on('click', '.um-unfollow-btn', function(e) {
		e.preventDefault();
		if ( um_followers_ajax === true ) {
			return false;
		}
		um_followers_ajax = true;
		var btn = jQuery(this);
		var member_directory = btn.parents( '.um-members-wrapper' ).length > 0;

		btn.addClass('um_followers_ajax');
		var user_id = btn.data('user_id');

		wp.ajax.send( 'um_followers_unfollow', {
			data: {
				user_id: user_id,
				nonce: um_scripts.nonce
			},
			success: function( response ) {
				if ( typeof ( response.count ) !== 'undefined' ) {
					if ( member_directory ) {
						btn.parents( '.um-member' ).find('span.um-ajax-count-followers').text( response.count );
					} else {
						jQuery('span.um-ajax-count-followers').text( response.count );
					}
				}

				btn.replaceWith( response.btn );
				um_followers_ajax = false;
			},
			error: function( e ) {
				console.log( e );
				um_followers_ajax = false;
			}
		});
		return false;
	});

	/* Show all user */
	jQuery( document.body ).on('click', '.um-follow-show-all', function(e) {
		var obj = jQuery(this);
		var widget = obj.parents('.um-followers-widget');
		var type = widget.data('type');
		var max = widget.data('max');
		var user_id = widget.data('user_id');

		widget.removeClass('um-is-loaded');

		wp.ajax.send( 'um_followers_get_users', {
			data: {
				user_id: user_id,
				max: max,
				offset: max,
				type: type,
				nonce: um_scripts.nonce
			},
			success: function( data ) {
				var template = wp.template( 'um-' + type + '-mini' );
				widget.find('.um-followers-widget-wrapper').append( template( data ) );
				widget.addClass('um-is-loaded');

				obj.remove();
			},
			error: function( e ) {
				console.log( e );

				widget.addClass('um-is-loaded');
			}
		});
	});


	if ( jQuery('.um-followers-widget').length > 0 ) {

		jQuery('.um-followers-widget').each( function() {
			var obj = jQuery(this);
			var type = obj.data('type');
			var max = obj.data('max');
			var user_id = obj.data('user_id');

			wp.ajax.send( 'um_followers_get_users', {
				data: {
					user_id: user_id,
					max: max,
					type: type,
					nonce: um_scripts.nonce
				},
				success: function( data ) {
					var template = wp.template( 'um-' + type + '-mini' );
					obj.find('.um-followers-widget-wrapper').append( template( data ) );
					obj.addClass('um-is-loaded');
				},
				error: function( e ) {
					console.log( e );
					obj.addClass('um-is-loaded');
				}
			});
		});
	}
});