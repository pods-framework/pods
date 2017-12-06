<?php
/**
 * @var array  $value {
 *     @type  array       $address {
 *         @type  string  $line_1
 *         @type  string  $line_2
 *         @type  string  $postal_code
 *         @type  string  $city
 *         @type  string  $region
 *         @type  string  $country
 *     }
 *     @type  array       $geo {
 *         @type  int  $lat
 *         @type  int  $lng
 *     }
 *     @type  string      $info_window  The info window content (with magic tags)
 *     @type  int|string  $marker_icon  (optional) Overwrite default marker from $options?
 *     @type  Pods        $pod          (optional) The pod object for this value.
 *     @type  string      $name         (optional) The pod name.
 *     @type  int         $id           (optional) The ID of this value object.
 * }
 * @var array  $options {
 *     @type  int         $maps_zoom         The default zoom depth.
 *     @type  string      $maps_type         The maps type
 *     @type  int|string  $maps_marker       The marker (can be an attachment ID or a URL)
 *     @type  bool        $maps_scrollwheel  Enable/Disable the scrollwheel?
 * }
 * @var string $name
 * @var string $type
 * @var bool   $multiple  Value contains an array of multiple values?
 */

wp_enqueue_script( 'googlemaps' );
wp_enqueue_script( 'pods-maps' );
wp_enqueue_style( 'pods-maps' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, $type, $options );

$map_options = array();

if ( ! empty( $options['maps_zoom'] ) ) {
	$map_options['zoom'] = (int) $options['maps_zoom'];
} else {
	$map_options['zoom'] = (int) pods_v( 'map_zoom', Pods_Component_Maps::$options );
}

if ( ! empty( $options['maps_type'] ) ) {
	$map_options['type'] = $options['maps_type'];
} else {
	$map_options['type'] = pods_v( 'map_type', Pods_Component_Maps::$options );
}

if ( ! empty( $options['maps_marker'] ) ) {
	$map_options['marker'] = $options['maps_marker'];
} else {
	$map_options['marker'] = pods_v( 'map_marker', Pods_Component_Maps::$options );
}

$map_options['scrollwheel'] = (bool) pods_v( 'maps_scrollwheel', $options, pods_v( 'map_scrollwheel', Pods_Component_Maps::$options, true ) );

if ( ! empty( $map_options['marker'] ) && is_numeric( $map_options['marker'] ) ) {
	$map_options['marker'] = wp_get_attachment_image_url( $map_options['marker'], 'full' );
}

if ( ! $multiple ) {
	$value = array( $value );
	$multiple = false;
}
foreach( $value as $key => $val ) {

	$val = wp_parse_args( $val, array(
		'address'      => array(),
		'geo'          => array(),
		'address_html' => '',
		'info_window'  => '', // Format.
		'marker_icon'  => null,
	) );

	// Allow custom overwrites.
	if ( 'custom' === pods_v( 'maps_info_window_content', $options, true ) ) {
		$address_html = '';
		if ( ! empty( $val['address_html'] ) ) {
			$address_html = $val['address_html'];
		} elseif ( ! empty( $val['info_window'] ) ) {
			$address_html = $val['info_window'];
		}
	}

	// Parse format.
	elseif ( ! isset( $address_html ) || $multiple ) {
		// @todo Check field type
		if ( ! empty( $val['info_window'] ) ) {
			$format = $val['info_window'];

		} elseif ( pods_components()->is_component_active( 'templates' ) &&
				   'template' === pods_v( 'maps_info_window_content', $options ) &&
				   isset( $val['pod'] ) && $val['pod'] instanceof Pods
		) {
			$template = get_post( pods_v( 'maps_info_window_template', $options ) );
			if ( $template instanceof WP_Post ) {
				$format = $val['pod']->template( $template->post_title );
				echo $format;
			}

		} else {
			$format = PodsForm::field_method( 'address', 'default_display_format' );
			if ( 'custom' === pods_v( 'address_display_type', $options ) ) {
				$format = pods_v( 'address_display_type_custom', $options );
			}
		}
		$address_html = PodsForm::field_method( 'address', 'format_to_html', $format, $val, $options );
	}

	unset( $value[ $key ]['info_window'] );
	$value[ $key ]['address_html'] = $address_html;

	if ( is_numeric( $val['marker_icon'] ) ) {
		$value[ $key ]['marker_icon'] = wp_get_attachment_image_url( $val['marker_icon'], 'full' );
	}

	if ( is_array( $val['geo'] ) ) {
		$value[ $key ]['geo'] = array_map( 'floatval', $val['geo'] );
	}

	unset( $value[ $key ]['pod'] );
}

if ( ! empty( $options['maps_combine_equal_geo'] ) ) {

	// User array keys to match locations.
	$combined_values = array();
	foreach ( $value as $key => $val ) {
		$geo_key = implode( ',', $val['geo'] );
		if ( array_key_exists( $geo_key, $combined_values ) ) {
			$combined_values[ $geo_key ]['address_html'] .= $val['address_html'];
			continue;
		}
		$combined_values[ $geo_key ] = $val;
	}

	// Reset array keys.
	$value = array();
	foreach ( $combined_values as $val ) {
		$value[] = $val;
	}
}
?>
<div id="<?php echo esc_attr( $attributes['id'] . '-map-canvas' ); ?>"
	class="pods-maps-map-canvas pods-<?php echo $type ?>-maps-map-canvas"
	data-value="<?php echo esc_attr( json_encode( $value ) ); ?>"></div>

<script type="text/javascript">
	jQuery( document ).ready( function ( $ ) {
		var mapCanvas = document.getElementById( '<?php echo esc_attr( $attributes['id'] . '-map-canvas' ); ?>' ),
			values = $( '#<?php echo esc_attr( $attributes['id'] . '-map-canvas' ); ?>' ).attr('data-value'),
			latlng = null,
			mapOptions = {
				center: new google.maps.LatLng( 41.850033, -87.6500523 ), // default (Chicago)
				marker: '<?php echo esc_attr( $map_options['marker'] ); ?>',
				zoom: <?php echo absint( $map_options['zoom'] ); ?>,
				mapTypeId: '<?php echo esc_attr( $map_options['type'] ); ?>',
				scrollwheel: <?php echo ( $map_options['scrollwheel'] ) ? 'true' : 'false'; ?>
			},
			marker_icon = <?php echo ( ! empty( $map_options['marker'] ) ? '\'' . esc_url( $map_options['marker'] ) . '\'' : 'null' ) ?>;

		if ( values ) {
			try {
				values = JSON.parse( values );
			} catch ( err ) {
				return;
			}
		} else {
			return;
		}

		//------------------------------------------------------------------------
		// Initialize the map, set default center to the first item.
		//
		if ( values.length && values[0].hasOwnProperty('geo') ) {
			latlng = values[0].geo;
			mapOptions.center = new google.maps.LatLng( latlng );
		}

		var map = new google.maps.Map( mapCanvas, mapOptions );
		var bounds = new google.maps.LatLngBounds();
		//var geocoder = new google.maps.Geocoder();

		// If there are more than one markers, do not open an infowindow on load.
		var autoOpenInfoWindow = ( 1 === values.length );

		//------------------------------------------------------------------------
		// Add the items.
		//
		$.each( values, function( i, val ) {

			if ( values[ i ].hasOwnProperty('geo') ) {

				//------------------------------------------------------------------------
				// Initialize marker.
				//
				values[ i ].markerOptions = {
					map : map,
					draggable: false
				};

				values[ i ].markerOptions.position = values[ i ].geo;

				if ( values[ i ].hasOwnProperty('marker_icon') ) {
					values[ i ].markerOptions.icon = values[ i ].marker_icon;
				} else if ( marker_icon ) {
					values[ i ].markerOptions.icon = marker_icon;
				}

				// Add the marker.
				values[ i ].marker = new google.maps.Marker( values[ i ].markerOptions );
				bounds.extend( values[ i ].markerOptions.position );

				//------------------------------------------------------------------------
				// Initialize info window.
				//
				if ( values[ i ].address_html ) {

					values[ i ].infowindowOpen = function( open ) {
						if ( ! this.infowindow ) {
							this.infowindow = new google.maps.InfoWindow();
						}

						// Close other intowindows.
						$.each( values, function( i, val ) {
							if ( values[ i ].hasOwnProperty('infowindow') ) {
								values[ i ].infowindow.close();
							}
						} );

						this.infowindow.setContent( this.address_html );
						if ( open ) {
							this.infowindow.open( map, this.marker );
						}
					};

					values[ i ].infowindowOpen( autoOpenInfoWindow );

					// InfoWindow trigger
					google.maps.event.addListener( values[ i ].marker, 'click', function () {
						values[ i ].infowindowOpen( true );
					} );
				}
			}

		} );

		if ( 1 < values.length ) {

			// Automatic sizing for multiple markers.
			map.fitBounds( bounds );
			map.panToBounds( bounds );
			mapOptions.center = map.getCenter();

			var listener = google.maps.event.addListener( map, "idle", function () {
				// If the current zoom is higher than the original zoom (due to fitBounds) set it to the original.
				if ( map.getZoom() > mapOptions.zoom ) {
					map.setZoom( mapOptions.zoom );
					google.maps.event.removeListener( listener );
				}
			} );

		} else {
			map.setCenter( mapOptions.center );
		}

	} ); // end document ready
</script>
