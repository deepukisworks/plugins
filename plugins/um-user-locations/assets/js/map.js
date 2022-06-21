if ( typeof ( wp.UM ) !== 'object' ) {
	wp.UM = {};
}

if ( typeof ( wp.UM.member_directory ) !== 'object' ) {
	wp.UM.member_directory = {};
}

wp.UM.member_directory.map = {
	infowindow: {
		customize: function() {
			var iwOuter = jQuery('.gm-style-iw');

			var iwBackground = iwOuter.prev();

			// Removes background shadow DIV
			iwBackground.children(':nth-child(2)').css({'display' : 'none'});

			// Removes white background DIV
			iwBackground.children(':nth-child(4)').css({'display' : 'none'});

			// Moves the infowindow 115px to the right.
			iwOuter.parent().parent().css({left: '115px'});

			// Moves the shadow of the arrow 76px to the left margin.
			iwBackground.children(':nth-child(1)').attr('style', function(i,s){ return s + 'left: 76px !important;'});

			// Moves the arrow 76px to the left margin.
			iwBackground.children(':nth-child(3)').attr('style', function(i,s){ return s + 'left: 76px !important;'});

			// Changes the desired tail shadow color.
			iwBackground.children(':nth-child(3)').find('div').children().css({'box-shadow': 'rgba(72, 181, 233, 0.6) 0px 1px 6px', 'z-index' : '1'});

			// Reference to the div that groups the close button elements.
			var iwCloseBtn = iwOuter.next();

			// Apply the desired effect to the close button
			iwCloseBtn.css({
				opacity: '1',
				right: '48px',
				top: '20px',
				'border-radius': '50%'
			});

			// The API automatically applies 0.7 opacity to the button after the mouseout event. This function reverses this event to the desired value.
			iwCloseBtn.mouseout(function(){
				jQuery(this).css({opacity: '1'});
			});
		},
		close: function( directory, flush ) {
			var hash = um_members_get_hash( directory );

			if ( typeof wp.UM.member_directory.map.blocks[ hash ] === 'undefined' ) {
				return;
			}

			if ( typeof wp.UM.member_directory.map.blocks[ hash ]['infowindows'] !== 'undefined' ) {
				jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['infowindows'], function( user_id ) {
					jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ], function( field ) {
						wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ].close();
					});
				});
			}

			if ( flush ) {
				wp.UM.member_directory.map.blocks[ hash ]['infowindows'] = {};
			}
		}
	},

	selectOnEnter: function( input ) {
		// store the original event binding function
		var _addEventListener = ( input.addEventListener ) ? input.addEventListener : input.attachEvent;

		function addEventListenerWrapper( type, listener ) {
			// Simulate a 'down arrow' keypress on hitting 'return' when no pac suggestion is selected,
			// and then trigger the original listener.

			if ( type === 'keydown' ) {
				var orig_listener = listener;

				listener = function( event ) {
					var suggestion_selected = jQuery(".pac-item-selected").length > 0;

					if ( event.which === 13 && ! suggestion_selected ) {
						var simulated_downarrow = jQuery.Event( "keydown", {
							keyCode: 40,
							which: 40
						});

						orig_listener.apply( input, [ simulated_downarrow ] );
					}

					orig_listener.apply( input, [ event ] );
				};
			}

			_addEventListener.apply( input, [ type, listener ] );
		}

		if ( input.addEventListener ) {
			input.addEventListener = addEventListenerWrapper;
		} else if ( input.attachEvent ) {
			input.attachEvent = addEventListenerWrapper;
		}
	},

	remove_markers: function( directory ) {
		var hash = um_members_get_hash( directory );

		if ( typeof wp.UM.member_directory.map.blocks[ hash ] === 'undefined' ) {
			return;
		}

		if ( typeof wp.UM.member_directory.map.blocks[ hash ]['markers'] === 'undefined' ) {
			wp.UM.member_directory.map.blocks[ hash ]['markers'] = {};
			return;
		}

		if ( typeof wp.UM.member_directory.map.blocks[ hash ]['markers'] !== 'undefined' ) {
			jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['markers'], function( user_id ) {
				jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ], function( field ) {
					wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ][ field ].setMap( null );
				});
			});
		}
		wp.UM.member_directory.map.blocks[ hash ]['markers'] = {};

		if ( typeof wp.UM.member_directory.map.blocks[ hash ]['marker_clusterer'] !== 'undefined' ) {
			wp.UM.member_directory.map.blocks[ hash ]['marker_clusterer'].clearMarkers();
		}
	},

	is_dynamically_searched: function( directory ) {
		return !! directory.find('.um-member-directory-map[data-dynamic-search="1"]').length;
	},

	is_searched: function( directory ) {
		return !! directory.find('.um_user_location_g_autocomplete').length;
	},

	get_map: function( directory ) {
		var hash = um_members_get_hash( directory );

		if ( typeof wp.UM.member_directory.map.blocks[ hash ] !== 'undefined' ) {
			if ( typeof wp.UM.member_directory.map.blocks[ hash ]['map'] !== 'undefined' ) {
				return wp.UM.member_directory.map.blocks[ hash ]['map'];
			}
		}

		return null;
	},

	move_map: function( map, directory ) {
		if ( um_is_directory_busy( directory ) ) {
			return;
		}

		var bounds =  map.getBounds();
		var ne = bounds.getNorthEast();
		var sw = bounds.getSouthWest();
		var zoom = map.getZoom();

		um_members_show_preloader( directory );

		um_set_url_from_data( directory, 'map_ne',ne.lat() + ',' + ne.lng() );
		um_set_url_from_data( directory, 'map_sw',sw.lat() + ',' + sw.lng() );
		um_set_url_from_data( directory, 'map_zoom', zoom );

		//set 1st page after filtration
		directory.data( 'page', 1 );
		um_set_url_from_data( directory, 'page', '' );

		um_ajax_get_members( directory );

		directory.data( 'searched', 1 );
		directory.find( '.um-member-directory-sorting-options' ).prop( 'disabled', false );
		directory.find( '.um-member-directory-view-type' ).removeClass( 'um-disabled' );
	},

	init: function( directory ) {
		// page first loading
		var directory_map = directory.find( '.um-member-directory-map' );

		if ( directory_map.hasClass( 'um-map-inited' ) ) {
			return;
		}

		var hash = um_members_get_hash( directory );

		wp.UM.member_directory.map.blocks[ hash ] = [];

		var latlng = new google.maps.LatLng( directory_map.data( 'lat' ), directory_map.data( 'lng' ) );
		var zoom = directory_map.data( 'zoom' );
		if ( typeof zoom == 'undefined' ) {
			zoom = 12;
		}

		var map_args = wp.hooks.applyFilters( 'um_user_locations_map_args_init', {
			center: latlng,
			zoom: zoom
		}, hash, directory );

		wp.UM.member_directory.map.blocks[ hash ]['map'] = new google.maps.Map( directory_map[0], map_args );

		if ( wp.UM.member_directory.map.is_dynamically_searched( directory ) ) {
			var map_ne = um_get_data_for_directory( directory, 'map_ne' );
			var map_sw = um_get_data_for_directory( directory, 'map_sw' );
			var map_zoom = um_get_data_for_directory( directory, 'map_zoom' );

			if ( typeof map_ne !== 'undefined' && typeof map_sw !== 'undefined' ) {
				directory_map.removeClass( wp.UM.member_directory.map.classes.hidden );

				var map_ne_array = map_ne.split(',');
				var map_sw_array = map_sw.split(',');

				var bounds = new google.maps.LatLngBounds();
				bounds.extend( new google.maps.LatLng( map_sw_array[0], map_sw_array[1] ) );
				bounds.extend( new google.maps.LatLng( map_ne_array[0], map_ne_array[1] ) );

				wp.UM.member_directory.map.blocks[ hash ]['map'].setZoom( parseInt( map_zoom ) );

				if ( map_sw_array[1]*1 > map_ne_array[1]*1 ) {
					var adjust_lng = ( 180 - map_sw_array[1]*1 + ( -180 - map_ne_array[1]*1 )*-1 ) / 2;

					var c_lng;
					if ( map_sw_array[1]*1 + adjust_lng <= 180 ) {
						c_lng = map_sw_array[1]*1 + adjust_lng;
					} else {
						c_lng = map_ne_array[1]*1 - adjust_lng;
					}

					var center = new google.maps.LatLng( ( map_ne_array[0]*1 + map_sw_array[0]*1 ) / 2, c_lng );

					wp.UM.member_directory.map.blocks[ hash ]['map'].setCenter( center );
				} else {
					wp.UM.member_directory.map.blocks[ hash ]['map'].setCenter( bounds.getCenter() );
				}
			}

			if ( typeof wp.UM.member_directory.map.listeners[ hash ] === 'undefined' ) {
				wp.UM.member_directory.map.listeners[ hash ] = [];
			}
			wp.UM.member_directory.map.listeners[ hash ]['moving'] = google.maps.event.addListener( wp.UM.member_directory.map.blocks[ hash ]['map'], 'idle', function() {

				if ( ! wp.UM.member_directory.map.listeners[ hash ]['skip_first_idle'] ) {
					wp.UM.member_directory.map.listeners[ hash ]['skip_first_idle'] = true;
					return;
				}

				wp.UM.member_directory.map.move_map( this, directory );
			});
		} else if ( wp.UM.member_directory.map.is_searched( directory ) ) {
			var search_lat = um_get_data_for_directory( directory, 'search_lat' );
			var search_lng = um_get_data_for_directory( directory, 'search_lng' );
			var map_zoom = um_get_data_for_directory( directory, 'map_zoom' );

			if ( typeof search_lat !== 'undefined' && typeof search_lng !== 'undefined' ) {
				directory_map.removeClass( wp.UM.member_directory.map.classes.hidden );

				wp.UM.member_directory.map.blocks[ hash ]['map'].setZoom( parseInt( map_zoom ) );

				wp.UM.member_directory.map.blocks[ hash ]['map'].setCenter( new google.maps.LatLng( search_lat, search_lng ) );
			}
		}

		directory.find('.um_user_location_g_autocomplete').each(function() {

			wp.UM.member_directory.map.selectOnEnter( jQuery(this).get(0) );

			var autocomplete_args = wp.hooks.applyFilters( 'um_user_locations_autocomplete_args', {
				types: ['geocode']
			}, directory, jQuery(this) );

			var autocomplete = new google.maps.places.Autocomplete( jQuery(this).get(0), autocomplete_args );

			var location_search = jQuery(this);
			var value = um_get_data_for_directory( directory, 'location_search' );
			if ( typeof value !== 'undefined' ) {
				location_search.val( value );
			}

			autocomplete.addListener( 'place_changed', function(e) {
				if ( um_is_directory_busy( directory ) ) {
					return;
				}

				um_members_show_preloader( directory );

				var place = this.getPlace();

				place = wp.hooks.applyFilters( 'um_user_locations_place_changed_autocomplete', place, directory );
				if ( typeof place == 'undefined' || typeof place.geometry == 'undefined' || typeof place.geometry.location == 'undefined' ) {
					if ( typeof place.name == 'undefined' ) {
						um_members_hide_preloader( directory );
						return;
					}
				}

				if ( place.name !== '' ) {
					var lat = place.geometry.location.lat();
					var lng = place.geometry.location.lng();

					um_set_url_from_data( directory, 'location_search', location_search.val() );

					if ( directory.find('.um-member-directory-map').length ) {
						var is_hidden = directory_map.hasClass( wp.UM.member_directory.map.classes.hidden );
						directory_map.removeClass( wp.UM.member_directory.map.classes.hidden );

						wp.UM.member_directory.map.blocks[ hash ]['map'].setCenter({
							lat: parseFloat(lat),
							lng: parseFloat(lng)
						});
						wp.UM.member_directory.map.blocks[ hash ]['map'].fitBounds( place.geometry.viewport );

						if ( ! is_hidden ) {

							var zoom = wp.UM.member_directory.map.blocks[ hash ]['map'].getZoom();

							if ( wp.UM.member_directory.map.is_dynamically_searched( directory ) ) {

								var bounds = wp.UM.member_directory.map.blocks[ hash ]['map'].getBounds();

								var ne = bounds.getNorthEast();
								var sw = bounds.getSouthWest();

								um_set_url_from_data( directory, 'map_ne', ne.lat() + ',' + ne.lng() );
								um_set_url_from_data( directory, 'map_sw', sw.lat() + ',' + sw.lng() );
								um_set_url_from_data( directory, 'map_zoom', zoom );

							} else if ( wp.UM.member_directory.map.is_searched( directory ) ) {
								um_set_url_from_data( directory, 'search_lat', parseFloat(lat) );
								um_set_url_from_data( directory, 'search_lng', parseFloat(lng) );
								um_set_url_from_data( directory, 'map_zoom', zoom );
							}

							wp.hooks.doAction( 'um_user_locations_after_place_changed_autocomplete', place, directory, wp.UM.member_directory.map.blocks[ hash ]['map'] );

							directory.data( 'searched', 1 );
							directory.find( '.um-member-directory-sorting-options' ).prop( 'disabled', false );
							directory.find( '.um-member-directory-view-type' ).removeClass( 'um-disabled' );

							//set 1st page after filtration
							directory.data( 'page', 1 );
							um_set_url_from_data( directory, 'page', '' );

							um_ajax_get_members( directory );

						} else {
							setTimeout( function() {
								var zoom = wp.UM.member_directory.map.blocks[ hash ]['map'].getZoom();

								if ( wp.UM.member_directory.map.is_dynamically_searched( directory ) ) {
									var bounds = wp.UM.member_directory.map.blocks[ hash ]['map'].getBounds();

									var ne = bounds.getNorthEast();
									var sw = bounds.getSouthWest();

									um_set_url_from_data( directory, 'map_ne', ne.lat() + ',' + ne.lng() );
									um_set_url_from_data( directory, 'map_sw', sw.lat() + ',' + sw.lng() );
									um_set_url_from_data( directory, 'map_zoom', zoom );
								} else if ( wp.UM.member_directory.map.is_searched( directory ) ) {
									um_set_url_from_data( directory, 'search_lat', parseFloat(lat) );
									um_set_url_from_data( directory, 'search_lng', parseFloat(lng) );
									um_set_url_from_data( directory, 'map_zoom', zoom );
								}

								wp.hooks.doAction( 'um_user_locations_after_place_changed_autocomplete', place, directory, wp.UM.member_directory.map.blocks[ hash ]['map'] );

								directory.data( 'searched', 1 );
								directory.find( '.um-member-directory-sorting-options' ).prop( 'disabled', false );
								directory.find( '.um-member-directory-view-type' ).removeClass( 'um-disabled' );

								//set 1st page after filtration
								directory.data( 'page', 1 );
								um_set_url_from_data( directory, 'page', '' );

								um_ajax_get_members( directory );
							}, 100 );
						}

					} else {

						directory.data( 'searched', 1 );
						directory.find( '.um-member-directory-sorting-options' ).prop( 'disabled', false );
						directory.find( '.um-member-directory-view-type' ).removeClass( 'um-disabled' );

						//set 1st page after filtration
						directory.data( 'page', 1 );
						um_set_url_from_data( directory, 'page', '' );

						um_ajax_get_members( directory );

					}

				} else {

					um_set_url_from_data( directory, 'location_search', '' );

					if ( directory.find('.um-member-directory-map').length ) {

						var zero_sw = new google.maps.LatLng(-90, 180);
						var zero_ne = new google.maps.LatLng(90, -180);
						var zero_bounds = new google.maps.LatLngBounds(zero_sw, zero_ne);
						wp.UM.member_directory.map.blocks[ hash ]['map'].fitBounds( zero_bounds );

						wp.UM.member_directory.map.blocks[ hash ]['map'].setCenter({
							lat: 0,
							lng: 0
						});

						wp.UM.member_directory.map.blocks[ hash ]['map'].setZoom(1);

						if ( wp.UM.member_directory.map.is_dynamically_searched( directory ) ) {
							um_set_url_from_data( directory, 'map_ne', '' );
							um_set_url_from_data( directory, 'map_sw', '' );
							um_set_url_from_data( directory, 'map_zoom', '' );
						} else if ( wp.UM.member_directory.map.is_searched( directory ) ) {
							um_set_url_from_data( directory, 'search_lat', '' );
							um_set_url_from_data( directory, 'search_lng', '' );
							um_set_url_from_data( directory, 'map_zoom', '' );
						}

					}

					var ignore_after_search = false;
					ignore_after_search = wp.hooks.applyFilters( 'um_member_directory_ignore_after_search', ignore_after_search, directory );

					if ( ! ignore_after_search ) {
						var show_after_search = directory.data('must-search');
						if ( show_after_search === 1 ) {
							var search = um_get_search( directory );
							if ( directory.find( '.um-members-filter-remove' ).length === 0 && ! search ) {
								directory.data( 'searched', 0 );
								directory.find('.um-members-grid, .um-members-list, .um-members-intro').remove();
								directory.find( '.um-member-directory-sorting-options' ).prop( 'disabled', true );
								directory.find( '.um-member-directory-view-type' ).addClass( 'um-disabled' );

								wp.hooks.doAction( 'um_member_directory_clear_not_searched', directory );
							}
						}
					}

					//set 1st page after filtration
					directory.data( 'page', 1 );
					um_set_url_from_data( directory, 'page', '' );

					um_ajax_get_members( directory );
				}
			});
		});

		directory_map.addClass( 'um-map-inited' );

		return directory_map;
	},

	ajax: {
		request: function( request, directory ) {

			if ( wp.UM.member_directory.map.is_dynamically_searched( directory ) ) {
				var map_ne = um_get_data_for_directory( directory, 'map_ne' );
				var map_sw = um_get_data_for_directory( directory, 'map_sw' );

				if ( typeof map_ne !== 'undefined' && typeof map_sw !== 'undefined' ) {
					request.map_ne = map_ne;
					request.map_sw = map_sw;
				} else {

					var map = wp.UM.member_directory.map.get_map( directory );

					if ( map ) {
						var hash = um_members_get_hash( directory );

						var bounds = map.getBounds();

						if ( typeof bounds != 'undefined' ) {
							var ne = bounds.getNorthEast();
							var sw = bounds.getSouthWest();

							map_ne = ne.lat() + ',' + ne.lng();
							map_sw = sw.lat() + ',' + sw.lng();

							request.map_ne = map_ne;
							request.map_sw = map_sw;
						}
					}
				}
			} else if ( wp.UM.member_directory.map.is_searched( directory ) ) {
				var search_lat = um_get_data_for_directory( directory, 'search_lat' );
				var search_lng = um_get_data_for_directory( directory, 'search_lng' );

				if ( typeof search_lat !== 'undefined' && typeof search_lng !== 'undefined' ) {
					request.search_lat = search_lat;
					request.search_lng = search_lng;
				}
			}

			return request;
		},
		response: function( directory, data ) {
			var hash = um_members_get_hash( directory );
			var directory_map = directory.find('.um-member-directory-map');

			directory_map.removeClass( wp.UM.member_directory.map.classes.hidden );

			var map = wp.UM.member_directory.map.get_map( directory );

			var current_markers = {};
			Object.getOwnPropertyNames( current_markers ).forEach(function ( prop ) {
				delete current_markers[ prop ];
			});

			jQuery.each( data.users, function( key ) {
				var userdata = data.users[ key ];

				if ( typeof current_markers[ userdata.id ] === 'undefined' ) {
					current_markers[ userdata.id ] = {};
				}

				jQuery.each( data.map_fields, function( field, title ) {
					if ( typeof userdata[ field + '_lat' ] !== 'undefined' && typeof userdata[ field + '_lng' ] !== 'undefined' ) {
						var latlng = new google.maps.LatLng( userdata[ field + '_lat' ], userdata[ field + '_lng' ] );

						var infowindow_template = wp.template( 'um-user-location-map-marker-infowindow' );

						var infowindow_attr = {
							content: infowindow_template( {
								'field': field,
								'userdata': userdata
							} ),
							maxWidth: 350
						};
						infowindow_attr = wp.hooks.applyFilters( 'um_user_locations_infowindow_data', infowindow_attr, hash, userdata.id );

						var marker_data = {
							position:       latlng,
							map:            map,
							title:          userdata.display_name + ' ' + title,
							member_anchor:  userdata.card_anchor,
							zIndex:         key + 1,
							optimized:      false,
							infowindow:     infowindow_attr
						};

						if ( userdata['avatar_url'] !== '' ) {
							var suffix;
							if ( userdata['avatar_url'].indexOf( '?' ) === -1 ) {
								suffix = '?um_avatar_marker=1';
							} else {
								suffix = '&um_avatar_marker=1';
							}

							marker_data.icon = {
								url:        userdata['avatar_url'] + suffix,
								scaledSize: new google.maps.Size(32, 32),
								size:       new google.maps.Size(36, 40),
								anchor:     new google.maps.Point(18, 18)
							};

							marker_data.shape = {
								coords: [ 18, 18, 20 ],
								type:   'circle'
							};
						}

						marker_data = wp.hooks.applyFilters( 'um_user_locations_marker_data', marker_data, hash, userdata );

						current_markers[ userdata.id ][ field ] = marker_data;
					}

				});
			});

			if ( typeof wp.UM.member_directory.map.blocks[ hash ]['markers'] === 'undefined' ) {
				wp.UM.member_directory.map.blocks[ hash ]['markers'] = {};
			}

			if ( typeof wp.UM.member_directory.map.blocks[ hash ]['infowindows'] === 'undefined' ) {
				wp.UM.member_directory.map.blocks[ hash ]['infowindows'] = {};
			}

			if ( typeof wp.UM.member_directory.map.blocks[ hash ]['marker_clusterer'] !== 'undefined' ) {
				wp.UM.member_directory.map.blocks[ hash ]['marker_clusterer'].clearMarkers();
			}


			if ( typeof wp.UM.member_directory.map.blocks[ hash ]['markers'] !== 'undefined' ) {
				jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['markers'], function( user_id ) {
					jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ], function( field ) {
						if ( typeof current_markers[ user_id ] === 'undefined' || typeof current_markers[ user_id ][ field ] === 'undefined' ) {
							wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ][ field ].setMap( null );
							wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ].close();

							delete wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ][ field ];
							if ( ! Object.keys( wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ] ).length ) {
								delete wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ];
							}

							delete wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ];
							if ( ! Object.keys( wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ] ).length ) {
								delete wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ];
							}
						} else {
							delete current_markers[ user_id ][ field ];

							if ( ! Object.keys( current_markers[ user_id ] ).length ) {
								delete current_markers[ user_id ];
							}
						}
					});
				});
			}

			jQuery.each( current_markers, function( user_id ) {
				if ( typeof wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ] === 'undefined' ) {
					wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ] = {};
				}

				if ( typeof wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ] == 'undefined' ) {
					wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ] = {};
				}

				jQuery.each( current_markers[ user_id ], function( field ) {
					wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ][ field ] = new google.maps.Marker( current_markers[ user_id ][ field ] );
					wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ] = new google.maps.InfoWindow( current_markers[ user_id ][ field ][ 'infowindow' ] );

					// Click on marker
					wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ][ field ].addListener('click', function(e) {
						// avoid the click conflict with markerClusterer: when click on cluster - the infowindow is opened
						if ( typeof e === 'undefined' ) {
							wp.UM.member_directory.map.infowindow.close( directory );
							return;
						}

						wp.UM.member_directory.map.infowindow.close( directory );

						wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ]['status'] = 'open';

						wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ].open(
							map,
							wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ][ field ]
						);
					});

					google.maps.event.addListener( wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ], 'closeclick', function() {
						wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ]['status'] = 'closed';
					});
				});
			});

			wp.hooks.doAction( 'um_member_directory_after_markers_init', directory, current_markers );

			var disableSpiderfier = false;
			disableSpiderfier = wp.hooks.applyFilters( 'um_member_directory_disable_spiderfier', disableSpiderfier, directory );

			if ( ! disableSpiderfier ) {
				var spiderConfig = {
					keepSpiderfied: true,
					event: 'mouseover'
				};

				var markerSpiderfier = new OverlappingMarkerSpiderfier( map, spiderConfig );

				var markers = [];
				if ( typeof wp.UM.member_directory.map.blocks[ hash ]['markers'] !== 'undefined' ) {
					jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['markers'], function( user_id ) {
						jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ], function( field ) {
							markers.push( wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ][ field ] );

							markerSpiderfier.addMarker( wp.UM.member_directory.map.blocks[ hash ]['markers'][ user_id ][ field ] );
						});
					});
				}

				markerSpiderfier.addListener('spiderfy', function( markers ) {
					if ( typeof wp.UM.member_directory.map.blocks[ hash ]['infowindows'] !== 'undefined' ) {
						jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['infowindows'], function( user_id ) {
							jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ], function( field ) {
								wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ].close();
							});
						});
					}
				});

				markerSpiderfier.addListener('unspiderfy', function( markers ) {
					if ( typeof wp.UM.member_directory.map.blocks[ hash ]['infowindows'] !== 'undefined' ) {
						jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['infowindows'], function( user_id ) {
							jQuery.each( wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ], function( field ) {
								wp.UM.member_directory.map.blocks[ hash ]['infowindows'][ user_id ][ field ].close();
							});
						});
					}
				});
			}

			var disableClustering = false;
			disableClustering = wp.hooks.applyFilters( 'um_member_directory_disable_clustering', disableClustering, directory );

			if ( ! disableClustering ) {
				// gridSize: 60 - default clustering gridSize, please use smaller for biggest zooms
				var clustering_options = wp.hooks.applyFilters( 'um_user_locations_marker_clustering_options',
					{imagePath: um_user_location_map.cluster_url}, hash, map, markers );

				// Add a marker clusterer to manage the markers.
				wp.UM.member_directory.map.blocks[ hash ]['marker_clusterer'] = new MarkerClusterer( map, markers, clustering_options );

				wp.UM.member_directory.map.blocks[ hash ]['marker_clusterer'].setMaxZoom( 20 );

				google.maps.event.addListener( wp.UM.member_directory.map.blocks[ hash ]['marker_clusterer'], 'clusterclick', function( cluster ) {
					var current_cluster = cluster.getMarkers()[0];
					setTimeout( function() {
						google.maps.event.trigger( current_cluster, 'click' );
					},1000 );
				});
			}

		}
	},
	blocks: [],
	listeners: [],
	classes: {
		hidden: 'um-member-directory-hidden-map'
	}
};


// hide map if isn't search
wp.hooks.addAction( 'um_member_directory_clear_not_searched', 'um_user_locations', function( directory ) {
	if ( ! directory.find('.um-member-directory-map').length ) {
		return;
	}

	directory.find('.um-member-directory-map').addClass( wp.UM.member_directory.map.classes.hidden );
});

// don't call AJAX while map isn't inited
wp.hooks.addFilter( 'um_member_directory_get_members_allow', 'um_user_locations', function( allow, hash, directory ) {
	if ( ! directory.find('.um-member-directory-map').length ) {
		return allow;
	}

	if ( typeof wp.UM.member_directory.map.blocks[ hash ] == 'undefined' ) {
		allow = false;
	} else if ( typeof wp.UM.member_directory.map.blocks[ hash ]['map'] == 'undefined' ) {
		allow = false;
	}

	return allow;
}, 10 );


// show header if there is map
wp.hooks.addFilter( 'um_member_directory_generate_header', 'um_user_locations', function( generate, directory ) {
	if ( ! directory.find('.um-member-directory-map').length ) {
		return generate;
	}

	generate = true;
	return generate;
}, 10 );

// fetch user locations markers to map after ajax response
wp.hooks.addAction( 'um_member_directory_loaded', 'um_user_locations', function( directory, data ) {
	if ( ! directory.find('.um-member-directory-map').length ) {
		return;
	}

	//workaround for slow Internet connections
	if ( typeof google == 'undefined' ) {
		um_whengoogleloadeddo( um_waiting_response_handler, [ directory, data ] );
	} else {
		wp.UM.member_directory.map.ajax.response( directory, data );
	}

}, 10 );

function um_waiting_response_handler( directory, data ) {
	wp.UM.member_directory.map.ajax.response( directory, data );
}

function um_whengoogleloadeddo( fnt, args ) {
	if ( typeof google != 'undefined' ) {
		fnt( args[0], args[1] );
	} else {
		setTimeout(function()
		{(function(fnt)
		{
			um_whengoogleloadeddo( fnt, args )
		})(fnt)}, 500); // You can set timer as you wish //
	}
}


// extends AJAX request arguments
wp.hooks.addFilter( 'um_member_directory_filter_request', 'um_user_locations', function( request ) {
	var directory = jQuery( '.um-directory[data-hash="' + request.directory_id + '"]' );

	if ( ! directory.length ) {
		return request;
	}

	request = wp.UM.member_directory.map.ajax.request( request, directory );
	return request;
}, 10 );

// ignore show after search/filtering option if we display individual User tag page
wp.hooks.addFilter( 'um_member_directory_ignore_after_search', 'um_user_locations', function( ignore, directory ) {
	if ( ! directory.find('.um-member-directory-map').length ) {
		return;
	}

	if ( wp.UM.member_directory.map.is_dynamically_searched( directory ) ) {
		var map_ne = um_get_data_for_directory( directory, 'map_ne' );
		var map_sw = um_get_data_for_directory( directory, 'map_sw' );

		if ( typeof map_ne !== 'undefined' && typeof map_sw !== 'undefined' ) {
			ignore = true;
		}
	}

	return ignore;
}, 10 );

// in directory loop
wp.hooks.addAction( 'um_member_directory_on_init', 'um_user_locations', function( directory, hash ) {
	directory.find('.um_current_user_location').on('click', function() {
		var $obj = jQuery(this);
		var directory = $obj.parents('.um-directory');
		var autocomplete = $obj.siblings('.um_user_location_g_autocomplete');

		if ( navigator.geolocation || ! um_user_location_var.is_ssl ) {
			navigator.geolocation.getCurrentPosition(function( data ) {
				if ( typeof data.coords != 'undefined' ) {
					// init google geocoder
					geocoder = new google.maps.Geocoder();

					// run geocoder
					geocoder.geocode( {
						latLng : new google.maps.LatLng( data.coords.latitude, data.coords.longitude ),
						region : um_user_location_map.region
					}, function( results, status ) {
						if ( status == google.maps.GeocoderStatus.OK ) { // on success
							if ( typeof results[0].formatted_address != 'undefined' ) {

								var current_location = results[0].formatted_address;
								autocomplete.val( current_location );

								if ( ! um_is_directory_busy( directory ) ) {
									um_members_show_preloader( directory );

									um_set_url_from_data( directory, 'location_search', current_location );

									if ( directory.find('.um-member-directory-map').length ) {
										directory.find('.um-member-directory-map').removeClass( wp.UM.member_directory.map.classes.hidden );

										wp.UM.member_directory.map.blocks[ hash ]['map'].setCenter({
											lat: parseFloat( data.coords.latitude ),
											lng: parseFloat( data.coords.longitude )
										});
										wp.UM.member_directory.map.blocks[ hash ]['map'].fitBounds( results[0].geometry.viewport );

										var zoom;
										if ( wp.UM.member_directory.map.is_dynamically_searched( directory ) ) {
											zoom = wp.UM.member_directory.map.blocks[ hash ]['map'].getZoom();
											var ne = results[0].geometry.viewport.getNorthEast();
											var sw = results[0].geometry.viewport.getSouthWest();

											um_set_url_from_data( directory, 'map_ne', ne.lat() + ',' + ne.lng() );
											um_set_url_from_data( directory, 'map_sw', sw.lat() + ',' + sw.lng() );
											um_set_url_from_data( directory, 'map_zoom', zoom );
										} else if ( wp.UM.member_directory.map.is_searched( directory ) ) {
											zoom = wp.UM.member_directory.map.blocks[ hash ]['map'].getZoom();
											um_set_url_from_data( directory, 'search_lat', parseFloat( data.coords.latitude ) );
											um_set_url_from_data( directory, 'search_lng', parseFloat( data.coords.longitude ) );
											um_set_url_from_data( directory, 'map_zoom', zoom );
										}
									}

									//set 1st page after filtration
									directory.data( 'page', 1 );
									um_set_url_from_data( directory, 'page', '' );

									um_ajax_get_members( directory );

									directory.data( 'searched', 1 );
									directory.find( '.um-member-directory-sorting-options' ).prop( 'disabled', false );
									directory.find( '.um-member-directory-view-type' ).removeClass( 'um-disabled' );
								}

							}
						} else { // on failed
							jQuery.support.cors = true;
							jQuery.ajax({
								url: 'https://freegeoip.app/json/',
								type: 'GET',
								crossDomain: true,
								dataType: 'jsonp',
								success: function ( response ) {
									if ( typeof response.country_name != 'undefined' ) {

										var current_location = response.country_name + ',' + response.region_name + ',' + response.city;
										$obj.prev().val( current_location );

										if ( ! um_is_directory_busy( directory ) ) {
											um_members_show_preloader( directory );

											if ( directory.find('.um-member-directory-map').length ) {
												directory.find('.um-member-directory-map').removeClass( wp.UM.member_directory.map.classes.hidden );

												wp.UM.member_directory.map.blocks[ hash ]['map'].setCenter({
													lat: parseFloat( data.coords.latitude ),
													lng: parseFloat( data.coords.longitude )
												});
												wp.UM.member_directory.map.blocks[ hash ]['map'].fitBounds( results[0].geometry.viewport );

												var zoom;
												if ( wp.UM.member_directory.map.is_dynamically_searched( directory ) ) {
													zoom = wp.UM.member_directory.map.blocks[ hash ]['map'].getZoom();
													var ne = results[0].geometry.viewport.getNorthEast();
													var sw = results[0].geometry.viewport.getSouthWest();

													um_set_url_from_data( directory, 'map_ne', ne.lat() + ',' + ne.lng() );
													um_set_url_from_data( directory, 'map_sw', sw.lat() + ',' + sw.lng() );
													um_set_url_from_data( directory, 'map_zoom', zoom );
												} else if ( wp.UM.member_directory.map.is_searched( directory ) ) {
													zoom = wp.UM.member_directory.map.blocks[ hash ]['map'].getZoom();
													um_set_url_from_data( directory, 'search_lat', parseFloat( data.coords.latitude ) );
													um_set_url_from_data( directory, 'search_lng', parseFloat( data.coords.longitude ) );
													um_set_url_from_data( directory, 'map_zoom', zoom );
												}
											}

											um_set_url_from_data( directory, 'location_search', current_location );

											//set 1st page after filtration
											directory.data( 'page', 1 );
											um_set_url_from_data( directory, 'page', '' );

											um_ajax_get_members( directory );

											directory.data( 'searched', 1 );
											directory.find( '.um-member-directory-sorting-options' ).prop( 'disabled', false );
											directory.find( '.um-member-directory-view-type' ).removeClass( 'um-disabled' );
										}

									} else {
										alert( wp.i18n.__( 'Can not get your current location', 'um-user-locations' ) );
									}
								},
								error: function ( data ) {
									alert( wp.i18n.__( 'Can not get your current location', 'um-user-locations' ) );
								}
							});
						}
					});
				}
			});
		}
		return false;
	});
}, 10 );


function UMInitUserLocationDirectory() {
	jQuery( '.um-directory' ).each( function() {
		var directory = jQuery(this);
		wp.UM.member_directory.map.init( directory );
	});
}


var um_ul_script = document.createElement('script');
um_ul_script.src = '//maps.googleapis.com/maps/api/js?key=' + um_user_location_map.api_key + '&libraries=places&callback=UMInitUserLocationDirectory';
if ( um_user_location_map.region ) {
	um_ul_script.src += '&language=' + um_user_location_map.region;
}
document.body.appendChild( um_ul_script );