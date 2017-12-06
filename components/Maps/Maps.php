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

	/**
	 * @var string
	 */
	static $component_path;

	/**
	 * @var string
	 */
	static $component_file;

	/**
	 * @var array
	 */
	static $options;

	/**
	 * @var \Pods_Component_Maps_Provider
	 */
	static $provider;

	/**
	 * @var string
	 */
	static $api_key = '';

	/**
	 * @var string
	 */
	static $dir = '';

	private static $nonce = 'pods_maps';

	public function __construct() {

		self::$dir = plugin_dir_path( __FILE__ );

		include_once( self::$dir . 'Maps-Provider.php' );

		// See https://github.com/pods-framework/pods/pull/3711
		add_filter( 'pods_admin_setup_edit_address_additional_field_options', array( $this, 'field_options' ), 10, 2 );

		// Add Maps input
		// do_action( 'pods_ui_field_address_input_view_extra', $view, $type, $name, $value, $options, $pod, $id );
		add_action( 'pods_ui_field_address_input_view_extra', array(
			$this,
			'pods_ui_field_address_input_view_extra'
		), 10, 7 );

		// Validate Address/Geo
		// apply_filters( 'pods_ui_field_address_validate', $errors, $value, $type, $name, $options, $fields, $pod, $id, $params );
		add_filter( 'pods_ui_field_address_validate', array( $this, 'pods_ui_field_address_validate' ), 10, 9 );

		// Add Address/Geo pre save
		// apply_filters( 'pods_ui_field_address_pre_save', $value, $type, $id, $name, $options, $fields, $pod, $params );
		add_filter( 'pods_ui_field_address_pre_save', array( $this, 'pods_ui_field_address_pre_save' ), 10, 8 );

		// Add or change the display value
		// apply_filters( 'pods_ui_field_address_display_value', $output, $value, $view, $display_type, $name, $options, $pod, $id );
		add_filter( 'pods_ui_field_address_display_value', array(
			$this,
			'pods_ui_field_address_display_value'
		), 10, 8 );

		// Ajax call handler
		add_action( 'wp_ajax_pods_maps', array( $this, 'ajax_handler' ) );
		// Allow calls from frontend when needed (always verify nonce!)
		add_action( 'wp_ajax_nopriv_pods_maps', array( $this, 'ajax_handler' ) );

		add_action( 'admin_enqueue_scripts', array( $this, 'global_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'global_assets' ) );
	}

	/**
	 * Enqueue styles
	 *
	 * @since 1.0
	 */
	public function admin_assets() {

		wp_enqueue_style( 'pods-admin' );

	}

	public function global_assets() {

		wp_register_style( 'pods-maps', plugin_dir_url( __FILE__ ) . 'ui/css/pods-maps.css', array(), '1.0' );

		// @todo Use pods-maps.js for global functions needed for the Google Maps API (also see pods-maps.js file)
		wp_register_script( 'pods-maps', plugin_dir_url( __FILE__ ) . 'ui/js/pods-maps.js', array( 'jquery' ), '1.0' );
		$provider = get_class( self::$provider );
		wp_localize_script( 'pods-maps', 'PodsMaps', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ),
			'_nonce'  => wp_create_nonce( self::$nonce )
		) );

		// @todo Allways load required front end assets (Maybe as an option?)
		// Enqueue doesn't work in the display function anymore (hook is already fires before that)
		self::$provider->assets();
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

		if ( ! empty( $options['api_key'] ) ) {
			self::$api_key = $options['api_key'];
		}

		$this->load_provider();

	}

	/**
	 * Load the selected provider
	 *
	 * @since 2.x
	 */
	private function load_provider() {

		/**
		 * Let Pods use a custom provider.
		 * Return string should match the instance you return in `pods_component_maps_provider_{provider}`
		 *
		 * @param string $provider Provider name slug.
		 * @return string Custom provider name slug.
		 */
		$provider = (string) apply_filters( 'pods_component_maps_provider', self::$options['provider'] );

		switch ( $provider ) {
			case 'google':
				if ( file_exists( plugin_dir_path( __FILE__ ) . 'Maps-Google.php' ) ) {
					include_once( plugin_dir_path( __FILE__ ) . 'Maps-Google.php' );
					self::$provider = new Pods_Component_Maps_Google();
				}
				break;
			default:
				/**
				 * Add your own maps API provider instance
				 *
				 * @param  string  $provider
				 * @return object  Custom provider class instance.
				 */
				self::$provider = apply_filters( 'pods_component_maps_provider_' . $provider, self::$options['provider'] );
				break;
		}

	}

	/**
	 * Ajax handler for geocode calls
	 *
	 * AJAX call data setup:
	 * action           => pods_maps
	 * _pods_maps_nonce => PodsMaps._nonce
	 * pods_maps_action => 'string' (the maps action)
	 * pods_maps_data   => 'string|array' (the provided data)
	 */
	public function ajax_handler() {

		if ( ! defined( 'DOING_AJAX' ) || ! DOING_AJAX || ! isset( $_POST['_pods_maps_nonce'] ) || ! wp_verify_nonce( $_POST['_pods_maps_nonce'], self::$nonce ) ) {
			wp_send_json_error( __( 'Cheatin uh?', 'pods' ) );
			die();
		}

		if ( isset( $_POST['pods_maps_action'] ) ) {
			$return = false;
			$data   = '';
			if ( ! empty( $_POST['pods_maps_data'] ) ) {
				if ( is_array( $_POST['pods_maps_data'] ) ) {
					$data = array_map( 'pods_sanitize', $_POST['pods_maps_data'] );
				} else {
					$data = pods_sanitize( $_POST['pods_maps_data'] );
				}
			}
			if ( ! empty( $data ) ) {
				switch ( pods_sanitize( $_POST['pods_maps_action'] ) ) {
					case 'geocode':
					case 'geocode_address':
						$return = self::geocode_address( $data );
						break;
					case 'geocode_address_to_latlng':
						$return = self::geocode_address_to_latlng( $data );
						break;
					case 'geocode_latlng_to_address':
						$return = self::geocode_latlng_to_address( $data );
						break;
				}
			}
			if ( ! empty( $return ) ) {
				wp_send_json_success( $return );
			} else {
				wp_send_json_error( __( 'Geocode error, please try again or type different address data.', 'pods' ) );
			}
		}
		die();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function options( $settings ) {

		$options = array(
			'provider'         => array(
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
		);

		if ( is_callable( array( self::$provider, 'options' ) ) ) {
			$options = self::$provider->options( $options );
		}

		return $options;

	}

	/**
	 * Add map field options
	 *
	 * @param array  $options
	 * @param string $type The field type
	 *
	 * @return array
	 *
	 * @since 2.x
	 */
	public function field_options( $options, $type ) {

		// Add lat/lng input type
		$options[ $type . '_type' ]['data']['lat-lng'] = __( 'Latitude / Longitude', 'pods' );

		// Add Map display types
		//$options[ $type . '_display_type' ]['data']['map'] = __( 'Map', 'pods' );
		//$options[ $type . '_display_type' ]['data']['default-map'] = __( 'Default and map', 'pods' );
		//$options[ $type . '_display_type' ]['data']['custom-map'] = __( 'Custom and map', 'pods' );

		// Add extra options

		$options['maps'] = array(
			'label'      => __( 'Display a map', 'pods' ),
			'default'    => 0,
			'type'       => 'boolean',
			'dependency' => true
		);
		$options['maps_autocorrect'] = array(
			'label'      => __( 'Autocorrect Address during save', 'pods' ),
			'depends-on' => array(
				'maps' => true,
				$type . '_type' => array( 'address', 'text' )
			),
			'default'    => 0,
			'type'       => 'boolean'
		);
		$options['maps_display']             = array(
			'label'      => __( 'Map Display', 'pods' ),
			'depends-on' => array( 'maps' => true ),
			'default'    => 'replace',
			'type'       => 'pick',
			'data'       => array(
				'replace' => __( 'Replace default display', 'pods' ),
				'before'  => __( 'Before default display', 'pods' ),
				'after'   => __( 'After default display', 'pods' ),
				'admin'   => __( 'Admin only', 'pods' ),
			)
		);

		if ( is_callable( array( self::$provider, 'field_options' ) ) ) {
			$options = self::$provider->field_options( $options, $type );
		}

		// Add option dependencies
		/*if ( empty( $options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ] ) ) {
			$options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ] = array( 'custom-map' );
		} else {
			$options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ] = $this->append_dependency(
				$options[ $type . '_display_type_custom' ]['depends-on'][ $type . '_display_type' ],
				'custom-map'
			);
		}*/

		//$options['maps_microdata']['excludes-on']['maps'] = true;

		return $options;
	}

	/**
	 * Get titles of all Pods Templates
	 *
	 * @return string[] Array of template names
	 *
	 * @since 2.x
	 */
	public static function get_template_titles() {

		static $template_titles;

		if ( empty( $template_titles ) ) {
			$all_templates = (array) pods_api()->load_templates( array() );

			$template_titles = array();
			foreach ( $all_templates as $template ) {
				$template_titles[ $template['id'] ] = $template['name'];
			}
		}

		return $template_titles;

	}

	/**
	 * Add/Change the display value
	 *
	 * @param $value
	 * @param $view
	 * @param $display_type
	 * @param $value
	 * @param $name
	 * @param $options
	 * @param $pod
	 * @param $id
	 *
	 * @return string
	 */
	public function pods_ui_field_address_display_value( $output, $value, $view, $display_type, $name, $options, $pod, $id ) {

		if ( pods_v( 'maps', $options ) && 'admin' !== pods_v( 'maps_display', $options ) ) {
			$view     = '';
			$provider = get_class( self::$provider );

			if ( is_callable( array( $provider, 'field_display_view' ) ) ) {
				$view = self::$provider->field_display_view();
			}

			if ( $view && file_exists( $view ) ) {
				// Add hidden lat/lng fields for non latlng view types
				$maps_value = pods_view( $view, compact( array_keys( get_defined_vars() ) ), false, 'cache', true );

				$maps_display = pods_v( 'maps_display', $options, 'replace', true );

				if ( 'before' === $maps_display ) {
					$output = $maps_value . $output;
				} elseif ( 'after' === $maps_display ) {
					$output .= $maps_value;
				} else {
					$output = $maps_value;
				}
			}
		}

		return $output;

	}

	/**
	 * Allow Map providers to add a map to the field input field
	 *
	 * @param $view
	 * @param $type
	 * @param $name
	 * @param $value
	 * @param $options
	 * @param $pod
	 * @param $id
	 */
	public function pods_ui_field_address_input_view_extra( $view, $type, $name, $value, $options, $pod, $id ) {

		if ( pods_v( 'maps', $options ) ) {
			$provider = get_class( self::$provider );
			if ( is_callable( array( $provider, 'field_input_view' ) ) ) {
				$view = self::$provider->field_input_view();
			}

			if ( $view && file_exists( $view ) ) {
				// Add hidden lat/lng fields for non latlng view types
				pods_view( $view, compact( array_keys( get_defined_vars() ) ) );
				if ( $type != 'lat-lng' ) {
					echo '<div style="display: none">';
					pods_view( plugin_dir_path( __FILE__ ) . 'ui/fields/lat-lng.php', compact( array_keys( get_defined_vars() ) ) );
					echo '</div>';
				}
			}

		}

	}

	/**
	 * Validate current value
	 *
	 * @param $errors
	 * @param $value
	 * @param $type
	 * @param $name
	 * @param $options
	 * @param $fields
	 * @param $pod
	 * @param $id
	 * @param $params
	 *
	 * @return array
	 */
	public function pods_ui_field_address_validate( $errors, $value, $type, $name, $options, $fields, $pod, $id, $params ) {

		// @todo: Validate based on address type ( lat / lon, address fields)

		if ( ! $value ) {
			return $errors;
		}

		// Get geocode from address fields
		if ( isset( $value['address'] ) ) {
			// @todo: What to do if Google doesn't respond?
			$geocode = self::geocode_address_to_latlng( $value['address'] );
			if ( empty( $geocode['lat'] ) && empty( $geocode['lng'] ) ) {
				$errors[] = __( 'Could not find geodata for this address', 'pods' );
			}
		}

		return $errors;

	}

	/**
	 * Save Additional geo data dependent on the field type
	 *
	 * @param $value
	 * @param $type
	 * @param $id
	 * @param $name
	 * @param $options
	 * @param $fields
	 * @param $pod
	 * @param $params
	 *
	 * @return mixed
	 */
	public function pods_ui_field_address_pre_save( $value, $type, $id, $name, $options, $fields, $pod, $params ) {

		$org_value = $value;

		// Get geocode from address fields
		if ( isset( $value['address'] ) ) {
			$geocode = array();
			if ( pods_v( 'maps_autocorrect', $options, 0 ) ) {
				$address = self::geocode_address( $value['address'] );
				if ( ! empty( $address['address'] ) ) {
					$value['address'] = $address['address'];
				}
				if ( ! empty( $address['geo'] ) ) {
					$geocode = $address['geo'];
				}
			} else {
				$geocode = self::geocode_address_to_latlng( $value['address'] );
			}
			if ( isset( $geocode['lat'] ) && isset( $geocode['lng'] ) ) {
				$value['geo'] = $geocode;
			}
		}

		$value = apply_filters( 'pods_ui_field_address_maps_pre_save', $value, $org_value, $type, $id, $name, $options, $fields, $pod, $params );

		return $value;

	}

	/**
	 * @param string|array $data
	 *
	 * @return mixed
	 */
	public static function geocode_address( $data ) {

		if ( is_object( self::$provider ) ) {
			$provider = get_class( self::$provider );
			if ( method_exists( $provider, 'geocode_address' ) ) {
				return $provider::geocode_address( $data, self::$api_key );
			}
		}

		return false;
	}

	/**
	 * @param string|array $data
	 *
	 * @return mixed
	 */
	public static function geocode_address_to_latlng( $data ) {

		if ( is_object( self::$provider ) ) {
			$provider = get_class( self::$provider );
			if ( method_exists( $provider, 'geocode_address_to_latlng' ) ) {
				return $provider::geocode_address_to_latlng( $data, self::$api_key );
			}
		}

		return false;
	}

	/**
	 * @param string|array $data
	 *
	 * @return mixed
	 */
	public static function geocode_latlng_to_address( $data ) {

		if ( is_object( self::$provider ) ) {
			$provider = get_class( self::$provider );
			if ( method_exists( $provider, 'geocode_latlng_to_address' ) ) {
				return $provider::geocode_latlng_to_address( $data, self::$api_key );
			}
		}

		return false;
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