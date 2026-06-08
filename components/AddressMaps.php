<?php

/**
 * Name: Address / Maps Field
 *
 * Menu Name: Address / Maps
 *
 * Description:
 *
 * Version: 1.0
 *
 * Category: Field Types
 *
 * Class: Pods_Component_AddressMaps
 */
class Pods_Component_AddressMaps extends PodsComponent {

	static $component_path;

	static $component_file;

	static $options;

	public function __construct() {
		add_filter( 'pods_api_field_types', array( $this, 'register_field' ) );
	}

	public function register_field( $field_types ) {
		$field_types[] = 'addressmap';
		return $field_types;
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
					//'bing' => __( 'Bing Maps', 'pods' )
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
}