<?php

/**
 * Name: Maps
 *
 * Menu Name: Maps
 *
 * Description:
 *
 * Version: 1.0
 *
 * Category: Field Types
 *
 * Class: Pods_Component_Maps
 */
class Pods_Component_Maps extends PodsComponent {

	static $component_path;

	static $component_file;

	static $options;

	public function __construct() {
		// See https://github.com/pods-framework/pods/pull/3711
		add_filter( 'pods_admin_setup_edit_address_additional_field_options', array( $this, 'maps_options' ), 10, 2 );
	}

	/**
	 * Enqueue styles
	 *
	 * @since 1.0
	 */
	public function admin_assets() {

		wp_enqueue_style( 'pods-admin' );

	}

	/**
	 * Register the component
	 *
	 * @param $components
	 *
	 * @return array
	 * @since 1.0
	 */
	public static function component_register( $components ) {

		$components[] = array( 'File' => realpath( self::$component_file ) );

		return $components;

	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function options( $settings ) {

		$options = array(
			'provider'                => array(
				'label'      => __( 'Maps Provider', 'pods' ),
				'help'       => __( 'help', 'pods' ),
				'default'    => 'google',
				'type'       => 'pick',
				'data'       => apply_filters( 'pods_component_maps_providers', array(
					'google' => __( 'Google Maps', 'pods' ),
					//'bing' => __( 'Bing Maps', 'pods' ),
					//'openstreetmap' => __( 'OpenStreetMap', 'pods' ),
				) ),
				'dependency' => true
			),
			'api_key'                 => array(
				'label'   => __( 'Maps API Key', 'pods' ),
				'help'    => __( 'help', 'pods' ),
				'default' => '',
				'type'    => 'text'
			),
			'google_client_id'        => array(
				'label'       => __( 'Google Maps Client ID', 'pods' ),
				'help'        => __( 'For use with Google Maps API for Business and Geocoding; A Client ID does not come with the Free edition.', 'pods' ),
				'includes-on' => array( 'provider' => 'google' ),
				'default'     => '',
				'type'        => 'text'
			),
			'address_map_style'       => array(
				'label'   => __( 'Default Map Output Type', 'pods' ),
				'default' => 'static',
				'type'    => 'pick',
				'data'    => array(
					'static' => __( 'Static (Image)', 'pods' ),
					'js'     => __( 'Javascript (Interactive)', 'pods' )
				)
			),
			'address_map_type_of_map' => array(
				'label'   => __( 'Default Map Type', 'pods' ),
				'default' => 'roadmap',
				'type'    => 'pick',
				'data'    => array(
					'roadmap'   => __( 'Roadmap', 'pods' ),
					'satellite' => __( 'Satellite', 'pods' ),
					'terrain'   => __( 'Terrain', 'pods' ),
					'hybrid'    => __( 'Hybrid', 'pods' )
				)
			),
			'address_map_zoom'        => array(
				'label'   => __( 'Default Map Zoom Level', 'pods' ),
				'help'    => array(
					__( 'Google Maps has documentation on the different zoom levels you can use.', 'pods' ),
					'https://developers.google.com/maps/documentation/staticmaps/#Zoomlevels'
				),
				'default' => 12,
				'type'    => 'number',
				'options' => array(
					'number_decimals'   => 0,
					'number_max_length' => 2
				)
			),
			'address_map_marker'      => array(
				'label'   => __( 'Default Map Custom Marker', 'pods' ),
				'type'    => 'file',
				'options' => array(
					'file_uploader'          => 'plupload',
					'file_edit_title'        => 0,
					'file_restrict_filesize' => '1MB',
					'file_type'              => 'images',
					'file_add_button'        => 'Upload Marker Icon'
				)
			)
		);

		return $options;

	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function handler( $options ) {

		self::$options = $options;

	}

	public function maps_options( $options, $type ) {

		// Add lat/lng input type
		$options[ $type . '_type' ]['data']['lat-lng'] = __( 'Latitude / Longitude', 'pods' );

		// Add Map display types
		$options[ $type . '_display_type' ]['data']['map'] = __( 'Map', 'pods' );
		$options[ $type . '_display_type' ]['data']['default-map'] = __( 'Default and map', 'pods' );
		$options[ $type . '_display_type' ]['data']['custom-map'] = __( 'Custom and map', 'pods' );

		// Add extra options
		$options[ $type . '_style' ] = array(
			'label'      => __( 'Map Output Type', 'pods' ),
			'depends-on' => array( $type . '_display_type' => array( 'map', 'default-map', 'custom-map' ) ),
			'default'    => pods_v( $type . '_style', self::$options, 'static', true ),
			'type'       => 'pick',
			'data'       => array(
				'static' => __( 'Static (Image)', 'pods' ),
				'js'     => __( 'Javascript (Interactive)', 'pods' )
			)
		);
		$options[ $type . '_type_of_map' ] = array(
			'label'      => __( 'Map Type', 'pods' ),
			'depends-on' => array( $type . '_display_type' => array( 'map', 'default-map', 'custom-map' ) ),
			'default'    => pods_v( $type . '_type', self::$options, 'roadmap', true ),
			'type'       => 'pick',
			'data'       => array(
				'roadmap'   => __( 'Roadmap', 'pods' ),
				'satellite' => __( 'Satellite', 'pods' ),
				'terrain'   => __( 'Terrain', 'pods' ),
				'hybrid'    => __( 'Hybrid', 'pods' )
			)
		);
		$options[ $type . '_zoom' ] = array(
			'label'      => __( 'Map Zoom Level', 'pods' ),
			'depends-on' => array( $type . '_display_type' => array( 'map', 'default-map', 'custom-map' ) ),
			'help'       => array(
				__( 'Google Maps has documentation on the different zoom levels you can use.', 'pods' ),
				'https://developers.google.com/maps/documentation/javascript/tutorial#zoom-levels'
				//'https://developers.google.com/maps/documentation/staticmaps/#Zoomlevels'
			),
			'default'    => pods_v( $type . '_zoom', self::$options, 12, true ),
			'type'       => 'number',
			'options'    => array(
				'number_decimals'   => 0,
				'number_max_length' => 2
			)
		);
		$options[ $type . '_marker' ] = array(
			'label'      => __( 'Map Custom Marker', 'pods' ),
			'depends-on' => array( $type . '_display_type' => array( 'map', 'default-map', 'custom-map' ) ),
			'default'    => pods_v( $type . '_marker', self::$options ),
			'type'       => 'file',
			'options'    => array(
				'file_uploader'          => 'plupload',
				'file_edit_title'        => 0,
				'file_restrict_filesize' => '1MB',
				'file_type'              => 'images',
				'file_add_button'        => 'Upload Marker Icon'
			)
		);

		// Add option dependencies
		if ( empty( $options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ] ) ) {
			$options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ] = array( 'custom-map' );
		} else {
			$options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ] = $this->append_dependency(
				$options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ],
				'custom-map'
			);
		}

		if ( empty( $options[ $type . '_microdata' ]['excludes-on'][ $type . '_display_type' ] ) ) {
			$options[ $type . '_microdata' ]['excludes-on'][ $type . '_display_type' ] = array( 'map' );
		} else {
			$options[ $type . '_microdata' ]['excludes-on'][ $type . '_display_type' ] = $this->append_dependency(
				$options[ $type . '_microdata' ]['excludes-on'][ $type . '_display_type' ],
				'map'
			);
		}

		return $options;
	}

	public function append_dependency( $value, $new ) {
		if ( ! is_array( $value ) ) {
			$value = array(
				(string) $value,
				$new
			);
		} else {
			$value[] = $new;
		}
		return $value;
	}

	/**
	 * Geocode a specific address into Latitude and Longitude values
	 *
	 * @param string|array $address Address
	 *
	 * @return array Latitude, Longitude, and Formatted Address values
	 *
	 * @public
	 * @since 2.7
	 */
	public static function geocode_address( $address ) {

		if ( is_array( $address ) ) {
			$address = implode( ', ', $address );
		}

		$address_data = array();

		$post = wp_remote_post( 'https://maps.googleapis.com/maps/api/geocode/json?address=' . $address . '&key=' . self::$api_key );

		if ( ! empty( $post['body'] ) ) {
			$data = json_decode( $post['body'] );

			if ( ! empty( $post['results']['geometry']['location'] ) ) {
				$lat_lng = $post['results']['geometry']['location'];
				$address_data = array_merge( $address_data, $lat_lng );
			}
		}

		return $address_data;

	}

	/**
	 * Get an address from a lat / long
	 *
	 * @param string|array $lat_lng Lat / long numbers
	 *
	 * @return string Address information
	 *
	 * @public
	 * @static
	 * @since 2.7
	 */
	public function geocode_lat_long( $lat_lng ) {

		return '';

	}

	/**
	 * @param $result
	 *
	 * @return array|bool
	 * @since 2.7
	 */
	public function parse_address( $result ) {

		return false;

	}
}