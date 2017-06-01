<?php
require_once( PODS_DIR . 'classes/fields/datetime.php' );
/**
 * @package Pods\Fields
 */
class PodsField_Date extends PodsField_DateTime {

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
	public static $type = 'date';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Date';

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
	public static $storage_format = 'Y-m-d';

	/**
	 * The default empty value (database)
	 *
	 * @var string
	 * @since 2.7
	 */
	public static $empty_value = '0000-00-00';

	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @since 2.0
	 */
	public function __construct () {
		static::$label = __( 'Date', 'pods' );
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
					'wp' => __( 'WordPress default', 'pods' ) . ': ' . date_i18n( get_option( 'date_format' ) ),
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
					'y' => date_i18n( 'Y' ),
				),
				'dependency' => true,
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
		$schema = 'DATE NOT NULL default "0000-00-00"';

		return $schema;
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
		// @see datetime field.
		return $this->format_date( $options, $js );
	}
}
