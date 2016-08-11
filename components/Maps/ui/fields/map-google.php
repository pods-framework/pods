<?php
wp_enqueue_script( 'googlemaps' );
wp_enqueue_script( 'pods-maps' );
wp_enqueue_style( 'pods-maps' );

$map_options = array();
if ( ! empty( $options[ $type . '_map_zoom' ] ) ) {
	$map_options['zoom'] = (int) $options[ $type . '_map_zoom' ];
} else {
	$map_options['zoom'] = (int) Pods_Component_Maps::$options['map_zoom'];
}
if ( ! empty( $options[ $type . '_map_type' ] ) ) {
	$map_options['type'] = $options[ $type . '_map_type' ];
} else {
	$map_options['type'] = Pods_Component_Maps::$options['map_type'];
}
if ( ! empty( $options[ $type . '_map_marker' ] ) ) {
	$map_options['marker'] = $options[ $type . '_map_marker' ];
} else {
	$map_options['marker'] = Pods_Component_Maps::$options['map_marker'];
}

if ( $options['address_map_info_window_content'] == 'custom' ) {
	echo PodsForm::label( $attributes['id'] . '-info-window', __( 'Info Window content', 'pod' ) );
	echo PodsForm::field( $name . '[info_window]', pods_v( 'info_window', $value ), 'wysiwyg', array( 'settings' => array( 'wpautop' => false, 'editor_height' => 150 ) ) );
}

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );
echo PodsForm::label( 'map-google', __( 'Google Maps', 'pod' ) );
?>
<input type="button" name="<?php echo $attributes['id'] . '-map-lookup-button' ?>" id="<?php echo $attributes['id'] . '-map-lookup-button' ?>" value="<?php _e( 'Lookup Location from Address', 'pods' ) ?>" />
<div id="<?php echo $attributes['id'] . '-map-canvas' ?>" class="pods-maps-map-canvas pods-<?php echo $form_field_type ?>-maps-map-canvas"></div>

<script type="text/javascript">
	jQuery( document ).ready( function ( $ ) {

		if ( typeof google != 'undefined' ) {

			var fieldId = '<?php echo $attributes['id'] ?>';
			var fieldType = '<?php echo $type ?>';
			var mapCanvas = document.getElementById( '<?php echo $attributes['id'] . '-map-canvas'?>' );
			var geocodeButton = $( '#<?php echo $attributes['id'] . '-map-lookup-button'  ?>' );

			var fields = {
				line_1: $( '#<?php echo $attributes['id'] . '-address-line-1'  ?>' ),
				line_2: $( '#<?php echo $attributes['id'] . '-address-line-2'  ?>' ),
				city: $( '#<?php echo $attributes['id'] . '-address-city'  ?>' ),
				postal_code: $( '#<?php echo $attributes['id'] . '-address-postal-code'  ?>' ),
				region: $( '#<?php echo $attributes['id'] . '-address-region'  ?>' ),
				country: $( '#<?php echo $attributes['id'] . '-address-country'  ?>' ),
				text: $( '#<?php echo $attributes['id'] . '-text' ?>' ),
				info_window: $( '#<?php echo $attributes['id'] . '-info-window' ?>' ),
				lat: $( '#<?php echo $attributes['id'] . '-geo-lat'  ?>' ),
				lng: $( '#<?php echo $attributes['id'] . '-geo-lng'  ?>' )
			};

			var map = null;
			var marker = null;
			var infowindow = null;
			var infowindowContent = '';
			var infowindowEditor = '';
			var geocoder = null;
			var address = null;
			var latlng = null;
			var mapOptions = {
				center: new google.maps.LatLng( 41.850033, -87.6500523 ), // default (Chicago)
				marker: '<?php echo $map_options['marker'] ?>',
				zoom: <?php echo $map_options['zoom'] ?>,
				type: '<?php echo $map_options['type'] ?>'
			};

			//------------------------------------------------------------------------
			// Initialze the map
			//
			if ( fields.lat.length && fields.lng.length ) {
				latlng = { 'lat': Number( fields.lat.val() ), 'lng': Number( fields.lng.val() ) };
				mapOptions.center = new google.maps.LatLng( latlng );
			}
			map = new google.maps.Map( mapCanvas, mapOptions );
			geocoder = new google.maps.Geocoder();

			podsMapsMarker();
			podsMapsInfoWindowContent();

			//------------------------------------------------------------------------
			// Geolocate from the address after clicking the button
			//
			geocodeButton.on( 'click', function ( event ) {

				event.preventDefault();

				if ( fieldType == 'lat-lng' ) {
					latlng = { 'lat': Number( fields.lat.val() ), 'lng': Number( fields.lng.val() ) };
					podsMapsCenter();
				} else {
					if ( fieldType == 'address' ) {
						address = podsMergeAddressFields();
					} else {
						address = fields.text.val();
					}
					podsMapsGeocodeAddress();
				}
			} ); // end button click event*/

			// Set & Update InfoWindow content
			function podsMapsInfoWindowContent() {

				if ( fields.info_window.length ) {
					infowindowContent = fields.info_window.val();
					podsMapsInfoWindow( false );
					// In case the tinyMCE editor doesn't load
					fields.info_window.on( 'change keyup', function () {
						clearInterval(wait);
						infowindowContent = fields.info_window.val();
						podsMapsInfoWindow( false );
					});
					// Wait for the tinyMCE editor to load
					var wait = setInterval( function () {
						if ( tinyMCE.editors.length ) {
							clearInterval(wait);
							infowindowEditor = tinyMCE.get( fields.info_window.attr('id') );
							infowindowEditor.on( 'change keyup', function () {
								infowindowContent = infowindowEditor.getContent({format : 'raw'});
								podsMapsInfoWindow( false );
							})
						}
					}, 100 );
				} else {
					if ( fieldType == 'text' ) {
						if ( fields.text.length ) {
							infowindowContent = fields.text.val();
							podsMapsInfoWindow( false );
							fields.text.on( 'change keyup', function () {
								infowindowContent = fields.text.val();
								podsMapsInfoWindow( false );
							})
						}
					}
				}
			}

			function podsMapsGeocodeAddress() {
				geocoder.geocode( { 'address': address }, function ( results, status ) {
					if ( status == google.maps.GeocoderStatus.OK ) {
						console.log(results[ 0 ]);
						latlng = {
							'lat': results[ 0 ].geometry.location.lat(),
							'lng': results[ 0 ].geometry.location.lng()
						};
						podsUpdateLatLng();
						podsMapsCenter();
					}
					// Geocode failure
					else {
						alert( "Geocode was not successful for the following reason: " + status );
					}
				} ); // end geocode
			}

			function podsMapsGeocodeLatLng() {
				geocoder.geocode( { 'location': latlng }, function ( results, status ) {
					if ( status == google.maps.GeocoderStatus.OK ) {
						address = results[ 0 ].address_components;
						podsUpdateAddress();
					}
					// Geocode failure
					else {
						alert( "Geocode was not successful for the following reason: " + status );
					}
				} ); // end geocode
			}

			function podsMapsCenter() {
				mapOptions.center = new google.maps.LatLng( latlng );
				map.setCenter( mapOptions.center );
				podsMapsMarker();
			}

			function podsMapsPanTo() {
				mapOptions.center = new google.maps.LatLng( latlng );
				map.panTo( mapOptions.center );
				podsMapsMarker();
			}

			function podsMapsMarker() {

				// Set the marker options
				var markerOptions = {
					map : map,
					position : latlng,
					draggable : true
				};

				// Create a new marker, if needed, and set the event listeners
				if ( ! marker ) {
					marker = new google.maps.Marker( markerOptions );
					// Move marker
					google.maps.event.addListener( marker, 'drag', function () {
						latlng = { 'lat': marker.getPosition().lat(), 'lng': marker.getPosition().lng() };
						podsUpdateLatLng();
					} );
					// Drop marker
					google.maps.event.addListener( marker, 'dragend', function () {
						latlng = { 'lat': marker.getPosition().lat(), 'lng': marker.getPosition().lng() };
						podsMapsPanTo();
						podsUpdateLatLng();
						podsMapsGeocodeLatLng();
					} );
					// InfoWindow
					google.maps.event.addListener( marker, 'click', function () {
						podsMapsInfoWindow( true );
					} );
				}
				// Marker is already set, just update its options
				else {
					marker.setOptions( markerOptions );
				}
			}

			function podsMapsInfoWindow( open ) {

				if ( ! infowindow ) {
					infowindow = new google.maps.InfoWindow();
				}

				infowindow.setContent( infowindowContent );
				if ( open ) {
					infowindow.open( map, marker );
				}
			}

			function podsUpdateAddress() {
				if ( typeof address == 'object' ) {

					if ( fieldType == 'address' ) {
						// Reset line_1 since this is made of two parts from Google (street_number and route)
						if ( fields.line_1.length ) {
							fields.line_1.val('');
						}
						$.each( address, function ( i, address_component ) {
							if ( fields.line_1.length && address_component.types[0] == "street_number" ){
								fields.line_1.val( ' ' + address_component.long_name );
							}
							if ( fields.line_1.length && address_component.types[0] == "route" ){
								fields.line_1.val( address_component.long_name + fields.line_1.val() );
							}
							if ( fields.city.length && address_component.types[0] == "locality" ){
								fields.city.val( address_component.long_name );
							}
							if ( fields.country.length && address_component.types[0] == "country" ) {
								if ( fields.country.is('select') ) {
									fields.country.val( address_component.short_name );
								} else {
									fields.country.val( address_component.long_name );
								}
							}
							if ( fields.region.length && address_component.types[0] == "administrative_area_level_1" ) {
								if ( fields.region.is('select') ) {
									// @todo Validate for US states
									fields.region.val( address_component.short_name );
								} else {
									fields.region.val( address_component.long_name );
								}
							}
							if ( fields.postal_code.length && address_component.types[0] == "postal_code" ) {
								fields.postal_code.val( address_component.long_name );
							}
						} );

					} else if ( fieldType == 'text' ) {

						if ( fields.text.length ) {
							// Reset value
							fields.text.val('');
							$.each( address, function ( i, address_component ) {
								if ( address_component.long_name != '' ) {
									if ( address_component.types[0] == "route" ) {
										fields.text.val( address_component.long_name + fields.text.val() );
									} else if ( address_component.types[0] == "street_number" ){
										fields.text.val( ' ' + address_component.long_name );
									} else {
										if ( fields.text.val() == '' ) {
											fields.text.val( address_component.long_name );
										} else {
											fields.text.val( fields.text.val() + ', ' + address_component.long_name );
										}
									}
								}
							} );
						}

					}
				}
			}

			function podsMergeAddressFields() {
				var tmpAddress = [];
				if ( fields.line_1.length ) { tmpAddress.push( fields.line_1.val() ); }
				if ( fields.line_2.length ) { tmpAddress.push( fields.line_2.val() ); }
				if ( fields.city.length ) { tmpAddress.push( fields.city.val() ); }
				if ( fields.postal_code.length ) { tmpAddress.push( fields.postal_code.val() ); }
				if ( fields.region.length ) { tmpAddress.push( fields.region.val() ); }
				if ( fields.country.length ) { tmpAddress.push( fields.country.val() ); }
				address = tmpAddress.join(', ');
			}

			function podsUpdateLatLng() {
				if ( fields.lat.length ) { fields.lat.val( latlng.lat )	}
				if ( fields.lng.length ) { fields.lng.val( latlng.lng )	}
			}

		}

	} ); // end document ready
</script>