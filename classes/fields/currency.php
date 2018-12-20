<?php
require_once PODS_DIR . 'classes/fields/number.php';

/**
 * @package Pods\Fields
 */
class PodsField_Currency extends PodsField_Number {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Number';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'currency';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Currency';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%d';

	/**
	 * Currency Formats
	 *
	 * @var array
	 * @since 2.0.0
	 */
	public static $currencies = array();

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		self::$label = __( 'Currency', 'pods' );
		static::data_currencies();
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$currency_options = array();
		foreach ( static::$currencies as $key => $value ) {
			$currency = $value['label'];
			if ( $value['label'] !== $value['name'] ) {
				$currency .= ': ' . $value['name'];
			}
			$currency                .= ' (' . $value['sign'] . ')';
			$currency_options[ $key ] = $currency;
		}

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
			static::$type . '_format_type'      => array(
				'label'      => __( 'Input Type', 'pods' ),
				'default'    => 'number',
				'type'       => 'pick',
				'data'       => array(
					'number' => __( 'Freeform Number', 'pods' ),
					'slider' => __( 'Slider', 'pods' ),
				),
				'dependency' => true,
			),
			static::$type . '_format_sign'      => array(
				'label'   => __( 'Currency Sign', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_number_currency_default', 'usd' ),
				'type'    => 'pick',
				'data'    => apply_filters( 'pods_form_ui_field_number_currency_options', $currency_options ),
			),
			static::$type . '_format_placement' => array(
				'label'   => __( 'Currency Placement', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_number_currency_placement_default', 'before' ),
				'type'    => 'pick',
				'data'    => array(
					'before'                => __( 'Before (ex. $100)', 'pods' ),
					'after'                 => __( 'After (ex. 100$)', 'pods' ),
					'before_space'          => __( 'Before with space (ex. $ 100)', 'pods' ),
					'after_space'           => __( 'After with space (ex. 100 $)', 'pods' ),
					'none'                  => __( 'None (ex. 100)', 'pods' ),
					'beforeaftercode'       => __( 'Before with Currency Code after (ex. $100 USD)', 'pods' ),
					'beforeaftercode_space' => __( 'Before width space and with Currency Code after (ex. $ 100 USD)', 'pods' ),
				),
			),
			static::$type . '_format'           => array(
				'label'   => __( 'Format', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_number_currency_format_default', 'i18n' ),
				'type'    => 'pick',
				'data'    => array(
					'i18n'      => __( 'Localized Default', 'pods' ),
					'9,999.99'  => '1,234.00',
					'9\'999.99' => '1\'234.00',
					'9.999,99'  => '1.234,00',
					'9 999,99'  => '1 234,00',
					'9999.99'   => '1234.00',
					'9999,99'   => '1234,00',
				),
			),
			static::$type . '_decimals'         => array(
				'label'   => __( 'Decimals', 'pods' ),
				'default' => 2,
				'type'    => 'number',
			),
			static::$type . '_decimal_handling' => array(
				'label'   => __( 'Decimal handling when zero', 'pods' ),
				'default' => 'none',
				'type'    => 'pick',
				'data'    => array(
					'none'   => __( 'Default', 'pods' ),
					'remove' => __( 'Remove decimals', 'pods' ),
					'dash'   => __( 'Convert to dash', 'pods' ) . ' (-)',
				),
			),
			static::$type . '_step'             => array(
				'label'      => __( 'Slider Increment (Step)', 'pods' ),
				'depends-on' => array( static::$type . '_format_type' => 'slider' ),
				'default'    => 1,
				'type'       => 'text',
			),
			static::$type . '_min'              => array(
				'label'      => __( 'Minimum Number', 'pods' ),
				'depends-on' => array( static::$type . '_format_type' => 'slider' ),
				'default'    => 0,
				'type'       => 'text',
			),
			static::$type . '_max'              => array(
				'label'      => __( 'Maximum Number', 'pods' ),
				'depends-on' => array( static::$type . '_format_type' => 'slider' ),
				'default'    => 1000,
				'type'       => 'text',
			),
			static::$type . '_max_length'       => array(
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => 12,
				'type'    => 'number',
				'help'    => __( 'Set to -1 for no limit', 'pods' ),
			),
			static::$type . '_placeholder'      => array(
				'label'   => __( 'HTML Placeholder', 'pods' ),
				'default' => '',
				'type'    => 'text',
				'help'    => array(
					__( 'Placeholders can provide instructions or an example of the required data format for a field. Please note: It is not a replacement for labels or description text, and it is less accessible for people using screen readers.', 'pods' ),
					'https://www.w3.org/WAI/tutorials/forms/instructions/#placeholder-text',
				),
			),
		);

		return $options;

	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$value = $this->format( $value, $name, $options, $pod, $id );

		$currency = 'usd';

		if ( isset( static::$currencies[ pods_v( static::$type . '_format_sign', $options, - 1 ) ] ) ) {
			$currency = pods_v( static::$type . '_format_sign', $options );
		}

		$currency_sign  = static::$currencies[ $currency ]['sign'];
		$currency_label = static::$currencies[ $currency ]['label'];

		$placement = pods_v( static::$type . '_format_placement', $options, 'before', true );

		// Currency placement policy
		// Single sign currencies: 100$, £100
		// Multiple sign currencies: 100 Fr, Kr 100
		$currency_gap = '';

		if ( mb_strlen( $currency_sign ) > 1 && false === strpos( $currency_sign, '&' ) ) {
			$currency_gap = ' ';
		} elseif ( in_array( $placement, array( 'before_space', 'after_space', 'beforeaftercode_space' ), true ) ) {
			$currency_gap = ' ';
		}

		switch ( $placement ) {
			case 'before':
			case 'before_space':
				$value = $currency_sign . $currency_gap . $value;
				break;
			case 'after':
			case 'after_space':
				$value .= $currency_gap . $currency_sign;
				break;
			case 'beforeaftercode':
			case 'beforeaftercode_space':
				$value = $currency_sign . $currency_gap . $value . ' ' . $currency_label;
				break;
		}

		return $value;

	}

	/**
	 * {@inheritdoc}
	 */
	public function regex( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$format_args = $this->get_number_format_args( $options );
		$thousands   = $format_args['thousands'];
		$dot         = $format_args['dot'];

		$currency = 'usd';

		if ( isset( static::$currencies[ pods_v( static::$type . '_format_sign', $options, - 1 ) ] ) ) {
			$currency = pods_v( static::$type . '_format_sign', $options );
		}

		$currency_sign = static::$currencies[ $currency ]['sign'];

		return '\-*\\' . $currency_sign . '*[0-9\\' . implode( '\\', array_filter( array( $dot, $thousands ) ) ) . ']+';

	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		$format_args = $this->get_number_format_args( $options );
		$thousands   = $format_args['thousands'];
		$dot         = $format_args['dot'];

		$currency = 'usd';

		if ( isset( static::$currencies[ pods_v( static::$type . '_format_sign', $options, - 1 ) ] ) ) {
			$currency = pods_v( static::$type . '_format_sign', $options );
		}

		$currency_sign   = static::$currencies[ $currency ]['sign'];
		$currency_entity = static::$currencies[ $currency ]['entity'];

		// Remove currency and thousands symbols
		$check = str_replace(
			array(
				$thousands,
				$currency_sign,
				$currency_entity,
				html_entity_decode( $thousands ),
				html_entity_decode( $currency_sign ),
				html_entity_decode( $currency_entity ),
			), '', $value
		);
		// Convert decimal type for numeric type
		$check = str_replace( $dot, '.', $check );
		$check = trim( $check );

		$check = preg_replace( '/[0-9\.\-\s]/', '', $check );

		$label = pods_v( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) );

		if ( 0 < strlen( $check ) ) {
			return sprintf( __( '%s is not numeric', 'pods' ), $label );
		}

		return true;

	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		$format_args = $this->get_number_format_args( $options );
		$thousands   = $format_args['thousands'];
		$dot         = $format_args['dot'];
		$decimals    = $format_args['decimals'];

		$currency = 'usd';

		if ( isset( static::$currencies[ pods_v( static::$type . '_format_sign', $options, - 1 ) ] ) ) {
			$currency = pods_v( static::$type . '_format_sign', $options );
		}

		$currency_sign   = static::$currencies[ $currency ]['sign'];
		$currency_entity = static::$currencies[ $currency ]['entity'];

		// Convert decimal type for numeric type
		$value = str_replace(
			array(
				$thousands,
				$currency_sign,
				$currency_entity,
				html_entity_decode( $thousands ),
				html_entity_decode( $currency_sign ),
				html_entity_decode( $currency_entity ),
			), '', $value
		);
		// Convert decimal type for numeric type
		$value = str_replace( $dot, '.', $value );
		$value = trim( $value );

		$value = preg_replace( '/[^0-9\.\-]/', '', $value );

		$value = number_format( (float) $value, $decimals, '.', '' );

		return $value;

	}

	/**
	 * {@inheritdoc}
	 */
	public function format( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		if ( null === $value ) {
			// Don't enforce a default value here
			return null;
		}

		$format_args = $this->get_number_format_args( $options );
		$thousands   = $format_args['thousands'];
		$dot         = $format_args['dot'];
		$decimals    = $format_args['decimals'];

		if ( 'i18n' === pods_v( static::$type . '_format', $options ) ) {
			$value = number_format_i18n( (float) $value, $decimals );
		} else {
			$value = number_format( (float) $value, $decimals, $dot, $thousands );
		}

		// Additional output handling for decimals
		$decimal_handling = pods_v( static::$type . '_decimal_handling', $options, 'none' );
		if ( 'none' !== $decimal_handling ) {
			$value_parts = explode( $dot, $value );
			// Make sure decimals are empty.
			if ( isset( $value_parts[1] ) && ! (int) $value_parts[1] ) {
				if ( 'remove' === $decimal_handling ) {
					array_pop( $value_parts );
				} elseif ( 'dash' === $decimal_handling ) {
					array_pop( $value_parts );
					$value_parts[] = '-';
				}
				$value = implode( $dot, $value_parts );
			}
		}

		return $value;

	}

	/**
	 * Get the currencies and place them in the local property
	 *
	 * @since  2.6.8
	 * @return array
	 */
	public static function data_currencies() {

		// If it's already done, do not redo the filter
		if ( ! empty( static::$currencies ) ) {
			return static::$currencies;
		}

		$default_currencies = array(
			'aud'     => array(
				'label'  => 'AUD',
				'name'   => __( 'Australian Dollar', 'pods' ),
				'sign'   => '$',
				'entity' => '&#36;',
			),
			'brl'     => array(
				'label'  => 'BRL',
				'name'   => __( 'Brazilian Real', 'pods' ),
				'sign'   => 'R$',
				'entity' => 'R&#36;',
			),
			'gbp'     => array(
				'label'  => 'GBP',
				'name'   => __( 'British Pound', 'pods' ),
				'sign'   => '£',
				'entity' => '&pound;',
			),
			'cad'     => array(
				'label'  => 'CAD',
				'name'   => __( 'Canadian Dollar', 'pods' ),
				'sign'   => '$',
				'entity' => '&#36;',
			),
			'cny'     => array(
				'label'  => 'CNY',
				'name'   => __( 'Chinese Yen (¥)', 'pods' ),
				'sign'   => '¥',
				'entity' => '&yen;',
			),
			'cny2'    => array(
				'label'  => 'CNY',
				'name'   => __( 'Chinese Yuan (元)', 'pods' ),
				'sign'   => '元',
				'entity' => '&#20803;',
			),
			'czk'     => array(
				'label'  => 'CZK',
				'name'   => __( 'Czech Koruna', 'pods' ),
				'sign'   => 'Kč',
				'entity' => 'K&#x10D;',
			),
			'dkk'     => array(
				'label'  => 'DKK',
				'name'   => __( 'Danish Krone', 'pods' ),
				'sign'   => 'kr.',
				'entity' => 'kr.',
			),
			'euro'    => array(
				'label'  => 'EUR',
				'name'   => __( 'Euro', 'pods' ),
				'sign'   => '€',
				'entity' => '&euro;',
			),
			'hkd'     => array(
				'label'  => 'HKD',
				'name'   => __( 'Hong Kong Dollar', 'pods' ),
				'sign'   => '$',
				'entity' => '&#36;',
			),
			'huf'     => array(
				'label'  => 'HUF',
				'name'   => __( 'Hungarian Forint', 'pods' ),
				'sign'   => 'Ft',
				'entity' => 'Ft',
			),
			'inr'     => array(
				'label'  => 'INR',
				'name'   => __( 'Indian Rupee', 'pods' ),
				'sign'   => '₹',
				'entity' => '&#x20B9;',
			),
			'idr'     => array(
				'label'  => 'IDR',
				'name'   => __( 'Indonesian Rupiah', 'pods' ),
				'sign'   => 'Rp',
				'entity' => 'Rp',
			),
			'ils'     => array(
				'label'  => 'ILS',
				'name'   => __( 'Israeli New Sheqel', 'pods' ),
				'sign'   => '₪',
				'entity' => '&#x20AA;',
			),
			'jpy'     => array(
				'label'  => 'JPY',
				'name'   => __( 'Japanese Yen', 'pods' ),
				'sign'   => '¥',
				'entity' => '&yen;',
			),
			'krw'     => array(
				'label'  => 'KRW',
				'name'   => __( 'Korean Won', 'pods' ),
				'sign'   => '₩',
				'entity' => '&#8361;',
			),
			'myr'     => array(
				'label'  => 'MYR',
				'name'   => __( 'Malaysian Ringgit', 'pods' ),
				'sign'   => 'MR',
				'entity' => 'MR',
			),
			'mxn'     => array(
				'label'  => 'MXN',
				'name'   => __( 'Mexican Peso', 'pods' ),
				'sign'   => '$',
				'entity' => '&#36;',
			),
			'nzd'     => array(
				'label'  => 'NZD',
				'name'   => __( 'New Zealand Dollar', 'pods' ),
				'sign'   => '$',
				'entity' => '&#36;',
			),
			'nok'     => array(
				'label'  => 'NOK',
				'name'   => __( 'Norwegian Krone', 'pods' ),
				'sign'   => 'kr',
				'entity' => 'kr',
			),
			'php'     => array(
				'label'  => 'PHP',
				'name'   => __( 'Philippine Peso', 'pods' ),
				'sign'   => '₱',
				'entity' => '&#x20B1;',
			),
			'pln'     => array(
				'label'  => 'PLN',
				'name'   => __( 'Polish Złoty', 'pods' ),
				'sign'   => 'zł',
				'entity' => 'z&#x142;',
			),
			'rub'     => array(
				'label'  => 'RUB',
				'name'   => __( 'Russian Ruble', 'pods' ),
				'sign'   => '₽',
				'entity' => '&#8381;',
			),
			'sek'     => array(
				'label'  => 'SEK',
				'name'   => __( 'Swedish Krona', 'pods' ),
				'sign'   => 'kr',
				'entity' => 'kr',
			),
			'sgd'     => array(
				'label'  => 'SGD',
				'name'   => __( 'Singapore Dollar', 'pods' ),
				'sign'   => '$',
				'entity' => '&#36;',
			),
			'zar'     => array(
				'label'  => 'ZAR',
				'name'   => __( 'South African Rand', 'pods' ),
				'sign'   => 'R',
				'entity' => 'R',
			),
			'chf'     => array(
				'label'  => 'CHF',
				'name'   => __( 'Swiss Franc', 'pods' ),
				'sign'   => 'Fr',
				'entity' => 'Fr',
			),
			'twd'     => array(
				'label'  => 'TWD',
				'name'   => __( 'Taiwan New Dollar', 'pods' ),
				'sign'   => '$',
				'entity' => '&#36;',
			),
			'thb'     => array(
				'label'  => 'THB',
				'name'   => __( 'Thai Baht', 'pods' ),
				'sign'   => '฿',
				'entity' => '&#x0E3F;',
			),
			'trl'     => array(
				'label'  => 'TRL',
				'name'   => __( 'Turkish Lira', 'pods' ),
				'sign'   => '₺',
				'entity' => '&#8378;',
			),
			'usd'     => array(
				'label'  => 'USD',
				'name'   => __( 'US Dollar', 'pods' ),
				'sign'   => '$',
				'entity' => '&#36;',
			),
			'usdcent' => array(
				'label'  => 'USDCENT',
				'name'   => __( 'US Dollar Cent', 'pods' ),
				'sign'   => '¢',
				'entity' => '&cent;',
			),
			'vnd'     => array(
				'label'  => 'VND',
				'name'   => __( 'Vietnamese Dong', 'pods' ),
				'sign'   => '₫',
				'entity' => '&#8363;',
			),
		);

		/**
		 * Add custom currencies
		 *
		 * @param  array $options {
		 *                        Required array of arrays.
		 *
		 * @type  array {
		 * @type  string $label   The label (example: USD).
		 * @type  string $name    The full name (example: US Dollar).
		 * @type  string $sign    The sign (example: $).
		 * @type  string $entity  The HTML entity (example: &#36;).
		 *     }
		 * }
		 * @return array
		 */
		static::$currencies = apply_filters( 'pods_form_ui_field_currency_currencies', $default_currencies );

		// Sort the currencies
		ksort( static::$currencies );

		// Backwards compatibility
		foreach ( static::$currencies as $key => $value ) {
			if ( is_string( $value ) ) {
				static::$currencies[ $key ] = array(
					'label'  => strtoupper( $key ),
					'name'   => strtoupper( $key ),
					'sign'   => $value,
					'entity' => $value,
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
				if ( empty( $value['entity'] ) ) {
					$value['entity'] = $key;
				}
			} else {
				// Invalid
				unset( static::$currencies[ $key ] );
			}//end if
		}//end foreach

		return static::$currencies;
	}

	/**
	 * Get the max allowed decimals.
	 * Overwrites the default value of Number field. 2 decimals instead of 0.
	 *
	 * @since 2.7.0
	 *
	 * @param array $options Field options.
	 *
	 * @return int
	 */
	public function get_max_decimals( $options ) {

		$length = (int) pods_v( static::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = (int) pods_v( static::$type . '_decimals', $options, 2 );

		if ( $decimals < 1 ) {
			$decimals = 0;
		} elseif ( 30 < $decimals ) {
			$decimals = 30;
		}

		if ( $length < $decimals ) {
			$decimals = $length;
		}

		return $decimals;
	}
}
