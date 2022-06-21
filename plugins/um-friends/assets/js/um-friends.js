um_friends_ajax = false;

jQuery( document ).ready(function() {
	if ( jQuery('.um-friends-m').length ) {
		
		jQuery('.um-friends-m').each( function(){
			
			var max = jQuery(this).attr('data-max');
			if ( max > 0 && jQuery(this).find('.um-friends-m-user').length > max ) {
				var n = max - 1;
				jQuery(this).find('.um-friends-m-user:gt('+n+')').hide();
				var more = jQuery(this).find('.um-friends-m-user').length - jQuery(this).find('.um-friends-m-user:visible').length;
				jQuery('<div class="um-friends-m-user show-all">+'+ more + '</div>').insertAfter( jQuery(this).find('.um-friends-m-user:visible:last') );
			}
			
		});

		jQuery( document.body ).on( 'click', '.um-friends-m-user.show-all', function(e){
			e.preventDefault();
			jQuery(this).parents('.um-friends-m').find('.um-friends-m-user').show();
			jQuery(this).hide();
			return false;
		});
	}


	/* Mouse over of friend button */
	jQuery( document.body ).on( 'mouseenter', '.um-unfriend-btn', function(e){
		if ( ! jQuery(this).hasClass('um_friends_ajax') ) {
			jQuery(this).addClass('um-unfriend-btn2');
			jQuery(this).html( jQuery(this).attr('data-unfriend') );
		}
	});


	/* Mouse out of friend button */
	jQuery( document.body ).on( 'mouseleave', '.um-unfriend-btn2', function(e){
		if ( ! jQuery(this).hasClass('um_friends_ajax') ) {
			jQuery(this).removeClass('um-unfriend-btn2');
			jQuery(this).html( jQuery(this).attr('data-friends') );
		}
	});


	/* Mouse over of pending friend button */
	jQuery( document.body ).on( 'mouseenter', '.um-friend-pending-btn', function() {
		jQuery(this).addClass('cancel-friend-request');
		jQuery(this).html( jQuery(this).attr('data-cancel-friend-request') );
	});


	/* Mouse out of pending friend button */
	jQuery( document.body ).on( 'mouseleave', '.um-friend-pending-btn', function() {
		jQuery(this).removeClass('cancel-friend-request');
		jQuery(this).html( jQuery(this).attr('data-pending-friend-request') );
	});


	/* Add friend user */
	jQuery( document.body ).on( 'click', '.um-friend-btn', function(e) {
		e.preventDefault();
		if ( um_friends_ajax ) {
			return false; 
		}

		um_friends_ajax = true;
		var btn = jQuery(this);
		btn.addClass( 'um_friends_ajax' );
		var user_id = btn.data( 'user_id' );

		wp.ajax.send( 'um_friends_add', {
			data: {
				user_id: user_id,
				nonce: um_scripts.nonce
			},
			success: function( response ) {
				btn.replaceWith( response.btn );
				um_friends_ajax = false;
			},
			error: function( e ) {
				console.log( e );
				um_friends_ajax = false;
			}
		});

		return false;
	});


	/* Unfriend user */
	jQuery( document.body ).on( 'click', '.um-unfriend-btn', function(e) {
		e.preventDefault();
		if ( um_friends_ajax ) {
			return false;
		}
		um_friends_ajax = true;

		var btn = jQuery(this);
		btn.addClass('um_friends_ajax');
		var user_id = btn.data('user_id');

		wp.ajax.send( 'um_friends_unfriend', {
			data: {
				user_id: user_id,
				nonce: um_scripts.nonce
			},
			success: function( response ) {
				btn.replaceWith( response.btn );
				um_friends_ajax = false;
			},
			error: function( e ) {
				console.log( e );
				um_friends_ajax = false;
			}
		});

		return false;
	});


	/* Confirm friend */
	jQuery( document.body ).on( 'click', '.um-friend-accept-btn', function(e) {
		e.preventDefault();
		in_dropdown = false;
		if ( um_friends_ajax ) {
			return false;
		}

		um_friends_ajax = true;
		var btn = jQuery(this);
		btn.addClass('um_friends_ajax');
		var user_id = btn.data('user_id');

		var btn2 = btn.parent().find('.um-friend-reject-btn');

		if ( btn.parents('.um-dropdown' ).length > 0 ) {
			in_dropdown = true;
		}

		wp.ajax.send( 'um_friends_approve', {
			data: {
				user_id: user_id,
				nonce: um_scripts.nonce
			},
			success: function( response ) {
				if ( in_dropdown ) {
					btn.parents('.um-friend-respond-zone').find('.um-friend-respond-btn').replaceWith( response.btn );
					UM.dropdown.hideAll();
				} else {
					btn.replaceWith( response.btn );
					btn2.remove();
				}

				um_friends_ajax = false;
			},
			error: function( e ) {
				console.log( e );
				um_friends_ajax = false;
			}
		});

		return false;
	});


	/* Reject friendship user */
	jQuery( document.body ).on( 'click', '.um-friend-reject-btn', function(e) {
		e.preventDefault();
		in_dropdown = false;
		if ( um_friends_ajax ) {
			return false;
		}
		um_friends_ajax = true;
		var btn = jQuery(this);
		btn.addClass('um_friends_ajax');
		var user_id = btn.data('user_id');

		var btn2 = btn.parent().find('.um-friend-accept-btn');

		if ( btn.parents('.um-dropdown' ).length > 0 ) {
			in_dropdown = true;
		}

		wp.ajax.send( 'um_friends_unfriend', {
			data: {
				user_id: user_id,
				nonce: um_scripts.nonce
			},
			success: function( response ) {
				if ( in_dropdown ) {
					btn.parents('.um-friend-respond-zone').find('.um-friend-respond-btn').replaceWith( response.btn );
					UM.dropdown.hideAll();
				} else {
					btn.replaceWith( response.btn );
					btn2.remove();
				}

				um_friends_ajax = false;
			},
			error: function( e ) {
				console.log( e );
				um_friends_ajax = false;
			}
		});

		return false;
	});


	jQuery( document.body ).on('click', '.um-friend-respond-btn', function() {
		jQuery(this).parent().trigger('click');
	});


	/* Cancel pending friend */
	jQuery( document.body ).on('click', '.um-friend-pending-btn.cancel-friend-request', function(e) {
		e.preventDefault();
		if ( um_friends_ajax ) {
			return false;
		}
		um_friends_ajax = true;
		var btn = jQuery(this);
		btn.addClass('um_friends_ajax');
		var user_id = btn.data('user_id');

		wp.ajax.send( 'um_friends_cancel_request', {
			data: {
				user_id: user_id,
				nonce: um_scripts.nonce
			},
			success: function( response ) {
				btn.replaceWith( response.btn );
				um_friends_ajax = false;
			},
			error: function( e ) {
				console.log( e );
				um_friends_ajax = false;
			}
		});
		return false;
	});
});