<?php
wp_enqueue_script( 'googlemaps' );
wp_enqueue_script( 'pods-maps' );

$attributes = array();
$attributes = PodsForm::merge_attributes( $attributes, $name, '', $options );

$map_options = array();
if ( ! empty( $options[ 'maps_zoom' ] ) ) {
	$map_options['zoom'] = (int) $options[ 'maps_zoom' ];
} else {
	$map_options['zoom'] = (int) Pods_Component_Maps::$options['map_zoom'];
}
if ( ! empty( $options[ 'maps_type' ] ) ) {
	$map_options['type'] = $options[ 'maps_type' ];
} else {
	$map_options['type'] = Pods_Component_Maps::$options['map_type'];
}
if ( ! empty( $options[ 'maps_marker' ] ) ) {
	$map_options['marker'] = $options[ 'maps_marker' ];
} else {
	$map_options['marker'] = Pods_Component_Maps::$options['map_marker'];
}

if ( ! isset( $address_html ) ) {
	// @todo Check field type
	$format = PodsForm::field_method( 'address', 'default_display_format' );
	if ( $options['address_display_type'] == 'custom' ) {
		$format = $options['address_display_type_custom'];
	}
	$address_html = PodsForm::field_method( 'address', 'format_to_html', $format, $value, $options );
}
$value['address_html'] = $address_html;

?>
<div id="<?php echo $attributes['id'] . '-map-canvas' ?>" class="pods-address-maps-map-canvas" data-value='<?php echo json_encode( $value ) ?>'></div>

<script type="text/javascript">
	document.addEventListener( "DOMContentLoaded", function() {
		jQuery( document ).ready( function ( $ ) {
			var mapDiv = document.getElementById( '<?php echo $attributes['id'] . '-map-canvas' ?>' );
			var value = $( '#<?php echo $attributes['id'] . '-map-canvas' ?>' ).attr('data-value');
			var address = '<?php echo $address; ?>';

			var map = null;
			var geocoder = null;
			var marker = null;
			var mapOptions = {
				center: null, // default (Chicago)
				marker: '<?php echo $map_options['marker'] ?>',
				zoom: <?php echo $map_options['zoom'] ?>,
				type: '<?php echo $map_options['type'] ?>'
			};

			//------------------------------------------------------------------------
			// Initialze the map
			//
			//(function () {

				var center = new google.maps.LatLng( 40.026, -82.936 );

				map = new google.maps.Map( mapDiv, {
					center : center, zoom : zoom, mapTypeId : google.maps.MapTypeId.ROADMAP
				} );

				geocoder = new google.maps.Geocoder();

				//------------------------------------------------------------------------
				// Geolocate from the address
				//
				geocoder.geocode( {'address' : address}, function ( results, status ) {

					if ( status == google.maps.GeocoderStatus.OK ) {
						var latField, lngField;
						var location = results[0].geometry.location;

						// Center the map and set the lat/lng values
						map.setCenter( location );

						// Set the marker options
						var markerOptions = {
							map : map, position : location, draggable : true
						};

						// Create a new marker, if needed, and set the event listeners
						if ( !marker ) {
							marker = new google.maps.Marker( markerOptions );
						}
						// Marker is already set, just update its options
						else {
							marker.setOptions( markerOptions );
						}
					}
					// Geocode failure
					else {
						alert( "Geocode was not successful for the following reason: " + status );
					}

				} ); // end geocode

			//})();

		} ); // end document ready
	}, false );
</script>