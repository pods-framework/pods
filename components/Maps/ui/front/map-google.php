<?php
/**
 * @var array  $value
 * @var string $name
 * @var array  $options
 * @var bool   $multiple  Value contains an array of multiple values?
 */

wp_enqueue_script( 'googlemaps' );
wp_enqueue_script( 'pods-maps' );
wp_enqueue_style( 'pods-maps' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, '', $options );

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

if ( ! empty( $map_options['marker'] ) ) {
	$map_options['marker'] = wp_get_attachment_image_url( $map_options['marker'], 'full' );
}

if ( ! array_key_exists( 0, $value ) ) {
	$value = array( $value );
	$multiple = false;
}

foreach( $value as $key => $val ) {
	if ( ! isset( $address_html ) || $multiple ) {
		// @todo Check field type
		if ( ! empty( $val['info_window'] ) ) {
			$format = $val['info_window'];
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

	if ( ! empty( $val['marker_icon'] ) && is_numeric( $val['marker_icon'] ) ) {
		$value[ $key ]['marker_icon'] = wp_get_attachment_image_url( $val['marker_icon'], 'full' );
	}
}

?>
<div id="<?php echo esc_attr( $attributes['id'] . '-map-canvas' ); ?>"
	class="pods-address-maps-map-canvas"
	data-value="<?php echo esc_attr( json_encode( $value ) ); ?>"></div>

<script type="text/javascript">
	jQuery( document ).ready( function ( $ ) {
		var mapCanvas = document.getElementById( '<?php echo esc_attr( $attributes['id'] . '-map-canvas' ); ?>' ),
			value = $( '#<?php echo esc_attr( $attributes['id'] . '-map-canvas' ); ?>' ).attr('data-value'),
			latlng = null,
			mapOptions = {
				center: new google.maps.LatLng( 41.850033, -87.6500523 ), // default (Chicago)
				marker: '<?php echo esc_attr( $map_options['marker'] ); ?>',
				zoom: <?php echo absint( $map_options['zoom'] ); ?>,
				mapTypeId: '<?php echo esc_attr( $map_options['type'] ); ?>',
				scrollwheel: <?php echo ( $map_options['scrollwheel'] ) ? 'true' : 'false'; ?>
			},
			marker_icon = <?php echo ( ! empty( $map_options['marker'] ) ? '\'' . esc_url( $map_options['marker'] ) . '\'' : 'null' ) ?>;

		if ( value ) {
			try {
				value = JSON.parse( value );
			} catch ( err ) {
				return;
			}
		} else {
			return;
		}

		//------------------------------------------------------------------------
		// Initialize the map, set default center to the first item.
		//
		if ( value.length && value[0].hasOwnProperty('geo') ) {
			latlng = value[0].geo;
			mapOptions.center = new google.maps.LatLng( latlng );
		}

		var map = new google.maps.Map( mapCanvas, mapOptions );
		var bounds = new google.maps.LatLngBounds();
		//var geocoder = new google.maps.Geocoder();

		//------------------------------------------------------------------------
		// Add the items.
		//
		var autoOpenInfoWindow = ( 1 === value.length );
		$.each( value, function( i, val ) {

			if ( value[ i ].hasOwnProperty('geo') ) {

				//------------------------------------------------------------------------
				// Initialize marker.
				//
				value[ i ].markerOptions = {
					map : map,
					draggable: false
				};

				value[ i ].markerOptions.position = value[ i ].geo;

				if ( value[ i ].hasOwnProperty('marker_icon') ) {
					value[ i ].markerOptions.icon = value[ i ].marker_icon;
				} else if ( marker_icon ) {
					value[ i ].markerOptions.icon = marker_icon;
				}

				// Add the marker.
				value[ i ].marker = new google.maps.Marker( value[ i ].markerOptions );
				bounds.extend( value[ i ].markerOptions.position );

				//------------------------------------------------------------------------
				// Initialize info window.
				//
				if ( value[ i ].address_html ) {

					value[ i ].infowindowOpen = function( open ) {
						if ( ! this.infowindow ) {
							this.infowindow = new google.maps.InfoWindow();
						}

						this.infowindow.setContent( this.address_html );
						if ( open ) {
							this.infowindow.open( map, this.marker );
						}
					};

					value[ i ].infowindowOpen( autoOpenInfoWindow );

					// InfoWindow trigger
					google.maps.event.addListener( value[ i ].marker, 'click', function () {
						value[ i ].infowindowOpen( true );
					} );
				}
			}

		} );

		if ( 1 < value.length ) {

			// Automatic sizing for multiple markers.
			map.fitBounds( bounds );
			map.panToBounds( bounds );
			mapOptions.center = map.getCenter();

			//(optional) restore the zoom level after the map is done scaling
			/*var listener = google.maps.event.addListener( map, "idle", function () {
				map.setZoom( mapOptions.zoom );
				google.maps.event.removeListener( listener );
			} );*/

		} else {
			map.setCenter( mapOptions.center );
		}

	} ); // end document ready
</script>