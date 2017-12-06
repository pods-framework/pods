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

		if ( ! empty( $data['address_components'] ) ) {
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

			switch ( $component['types'] ) {
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
					$address['region'][] = $value;
					break;
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

		return array();
	}

}