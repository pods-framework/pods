<?php

/**
 * @package Pods\Fields
 */
class PodsField_Currency extends PodsField {

	/**
	 * Field Type Group
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $group = 'Number';

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'currency';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Currency';

	/**
	 * Field Type Preparation
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $prepare = '%d';

	/**
	 * Currency Formats
	 *
	 * @var array
	 * @since 2.0
	 */
	public static $currencies = array();

	/**
	 * Do things like register/enqueue scripts and stylesheets
	 *
	 * @since 2.0
	 */
	public function __construct() {
		self::$label = __( 'Currency', 'pods' );
		self::data_currencies();
	}

	/**
	 * Add options and set defaults to
	 *
	 * @return array
	 *
	 * @since 2.0
	 */
	public function options() {

		$currency_options = array();
		foreach ( self::$currencies as $key => $value ) {
			$currency = $value['label'];
			if ( $value['label'] != $value['name'] ) {
				$currency .= ': ' . $value['name'];
			}
			$currency .= ' (' . $value['sign'] . ')';
			$currency_options[ $key ] = $currency;
		}

		$options = array(
			self::$type . '_repeatable' => array(
				'label' => __( 'Repeatable Field', 'pods' ),
				'default' => 0,
				'type' => 'boolean',
				'help' => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency' => true,
				'developer_mode' => true
			),
			self::$type . '_format_type' => array(
				'label' => __( 'Input Type', 'pods' ),
				'default' => 'number',
				'type' => 'pick',
				'data' => array(
					'number' => __( 'Freeform Number', 'pods' ),
					'slider' => __( 'Slider', 'pods' )
				),
				'dependency' => true
			),
			self::$type . '_format_sign' => array(
				'label' => __( 'Currency Sign', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_number_currency_default', 'usd' ),
				'type' => 'pick',
				'data' => apply_filters( 'pods_form_ui_field_number_currency_options', $currency_options )
			),
			self::$type . '_format_placement' => array(
				'label' => __( 'Currency Placement', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_number_currency_placement_default', 'before' ),
				'type' => 'pick',
				'data' => array(
					'before' => __( 'Before (ex. $100)', 'pods' ),
					'after' => __( 'After (ex. 100$)', 'pods' ),
					'none' => __( 'None (ex. 100)', 'pods' ),
					'beforeaftercode' => __( 'Before with Currency Code after (ex. $100 USD)', 'pods' )
				)
			),
			self::$type . '_format' => array(
				'label' => __( 'Format', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_number_currency_format_default', 'i18n' ),
				'type' => 'pick',
				'data' => array(
					'i18n' => __( 'Localized Default', 'pods' ),
					'9,999.99' => '1,234.00',
					'9\'999.99' => '1\'234.00',
					'9.999,99' => '1.234,00',
					'9 999,99' => '1 234,00',
					'9999.99' => '1234.00',
					'9999,99' => '1234,00'
				)
			),
			self::$type . '_decimals' => array(
				'label' => __( 'Decimals', 'pods' ),
				'default' => 2,
				'type' => 'number'
			),
			self::$type . '_decimal_handling' => array(
				'label' => __( 'Decimal handling when zero', 'pods' ),
				'default' => 'none',
				'type' => 'pick',
				'data' => array(
					'none' => __( 'Default', 'pods' ),
					'remove' => __( 'Remove decimals', 'pods' ),
					'dash' => __( 'Convert to dash', 'pods' ) . ' (-)',
				)
			),
			self::$type . '_step' => array(
				'label' => __( 'Slider Increment (Step)', 'pods' ),
				'depends-on' => array( self::$type . '_format_type' => 'slider' ),
				'default' => 1,
				'type' => 'text'
			),
			self::$type . '_min' => array(
				'label' => __( 'Minimum Number', 'pods' ),
				'depends-on' => array( self::$type . '_format_type' => 'slider' ),
				'default' => 0,
				'type' => 'text'
			),
			self::$type . '_max' => array(
				'label' => __( 'Maximum Number', 'pods' ),
				'depends-on' => array( self::$type . '_format_type' => 'slider' ),
				'default' => 1000,
				'type' => 'text'
			),
			self::$type . '_max_length' => array(
				'label' => __( 'Maximum Length', 'pods' ),
				'default' => 12,
				'type' => 'number',
				'help' => __( 'Set to -1 for no limit', 'pods' )
			)
			/*,
			self::$type . '_size' => array(
				'label' => __( 'Field Size', 'pods' ),
				'default' => 'medium',
				'type' => 'pick',
				'data' => array(
					'small' => __( 'Small', 'pods' ),
					'medium' => __( 'Medium', 'pods' ),
					'large' => __( 'Large', 'pods' )
				)
			)*/
		);

		return $options;

	}

	/**
	 * Define the current field's schema for DB table storage
	 *
	 * @param array $options
	 *
	 * @return array
	 * @since 2.0
	 */
	public function schema( $options = null ) {

		$length = (int) pods_v( self::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = (int) pods_v( self::$type . '_decimals', $options, 2, true );

		if ( $decimals < 1 ) {
			$decimals = 0;
		}
		elseif ( 30 < $decimals ) {
			$decimals = 30;
		}

		if ( $length < $decimals ) {
			$decimals = $length;
		}

		$schema = 'DECIMAL(' . $length . ',' . $decimals . ')';

		return $schema;

	}

	/**
	 * Define the current field's preparation for sprintf
	 *
	 * @param array $options
	 *
	 * @return array
	 * @since 2.0
	 */
	public function prepare( $options = null ) {

		$format = self::$prepare;

		$length = (int) pods_v( self::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = (int) pods_v( self::$type . '_decimals', $options, 2, true );

		if ( $decimals < 1 ) {
			$decimals = 0;
		}
		elseif ( 30 < $decimals ) {
			$decimals = 30;
		}

		if ( $length < $decimals ) {
			$decimals = $length;
		}

		if ( 0 < $decimals ) {
			$format = '%01.' . $decimals . 'F';
		}
		else {
			$format = '%d';
		}

		return $format;

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
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$value = $this->format( $value, $name, $options, $pod, $id );

		$currency = 'usd';

		if ( isset( self::$currencies[ pods_v( self::$type . '_format_sign', $options, -1 ) ] ) ) {
			$currency = pods_v( self::$type . '_format_sign', $options );
		}

		$currency_sign = self::$currencies[ $currency ]['sign'];
		$currency_label = self::$currencies[ $currency ]['label'];

		$placement = pods_v( self::$type . '_format_placement', $options, 'before', true );

		// Currency placement policy
		// Single sign currencies: 100$, £100
		// Multiple sign currencies: 100 Fr, Kr 100
		$currency_gap = '';

		if ( strlen( $currency_sign ) > 1 && false === strpos( $currency_sign, '&' ) ) {
			$currency_gap = ' ';
		}

		if ( 'before' == $placement ) {
			$value = $currency_sign . $currency_gap . $value;
		}
		elseif ( 'after' == $placement ) {
			$value .= $currency_gap . $currency_sign;
		}
		elseif ( 'beforeaftercode' == $placement ) {
			$value = $currency_sign . $currency_gap . $value . ' ' . $currency_label;
		}

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
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options = (array) $options;
		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( '', $value );
		}

		if ( 'slider' == pods_v( self::$type . '_format_type', $options, 'number' ) ) {
			$field_type = 'slider';
		}
		else {
			$field_type = 'currency';
		}

		if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options[ 'readonly' ] = true;

				$field_type = 'text';

				$value = $this->format( $value, $name, $options, $pod, $id );
			}
			else {
				return;
			}
		}
		elseif ( !pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options[ 'readonly' ] = true;

			$field_type = 'text';

			$value = $this->format( $value, $name, $options, $pod, $id );
		}

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * Build regex necessary for JS validation
	 *
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param string $pod
	 * @param int $id
	 *
	 * @return bool|string
	 * @since 2.0
	 */
	public function regex( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		global $wp_locale;

		if ( '9.999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot = ',';
		}
		elseif ( '9,999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot = '.';
		}
		elseif ( '9\'999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '\'';
			$dot = '.';
		}
		elseif ( '9 999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ' ';
			$dot = ',';
		}
		elseif ( '9999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '';
			$dot = '.';
		}
		elseif ( '9999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '';
			$dot = ',';
		}
		else {
			$thousands = $wp_locale->number_format[ 'thousands_sep' ];
			$dot = $wp_locale->number_format[ 'decimal_point' ];
		}

		$currency = 'usd';

		if ( isset( self::$currencies[ pods_v( self::$type . '_format_sign', $options, -1 ) ] ) ) {
			$currency = pods_v( self::$type . '_format_sign', $options );
		}

		$currency_sign = self::$currencies[ $currency ];

		return '\-*\\' . $currency_sign . '*[0-9\\' . implode( '\\', array_filter( array( $dot, $thousands ) ) ) . ']+';

	}

	/**
	 * Validate a value before it's saved
	 *
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param array $fields
	 * @param array $pod
	 * @param int $id
	 * @param null $params
	 *
	 * @return bool|mixed
	 * @since 2.0
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		global $wp_locale;

		if ( '9.999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot = ',';
		}
		elseif ( '9,999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot = '.';
		}
		elseif ( '9\'999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '\'';
			$dot = '.';
		}
		elseif ( '9 999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ' ';
			$dot = ',';
		}
		elseif ( '9999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot = '.';
		}
		elseif ( '9999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot = ',';
		}
		else {
			$thousands = $wp_locale->number_format[ 'thousands_sep' ];
			$dot = $wp_locale->number_format[ 'decimal_point' ];
		}

		$currency = 'usd';

		if ( isset( self::$currencies[ pods_v( self::$type . '_format_sign', $options, -1 ) ] ) ) {
			$currency = pods_v( self::$type . '_format_sign', $options );
		}

		$currency_sign = self::$currencies[ $currency ];

		$check = str_replace(
			array( $thousands, $dot, $currency_sign, html_entity_decode( $currency_sign ), html_entity_decode( $thousands ) ),
			array( '', '.', '', '', '' ),
			$value
		);
		$check = trim( $check );

		$check = preg_replace( '/[0-9\.\-\s]/', '', $check );

		$label = pods_v( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) );

		if ( 0 < strlen( $check ) ) {
			return sprintf( __( '%s is not numeric', 'pods' ), $label );
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
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		global $wp_locale;

		if ( '9.999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot = ',';
		}
		elseif ( '9,999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot = '.';
		}
		elseif ( '9\'999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '\'';
			$dot = '.';
		}
		elseif ( '9 999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ' ';
			$dot = ',';
		}
		elseif ( '9999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot = '.';
		}
		elseif ( '9999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot = ',';
		}
		else {
			$thousands = $wp_locale->number_format[ 'thousands_sep' ];
			$dot = $wp_locale->number_format[ 'decimal_point' ];
		}

		$currency = 'usd';

		if ( isset( self::$currencies[ pods_v( self::$type . '_format_sign', $options, -1 ) ] ) ) {
			$currency = pods_v( self::$type . '_format_sign', $options );
		}

		$currency_sign = self::$currencies[ $currency ];

		$value = str_replace( array( $thousands, $dot, $currency_sign, html_entity_decode( $currency_sign ) ), array( '', '.', '', '' ), $value );
		$value = trim( $value );

		$value = preg_replace( '/[^0-9\.\-]/', '', $value );

		$length = (int) pods_v( self::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = (int) pods_v( self::$type . '_decimals', $options, 2, true );

		if ( $decimals < 1 ) {
			$decimals = 0;
		}
		elseif ( 30 < $decimals ) {
			$decimals = 30;
		}

		if ( $length < $decimals ) {
			$decimals = $length;
		}

		$value = number_format( (float) $value, $decimals, '.', '' );

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
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		return $this->display( $value, $name, $options, $pod, $id );

	}

	/**
	 * Reformat a number to the way the value of the field is displayed
	 *
	 * @param mixed $value
	 * @param string $name
	 * @param array $options
	 * @param array $pod
	 * @param int $id
	 *
	 * @return string
	 * @since 2.0
	 */
	public function format( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		global $wp_locale;

		if ( null === $value ) {
			// Don't enforce a default value here
			return null;
		}

		if ( '9.999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '.';
			$dot = ',';
		}
		elseif ( '9,999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ',';
			$dot = '.';
		}
		elseif ( '9\'999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '\'';
			$dot = '.';
		}
		elseif ( '9 999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = ' ';
			$dot = ',';
		}
		elseif ( '9999.99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '';
			$dot = '.';
		}
		elseif ( '9999,99' == pods_v( self::$type . '_format', $options ) ) {
			$thousands = '';
			$dot = ',';
		}
		else {
			$thousands = $wp_locale->number_format[ 'thousands_sep' ];
			$dot = $wp_locale->number_format[ 'decimal_point' ];
		}

		$length = (int) pods_v( self::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = (int) pods_v( self::$type . '_decimals', $options, 2 );

		if ( $decimals < 1 ) {
			$decimals = 0;
		}
		elseif ( 30 < $decimals ) {
			$decimals = 30;
		}

		if ( $length < $decimals ) {
			$decimals = $length;
		}

		if ( 'i18n' == pods_v( self::$type . '_format', $options ) ) {
			$value = number_format_i18n( (float) $value, $decimals );
		}
		else {
			$value = number_format( (float) $value, $decimals, $dot, $thousands );
		}

		// Additional output handling for decimals
		$decimal_handling = pods_v( self::$type . '_decimal_handling', $options, 'none' ) ;
		if ( 'none' !== $decimal_handling ) {
			$value_parts = explode( $dot, $value );
			if ( 'remove' === $decimal_handling ) {
				array_pop( $value_parts );
			} elseif ( 'dash' === $decimal_handling ) {
				array_pop( $value_parts );
				$value_parts[] = '-';
			}
			$value = implode( $dot, $value_parts );
		}

		return $value;

	}

	/**
	 * Get the currencies and place them in the local property
	 * @since  2.6.8
	 * @return array
	 */
	public static function data_currencies() {

		// If it's already done, do not redo the filter
		if ( ! empty( self::$currencies ) ) {
			return self::$currencies;
		}

		$default_currencies = array(
			'usd' => array(
				'label' => 'USD',
				'name'  => __( 'US Dollar', 'pods' ),
				'sign'  => '$',
			),
			'euro' => array(
				'label' => 'EUR',
				'name'  => __( 'Euro', 'pods' ),
				'sign'  => '&euro;',
			),
			'gbp' => array(
				'label' => 'GBP',
				'name'  => __( 'British Pound', 'pods' ),
				'sign'  => '&pound;',
			),
			'cad' => array(
				'label' => 'CAD',
				'name'  => __( 'Canadian Dollar', 'pods' ),
				'sign'  => '$',
			),
			'aud' => array(
				'label' => 'AUD',
				'name'  => __( 'Australian Dollar', 'pods' ),
				'sign'  => '$',
			),
			'nzd' => array(
				'label' => 'NZD',
				'name'  => __( 'New Zealand Dollar', 'pods' ),
				'sign'  => '$',
			),
			'rub' => array(
				'label' => 'RUB',
				'name'  => __( 'Russian Ruble', 'pods' ),
				'sign'  => '&#8381;',
			),
			'chf' => array(
				'label' => 'CHF',
				'name'  => __( 'Swiss Franc', 'pods' ),
				'sign'  => 'Fr.',
			),
			'dkk' => array(
				'label' => 'DKK',
				'name'  => __( 'Danish Krone', 'pods' ),
				'sign'  => 'kr.',
			),
			'nok' => array(
				'label' => 'NOK',
				'name'  => __( 'Norwegian Krone', 'pods' ),
				'sign'  => 'kr',
			),
			'sek' => array(
				'label' => 'SEK',
				'name'  => __( 'Swedish Krona', 'pods' ),
				'sign'  => 'kr',
			),
			'zar' => array(
				'label' => 'ZAR',
				'name'  => __( 'South African Rand', 'pods' ),
				'sign'  => 'R',
			),
			'inr' => array(
				'label' => 'INR',
				'name'  => __( 'Indian Rupee', 'pods' ),
				'sign'  => '&#x20B9;',
			),
			'jpy' => array(
				'label' => 'JPY',
				'name'  => __( 'Japanese Yen', 'pods' ),
				'sign'  => '&yen;',
			),
			'cny' => array(
				'label' => 'CNY',
				'name'  => __( 'Chinese Yuan', 'pods' ),
				'sign'  => '&yen;',
			),
			'sgd' => array(
				'label' => 'SGD',
				'name'  => __( 'Singapore Dollar', 'pods' ),
				'sign'  => '$',
			),
			'krw' => array(
				'label' => 'KRW',
				'name'  => __( 'Korean Won', 'pods' ),
				'sign'  => '&#8361;',
			),
			'thb' => array(
				'label' => 'THB',
				'name'  => __( 'Thai Baht', 'pods' ),
				'sign'  => '&#x0E3F;',
			),
			'trl' => array(
				'label' => 'TRL',
				'name'  => __( 'Turkish Lira', 'pods' ),
				'sign'  => '&#8378;',
			),
			'vnd' => array(
				'label' => 'VND',
				'name'  => __( 'Vietnamese Dong', 'pods' ),
				'sign'  => '&#8363;',
			),
			'myr' => array(
				'label' => 'MYR',
				'name'  => __( 'Malaysian Ringgit', 'pods' ),
				'sign'  => 'MR',
			),
		);

		/**
		 * Add custom currencies
		 *
		 * @param  array  $options {
		 *     Required array of arrays.
		 *     @type  array {
		 *         @type  string  $label  The label (example: USD).
		 *         @type  string  $name   The full name (example: US Dollar).
		 *         @type  string  $sign   The sign (example: $).
		 *     }
		 * }
		 * @return array
		 */
		self::$currencies = apply_filters( 'pods_form_ui_field_currency_currencies', $default_currencies );

		// Sort the currencies
		ksort( self::$currencies );

		// Backwards compatibility
		foreach ( self::$currencies as $key => $value ) {
			if ( is_string( $value ) ) {
				self::$currencies[ $key ] = array(
					'label' => strtoupper( $key ),
					'name' => strtoupper( $key ),
					'sign' => $value,
				);
			} elseif ( is_array( $value ) ) {
				// Make sure all required values are set
				if ( empty( $value['label'] ) ) {
					$value['label'] = $key;
				}
				if ( empty( $value['name'] ) ) {
					$value['name'] = $key;
				}
				if ( empty( $value['sign'] ) ) {
					$value['sign'] = $key;
				}
			} else {
				// Invalid
				unset( self::$currencies[ $key ] );
			}
		}

		return self::$currencies;
	}
}
