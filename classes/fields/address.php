<?php
/**
 * Class PodsField_AddressMap
 * @package Pods\Fields
 */
class PodsField_Address extends PodsField {

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
	public static $type = 'address';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 1.0
	 */
	public static $label = 'Address';

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
	 * API key
	 *
	 * @var string
	 * @since 1.0
	 */
	public static $api_key = '';

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

		/*if ( class_exists( 'Pods_Component_AddressMaps' ) && ! empty( Pods_Component_AddressMaps::$options ) ) {
			self::$file_path = Pods_Component_AddressMaps::$component_path;

			self::$component_options = Pods_Component_AddressMaps::$options;

			self::$api_key = $api_key = ( ! empty( self::$component_options['api_key'] ) ) ? self::$component_options['api_key'] : '';

			wp_register_style( 'pods-component-address-maps', PODS_URL . 'ui/css/pods-address-maps.css', array(), '1.0' );
			wp_register_script( 'pods-component-address-maps', PODS_URL . 'ui/js/pods-address-maps.js', array(), '1.0' );
			wp_register_script( 'googlemaps', 'http://maps.googleapis.com/maps/api/js?key='.$api_key, false, '3' ); //sensor=false&
		}*/

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
					'text'     => __( 'Freeform Text', 'pods' )
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
					self::$type . '_address_postal_code'  => array(
						'label'   => __( 'Enable ZIP / Postal Code', 'pods' ),
						'default' => 0,
						'type'    => 'boolean'
					),
					self::$type . '_address_city'    => array(
						'label'   => __( 'Enable City', 'pods' ),
						'default' => 1,
						'type'    => 'boolean'
					),
					self::$type . '_address_region'   => array(
						'label'      => __( 'Enable Region (State / Province)', 'pods' ),
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
			self::$type . '_address_region_input' => array(
				'label'      => __( 'Region Input Type', 'pods' ),
				'depends-on' => array( self::$type . '_address_region' => true, self::$type . '_type' => 'address' ),
				'default'    => 'text',
				'type'       => 'pick',
				'data'       => array(
					'text' => __( 'Freeform Text', 'pods' ),
					'pick' => __( 'Drop-down Select Box (US States)', 'pods' )
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
			self::$type . '_display_type' => array(
				'label'      => __( 'Display Type', 'pods' ),
				'default'    => 'default',
				'type'       => 'pick',
				'data'       => array(
					'default' => __( 'Default', 'pods' ),
					'custom'  => __( 'Custom', 'pods' )
				),
				'depends-on' => array( self::$type . '_type' => 'address' ),
				'dependency' => true
			),
			self::$type . '_display_type_custom' => array(
				'label'      => __( 'Custom display', 'pods' ),
				'help' => __( 'You can use the following tags for address fields', 'pods' ) . ': <code>{{line_1}}</code>, <code>{{line_2}}</code>, <code>{{postal_code}}</code>, <code>{{city}}</code>, <code>{{region}}</code>, <code>{{country}}</code>',
				'default'    => self::default_display_format(),
				'type'       => 'paragraph',
				'depends-on' => array( self::$type . '_display_type' => 'custom', self::$type . '_type' => 'address' )
			),
			self::$type . '_microdata' => array(
				'label'      => __( 'Format with microdata?', 'pods' ) . ' (schema.org)',
				'default'    => 0,
				'type'       => 'boolean',
				'depends-on' => array( self::$type . '_type' => 'address' )
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

		$view = PODS_DIR . 'ui/front/address.php';
		$view = apply_filters( 'pods_ui_field_address_display_view', $view, $display_type, $value, $name, $options, $pod, $id );

		$value = pods_view( $view, compact( array_keys( get_defined_vars() ) ), false, 'cache', true );
		$value = apply_filters( 'pods_ui_field_address_display_value', $value, $view, $display_type, $value, $name, $options, $pod, $id );

		return $value;

	}

	/**
	 * {@inheritDoc}
	 *
	 * @since 1.0
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$form_field_type = PodsForm::$field_type;

		$type = pods_v( self::$type . '_type', $options );

		$view = PODS_DIR . 'ui/fields/text.php';
		if ( 'address' == $type ) {
			$view = PODS_DIR . 'ui/fields/address.php';
		}
		$view = apply_filters( 'pods_ui_field_address_input_view', $view, $type, $name, $value, $options, $pod, $id );

		if ( ! empty( $view ) ) {
			pods_view( $view, compact( array_keys( get_defined_vars() ) ) );
		}

		do_action( 'pods_ui_field_address_input_view_extra', $view, $type, $name, $value, $options, $pod, $id );

	}

	/**
	 * {@inheritDoc}
	 * @since 1.0
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		// @todo: Validate based on address type ( lat / lon, address fields)
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
	 * Convert the field format into HTML for display
	 *
	 * @since 2.7
	 *
	 * @param string $format The format to be used (default or custom)
	 * @param array $value The field value
	 * @param array $options The field options
	 *
	 * @return string
	 */
	public static function format_to_html( $format, $value, $options ) {
		$output = '';

		if ( ! empty ( $value['address'] ) ) {
			$address = $value['address'];
		}

		if ( ! empty( $address ) ) {

			// Format in microdata?
			$microdata = ( ! empty( $options[ self::$type . '_microdata' ] ) ) ? true : false;

			// Convert actual line breaks into an array
			$lines = explode( '\r\n', preg_replace("/\n/m", '\r\n', $format) );

			foreach ( $lines as $key => $line ) {

				// preg_match to all tags
				preg_match_all( '#{{(.*?)}}#', $line, $tags );
				if ( ! empty( $tags[1] ) ) {
					foreach( $tags[1] as $tag ) {
						// Default value is empty. Only known tags are allowed, remove all unknown tags
						$value = '';
						if ( ! empty( $address[ $tag ] ) ) {
							$value = self::wrap_html_format( $address[ $tag ], $tag, 'span', $microdata );
						}
						$lines[ $key ] = str_replace( '{{' . $tag . '}}', $value, $lines[ $key ] );
					}
				}
				if ( empty( trim( $lines[ $key ] ) ) ) {
					unset( $lines[ $key ] );
				}
			}
			// Lines to HTML line breaks
			$output = implode( '<br>', $lines );

			$output = self::wrap_html_format( $output, 'address', 'div', $microdata );
		}
		return $output;
	}

	/**
	 * Wrap values in the correct HTML format with optional schema.org microdata based on the address tag
	 *
	 * @since 2.7
	 *
	 * @param string $value
	 * @param string $tag The address tag
	 *
	 * @return string
	 */
	public static function wrap_html_format( $value, $tag, $element, $microdata = false ) {

		$atts['class'] = 'pods-address' . $tags;

		switch ( $tag ) {
			case 'address':
				$atts['class'] = 'pods-address';
				if ( $microdata ) {
					$atts['itemprop'] = 'address';
					$atts['itemscope'] = '';
					$atts['itemtype'] = 'http://schema.org/PostalAddress';
				};
				break;

			case 'line_1':
			case 'line_2':
				if ( $microdata ) {
					$atts['itemprop'] = 'streetAddress';
				};
				break;

			case 'postal_code':
				if ( $microdata ) {
					$atts['itemprop'] = 'postalCode';
				};
				break;

			case 'city':
				if ( $microdata ) {
					$atts['itemprop'] = 'addressLocality';
				};
				break;

			case 'region':
				if ( $microdata ) {
					$atts['itemprop'] = 'addressRegion';
				};
				break;

			case 'country':
				if ( $microdata ) {
					$atts['itemprop'] = 'addressCountry';
				};
				break;

			default:
				$atts = false;
				break;
		}

		if ( $atts ) {
			$attributes = '';
			foreach ( $atts as $key => $val ) {
				$attributes .= ' ' . $key . '="' . $val . '"';
			}
			$value = '<' . $element . $attributes . '>' . $value . '</' . $element . '>';
		}

		return $value;
	}

	/**
	 * The default display format
	 *
	 * @since 2.7
	 *
	 * @return string
	 */
	public static function default_display_format() {
		return '{{line_1}}
{{line_2}}
{{postal_code}} {{city}}
{{region}}
{{country}}';
	}

}