<?php
require_once PODS_DIR . 'classes/fields/datetime.php';

/**
 * @package Pods\Fields
 */
class PodsField_Time extends PodsField_DateTime {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Date / Time';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'time';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Time';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * Storage format.
	 *
	 * @var string
	 * @since 2.7.0
	 */
	public static $storage_format = 'H:i:s';

	/**
	 * The default empty value (database)
	 *
	 * @var string
	 * @since 2.7.0
	 */
	public static $empty_value = '';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$label = __( 'Time', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			static::$type . '_repeatable'       => array(
				'label'             => __( 'Repeatable Field', 'pods' ),
				'default'           => 0,
				'type'              => 'boolean',
				'help'              => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency'        => true,
				'developer_mode'    => true,
			),
			static::$type . '_type'             => array(
				'label'                        => __( 'Time Format Type', 'pods' ),
				'default'                      => '12',
				// Backwards compatibility
										'type' => 'pick',
				'help'                         => __( 'WordPress Default is the format used in Settings, General under "Time Format".', 'pods' ) . '<br>' . __( '12/24 hour will allow you to select from a list of commonly used time formats.', 'pods' ) . '<br>' . __( 'Custom will allow you to enter your own using PHP Date/Time Strings.', 'pods' ),
				'data'                         => array(
					'wp'     => __( 'WordPress default', 'pods' ) . ': ' . date_i18n( get_option( 'time_format' ) ),
					'12'     => __( '12 hour', 'pods' ),
					'24'     => __( '24 hour', 'pods' ),
					'custom' => __( 'Custom format', 'pods' ),
				),
				'dependency'                   => true,
			),
			static::$type . '_format_custom'    => array(
				'label'      => __( 'Time format for display', 'pods' ),
				'depends-on' => array( static::$type . '_type' => 'custom' ),
				'default'    => '',
				'type'       => 'text',
				'help'       => sprintf(
					'<a href="http://php.net/manual/function.date.php" target="_blank">%s</a>',
					esc_html__( 'PHP date documentation', 'pods' )
				),
			),
			static::$type . '_format_custom_js' => array(
				'label'      => __( 'Time format for input', 'pods' ),
				'depends-on' => array( static::$type . '_type' => 'custom' ),
				'default'    => '',
				'type'       => 'text',
				'help'       => sprintf(
					'<a href="http://trentrichardson.com/examples/timepicker/#tp-formatting" target="_blank">%1$s</a><br />%2$s',
					esc_html__( 'jQuery UI timepicker documentation', 'pods' ),
					esc_html__( 'Leave empty to auto-generate from PHP format.', 'pods' )
				),
			),
			static::$type . '_format'           => array(
				'label'      => __( 'Time Format', 'pods' ),
				'depends-on' => array( static::$type . '_type' => '12' ),
				'default'    => 'h_mma',
				'type'       => 'pick',
				'data'       => array(
					'h_mm_A'     => date_i18n( 'g:i A' ),
					'h_mm_ss_A'  => date_i18n( 'g:i:s A' ),
					'hh_mm_A'    => date_i18n( 'h:i A' ),
					'hh_mm_ss_A' => date_i18n( 'h:i:s A' ),
					'h_mma'      => date_i18n( 'g:ia' ),
					'hh_mma'     => date_i18n( 'h:ia' ),
					'h_mm'       => date_i18n( 'g:i' ),
					'h_mm_ss'    => date_i18n( 'g:i:s' ),
					'hh_mm'      => date_i18n( 'h:i' ),
					'hh_mm_ss'   => date_i18n( 'h:i:s' ),
				),
				'dependency' => true,
			),
			static::$type . '_format_24'        => array(
				'label'      => __( 'Time Format', 'pods' ),
				'depends-on' => array( static::$type . '_type' => '24' ),
				'default'    => 'hh_mm',
				'type'       => 'pick',
				'data'       => array(
					'hh_mm'    => date_i18n( 'H:i' ),
					'hh_mm_ss' => date_i18n( 'H:i:s' ),
				),
			),
			static::$type . '_allow_empty'      => array(
				'label'   => __( 'Allow empty value?', 'pods' ),
				'default' => 1,
				'type'    => 'boolean',
			),
			static::$type . '_html5'            => array(
				'label'   => __( 'Enable HTML5 Input Field?', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_html5', 0, static::$type ),
				'type'    => 'boolean',
			),
		);

		$options[ static::$type . '_type' ]['default']      = apply_filters( 'pods_form_ui_field_time_format_type_default', $options[ static::$type . '_type' ]['default'] );
		$options[ static::$type . '_format' ]['data']       = apply_filters( 'pods_form_ui_field_time_format_options', $options[ static::$type . '_format' ]['data'] );
		$options[ static::$type . '_format' ]['default']    = apply_filters( 'pods_form_ui_field_time_format_default', $options[ static::$type . '_format' ]['default'] );
		$options[ static::$type . '_format_24' ]['data']    = apply_filters( 'pods_form_ui_field_time_format_24_options', $options[ static::$type . '_format_24' ]['data'] );
		$options[ static::$type . '_format_24' ]['default'] = apply_filters( 'pods_form_ui_field_time_format_24_default', $options[ static::$type . '_format_24' ]['default'] );

		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$schema = 'TIME NOT NULL default "00:00:00"';

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_empty( $value = null ) {

		$value = trim ( $value );
		return empty( $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function format_display( $options, $js = false ) {

		if ( $js && 'custom' === pods_v( static::$type . '_type', $options, 'format' ) ) {
			$format = $this->format_datetime( $options, $js );
			return $this->convert_format( $format, array( 'source' => 'jquery_ui', 'type' => 'time' ) );
		}

		return parent::format_display( $options, $js );
	}

	/**
	 * {@inheritdoc}
	 */
	public function format_datetime( $options, $js = false ) {

		return $this->format_time( $options, $js );
	}

	/**
	 * Build time format string based on options
	 *
	 * @param array $options Field options.
	 * @param bool  $js      Return formatted from jQuery UI format? (only for custom formats).
	 *
	 * @return string
	 * @since 2.7.0
	 */
	public function format_time( $options, $js = false ) {

		switch ( (string) pods_v( static::$type . '_type', $options, '12', true ) ) {
			case '12':
				$time_format = $this->get_time_formats( $js );

				$format = $time_format[ pods_v( static::$type . '_format', $options, 'hh_mm', true ) ];

				break;
			case '24':
				$time_format_24 = $this->get_time_formats_24( $js );

				$format = $time_format_24[ pods_v( static::$type . '_format_24', $options, 'hh_mm', true ) ];

				break;
			case 'custom':
				if ( ! $js ) {
					$format = pods_v( static::$type . '_format_custom', $options, '' );
				} else {
					$format = pods_v( static::$type . '_format_custom_js', $options, '' );

					if ( empty( $format ) ) {
						$format = pods_v( static::$type . '_format_custom', $options, '' );

						if ( $js ) {
							$format = $this->convert_format( $format, array( 'source' => 'php', 'type' => 'time' ) );
						}
					}
				}

				break;
			default:
				$format = get_option( 'time_format' );

				if ( $js ) {
					$format = $this->convert_format( $format, array( 'source' => 'php', 'type' => 'time' ) );
				}

				break;
		}//end switch

		return $format;
	}
}
