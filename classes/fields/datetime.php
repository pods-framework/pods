<?php
/**
 * @package Pods\Fields
 */
class PodsField_DateTime extends PodsField {

	/**
	 * Field Type Group
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $group = 'Date / Time';

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'datetime';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Date / Time';

	/**
	 * Field Type Preparation
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $prepare = '%s';

	/**
	 * Storage format.
	 *
	 * @var string
	 * @since 2.7
	 */
	public static $storage_format = 'Y-m-d H:i:s';

	/**
	 * The default empty value (database)
	 *
	 * @var string
	 * @since 2.7
	 */
	public static $empty_value = '0000-00-00 00:00:00';

	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @since 2.0
	 */
	public function __construct () {
		static::$label = __( 'Date / Time', 'pods' );
	}

	/**
	 * Add options and set defaults to
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public function options () {
		$options = array(
			static::$type . '_repeatable' => array(
				'label' => __( 'Repeatable Field', 'pods' ),
				'default' => 0,
				'type' => 'boolean',
				'help' => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency' => true,
				'developer_mode' => true
			),
			static::$type . '_type' => array(
				'label' => __( 'Date Format Type', 'pods' ),
				'default' => 'format', // Backwards compatibility
				'type' => 'pick',
				'help' => __( 'WordPress Default is the format used in Settings, General under "Date Format".', 'pods' ) . '<br>'
						  . __( 'Predefined Format will allow you to select from a list of commonly used date formats.', 'pods' ) . '<br>'
						  . __( 'Custom will allow you to enter your own using PHP Date/Time Strings.', 'pods' ),
				'data' => array(
					'wp' => __( 'WordPress default', 'pods' ) . ': '. date_i18n( get_option( 'date_format' ) ),
					'format' => __( 'Predefined format', 'pods' ),
					'custom' => __( 'Custom format', 'pods' ),
				),
				'dependency' => true
			),
			static::$type . '_format_custom' => array(
				'label' => __( 'Date format for display', 'pods' ),
				'depends-on' => array( static::$type . '_type' => 'custom' ),
				'default' => '',
				'type' => 'text',
				'help' => '<a href="http://php.net/manual/function.date.php" target="_blank">' . __( 'PHP date documentation', 'pods' ) . '</a>',
			),
			static::$type . '_format_custom_js' => array(
				'label' => __( 'Date format for input', 'pods' ),
				'depends-on' => array( static::$type . '_type' => 'custom' ),
				'default' => '',
				'type' => 'text',
				'help' => '<a href="https://api.jqueryui.com/datepicker/" target="_blank">' . __( 'jQuery UI datepicker documentation', 'pods' ) . '</a>'
						  . '<br>' . __( 'Leave empty to auto-generate from PHP format.', 'pods' ),
			),
			static::$type . '_format' => array(
				'label' => __( 'Date Format', 'pods' ),
				'depends-on' => array( static::$type . '_type' => 'format' ),
				'default' => 'mdy',
				'type' => 'pick',
				'data' => array(
					'mdy' => date_i18n( 'm/d/Y' ),
					'mdy_dash' => date_i18n( 'm-d-Y' ),
					'mdy_dot' => date_i18n( 'm.d.Y' ),
					'ymd_slash' => date_i18n( 'Y/m/d' ),
					'ymd_dash' => date_i18n( 'Y-m-d' ),
					'ymd_dot' => date_i18n( 'Y.m.d' ),
					'fjy' => date_i18n( 'F j, Y' ),
					'fjsy' => date_i18n( 'F jS, Y' ),
					'c' => date_i18n( 'c' ),
				),
				'dependency' => true
			),
			static::$type . '_time_type' => array(
				'label' => __( 'Time Format Type', 'pods' ),
				'excludes-on' => array( static::$type . '_format' => 'c' ),
				'default' => '12', // Backwards compatibility
				'type' => 'pick',
				'help' => __( 'WordPress Default is the format used in Settings, General under "Time Format".', 'pods' ) . '<br>'
						  . __( '12/24 hour will allow you to select from a list of commonly used time formats.', 'pods' ) . '<br>'
						  . __( 'Custom will allow you to enter your own using PHP Date/Time Strings.', 'pods' ),
				'data' => array(
					'wp' => __( 'WordPress default', 'pods' ) . ': ' . date_i18n( get_option( 'time_format' ) ),
					'12' => __( '12 hour', 'pods' ),
					'24' => __( '24 hour', 'pods' ),
					'custom' => __( 'Custom', 'pods' ),
				),
				'dependency' => true
			),
			static::$type . '_time_format_custom' => array(
				'label' => __( 'Time format', 'pods' ),
				'depends-on' => array( static::$type . '_time_type' => 'custom' ),
				'excludes-on' => array( static::$type . '_format' => 'c' ),
				'default' => '',
				'type' => 'text',
				'help' => '<a href="http://php.net/manual/function.date.php" target="_blank">' . __( 'PHP date documentation', 'pods' ) . '</a>',
			),
			static::$type . '_time_format_custom_js' => array(
				'label' => __( 'Time format field input', 'pods' ),
				'depends-on' => array( static::$type . '_time_type' => 'custom' ),
				'excludes-on' => array( static::$type . '_format' => 'c' ),
				'default' => '',
				'type' => 'text',
				'help' => '<a href="http://trentrichardson.com/examples/timepicker/#tp-formatting" target="_blank">' . __( 'jQuery UI timepicker documentation', 'pods' ) . '</a>'
						  . '<br>' . __( 'Leave empty to auto-generate from PHP format.', 'pods' ),
			),
			static::$type . '_time_format' => array(
				'label' => __( 'Time Format', 'pods' ),
				'depends-on' => array( static::$type . '_time_type' => '12' ),
				'excludes-on' => array( static::$type . '_format' => 'c' ),
				'default' => 'h_mma',
				'type' => 'pick',
				'data' => array(
					'h_mm_A' => date_i18n( 'g:i A' ),
					'h_mm_ss_A' => date_i18n( 'g:i:s A' ),
					'hh_mm_A' => date_i18n( 'h:i A' ),
					'hh_mm_ss_A' => date_i18n( 'h:i:s A' ),
					'h_mma' => date_i18n( 'g:ia' ),
					'hh_mma' => date_i18n( 'h:ia' ),
					'h_mm' => date_i18n( 'g:i' ),
					'h_mm_ss' => date_i18n( 'g:i:s' ),
					'hh_mm' => date_i18n( 'h:i' ),
					'hh_mm_ss' => date_i18n( 'h:i:s' ),
				)
			),
			static::$type . '_time_format_24' => array(
				'label' => __( 'Time Format', 'pods' ),
				'depends-on' => array( static::$type . '_time_type' => '24' ),
				'excludes-on' => array( static::$type . '_format' => 'c' ),
				'default' => 'hh_mm',
				'type' => 'pick',
				'data' => array(
					'hh_mm' => date_i18n( 'H:i' ),
					'hh_mm_ss' => date_i18n( 'H:i:s' )
				)
			),
			static::$type . '_allow_empty' => array(
				'label' => __( 'Allow empty value?', 'pods' ),
				'default' => 1,
				'type' => 'boolean'
			),
			static::$type . '_html5' => array(
				'label' => __( 'Enable HTML5 Input Field?', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_html5', 0, static::$type ),
				'type' => 'boolean'
			)
		);

		// Check if PHP DateTime::createFromFormat exists for additional supported formats
		if ( method_exists( 'DateTime', 'createFromFormat' ) || apply_filters( 'pods_form_ui_field_datetime_custom_formatter', false ) ) {
			$options[ static::$type . '_format' ][ 'data' ] = array_merge(
				$options[ static::$type . '_format' ][ 'data' ],
				array(
					'dmy' => date_i18n( 'd/m/Y' ),
					'dmy_dash' => date_i18n( 'd-m-Y' ),
					'dmy_dot' => date_i18n( 'd.m.Y' ),
					'dMy' => date_i18n( 'd/M/Y' ),
					'dMy_dash' => date_i18n( 'd-M-Y' )
				)
			);
		}

		$options[ static::$type . '_format' ][ 'data' ] = apply_filters( 'pods_form_ui_field_date_format_options', $options[ static::$type . '_format' ][ 'data' ] );
		$options[ static::$type . '_format' ][ 'default' ] = apply_filters( 'pods_form_ui_field_date_format_default', $options[ static::$type . '_format' ][ 'default' ] );

		$options[ static::$type . '_time_type' ][ 'default' ] = apply_filters( 'pods_form_ui_field_time_format_type_default', $options[ static::$type . '_time_type' ][ 'default' ] );
		$options[ static::$type . '_time_format' ][ 'data' ] = apply_filters( 'pods_form_ui_field_time_format_options', $options[ static::$type . '_time_format' ][ 'data' ] );
		$options[ static::$type . '_time_format' ][ 'default' ] = apply_filters( 'pods_form_ui_field_time_format_default', $options[ static::$type . '_time_format' ][ 'default' ] );
		$options[ static::$type . '_time_format_24' ][ 'data' ] = apply_filters( 'pods_form_ui_field_time_format_24_options', $options[ static::$type . '_time_format_24' ][ 'data' ] );
		$options[ static::$type . '_time_format_24' ][ 'default' ] = apply_filters( 'pods_form_ui_field_time_format_24_default', $options[ static::$type . '_time_format_24' ][ 'default' ] );

		return $options;
	}

	/**
	 * Define the current field's schema for DB table storage
	 *
	 * @param array $options
	 *
	 * @return string
	 * @since 2.0
	 */
	public function schema ( $options = null ) {
		$schema = 'DATETIME NOT NULL default "0000-00-00 00:00:00"';

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_empty( $value = null ) {

		$is_empty = false;

		$value = trim( $value );

		if ( empty( $value ) || in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
			$is_empty = true;
		}

		return $is_empty;

	}

	/**
	 * Change the way the value of the field is displayed with Pods::get
	 *
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param array $pod
	 * @param int $id
	 *
	 * @return mixed|null|string
	 * @since 2.0
	 */
	public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$value = $this->format_value_display( $value, $options, false );

		return $value;
	}

	/**
	 * Customize output of the form field
	 *
	 * @param string $name
	 * @param mixed $value
	 * @param array $options
	 * @param array $pod
	 * @param int $id
	 *
	 * @since 2.0
	 */
	public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
		$options = (array) $options;
		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) )
			$value = implode( ' ', $value );

		// Format Value
		$value = $this->format_value_display( $value, $options, true );

		$field_type = static::$type;

		if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( static::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options[ 'readonly' ] = true;

				$field_type = 'text';
			}
			else
				return;
		}
		elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options[ 'readonly' ] = true;

			$field_type = 'text';
		}

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		if ( ! empty( $value ) && ( 0 == pods_v( static::$type . '_allow_empty', $options, 1 ) || ! in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) ) {
			$js = true;

			if ( 'custom' !== pods_v( static::$type . '_type', $options, 'format' ) ) {
				$js = false;
			}

			$format = $this->format( $options, $js );

			if ( $js ) {
				$format = $this->convert_format( $format, array( 'source' => 'jquery_ui' ) );
			}

			$check = $this->convert_date( $value, static::$storage_format, $format, true );

			if ( false === $check ) {
				$label = pods_var( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) );

				return sprintf( esc_html__( '%1$s was not provided in a recognizable format: "%2$s"', 'pods' ), $label, $value );
			}
		}

		return true;

	}

	/**
	 * Change the value or perform actions after validation but before saving to the DB
	 *
	 * @param mixed $value
	 * @param int $id
	 * @param string $name
	 * @param array $options
	 * @param array $fields
	 * @param array $pod
	 * @param object $params
	 *
	 * @return mixed|string
	 * @since 2.0
	 */
	public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		$js = true;
		if ( 'custom' !== pods_v( static::$type . '_type', $options, 'format' ) ) {
			$js = false;
		}
		$format = $this->format( $options, $js );
		if ( $js ) {
			$format = $this->convert_format( $format, array( 'source' => 'jquery_ui' ) );
		}

		if ( ! empty( $value ) && ( 0 == pods_v( static::$type . '_allow_empty', $options, 1 ) || ! in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) )
			$value = $this->convert_date( $value, static::$storage_format, $format );
		elseif ( 1 == pods_v( static::$type . '_allow_empty', $options, 1 ) )
			$value = static::$empty_value;
		else
			$value = date_i18n( static::$storage_format );

		return $value;
	}

	/**
	 * Customize the Pods UI manage table column output
	 *
	 * @param int $id
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param array $fields
	 * @param array $pod
	 *
	 * @return mixed|null|string
	 * @since 2.0
	 */
	public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		$value = $this->display( $value, $name, $options, $pod, $id );

		if ( 1 == pods_v( static::$type . '_allow_empty', $options, 1 ) && ( empty( $value ) || in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) )
			$value = false;

		return $value;
	}

	/**
	 * Convert value to the correct format for display.
	 *
	 * @param string $value
	 * @param array  $options
	 * @param bool   $js       Return formatted from jQuery UI format? (only for custom formats).
	 * @return string
	 * @since 2.7
	 */
	public function format_value_display( $value, $options, $js = false ) {
		if ( 'custom' !== pods_v( static::$type . '_type', $options, 'format' ) ) {
			$js = false;
		}
		$format = $this->format( $options, $js );
		if ( $js ) {
			$format = $this->convert_format( $format, array( 'source' => 'jquery_ui' ) );
		}

		if ( ! empty( $value ) && ! in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
			// Try default storage format.
			$date = $this->createFromFormat( static::$storage_format, (string) $value );
			// Try field format.
			$date_local = $this->createFromFormat( $format, (string) $value );

			if ( $date instanceof DateTime )
				$value = $date->format( $format );
			elseif ( $date_local instanceof DateTime )
				$value = $date_local->format( $format );
			else
				$value = date_i18n( $format, strtotime( (string) $value ) );
		}
		elseif ( 0 == pods_v( static::$type . '_allow_empty', $options, 1 ) )
			$value = date_i18n( $format );
		else
			$value = '';

		return $value;
	}

	/**
	 * Build date/time format string based on options
	 *
	 * @param array $options
	 * @param bool  $js       Return format for jQuery UI?
	 *
	 * @return string
	 * @since 2.0
	 */
	public function format ( $options, $js = false ) {

		$format = $this->format_date( $options, $js );

		$type = pods_v( static::$type . '_type', $options, 'format' );

		if ( 'format' !== $type || 'c' !== pods_v( static::$type . '_format', $options, '' ) ) {
			$format .= ' ' . $this->format_time( $options, $js );
		}

		return $format;
	}

	/**
	 * Build date format string based on options
	 *
	 * @since  2.7
	 *
	 * @param  array $options
	 * @param  bool  $js       Return format for jQuery UI?
	 * @return string
	 */
	public function format_date( $options, $js = false ) {

		switch ( (string) pods_v( static::$type . '_type', $options, 'format', true ) ) {
			case 'wp':
				$format = get_option( 'date_format' );
				if ( $js ) {
					$format = $this->convert_format( $format, array( 'source' => 'php' ) );
				}
			break;
			case 'custom':
				if ( ! $js ) {
					$format = pods_v( static::$type . '_format_custom', $options, '' );
				} else {
					$format = pods_v( static::$type . '_format_custom_js', $options, '' );
					if ( empty( $format ) ) {
						$format = pods_v( static::$type . '_format_custom', $options, '' );
						$format = $this->convert_format( $format, array( 'source' => 'php' ) );
					}
				}
			break;
			default:
				$date_format = $this->get_date_formats( $js );
				$format = $date_format[ pods_v( static::$type . '_format', $options, 'ymd_dash', true ) ];
			break;
		}

		return $format;
	}

	/**
	 * Build time format string based on options
	 *
	 * @since  2.7
	 *
	 * @param  array $options
	 * @param  bool  $js       Return format for jQuery UI?
	 * @return string
	 */
	public function format_time( $options, $js = false ) {

		switch ( (string) pods_v( static::$type . '_time_type', $options, '12', true ) ) {
			case '12':
				$time_format = $this->get_time_formats( $js );
				$format = $time_format[ pods_v( static::$type . '_time_format', $options, 'hh_mm', true ) ];
			break;
			case '24':
				$time_format_24 = $this->get_time_formats_24( $js );
				$format = $time_format_24[ pods_v( static::$type . '_time_format_24', $options, 'hh_mm', true ) ];
			break;
			case 'custom':
				if ( ! $js ) {
					$format = pods_v( static::$type . '_time_format_custom', $options, '' );
				} else {
					$format = pods_v( static::$type . '_time_format_custom_js', $options, '' );
					if ( empty( $format ) ) {
						$format = pods_v( static::$type . '_time_format_custom', $options, '' );
						$format = $this->convert_format( $format, array( 'source' => 'php' ) );
					}
				}
			break;
			default:
				$format = get_option( 'time_format' );
				if ( $js ) {
					$format = $this->convert_format( $format, array( 'source' => 'php' ) );
				}
			break;
		}

		return $format;
	}

	/**
	 * Get the date formats.
	 *
	 * @since  2.7
	 *
	 * @param  bool  $js       Return formats for jQuery UI?
	 * @return array
	 */
	public function get_date_formats( $js = false ) {
		$date_format = array(
			'mdy'       => 'm/d/Y',
			'mdy_dash'  => 'm-d-Y',
			'mdy_dot'   => 'm.d.Y',
			'dmy'       => 'd/m/Y',
			'dmy_dash'  => 'd-m-Y',
			'dmy_dot'   => 'd.m.Y',
			'ymd_slash' => 'Y/m/d',
			'ymd_dash'  => 'Y-m-d',
			'ymd_dot'   => 'Y.m.d',
			'dMy'       => 'd/M/Y',
			'dMy_dash'  => 'd-M-Y',
			'fjy'       => 'F j, Y',
			'fjsy'      => 'F jS, Y',
			'y'         => 'Y',
		);
		$filter = 'pods_form_ui_field_date_formats';
		if ( $js ) {
			// @todo Method parameters? (Not supported by array_map)
			$date_format = array_map( array( $this, 'convert_format' ), $date_format );
			$filter = 'pods_form_ui_field_date_js_formats';
		}
		return apply_filters( $filter, $date_format );
	}

	/**
	 * Get the time formats.
	 *
	 * @since  2.7
	 *
	 * @param  bool  $js       Return formats for jQuery UI?
	 * @return array
	 */
	public function get_time_formats( $js = false ) {
		$time_format = array(
			'h_mm_A'     => 'g:i A',
			'h_mm_ss_A'  => 'g:i:s A',
			'hh_mm_A'    => 'h:i A',
			'hh_mm_ss_A' => 'h:i:s A',
			'h_mma'      => 'g:ia',
			'hh_mma'     => 'h:ia',
			'h_mm'       => 'g:i',
			'h_mm_ss'    => 'g:i:s',
			'hh_mm'      => 'h:i',
			'hh_mm_ss'   => 'h:i:s',
		);
		$filter = 'pods_form_ui_field_time_formats';
		if ( $js ) {
			// @todo Method parameters? (Not supported by array_map)
			$time_format = array_map( array( $this, 'convert_format' ), $time_format );
			$filter = 'pods_form_ui_field_time_js_formats';
		}
		return apply_filters( $filter, $time_format );
	}

	/**
	 * Get the time formats.
	 *
	 * @since  2.7
	 *
	 * @param  bool  $js       Return formats for jQuery UI?
	 * @return array
	 */
	public function get_time_formats_24( $js = false ) {
		$time_format_24 = array(
			'hh_mm'    => 'H:i',
			'hh_mm_ss' => 'H:i:s'
		);
		$filter = 'pods_form_ui_field_time_formats_24';
		if ( $js ) {
			// @todo Method parameters? (Not supported by array_map)
			$time_format_24 = array_map( array( $this, 'convert_format' ), $time_format_24 );
			$filter = 'pods_form_ui_field_time_js_formats_24';
		}
		return apply_filters( $filter, $time_format_24 );
	}

	/**
	 * @param string $format
	 * @param string $date Defaults to time() if empty.
	 * @param boolean $return_timestamp Whether to return the strtotime() or createFromFormat result or not
	 *
	 * @return DateTime|null|int|false
	 */
	public function createFromFormat ( $format, $date, $return_timestamp = false ) {
		$datetime = null;

		if ( method_exists( 'DateTime', 'createFromFormat' ) ) {
			$timezone = get_option( 'timezone_string' );

			if ( empty( $timezone ) )
				$timezone = timezone_name_from_abbr( '', get_option( 'gmt_offset' ) * HOUR_IN_SECONDS, 0 );

			if ( !empty( $timezone ) ) {
				$datetimezone = new DateTimeZone( $timezone );

				$datetime = DateTime::createFromFormat( $format, (string) $date, $datetimezone );

				if ( false === $datetime ) {
					$datetime = DateTime::createFromFormat( static::$storage_format, (string) $date, $datetimezone );
				}

				if ( false !== $datetime && $return_timestamp ) {
					return $datetime;
				}
			}
		}

		if ( in_array( $datetime, array( null, false ), true ) ) {
			if ( empty( $date ) ) {
				$timestamp = time();
			} else {
				$timestamp = strtotime( (string) $date );

				if ( $return_timestamp ) {
					return $timestamp;
				}
			}
			if ( $timestamp ) {
				$datetime = new DateTime( date_i18n( static::$storage_format, $timestamp ) );
			}
		}

		return apply_filters( 'pods_form_ui_field_datetime_formatter', $datetime, $format, $date );
	}

	/**
	 * Convert a date from one format to another
	 *
	 * @param $value
	 * @param $new_format
	 * @param string $original_format
	 * @param boolean $return_timestamp Whether to return the strtotime() or createFromFormat result or not
	 *
	 * @return string|int|boolean|DateTime
	 */
	public function convert_date ( $value, $new_format, $original_format = '', $return_timestamp = false ) {
		if ( empty( $original_format ) ) {
			$original_format = static::$storage_format;
		}

		$date = '';

		if ( ! empty( $value ) && ! in_array( $value, array( '0000-00-00', '0000-00-00 00:00:00', '00:00:00' ) ) ) {
			$date = $this->createFromFormat( $original_format, (string) $value, $return_timestamp );

			if ( $date instanceof DateTime ) {
				$value = $date->format( $new_format );
			} elseif ( false !== $date ) {
				$date = strtotime( (string) $value );

				$value = date_i18n( $new_format, $date );
			}
		} else {
			$value = date_i18n( $new_format );
		}

		// Return timestamp conversion result instead
		if ( $return_timestamp ) {
			return $date;
		}

		return $value;
	}

	/**
	 * Matches each symbol of PHP date format standard with jQuery equivalent codeword.
	 *
	 * @link http://stackoverflow.com/questions/16702398/convert-a-php-date-format-to-a-jqueryui-datepicker-date-format
	 * @link https://api.jqueryui.com/datepicker/
	 * @link http://trentrichardson.com/examples/timepicker/
	 *
	 * @since  2.7
	 *
	 * @param  string  $source_format
	 * @param  array   $args
	 * @return string
	 */
	public function convert_format( $source_format, $args = array() ) {
		// @todo Improve source/target logic.
		$args = array_merge( array(
			'source' => 'php', // 'jquery_ui' for reverse.
		), $args );

		// Keep keys and values sorted by string length.
		$symbols = array(
			// Day
			'd' => 'dd',
			'l' => 'DD',
			'D' => 'D',
			'j' => 'd',
			'N' => '',
			'S' => '',
			'w' => '',
			'z' => 'o',
			// Week
			'W' => '',
			// Month
			'F' => 'MM',
			'm' => 'mm',
			'M' => 'M',
			'n' => 'm',
			't' => '',
			// Year
			'L' => '',
			'o' => '',
			'Y' => 'yy',
			'y' => 'y',
			// AM/PM
			'a' => 'tt',
			'A' => 'TT',
			// Swatch internet time (not supported)
			'B' => '',
			// Hour
			'h' => 'hh',
			'H' => 'HH',
			'g' => 'h',
			'G' => 'H',
			// Minute
			'i' => 'mm',
			// Second
			's' => 'ss',
			// Microsecond
			'u' => 'c',
		);
		if ( version_compare( PHP_VERSION, '7.0.0' ) >= 0 ) {
			// Millisecond
			$symbols['v'] = 'l';
		}
		if ( 'jquery_ui' === $args[ 'source' ] ) {
			// Remove empty values.
			$symbols = array_filter( $symbols );
			$symbols = array_flip( $symbols );
		}
		$new_format = "";
		$escaping = false;
		for( $i = 0; $i < strlen( $source_format ); $i++ ) {
			$char = $source_format[ $i ];
			// PHP date format escaping character
			// @todo Do we want to support non-format characters?
			if( $char === '\\' ) {
				$i++;
				if( $escaping ) {
					$new_format .= $source_format[ $i ];
				}
				else {
					$new_format .= '\'' . $source_format[ $i ];
				}
				$escaping = true;
			} else {
				if( $escaping ) {
					$new_format .= "'"; $escaping = false;
				}
				// Support 2 characters.
				if ( isset( $source_format[ $i + 1 ] ) && isset( $symbols[ $char . $source_format[ $i + 1 ] ] ) ) {
					$new_format .= $symbols[ $char . $source_format[ $i + 1 ] ];
					$i++;
				}
				elseif ( isset ( $symbols[ $char ] ) ) {
					$new_format .= $symbols[ $char ];
				}
				else {
					$new_format .= $char;
				}
			}
		}
		return $new_format;
	}

	/**
	 * Enqueue the i18n files for jquery date/timepicker
	 *
	 * @since  2.7
	 */
	public function enqueue_jquery_ui_i18n() {
		static $done = array();

		$types = array();
		switch ( static::$type ) {
			case 'time':
				$types[] = 'time';
			break;
			case 'date':
				$types[] = 'date';
			break;
			case 'datetime':
				$types[] = 'time';
				$types[] = 'date';
			break;
		}

		if ( in_array( 'date', $types, true ) && ! in_array( 'date', $done, true ) ) {

			if ( function_exists( 'wp_localize_jquery_ui_datepicker' ) ) {
				wp_localize_jquery_ui_datepicker();
			}

			$done[] = 'date';
		}

		if ( in_array( 'time', $types, true ) && ! in_array( 'time', $done, true ) ) {

			$locale = str_replace( '_', '-', get_locale() );

			// Local files.
			if ( ! file_exists( PODS_DIR . 'ui/js/timepicker/i18n/jquery-ui-timepicker-' . $locale . '.js' ) ) {
				// Fallback to the base language (non-region specific).
				$locale = substr( $locale, 0, strpos( $locale, '-' ) );
			}

			if ( ! wp_script_is( 'jquery-ui-timepicker-i18n-' . $locale, 'registered' ) &&
			     file_exists( PODS_DIR . 'ui/js/timepicker/i18n/jquery-ui-timepicker-' . $locale . '.js' )
			) {
				wp_enqueue_script(
					'jquery-ui-timepicker-i18n-' . $locale,
					PODS_URL . 'ui/js/timepicker/i18n/jquery-ui-timepicker-' . $locale . '.js',
					array( 'jquery-ui-timepicker' ),
					'1.6.3'
				);
			}

			$done[] = 'time';
		}
	}
}
