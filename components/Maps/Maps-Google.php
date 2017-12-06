<?php

class Pods_Component_Maps_Google implements Pods_Component_Maps_Provider {

	public static $geocode_url = '';

	public function __construct() {

		self::$geocode_url = apply_filters( 'pods_maps_google_geocode_url', 'https://maps.googleapis.com/maps/api/geocode/json' );
	}

	public function assets() {

		if ( ! empty( Pods_Component_Maps::$api_key ) ) {
			wp_register_script( 'googlemaps', '//maps.googleapis.com/maps/api/js?key=' . Pods_Component_Maps::$api_key, false, '3' ); //sensor=false&
			wp_register_script( 'googlemaps-static', '//maps.googleapis.com/maps/api/staticmap?key=' . Pods_Component_Maps::$api_key, false, '3' ); //sensor=false&
		}

	}

	/**
	 * Add options to the maps component.
	 * @inheritdoc
	 */
	public function options( $options = array() ) {

		$options['api_key'] = array(
			'label'   => __( 'Maps API Key or Client ID', 'pods' ),
			'help'    => __( 'help', 'pods' ),
			'default' => '',
			'type'    => 'text',
		);

		/*$options['google_client_id'] = array(
			'label'       => __( 'Google Maps Client ID', 'pods' ),
			'help'        => __( 'For use with Google Maps API for Business and Geocoding; A Client ID does not come with the Free edition.', 'pods' ),
			'includes-on' => array( 'provider' => 'google' ),
			'default'     => '',
			'type'        => 'text',
		);*/

		$options['maps_style'] = array(
			'label'   => __( 'Default Map Output Type', 'pods' ),
			'default' => 'static',
			'type'    => 'pick',
			'data'    => array(
				'static' => __( 'Static (Image)', 'pods' ),
				'js'     => __( 'Javascript (Interactive)', 'pods' ),
			),
		);

		$options['maps_type'] = array(
			'label'   => __( 'Default Map Type', 'pods' ),
			'default' => 'roadmap',
			'type'    => 'pick',
			'data'    => array(
				'roadmap'   => __( 'Roadmap', 'pods' ),
				'satellite' => __( 'Satellite', 'pods' ),
				'terrain'   => __( 'Terrain', 'pods' ),
				'hybrid'    => __( 'Hybrid', 'pods' ),
			),
		);

		$options['maps_zoom'] = array(
			'label'   => __( 'Default Map Zoom Level', 'pods' ),
			'help'    => array(
				__( 'Google Maps has documentation on the different zoom levels you can use.', 'pods' ),
				'https://developers.google.com/maps/documentation/javascript/tutorial#zoom-levels',
				//'https://developers.google.com/maps/documentation/staticmaps/#Zoomlevels'
			),
			'default' => 12,
			'type'    => 'number',
			'options' => array(
				'number_decimals'   => 0, // 2
				'number_max_length' => 2,
				'number_min'        => 1,
				'number_max'        => 21,
				'number_format'     => '9999.99',
				//'number_format_type' => 'slider',
			),
		);

		$options['maps_scrollwheel'] = array(
			'label'   => __( 'Enable scrollwheel?', 'pods' ),
			'default' => 1,
			'type'    => 'boolean',
		);

		$options['maps_marker'] = array(
			'label'   => __( 'Default Map Custom Marker', 'pods' ),
			'type'    => 'file',
			'options' => array(
				'file_uploader'          => 'plupload',
				'file_edit_title'        => 0,
				'file_restrict_filesize' => '1MB',
				'file_type'              => 'images',
				'file_add_button'        => __( 'Upload Marker Icon', 'pods' ),
			),
		);

		return $options;
	}

	/**
	 * Add options to the maps fields.
	 * @inheritdoc
	 */
	public function field_options( $options = array(), $type = '' ) {

		$options['maps_style'] = array(
			'label'      => __( 'Map Output Type', 'pods' ),
			'depends-on' => array( 'maps' => true ),
			'default'    => pods_v( 'maps_style', Pods_Component_Maps::$options, 'static', true ),
			'type'       => 'pick',
			'data'       => array(
				'static' => __( 'Static (Image)', 'pods' ),
				'js'     => __( 'Javascript (Interactive)', 'pods' ),
			),
		);

		$options['maps_type'] = array(
			'label'      => __( 'Map Type', 'pods' ),
			'depends-on' => array( 'maps' => true ),
			'default'    => pods_v( 'maps_type', Pods_Component_Maps::$options, 'roadmap', true ),
			'type'       => 'pick',
			'data'       => array(
				'roadmap'   => __( 'Roadmap', 'pods' ),
				'satellite' => __( 'Satellite', 'pods' ),
				'terrain'   => __( 'Terrain', 'pods' ),
				'hybrid'    => __( 'Hybrid', 'pods' ),
			),
		);

		$options['maps_zoom'] = array(
			'label'      => __( 'Map Zoom Level', 'pods' ),
			'depends-on' => array( 'maps' => true ),
			'help'       => array(
				__( 'Google Maps has documentation on the different zoom levels you can use.', 'pods' ),
				'https://developers.google.com/maps/documentation/javascript/tutorial#zoom-levels',
				//'https://developers.google.com/maps/documentation/staticmaps/#Zoomlevels'
			),
			'default'    => pods_v( 'maps_zoom', Pods_Component_Maps::$options, 12, true ),
			'type'       => 'number',
			'options'    => array(
				'number_decimals'   => 0, // 2
				'number_max_length' => 2,
				'number_min'        => 1,
				'number_max'        => 21,
				'number_format'     => '9999.99',
				//'number_format_type' => 'slider',
			),
		);

		$options['maps_scrollwheel'] = array(
			'label'   => __( 'Enable scroll wheel?', 'pods' ),
			'default' => 1,
			'type'    => 'boolean',
		);

		$options['maps_info_window'] = array(
			'label'      => __( 'Display an Info Window', 'pods' ),
			'default'    => 0,
			'type'       => 'boolean',
			'depends-on' => array( 'maps' => true ),
			'dependency' => true,
		);

		$options['maps_info_window_content'] = array(
			'label'      => __( 'Info Window content', 'pods' ),
			'depends-on' => array(
				'maps'             => true,
				'maps_info_window' => true,
			),
			'default'    => 'paragraph',
			'type'       => 'pick',
			'data'       => array(
				'paragraph'    => __( 'Custom', 'pods' ),
				'wysiwyg'      => __( 'Custom (WYSIWYG)', 'pods' ),
				// @todo 'display_type' is only available for field type 'address'
				'display_type' => __( 'Display Type', 'pods' ),
			),
		);

		if ( pods_components()->is_component_active( 'templates' ) ) {
			//$titles = (array) $this->get_template_titles();
			//if ( ! empty( $title ) ) {
			$options['maps_info_window_content']['data']['template'] = __( 'Template', 'pods' );
			$options['maps_info_window_template'] = array(
				'label'      => __( 'Info Window template', 'pods' ),
				'depends-on' => array(
					'maps'                     => true,
					'maps_info_window'         => true,
					'maps_info_window_content' => 'template',
				),
				'default'    => 'true',
				'type'       => 'pick',
				'data'       => (array) Pods_Component_Maps::get_template_titles(),//array_combine( $titles, $titles ),
				'pick_format_type'   => 'single',
				'pick_format_single' => 'dropdown',
			);
			//}
		}

		$options['maps_marker'] = array(
			'label'      => __( 'Map Custom Marker', 'pods' ),
			'depends-on' => array( 'maps' => true ),
			'default'    => pods_v( 'maps_marker', Pods_Component_Maps::$options ),
			'type'       => 'file',
			'options'    => array(
				'file_uploader'          => 'plupload',
				'file_edit_title'        => 0,
				'file_restrict_filesize' => '1MB',
				'file_type'              => 'images',
				'file_add_button'        => 'Upload Marker Icon',
			),
		);

		return $options;
	}

	public function field_input_view() {

		$view = false;
		if ( ! empty( Pods_Component_Maps::$api_key ) ) {
			$view = plugin_dir_path( __FILE__ ) . 'ui/fields/map-google.php';
		}

		return $view;
	}

	public function field_display_view() {

		$view = false;
		if ( ! empty( Pods_Component_Maps::$api_key ) ) {
			$view = plugin_dir_path( __FILE__ ) . 'ui/front/map-google.php';
		}

		return $view;
	}

	/**
	 * Geocode an address with given data
	 *
	 * @param string|array $data Any type of address data
	 * @param string       $api_key
	 *
	 * @return array Latitude, Longitude (format: array( 'lat' => value, 'lng' => value ) )
	 *
	 * @public
	 * @static
	 * @since 2.7
	 */
	public static function geocode_address( $data, $api_key = '' ) {

		$data = self::geocode( $data, $api_key );

		$address = self::get_address( $data );
		$latlng  = self::get_latlng( $data );

		return array( 'address' => $address, 'geo' => $latlng );
	}

	/**
	 * Geocode an address into Latitude and Longitude values
	 *
	 * @param string|array $address Address
	 * @param string       $api_key
	 *
	 * @return array Latitude, Longitude (format: array( 'lat' => value, 'lng' => value ) )
	 *
	 * @public
	 * @static
	 * @since 2.7
	 */
	public static function geocode_address_to_latlng( $address, $api_key = '' ) {

		if ( is_array( $address ) ) {
			foreach ( $address as $key => $val ) {
				if ( is_array( $val ) ) {
					$address[ $key ] = implode( ', ', $val );
				}
			}
			$address = implode( ', ', $address );
		}

		$data = self::geocode( $address, $api_key );

		return self::get_latlng( $data );
	}

	/**
	 * Get address data from Latitude and Longitude values
	 *
	 * @param string|array $lat_lng Lat / long numbers
	 * @param string       $api_key
	 *
	 * @return string Address information
	 *
	 * @public
	 * @static
	 * @since 2.7
	 */
	public static function geocode_latlng_to_address( $lat_lng, $api_key = '' ) {

		if ( is_array( $lat_lng ) ) {
			$lat_lng = implode( ',', array_map( 'floatval', $lat_lng ) );
		}

		$data = self::geocode( $lat_lng, $api_key, 'latlng' );

		return self::get_address( $data );
	}

	/**
	 * Return address data from returned Google data
	 *
	 * @param array $data The data from Google
	 *
	 * @return array
	 *
	 * @public
	 * @static
	 * @since 2.7
	 */
	public static function get_address( $data ) {

		if ( ! empty( $data['results'][0] ) ) {
			$data = $data['results'][0];
		}

		if ( empty( $data['address_components'] ) ) {
			return array();
		}

		$address = array(
			'line_1'      => array(),
			'line_2'      => array(),
			'postal_code' => '',
			'city'        => '',
			'region'      => array(),
			'country'     => '',
		);

		foreach ( $data['address_components'] as $component ) {

			$value = $component['long_name'];

			// https://developers.google.com/maps/documentation/javascript/geocoding#GeocodingAddressTypes
			switch ( $component['types'][0] ) {
				case 'street_number':
					$address['line_1'][1] = $value;
					break;
				case 'route':
					$address['line_1'][0] = $value;
					break;
				case 'locality':
					$address['city'] = $value;
					break;
				case 'country':
					$address['country'] = $value;
					break;
				case 'postal_code':
					$address['postal_code'] = $value;
					break;
				case 'administrative_area_level_1':
				case 'administrative_area_level_2':
				case 'administrative_area_level_3':
				case 'administrative_area_level_4':
				case 'administrative_area_level_5':
					$address['region'][ $component['types'][0] ] = $value;
					break;
			}
		}

		/**
		 * Change the values and format passed by Google Maps.
		 * @param  array  $address             The parsed value.
		 * @param  array  $address_components  The address parts from Google Maps API.
		 */
		$address = apply_filters( 'pods_component_maps_google_get_address', $address, $data['address_components'] );

		foreach ( $address as $key => $parts ) {
			if ( is_array( $parts ) ) {
				ksort( $address[ $key ] );
			}
		}

		foreach ( $address as $key => $value ) {
			if ( is_array( $value ) ) {
				$address[ $key ] = implode( ' ', $value );
			}
		}

		return $address;
	}

	/**
	 * Return lat/lng data from returned Google data
	 *
	 * @param array $data The data from Google
	 *
	 * @return array
	 *
	 * @public
	 * @static
	 * @since 2.7
	 */
	public static function get_latlng( $data ) {

		if ( ! empty( $data['results'][0] ) ) {
			$data = $data['results'][0];
		}

		if ( empty( $data['geometry']['location'] ) ) {
			return array();
		}

		return array_map( 'floatval', $data['geometry']['location'] );
	}

	/**
	 * Call to Google Maps API
	 *
	 * @param string|array $data
	 * @param string       $api_key Optional
	 * @param string       $type    ( address | latlng )
	 *
	 * @return array
	 *
	 * @public
	 * @static
	 * @since 2.7
	 */
	public static function geocode( $data, $api_key = '', $type = 'address' ) {

		if ( is_array( $data ) ) {
			$data = implode( ',', $data );
		}

		$url = self::$geocode_url . '?' . $type . '=' . $data;
		/*if ( ! empty( $api_key ) ) {
			$url .= '&key=' . $api_key;
		}*/

		$post = wp_remote_post( $url );

		if ( ! empty( $post['body'] ) ) {
			$data = json_decode( $post['body'], true );
			if ( ! empty( $data['results'][0] ) ) {
				return $data['results'][0];
			}
		}

		// Try again once.
		$post = wp_remote_post( $url );

		if ( ! empty( $post['body'] ) ) {
			$data = json_decode( $post['body'], true );
			if ( ! empty( $data['results'][0] ) ) {
				return $data['results'][0];
			}
		}

		return array();
	}

}