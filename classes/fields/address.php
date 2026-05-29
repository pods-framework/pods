<?php

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

/**
 * @package Pods\Fields
 */
class PodsField_Address extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'address';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Address';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {
		static::$label = __( 'Address', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {
		$type = static::$type;

		return [
			$type . '_type'                   => [
				'label'      => __( 'Address Type', 'pods' ),
				'default'    => 'address',
				'type'       => 'pick',
				'data'       => [
					'address' => __( 'Address Field Group', 'pods' ),
					'text'    => __( 'Freeform Text', 'pods' ),
				],
				'dependency' => true,
			],
			$type . '_address_options'        => [
				'label'         => __( 'Address Options', 'pods' ),
				'depends-on'    => [ $type . '_type' => 'address' ],
				'type'          => 'boolean_group',
				'boolean_group' => [
					$type . '_address_line_1'      => [
						'label'   => __( 'Enable Address Line 1', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
					],
					$type . '_address_line_2'      => [
						'label'   => __( 'Enable Address Line 2', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
					],
					$type . '_address_postal_code' => [
						'label'   => __( 'Enable ZIP / Postal Code', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
					],
					$type . '_address_city'        => [
						'label'   => __( 'Enable City', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
					],
					$type . '_address_region'      => [
						'label'      => __( 'Enable Region (State / Province)', 'pods' ),
						'default'    => 1,
						'type'       => 'boolean',
						'dependency' => true,
					],
					$type . '_address_country'     => [
						'label'      => __( 'Enable Country', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
					],
				],
			],
			$type . '_address_region_input'   => [
				'label'      => __( 'Region Input Type', 'pods' ),
				'depends-on' => [
					$type . '_address_region' => true,
					$type . '_type'           => 'address',
				],
				'default'    => 'text',
				'type'       => 'pick',
				'data'       => [
					'text' => __( 'Freeform Text', 'pods' ),
					'pick' => __( 'Drop-down Select Box (US States)', 'pods' ),
				],
				'dependency' => true,
			],
			$type . '_address_region_output'  => [
				'label'      => __( 'Region Output Type', 'pods' ),
				'depends-on' => [
					$type . '_address_region_input' => 'pick',
					$type . '_address_region'       => true,
					$type . '_type'                 => 'address',
				],
				'default'    => 'long',
				'type'       => 'pick',
				'data'       => [
					'long'  => __( 'Full name', 'pods' ),
					'short' => __( 'State / Province code', 'pods' ),
				],
			],
			$type . '_address_country_input'  => [
				'label'      => __( 'Country Input Type', 'pods' ),
				'depends-on' => [
					$type . '_address_country' => true,
					$type . '_type'            => 'address',
				],
				'default'    => 'text',
				'type'       => 'pick',
				'data'       => [
					'text' => __( 'Freeform Text', 'pods' ),
					'pick' => __( 'Drop-down Select Box', 'pods' ),
				],
				'dependency' => true,
			],
			$type . '_address_country_output' => [
				'label'      => __( 'Country Output Type', 'pods' ),
				'depends-on' => [
					$type . '_address_country_input' => 'pick',
					$type . '_address_country'       => true,
					$type . '_type'                  => 'address',
				],
				'default'    => 'long',
				'type'       => 'pick',
				'data'       => [
					'long'  => __( 'Full name', 'pods' ),
					'short' => __( 'Country code', 'pods' ),
				],
			],
			$type . '_address_geo'         => [
				'label'   => __( 'Enable Latitude / Longitude', 'pods' ),
				'default' => 0,
				'type'    => 'boolean',
			],
			$type . '_display_type'           => [
				'label'      => __( 'Display Type', 'pods' ),
				'default'    => 'default',
				'type'       => 'pick',
				'data'       => [
					'default' => __( 'Default', 'pods' ),
					'custom'  => __( 'Custom', 'pods' ),
				],
				'depends-on' => [ $type . '_type' => 'address' ],
				'dependency' => true,
			],
			$type . '_display_type_custom'    => [
				'label'      => __( 'Custom display', 'pods' ),
				'help'       => __( 'You can use the following tags for address fields', 'pods' ) . ': <code>{{line_1}}</code>, <code>{{line_2}}</code>, <code>{{postal_code}}</code>, <code>{{city}}</code>, <code>{{region}}</code>, <code>{{country}}</code>',
				'default'    => self::default_display_format(),
				'type'       => 'paragraph',
				'depends-on' => [
					$type . '_display_type' => 'custom',
					$type . '_type'         => 'address',
				],
			],
			$type . '_microdata'              => [
				'label'      => __( 'Format with microdata?', 'pods' ) . ' (schema.org)',
				'default'    => 0,
				'type'       => 'boolean',
				'depends-on' => [ $type . '_type' => 'address' ],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {
		return 'LONGTEXT';
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_dfv_field_item_data( $args ) {
		$data = parent::build_dfv_field_item_data( $args );

		$data['regions'] = PodsForm::field_method( 'pick', 'data_us_states' );
		$data['countries'] = PodsForm::field_method( 'pick', 'data_countries' );

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		$value_raw    = $value;
		$display_type = pods_v( static::$type . '_display_type', $options );

		$view = PODS_DIR . 'ui/front/address.php';
		$view = apply_filters( 'pods_ui_field_address_display_view', $view, $display_type, $value, $name, $options, $pod, $id );

		$output = pods_view( $view, compact( array_keys( get_defined_vars() ) ), false, 'cache', true );
		$output = apply_filters( 'pods_ui_field_address_display_value', $output, $value, $view, $display_type, $name, $options, $pod, $id );

		return $output;
	}

	/**
	 * Change the way a list of values of the field is displayed with Pods::field.
	 *
	 * @param mixed|null  $value
	 * @param string|null $name
	 * @param array|null  $options
	 * @param array|null  $pod
	 * @param int|null    $id
	 *
	 * @return mixed|null|string
	 */
	public function display_list( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		return call_user_func_array( [ $this, 'display' ], func_get_args() );
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {
		$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;
		$form_field_type = PodsForm::$field_type;

		if ( isset( $options['name'] ) && ! pods_permission( $options ) ) {
			if ( pods_v_bool( 'read_only_restricted', $options ) ) {
				$options['readonly'] = true;
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) ) {
			if ( pods_v_bool( 'read_only_restricted', $options ) ) {
				$options['readonly'] = true;
			}
		}

		if ( ! empty( $options['disable_dfv'] ) ) {
			$type            = pods_v( static::$type . '_type', $options, 'address' );

			pods_view( PODS_DIR . 'ui/fields/address.php', compact( array_keys( get_defined_vars() ) ) );

			do_action( 'pods_ui_field_address_input_view_extra', PODS_DIR . 'ui/fields/address.php', $type, $name, $value, $options, $pod, $id );

			return;
		}

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );

		do_action( 'pods_ui_field_address_input_view_extra', PODS_DIR . 'ui/fields/address.php', $type, $name, $value, $options, $pod, $id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		// @todo: Validate each returned value for variable type and content (sanitizing)
		$validate = parent::validate( $value, $name, $options, $fields, $pod, $id, $params );

		$errors = [];

		if ( is_array( $validate ) ) {
			$errors = $validate;
		}

		$type = pods_v( static::$type . '_type', $options, 'address' );

		/**
		 * Add extra validation checks
		 *
		 * @param array  $errors
		 * @param mixed  $value
		 * @param string $type Field address type
		 * @param string $name
		 * @param array  $options
		 * @param array  $fields
		 * @param array  $pod
		 * @param int    $id
		 * @param array  $params
		 */
		$errors = apply_filters( 'pods_ui_field_address_validate', $errors, $value, $type, $name, $options, $fields, $pod, $id, $params );

		if ( $this->is_required( $options ) ) {
			$values_to_check = $value;

			if ( is_array( $value ) ) {
				if ( 'text' === $type ) {
					$values_to_check = pods_v( 'text', $value, '' );
				} elseif ( isset( $value['address'] ) ) {
					$values_to_check = $value['address'];

					if ( pods_v_bool( static::$type . '_address_geo', $options ) && ! empty( $value['geo'] ) && is_array( $value['geo'] ) ) {
						$values_to_check = array_merge( $values_to_check, $value['geo'] );
					}
				}
			}

			$is_empty = false;

			if ( is_array( $values_to_check ) ) {
				$filtered_values = array_filter(
					$values_to_check,
					static function ( $check_value ) {
						if ( is_array( $check_value ) ) {
							$check_value = implode( '', $check_value );
						}

						return '' !== trim( (string) $check_value );
					}
				);

				$is_empty = empty( $filtered_values );
			} else {
				$is_empty = '' === trim( (string) $values_to_check );
			}

			if ( $is_empty ) {
				$errors[] = __( 'This field is required.', 'pods' );
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return $validate;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		$type = pods_v( static::$type . '_type', $options, 'address' );

		/**
		 * Add extra value sanitation
		 *
		 * @param mixed  $value
		 * @param string $type Field address type
		 * @param int    $id
		 * @param string $name
		 * @param array  $options
		 * @param array  $fields
		 * @param array  $pod
		 * @param array  $params
		 */
		return apply_filters( 'pods_ui_field_address_pre_save', $value, $type, $id, $name, $options, $fields, $pod, $params );
	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		return $value;
	}

	/**
	 * Convert the value for output display based on the field options.
	 *
	 * @param array $value
	 * @param array $options
	 *
	 * @return array
	 */
	public static function format_value_for_output( $value, $options ) {
		if ( ! empty( $value['address'] ) ) {
			foreach ( $value['address'] as $key => $val ) {
				if (
					'region' === $key
					&& 'pick' === pods_v( static::$type . '_address_region_input', $options )
					&& 'long' === pods_v( static::$type . '_address_region_output', $options )
				) {
					$regions = PodsForm::field_method( 'pick', 'data_us_states' );

					if ( array_key_exists( $val, $regions ) ) {
						$value['address'][ $key ] = $regions[ $val ];
					}
				} elseif (
					'country' === $key
					&& 'pick' === pods_v( static::$type . '_address_country_input', $options )
					&& 'long' === pods_v( static::$type . '_address_country_output', $options )
				) {
					$countries = PodsForm::field_method( 'pick', 'data_countries' );

					if ( array_key_exists( $val, $countries ) ) {
						$value['address'][ $key ] = $countries[ $val ];
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Convert the field format into HTML for display.
	 *
	 * @since 2.7
	 *
	 * @param string $format  The format to be used (default or custom).
	 * @param array  $value   The field value.
	 * @param array  $options The field options.
	 *
	 * @return string
	 */
	public static function format_to_html( $format, $value, $options ) {
		$output = '';

		$value = self::format_value_for_output( $value, $options );

		$address = [];

		if ( ! empty( $value['address'] ) ) {
			$address = $value['address'];
		}

		if ( ! empty( $address ) ) {
			// Format in microdata?
			$microdata = ! empty( $options[ static::$type . '_microdata' ] );

			// @todo check pregreplace, maybe this can be done better (nl2br not working)
			// Convert actual line breaks into an array
			$lines = explode( '\r\n', preg_replace( "/\n/m", '\r\n', $format ) );

			foreach ( $lines as $key => $line ) {
				// preg_match to all tags
				preg_match_all( '#{{(.*?)}}#', $line, $tags );

				if ( ! empty( $tags[1] ) ) {
					foreach ( $tags[1] as $tag ) {
						// Default value is empty. Only known tags are allowed, remove all unknown tags
						$tag_value = '';

						if ( ! empty( $address[ $tag ] ) ) {
							// Format the value for HTML
							$tag_value = self::wrap_html_format( $address[ $tag ], $tag, 'span', $microdata );
						}

						$lines[ $key ] = str_replace( '{{' . $tag . '}}', $tag_value, $lines[ $key ] );
					}
				}

				$lines[ $key ] = trim( $lines[ $key ] );

				if ( empty( $lines[ $key ] ) ) {
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
	 * Wrap values in the correct HTML format.
	 *
	 * @param string $value
	 * @param string $tag The address tag.
	 * @param string $element The HTML element.
	 * @param bool   $microdata Whether to enable schema.org microdata.
	 *
	 * @return string
	 */
	public static function wrap_html_format( $value, $tag, $element, $microdata = false ) {
		$atts = [
			'class' => 'pods-address' . $tag,
		];

		switch ( $tag ) {
			case 'address':
				$atts['class'] = 'pods-address';

				if ( $microdata ) {
					$atts['itemprop']  = 'address';
					$atts['itemscope'] = '';
					$atts['itemtype']  = 'http://schema.org/PostalAddress';
				}

				break;

			case 'line_1':
			case 'line_2':
				if ( $microdata ) {
					$atts['itemprop'] = 'streetAddress';
				}

				break;

			case 'postal_code':
				if ( $microdata ) {
					$atts['itemprop'] = 'postalCode';
				}

				break;

			case 'city':
				if ( $microdata ) {
					$atts['itemprop'] = 'addressLocality';
				}

				break;

			case 'region':
				if ( $microdata ) {
					$atts['itemprop'] = 'addressRegion';
				}

				break;

			case 'country':
				if ( $microdata ) {
					$atts['itemprop'] = 'addressCountry';
				}

				break;

			default:
				$atts = false;

				break;
		}

		if ( $atts ) {
			$attributes = '';

			foreach ( $atts as $key => $val ) {
				$attributes .= ' ' . esc_html( $key ) . '="' . esc_attr( $val ) . '"';
			}

			$value = '<' . esc_html( $element ) . $attributes . '>' . wp_kses_post( $value ) . '</' . esc_html( $element ) . '>';
		}

		return $value;
	}

	/**
	 * The default display format
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