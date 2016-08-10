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

	static $provider;

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
	public function handler( $options ) {

		self::$options = $options;

		$this->load_provider();

	}

	/**
	 * Load the selected provider
	 *
	 * @since 2.7
	 */
	private function load_provider() {

		switch ( self::$options['provider'] ) {
			case 'google':
				if ( file_exists( plugin_dir_path( __FILE__ ) . '/Maps-Google.php' ) ) {
					include_once( plugin_dir_path( __FILE__ ) . '/Maps-Google.php' );
					self::$provider = new Pods_Component_Maps_Google();
				}
				break;
		}

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
					'number_decimals'   => 0, // 2
					'number_max_length' => 2,
					'number_min' => 1,
					'number_max' => 21,
					'number_format' => '9999.99',
					//'number_format_type' => 'slider'
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
	 * Add map field options
	 *
	 * @param array $options
	 * @param string $type
	 *
	 * @return array
	 *
	 * @since 2.7
	 */
	public function maps_options( $options, $type ) {

		// Add lat/lng input type
		$options[ $type . '_type' ]['data']['lat-lng'] = __( 'Latitude / Longitude', 'pods' );

		// Add Map display types
		//$options[ $type . '_display_type' ]['data']['map'] = __( 'Map', 'pods' );
		//$options[ $type . '_display_type' ]['data']['default-map'] = __( 'Default and map', 'pods' );
		//$options[ $type . '_display_type' ]['data']['custom-map'] = __( 'Custom and map', 'pods' );

		// Add extra options

		$options[ $type . '_map' ] = array(
			'label'      => __( 'Display a map', 'pods' ),
			'default'    => 0,
			'type'       => 'boolean',
			'dependency' => true
		);
		$options[ $type . '_map_display' ] = array(
			'label'      => __( 'Map Display', 'pods' ),
			'depends-on' => array( $type . '_map' => true ),
			'default'    => 'replace',
			'type'       => 'pick',
			'data'       => array(
				'replace' => __( 'Replace default display', 'pods' ),
				'before'     => __( 'Before default display', 'pods' ),
				'after'     => __( 'After default display', 'pods' )
			)
		);
		$options[ $type . '_autocorrect' ] = array(
			'label'      => __( 'Autocorrect Address during save', 'pods' ),
			'depends-on' => array( $type . '_map' => true, $type . '_type' => array( 'address', 'text' ) ),
			'default'    => 0,
			'type'       => 'boolean'
		);
		$options[ $type . '_style' ] = array(
			'label'      => __( 'Map Output Type', 'pods' ),
			'depends-on' => array( $type . '_map' => true ),
			'default'    => pods_v( $type . '_style', self::$options, 'static', true ),
			'type'       => 'pick',
			'data'       => array(
				'static' => __( 'Static (Image)', 'pods' ),
				'js'     => __( 'Javascript (Interactive)', 'pods' )
			)
		);
		$options[ $type . '_type_of_map' ] = array(
			'label'      => __( 'Map Type', 'pods' ),
			'depends-on' => array( $type . '_map' => true ),
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
			'depends-on' => array( $type . '_map' => true ),
			'help'       => array(
				__( 'Google Maps has documentation on the different zoom levels you can use.', 'pods' ),
				'https://developers.google.com/maps/documentation/javascript/tutorial#zoom-levels'
				//'https://developers.google.com/maps/documentation/staticmaps/#Zoomlevels'
			),
			'default'    => pods_v( $type . '_zoom', self::$options, 12, true ),
			'type'       => 'number',
			'options'    => array(
				'number_decimals'   => 0, // 2
				'number_max_length' => 2,
				'number_min' => 1,
				'number_max' => 21,
				'number_format' => '9999.99',
				//'number_format_type' => 'slider'
			)
		);
		$options[ $type . '_info_window_content' ] = array(
			'label'      => __( 'Map Info Window content', 'pods' ),
			'depends-on' => array( $type . '_map' => true ),
			'default'    => 'default',
			'type'       => 'pick',
			'data'       => array(
				'default'   => __( 'Default display', 'pods' ),
				// Custom will add a WYSIWYG window at the edit screen
				'custom' => __( 'Custom (WYSIWYG)', 'pods' )
			)
		);
		$options[ $type . '_marker' ] = array(
			'label'      => __( 'Map Custom Marker', 'pods' ),
			'depends-on' => array( $type . '_map' => true ),
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
		/*if ( empty( $options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ] ) ) {
			$options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ] = array( 'custom-map' );
		} else {
			$options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ] = $this->append_dependency(
				$options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ],
				'custom-map'
			);
		}*/
		$options[ $type . '_microdata' ]['excludes-on'][ $type . '_map' ] = true;

		return $options;
	}

	/**
	 * Append new dependency to existing data
	 *
	 * @param $value
	 * @param $new
	 *
	 * @return array
	 */
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

}