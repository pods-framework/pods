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
				text: $( '#<?php echo $attributes['id'] ?>' ),
				lat: $( '#<?php echo $attributes['id'] . '-lat'  ?>' ),
				lng: $( '#<?php echo $attributes['id'] . '-lng'  ?>' )
			};

			var map = null;
			var geocoder = null;
			var mapOptions = {
				center: new google.maps.LatLng( 41.850033, -87.6500523 ), // default
				marker: '<?php echo $map_options['marker'] ?>',
				zoom: <?php echo $map_options['zoom'] ?>,
				type: '<?php echo $map_options['type'] ?>'
			};

			//------------------------------------------------------------------------
			// Initialze the map
			//
			(function () {

				if ( fields.lat.length && fields.lng.length ) {
					mapOptions.center = new google.maps.LatLng( fields.lat.val(), fields.lng.val() );
				}

				map = new google.maps.Map( mapCanvas, mapOptions );

				geocodeButton.on('click', function(){
					if ( fieldType == 'lat-lng' ) {
						mapOptions.center = new google.maps.LatLng( fields.lat.val(), fields.lng.val() );
					} else {
						if ( fieldType == 'address' ) {
							PodsMaps.geocodeAddressToLatLng( PodsMaps.mergeAddressFromInputs( fields ) );
						} else {
							PodsMaps.geocodeAddressToLatLng( fields.text );
						}
						$(document).on('PodsMapsAjaxDone', function(){
							podsFieldUpdateLatLng( PodsMaps.ajaxResults );
						});
					}
				});

				if ( typeof podsFieldUpdateLatLng == 'undefined' ) {
					function podsFieldUpdateLatLng( latlng ) {
						if ( typeof latlng != 'object' ) {
							// error
						} else {
							console.log(latlng);
							map.setCenter( latlng );
							if ( fields.lat.length ) {
								fields.lat.val( latlng.lat );
							}
							if ( fields.lng.length ) {
								fields.lng.val( latlng.lng );
							}
						}

					}
				}

				//geocoder = new google.maps.Geocoder();

			})();

			//------------------------------------------------------------------------
			// Geolocate from the address
			//
			/*geocodeButton.on( 'click', function ( event ) {

			 event.preventDefault();

			 var address = addressField.val();

			 geocoder.geocode( {'address' : address}, function ( results, status ) {

			 if ( status == google.maps.GeocoderStatus.OK ) {
			 var location = results[0].geometry.location;

			 // Center the map and set the lat/lng values
			 map.setCenter( location );
			 latField.val( location.lat() );
			 lngField.val( location.lng() );

			 // Set the marker options
			 var markerOptions = {
			 map : map, position : location, draggable : true
			 };

			 // Create a new marker, if needed, and set the event listeners
			 if ( !marker ) {
			 marker = new google.maps.Marker( markerOptions );
			 google.maps.event.addListener( marker, 'drag', function () {
			 latField.val( marker.getPosition().lat() );
			 lngField.val( marker.getPosition().lng() );
			 } );
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

			 } ); // end button click event*/

		}

	} ); // end document ready
</script>