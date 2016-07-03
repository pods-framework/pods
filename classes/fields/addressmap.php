<?php
/**
 * Class PodsField_AddressMap
 * @package Pods\Fields
 */
class PodsField_AddressMap extends PodsField {

	/**
	 * Field Type Group
	 *
	 * @var string
	 * @since 1.0
	 */
	//public static $group = 'Text';

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 1.0
	 */
	public static $type = 'addressmap';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 1.0
	 */
	public static $label = 'Address / Map';

	/**
	 * Field Type Preparation
	 *
	 * @var string
	 * @since 1.0
	 */
	public static $prepare = '%s';

	/**
	 * File path to related files of this field type
	 *
	 * @var string
	 * @since 1.0
	 */
	public static $file_path = '';

	/**
	 * Maps Component Options
	 *
	 * @var array
	 * @since 1.0
	 */
	public static $component_options = array();

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function __construct() {

		if ( class_exists( 'Pods_Component_AddressMaps' ) && ! empty( Pods_Component_AddressMaps::$options ) ) {
			self::$file_path = Pods_Component_AddressMaps::$component_path;

			self::$component_options = Pods_Component_AddressMaps::$options;

			$api_key = ( ! empty( self::$component_options['api_key'] ) ) ? self::$component_options['api_key'] : '';

			wp_register_style( 'pods-component-address-maps', PODS_URL . 'ui/css/pods-address-maps.css', array(), '1.0' );
			wp_register_script( 'pods-component-address-maps', PODS_URL . 'ui/js/pods-address-maps.js', array(), '1.0' );
			wp_register_script( 'googlemaps', 'http://maps.googleapis.com/maps/api/js?key='.$api_key, false, '3' ); //sensor=false&
		}

	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function options() {

		$options = array(
			self::$type . '_type' => array(
				'label'      => __( 'Address Type', 'pods' ),
				'default'    => 'address',
				'type'       => 'pick',
				'data'       => array(
					'address'  => __( 'Address Field Group', 'pods' ),
					'text'     => __( 'Freeform Text', 'pods' ),
					'lat-long' => __( 'Latitude / Longitude', 'pods' )
				),
				'dependency' => true
			),
			self::$type . '_address_options'       => array(
				'label'      => __( 'Address Options', 'pods' ),
				'depends-on' => array( self::$type . '_type' => 'address' ),
				'group'      => array(
					self::$type . '_address_line_1'  => array(
						'label'   => __( 'Enable Address Line 1', 'pods' ),
						'default' => 1,
						'type'    => 'boolean'
					),
					self::$type . '_address_line_2'  => array(
						'label'   => __( 'Enable Address Line 2', 'pods' ),
						'default' => 0,
						'type'    => 'boolean'
					),
					self::$type . '_address_city'    => array(
						'label'   => __( 'Enable City', 'pods' ),
						'default' => 1,
						'type'    => 'boolean'
					),
					self::$type . '_address_postal_code'  => array(
						'label'   => __( 'Enable ZIP / Postal Code', 'pods' ),
						'default' => 0,
						'type'    => 'boolean'
					),
					self::$type . '_address_state'   => array(
						'label'      => __( 'Enable State / Province', 'pods' ),
						'default'    => 1,
						'type'       => 'boolean',
						'dependency' => true
					),
					self::$type . '_address_country' => array(
						'label'      => __( 'Enable Country', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true
					)
				)
			),
			self::$type . '_address_state_input'   => array(
				'label'      => __( 'State Input Type', 'pods' ),
				'depends-on' => array( self::$type . '_address_state' => true, self::$type . '_type' => 'address' ),
				'default'    => 'text',
				'type'       => 'pick',
				'data'       => array(
					'text' => __( 'Freeform Text', 'pods' ),
					'pick' => __( 'Drop-down Select Box', 'pods' )
				),
			),
			self::$type . '_address_country_input' => array(
				'label'      => __( 'Country Input Type', 'pods' ),
				'depends-on' => array( self::$type . '_address_country' => true, self::$type . '_type' => 'address' ),
				'default'    => 'text',
				'type'       => 'pick',
				'data'       => array(
					'text' => __( 'Freeform Text', 'pods' ),
					'pick' => __( 'Drop-down Select Box', 'pods' )
				),
			),
			self::$type . '_autocorrect'           => array(
				'label'      => __( 'Autocorrect Address during save', 'pods' ),
				'depends-on' => array( self::$type . '_display_type' => array( 'single', 'multi' ) ),
				'default'    => 0,
				'type'       => 'boolean'
			),
			self::$type . '_show_map_input'        => array(
				'label'   => __( 'Show Map below Input', 'pods' ),
				'default' => 0,
				'type'    => 'boolean'
			),
			self::$type . '_display_type'          => array(
				'label'      => __( 'Display Type', 'pods' ),
				'default'    => 'address',
				'type'       => 'pick',
				'data'       => array(
					'map'         => __( 'Map', 'pods' ),
					'address-map' => __( 'Address and Map', 'pods' ),
					'address'     => __( 'Address', 'pods' ),
					'lat-long'    => __( 'Latitude, Longitude', 'pods' )
				),
				'dependency' => true
			),
			self::$type . '_style'                 => array(
				'label'      => __( 'Map Output Type', 'pods' ),
				'depends-on' => array( self::$type . '_display_type' => array( 'map', 'address-map' ) ),
				'default'    => pods_v( self::$type . '_style', self::$component_options, 'static', true ),
				'type'       => 'pick',
				'data'       => array(
					'static' => __( 'Static (Image)', 'pods' ),
					'js'     => __( 'Javascript (Interactive)', 'pods' )
				)
			),
			self::$type . '_type_of_map'           => array(
				'label'      => __( 'Map Type', 'pods' ),
				'depends-on' => array( self::$type . '_display_type' => array( 'map', 'address-map' ) ),
				'default'    => pods_v( self::$type . '_type', self::$component_options, 'roadmap', true ),
				'type'       => 'pick',
				'data'       => array(
					'roadmap'   => __( 'Roadmap', 'pods' ),
					'satellite' => __( 'Satellite', 'pods' ),
					'terrain'   => __( 'Terrain', 'pods' ),
					'hybrid'    => __( 'Hybrid', 'pods' )
				)
			),
			self::$type . '_zoom'                  => array(
				'label'      => __( 'Map Zoom Level', 'pods' ),
				'depends-on' => array( self::$type . '_display_type' => array( 'map', 'address-map' ) ),
				'help'       => array(
					__( 'Google Maps has documentation on the different zoom levels you can use.', 'pods' ),
					'https://developers.google.com/maps/documentation/javascript/tutorial#zoom-levels'
					//'https://developers.google.com/maps/documentation/staticmaps/#Zoomlevels'
				),
				'default'    => pods_v( self::$type . '_zoom', self::$component_options, 12, true ),
				'type'       => 'number',
				'options'    => array(
					'number_decimals'   => 0,
					'number_max_length' => 2
				)
			),
			self::$type . '_marker'                => array(
				'label'      => __( 'Map Custom Marker', 'pods' ),
				'depends-on' => array( self::$type . '_display_type' => array( 'map', 'address-map' ) ),
				'default'    => pods_v( self::$type . '_marker', self::$component_options ),
				'type'       => 'file',
				'options'    => array(
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
	public function schema( $options = null ) {

		$schema = 'LONGTEXT';

		return $schema;

	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$display_type = pods_v( self::$type . '_display_type', $options );
		$view         = PODS_DIR . 'ui/front/address.php';
		if ( 'lat-long' == $display_type ) {
			$view = PODS_DIR . 'ui/front/lat-long.php';
		} elseif ( 'address-map' == $display_type ) {
			$view = PODS_DIR . 'ui/front/address-map.php';

		} elseif ( 'map' == $display_type ) {
			$view = PODS_DIR . 'ui/front/map.php';
		}
		$value = pods_view( $view, compact( array_keys( get_defined_vars() ) ), false, 'cache', true );

		return $value;

	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$form_field_type = PodsForm::$field_type;

		$field = PODS_DIR . 'ui/fields/text.php';
		if ( 'address' == pods_v( self::$type . '_type', $options ) ) {
			$field = PODS_DIR . 'ui/fields/address.php';
		} elseif ( 'lat-long' == pods_v( self::$type . '_type', $options ) ) {
			$field = PODS_DIR . 'ui/fields/lat-long.php';
		}

		pods_view( $field, compact( array_keys( get_defined_vars() ) ) );

		if ( 1 == pods_v( self::$type . '_show_map_input', $options ) ) {
			pods_view( PODS_DIR . 'ui/fields/map.php', compact( array_keys( get_defined_vars() ) ) );
		}

	}

	/**
	 * {@inheritDoc}
	 * @since 1.0
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		// TODO: Validate based on address type ( lat / lon, address fields)
		$errors = array();

		if ( 1 == pods_v( 'required', $options ) ) {
			$errors[] = __( 'This field is required.', 'pods' );
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;

	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		return $value;

	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		return $value;

	}

	/**
	 * Output a map
	 *
	 * @param array $args Map options
	 *
	 * @return string Map output
	 * @since 1.0
	 */
	public static function map( $args ) {

		$defaults = array(
			'address'    => '',
			'lat'        => '',
			'long'       => '',
			'width'      => '',
			'height'     => '',
			'type'       => '',
			'zoom'       => '',
			'style'      => '',
			'marker'     => '',
			'expires'    => ( 60 * 60 * 24 ),
			'cache_type' => 'cache'
		);

		$args = array_merge( (array) $args, $defaults );

		$lat_long = array(
			'lat'  => $args['lat'],
			'long' => $args['long']
		);

		if ( empty( $lat_long['lat'] ) && empty( $lat_long['long'] ) ) {
			if ( ! empty( $args['address'] ) ) {
				$address_data = self::geocode_address( $args['address'] );

				if ( ! empty( $address_data ) ) {
					$lat_long['lat']  = $address_data['lat'];
					$lat_long['long'] = $address_data['long'];
				} else {
					return '';
				}
			} else {
				return '';
			}
		}

		return pods_view( self::$file_path . 'ui/front/map.php', compact( array_keys( get_defined_vars() ) ), $args['expires'], $args['cache_type'], true );

	}

	/**
	 * Geocode a specific address into Latitude and Longitude values
	 *
	 * @param string|array $address Address
	 *
	 * @return array Latitude, Longitude, and Formatted Address values
	 *
	 * @public
	 * @since 1.0
	 */
	public function geocode_address( $address ) {

		return array();

	}

	/**
	 * Get an address from a lat / long
	 *
	 * @param string|array $lat_long Lat / long numbers
	 *
	 * @return string Address information
	 *
	 * @public
	 * @static
	 * @since 1.0
	 */
	public function geocode_lat_long( $lat_long ) {

		return '';

	}

	/**
	 * @param $result
	 *
	 * @return array|bool
	 * @since 1.0
	 */
	public function parse_address( $result ) {

		return false;

	}

}