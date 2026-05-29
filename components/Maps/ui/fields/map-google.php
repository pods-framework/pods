<?php
wp_enqueue_script( 'googlemaps' );
wp_enqueue_script( 'pods-maps' );
wp_enqueue_style( 'pods-maps' );

if ( ! isset( $form_field_type ) ) {
	$form_field_type = PodsForm::$field_type;
}

$map_options = array();
if ( ! empty( $options['maps_zoom'] ) ) {
	$map_options['zoom'] = (int) $options['maps_zoom'];
} else {
	$map_options['zoom'] = (int) Pods_Component_Maps::$options['maps_zoom'];
}

if ( ! empty( $options['maps_type'] ) ) {
	$map_options['type'] = $options['maps_type'];
} else {
	$map_options['type'] = Pods_Component_Maps::$options['maps_type'];
}

if ( ! empty( $options['maps_marker'] ) ) {
	$map_options['marker'] = $options['maps_marker'];
} else {
	$map_options['marker'] = Pods_Component_Maps::$options['maps_marker'];
}

if ( ! empty( $map_options['marker'] ) ) {
	$map_options['marker'] = wp_get_attachment_image_url( $map_options['marker'], 'full' );
}

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, $form_field_type, $options );

if ( ! empty( $options['maps_info_window'] ) && in_array( $options['maps_info_window_content'], array(
		'paragraph',
		'wysiwyg'
	) )
) {
	echo PodsForm::label( $name . '-info-window', __( 'Info Window content', 'pod' ) );
	if ( $type == 'address' ) {
		echo PodsForm::comment( $name . '-info-window', __( 'You can use the following tags for address fields', 'pods' ) . ': <br><code>{{line_1}}</code>, <code>{{line_2}}</code>, <code>{{postal_code}}</code>, <code>{{city}}</code>, <code>{{region}}</code>, <code>{{country}}</code>' );
	}
	echo PodsForm::field( $name . '[info_window]', pods_v( 'info_window', $value ), $options['maps_info_window_content'], array(
		'settings' => array(
			'wpautop' => false,
			'editor_height' => 150
		)
	) );
}

echo PodsForm::label( 'map-google', __( 'Google Maps', 'pod' ) );
?>
<input type="button" name="<?php echo esc_attr( $attributes['id'] . '-map-lookup-button' ); ?>"
	id="<?php echo esc_attr( $attributes['id'] . '-map-lookup-button' ); ?>"
	value="<?php esc_attr_e( 'Lookup Location from Address', 'pods' ) ?>" />
<div id="<?php echo esc_attr( $attributes['id'] . '-map-canvas' ); ?>"
	class="pods-maps-map-canvas pods-<?php echo esc_attr( $form_field_type ); ?>-maps-map-canvas"></div>

<script type="text/javascript">
jQuery( document ).ready( function ( $ ) {
	// Window.load required. We need to wait until DFV is ready.
	$(window).load( function() {

		if ( typeof google !== 'undefined' ) {

			var fieldId = '<?php echo esc_attr( $attributes['id'] ); ?>',
				fieldType = '<?php echo esc_attr( $type ); ?>',
				mapCanvas = document.getElementById( '<?php echo esc_attr( $attributes['id'] . '-map-canvas' ); ?>' ),
				geocodeButton = $( '#<?php echo esc_attr( $attributes['id'] . '-map-lookup-button' ); ?>' ),

				fields = {
					line_1: $( '#<?php echo esc_attr( $attributes['id'] . '-address-line-1' ); ?>' ),
					line_2: $( '#<?php echo esc_attr( $attributes['id'] . '-address-line-2' ); ?>' ),
					city: $( '#<?php echo esc_attr( $attributes['id'] . '-address-city' ); ?>' ),
					postal_code: $( '#<?php echo esc_attr( $attributes['id'] . '-address-postal-code' ); ?>' ),
					region: $( '#<?php echo esc_attr( $attributes['id'] . '-address-region' ); ?>' ),
					country: $( '#<?php echo esc_attr( $attributes['id'] . '-address-country' ); ?>' ),
					text: $( '#<?php echo esc_attr( $attributes['id'] . '-text' ); ?>' ),
					info_window: $( '#<?php echo esc_attr( $attributes['id'] . '-info-window' ); ?>' ),
					lat: $( '#<?php echo esc_attr( $attributes['id'] . '-geo-lat' ); ?>' ),
					lng: $( '#<?php echo esc_attr( $attributes['id'] . '-geo-lng' ); ?>' )
				},
				// @todo check pregreplace, maybe this can be done better (nl2br not working)
				// @todo check field type
				fieldsFormat = '<?php echo esc_attr(
					preg_replace(
						"/\n/m",
						'<br>',
						pods_v( 'address_display_type_custom', $options )
					)
				); ?>',

				map = null,
				marker = null,
				marker_icon = <?php echo( ! empty( $map_options['marker'] ) ? '\'' .
				                                                              esc_url( $map_options['marker'] ) .
				                                                              '\'' : 'null' ) ?>,
				infowindow = <?php echo esc_attr( ( ! empty( $options['maps_info_window'] ) ) ? 'null' : 'false' ); ?>,
				infowindowContent = '',
				infowindowEditor = '',
				geocoder = null,
				address = null,
				latlng = null,
				mapOptions = {
					center: new google.maps.LatLng( 41.850033, - 87.6500523 ), // default (Chicago)
					marker: '<?php echo esc_attr( $map_options['marker'] ); ?>',
					zoom: <?php echo absint( $map_options['zoom'] ); ?>,
					mapTypeId: '<?php echo esc_attr( $map_options['type'] ); ?>'
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
			if ( infowindow !== false ) {
				podsMapsInfoWindowContent();
			}

			//------------------------------------------------------------------------
			// Geolocate button
			//
			geocodeButton.on( 'click', function ( event ) {

				event.preventDefault();

				if ( fieldType === 'lat-lng' ) {
					latlng = { 'lat': Number( fields.lat.val() ), 'lng': Number( fields.lng.val() ) };
					podsMapsCenter();
				} else {
					if ( fieldType === 'address' ) {
						address = podsMergeAddressFields();
					} else {
						address = fields.text.val();
					}
					podsMapsGeocodeAddress();
				}
			} ); // end button click event*/

			//------------------------------------------------------------------------
			// Set & Update InfoWindow content
			//
			function podsMapsInfoWindowContent() {

				if ( fields.info_window.length ) {
					infowindowContent = podsFormatFieldsToHTML( fields.info_window.val() );

					podsMapsInfoWindow( false );
					// In case the tinyMCE editor doesn't load
					fields.info_window.on( 'change keyup', function () {
						clearInterval( wait );
						infowindowContent = podsFormatFieldsToHTML( fields.info_window.val() );
						podsMapsInfoWindow( false );
					} );
					// Wait for the tinyMCE editor to load
					var wait = setInterval( function () {
						if ( tinyMCE.editors.length ) {
							clearInterval( wait );
							infowindowEditor = tinyMCE.get( fields.info_window.attr( 'id' ) );
							// No need to instantly call podsMapsInfoWindow since this is already done from the textarea
							infowindowEditor.on( 'change keyup', function () {
								infowindowContent = podsFormatFieldsToHTML( infowindowEditor.getContent( { format: 'raw' } ) );
								podsMapsInfoWindow( false );
							} )
						}
					}, 100 );
				} else {
					if ( fieldType === 'text' ) {
						if ( fields.text.length ) {
							infowindowContent = fields.text.val();
							podsMapsInfoWindow( false );
							fields.text.on( 'change keyup', function () {
								infowindowContent = fields.text.val();
								podsMapsInfoWindow( false );
							} )
						}
					} else {
						infowindowContent = podsFormatFieldsToHTML( fieldsFormat );
						podsMapsInfoWindow( false );
						$.each( fields, function ( key, field ) {
							field.on( 'change keyup', function () {
								infowindowContent = podsFormatFieldsToHTML( fieldsFormat );
								podsMapsInfoWindow( false );
							} )
						} );
					}
				}
			}


			//------------------------------------------------------------------------
			// Map handlers
			//
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


			//------------------------------------------------------------------------
			// Markers
			//
			function podsMapsMarker() {

				// Set the marker options
				var markerOptions = {
					map: map,
					position: latlng,
					draggable: true
				};

				if ( marker_icon ) {
					markerOptions.icon = marker_icon;
				}

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
					if ( infowindow !== false ) {
						google.maps.event.addListener( marker, 'click', function () {
							podsMapsInfoWindow( true );
						} );
					}
				}
				// Marker is already set, just update its options
				else {
					marker.setOptions( markerOptions );
				}
			}

			//------------------------------------------------------------------------
			// InfoWindows
			//
			function podsMapsInfoWindow( open ) {

				if ( ! infowindow ) {
					infowindow = new google.maps.InfoWindow();
				}

				infowindow.setContent( infowindowContent );
				if ( open ) {
					infowindow.open( map, marker );
				}
			}


			//------------------------------------------------------------------------
			// Geocoding
			//
			function podsMapsGeocodeAddress() {
				geocoder.geocode( { 'address': address }, function ( results, status ) {
					if ( status == google.maps.GeocoderStatus.OK ) {
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

			//------------------------------------------------------------------------
			// Field updates
			//
			function podsUpdateLatLng() {
				if ( fields.lat.length ) {
					fields.lat.val( latlng.lat )
				}
				if ( fields.lng.length ) {
					fields.lng.val( latlng.lng )
				}
			}

			function podsUpdateAddress() {
				if ( typeof address === 'object' ) {

					if ( fieldType === 'address' ) {
						// Reset line_1 since this is made of two parts from Google (street_number and route)
						if ( fields.line_1.length ) {
							fields.line_1.val( '' );
						}
						$.each( address, function ( i, address_component ) {
							if ( fields.line_1.length && address_component.types[ 0 ] === "street_number" ) {
								fields.line_1.val( ' ' + address_component.long_name );
							}
							if ( fields.line_1.length && address_component.types[ 0 ] === "route" ) {
								fields.line_1.val( address_component.long_name + fields.line_1.val() );
							}
							if ( fields.city.length && address_component.types[ 0 ] === "locality" ) {
								fields.city.val( address_component.long_name );
							}
							if ( fields.country.length && address_component.types[ 0 ] === "country" ) {
								if ( fields.country.is( 'select' ) ) {
									fields.country.val( address_component.short_name );
								} else {
									fields.country.val( address_component.long_name );
								}
							}
							if ( fields.region.length && address_component.types[ 0 ] === "administrative_area_level_1" ) {
								if ( fields.region.is( 'select' ) ) {
									// @todo Validate for US states
									fields.region.val( address_component.short_name );
								} else {
									fields.region.val( address_component.long_name );
								}
							}
							if ( fields.postal_code.length && address_component.types[ 0 ] === "postal_code" ) {
								fields.postal_code.val( address_component.long_name );
							}
						} );

					} else if ( fieldType === 'text' ) {

						if ( fields.text.length ) {
							// Reset value
							fields.text.val( '' );
							$.each( address, function ( i, address_component ) {
								if ( address_component.long_name !== '' ) {
									if ( address_component.types[ 0 ] === "route" ) {
										fields.text.val( address_component.long_name + fields.text.val() );
									} else if ( address_component.types[ 0 ] === "street_number" ) {
										fields.text.val( ' ' + address_component.long_name );
									} else {
										if ( fields.text.val() === '' ) {
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

			//------------------------------------------------------------------------
			// Formatting and variable handlers
			//
			function podsMergeAddressFields() {
				var tmpAddress = [];
				if ( fields.line_1.length ) {
					tmpAddress.push( fields.line_1.val() );
				}
				if ( fields.line_2.length ) {
					tmpAddress.push( fields.line_2.val() );
				}
				if ( fields.city.length ) {
					tmpAddress.push( fields.city.val() );
				}
				if ( fields.postal_code.length ) {
					tmpAddress.push( fields.postal_code.val() );
				}
				if ( fields.region.length ) {
					tmpAddress.push( fields.region.val() );
				}
				if ( fields.country.length ) {
					tmpAddress.push( fields.country.val() );
				}
				address = tmpAddress.join( ', ' );
				return address;
			}

			function podsFormatFieldsToHTML( html ) {
				// Convert magic tags to field values or remove them
				$.each( fields, function( key, field ) {
					if ( field.length && field.val().length ) {
						html = html.replace( '{{' + key + '}}', field.val() );
					} else {
						// Replace with {{PODS}} so we can remove this line if needed
						html = html.replace( '{{' + key + '}}', '{{REMOVE}}' );
					}
				} );
				// Remove empty lines
				var lines = html.split( '<br>' );
				$.each( lines, function( key, line ) {
					if ( line === '{{REMOVE}}' ) {
						// Delete the key it this line only has {{REMOVE}}
						delete lines[ key ];
					} else {
						// Remove {{REMOVE}}
						lines[ key ] = line.replace( '{{REMOVE}}', '' )
					}
				} );
				// Reset array keys and join it back together
				html = lines.filter( function () {return true;} ).join( '<br>' );
				return html;
			}

		}

	} ); // end window load
} ); // end document ready
</script>
