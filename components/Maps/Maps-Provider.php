<?php

interface Pods_Component_Maps_Provider {

	/**
	 * Load provider assets.
	 *
	 * @public
	 * @since 2.x
	 */
	public function assets();

	/**
	 * Add options to the maps component.
	 *
	 * @param  array  $options  The component options
	 * @return array
	 *
	 * @public
	 * @since 2.x
	 */
	public function options( $options = array() );

	/**
	 * Add options to the maps fields.
	 *
	 * @param  array   $options  The field options.
	 * @param  string  $type     The field type.
	 * @return array
	 *
	 * @public
	 * @since 2.x
	 */
	public function field_options( $options = array(), $type = '' );

	/**
	 * The input field view file. Used by pods_view();
	 *
	 * @return string
	 *
	 * @public
	 * @since 2.x
	 */
	public function field_input_view();

	/**
	 * The display field view file. Used by pods_view();
	 *
	 * @return string
	 *
	 * @public
	 * @since 2.x
	 */
	public function field_display_view();

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
	 * @since 2.x
	 */
	public static function geocode_address( $data, $api_key = '' );

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
	 * @since 2.x
	 */
	public static function geocode_address_to_latlng( $address, $api_key = '' );

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
	 * @since 2.x
	 */
	public static function geocode_latlng_to_address( $lat_lng, $api_key = '' );

}
