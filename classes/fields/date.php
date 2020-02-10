<?php
require_once PODS_DIR . 'classes/fields/datetime.php';

/**
 * @package Pods\Fields
 */
class PodsField_Date extends PodsField_DateTime {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Date / Time';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'date';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Date';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public static $storage_format = 'Y-m-d';

	/**
	 * {@inheritdoc}
	 */
	public static $empty_value = '0000-00-00';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$label = __( 'Date', 'pods' );
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
				'label'      => __( 'Date Format Type', 'pods' ),
				'default'    => 'format',
				// Backwards compatibility
				'type'       => 'pick',
				'help'       => __( 'WordPress Default is the format used in Settings, General under "Date Format".', 'pods' ) . '<br>' . __( 'Predefined Format will allow you to select from a list of commonly used date formats.', 'pods' ) . '<br>' . __( 'Custom will allow you to enter your own using PHP Date/Time Strings.', 'pods' ),
				'data'       => array(
					'wp'     => __( 'WordPress default', 'pods' ) . ': ' . date_i18n( get_option( 'date_format' ) ),
					'format' => __( 'Predefined format', 'pods' ),
					'custom' => __( 'Custom format', 'pods' ),
				),
				'dependency' => true,
			),
			static::$type . '_format_custom'    => array(
				'label'      => __( 'Date format for display', 'pods' ),
				'depends-on' => array( static::$type . '_type' => 'custom' ),
				'default'    => '',
				'type'       => 'text',
				'help'       => sprintf(
					'<a href="http://php.net/manual/function.date.php" target="_blank">%s</a>',
					esc_html__( 'PHP date documentation', 'pods' )
				),
			),
			static::$type . '_format_custom_js' => array(
				'label'      => __( 'Date format for input', 'pods' ),
				'depends-on' => array( static::$type . '_type' => 'custom' ),
				'default'    => '',
				'type'       => 'text',
				'help'       => sprintf(
					'<a href="https://api.jqueryui.com/datepicker/" target="_blank">%1$s</a><br />%2$s',
					esc_html__( 'jQuery UI datepicker documentation', 'pods' ),
					esc_html__( 'Leave empty to auto-generate from PHP format.', 'pods' )
				),
			),
			static::$type . '_format'           => array(
				'label'      => __( 'Date Format', 'pods' ),
				'depends-on' => array( static::$type . '_type' => 'format' ),
				'default'    => 'mdy',
				'type'       => 'pick',
				'data'       => array(
					'mdy'       => date_i18n( 'm/d/Y' ),
					'mdy_dash'  => date_i18n( 'm-d-Y' ),
					'mdy_dot'   => date_i18n( 'm.d.Y' ),
					'ymd_slash' => date_i18n( 'Y/m/d' ),
					'ymd_dash'  => date_i18n( 'Y-m-d' ),
					'ymd_dot'   => date_i18n( 'Y.m.d' ),
					'fjy'       => date_i18n( 'F j, Y' ),
					'fjsy'      => date_i18n( 'F jS, Y' ),
					'y'         => date_i18n( 'Y' ),
				),
				'dependency' => true,
			),
			static::$type . '_year_range_custom' => array(
				'label'   => __( 'Year range', 'pods' ),
				'default' => '',
				'type'    => 'text',
				'help'    => sprintf(
					'%1$s<br /><a href="https://api.jqueryui.com/datepicker/#option-yearRange" target="_blank">%2$s</a>',
					sprintf(
						esc_html__( 'Example: %1$s for specifying a hard coded year range or %2$s for the last and next 10 years.', 'pods' ),
						'<code>2010:2030</code>',
						'<code>-10:+10</code>'
					),
					esc_html__( 'jQuery UI datepicker documentation', 'pods' )
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

		// Check if PHP DateTime::createFromFormat exists for additional supported formats
		if ( method_exists( 'DateTime', 'createFromFormat' ) || apply_filters( 'pods_form_ui_field_datetime_custom_formatter', false ) ) {
			$options[ static::$type . '_format' ]['data'] = array_merge(
				$options[ static::$type . '_format' ]['data'], array(
					'dmy'      => date_i18n( 'd/m/Y' ),
					'dmy_dash' => date_i18n( 'd-m-Y' ),
					'dmy_dot'  => date_i18n( 'd.m.Y' ),
					'dMy'      => date_i18n( 'd/M/Y' ),
					'dMy_dash' => date_i18n( 'd-M-Y' ),
				)
			);
		}

		$options[ static::$type . '_format' ]['data']    = apply_filters( 'pods_form_ui_field_date_format_options', $options[ static::$type . '_format' ]['data'] );
		$options[ static::$type . '_format' ]['default'] = apply_filters( 'pods_form_ui_field_date_format_default', $options[ static::$type . '_format' ]['default'] );

		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$schema = 'DATE NOT NULL default "0000-00-00"';

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function format_display( $options, $js = false ) {

		if ( $js && 'custom' === pods_v( static::$type . '_type', $options, 'format' ) ) {
			$format = $this->format_datetime( $options, $js );
			return $this->convert_format( $format, array( 'source' => 'jquery_ui', 'type' => 'date' ) );
		}

		return parent::format_display( $options, $js );
	}

	/**
	 * {@inheritdoc}
	 */
	public function format_datetime( $options, $js = false ) {

		return $this->format_date( $options, $js );
	}
}
