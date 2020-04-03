<?php

/**
 * @package Pods\Fields
 */
class PodsField_Number extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Number';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'number';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Plain Number';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%d';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		self::$label = __( 'Plain Number', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			static::$type . '_repeatable'  => array(
				'label'             => __( 'Repeatable Field', 'pods' ),
				'default'           => 0,
				'type'              => 'boolean',
				'help'              => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency'        => true,
				'developer_mode'    => true,
			),
			static::$type . '_format_type' => array(
				'label'      => __( 'Input Type', 'pods' ),
				'default'    => 'number',
				'type'       => 'pick',
				'data'       => array(
					'number' => __( 'Freeform Number', 'pods' ),
					'slider' => __( 'Slider', 'pods' ),
				),
				'dependency' => true,
			),
			static::$type . '_format'      => array(
				'label'   => __( 'Format', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_number_format_default', 'i18n' ),
				'type'    => 'pick',
				'data'    => array(
					'i18n'     => __( 'Localized Default', 'pods' ),
					'9,999.99' => '1,234.00',
					'9.999,99' => '1.234,00',
					'9 999,99' => '1 234,00',
					'9999.99'  => '1234.00',
					'9999,99'  => '1234,00',
				),
			),
			static::$type . '_decimals'    => array(
				'label'      => __( 'Decimals', 'pods' ),
				'default'    => 0,
				'type'       => 'number',
				'dependency' => true,
			),
			static::$type . '_format_soft' => array(
				'label'       => __( 'Soft format?', 'pods' ),
				'help'        => __( 'Remove trailing decimals (0)', 'pods' ),
				'default'     => 0,
				'type'        => 'boolean',
				'excludes-on' => array( static::$type . '_decimals' => 0 ),
			),
			static::$type . '_step'        => array(
				'label'      => __( 'Slider Increment (Step)', 'pods' ),
				'depends-on' => array( static::$type . '_format_type' => 'slider' ),
				'default'    => 1,
				'type'       => 'text',
			),
			static::$type . '_min'         => array(
				'label'      => __( 'Minimum Number', 'pods' ),
				'depends-on' => array( static::$type . '_format_type' => 'slider' ),
				'default'    => 0,
				'type'       => 'text',
			),
			static::$type . '_max'         => array(
				'label'      => __( 'Maximum Number', 'pods' ),
				'depends-on' => array( static::$type . '_format_type' => 'slider' ),
				'default'    => 100,
				'type'       => 'text',
			),
			static::$type . '_max_length'  => array(
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => 12,
				'type'    => 'number',
				'help'    => __( 'Set to -1 for no limit', 'pods' ),
			),
			static::$type . '_placeholder' => array(
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
	public function schema( $options = null ) {

		$length = (int) pods_v( static::$type . '_max_length', $options, 12, true );

		if ( $length < 1 || 64 < $length ) {
			$length = 64;
		}

		$decimals = $this->get_max_decimals( $options );

		$schema = 'DECIMAL(' . $length . ',' . $decimals . ')';

		return $schema;

	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare( $options = null ) {

		$format = static::$prepare;

		$decimals = $this->get_max_decimals( $options );

		if( 6 < $decimals ) {
			// %F only allows 6 decimals by default
			$format = '%.' . $decimals . 'F';
		} else if ( 0 < $decimals ) {
			$format = '%F';
		}

		return $format;

	}

	/**
	 * @todo 2.8 Centralize the usage of this method. See PR #5540.
	 * {@inheritdoc}
	 */
	public function is_empty( $value = null ) {

		$is_empty = false;

		$value = (float) $value;

		if ( empty( $value ) ) {
			$is_empty = true;
		}

		return $is_empty;

	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$value = $this->format( $value, $name, $options, $pod, $id );

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = (array) $options;
		$form_field_type = PodsForm::$field_type;
		$is_read_only    = false;

		if ( is_array( $value ) ) {
			$value = implode( '', $value );
		}

		if ( 'slider' === pods_v( static::$type . '_format_type', $options, 'number' ) ) {
			$field_type = 'slider';
		} else {
			$field_type = static::$type;
		}

		if ( isset( $options['name'] ) && false === PodsForm::permission( static::$type, $options['name'], $options, null, $pod, $id ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$is_read_only = true;
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$is_read_only = true;
		}

		if ( $is_read_only ) {
			$options['readonly'] = true;

			$field_type = 'text';

			$value = $this->format( $value, $name, $options, $pod, $id );
		}

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * {@inheritdoc}
	 */
	public function regex( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$format_args = $this->get_number_format_args( $options );
		$thousands   = $format_args['thousands'];
		$dot         = $format_args['dot'];

		return '\-*[0-9\\' . implode( '\\', array_filter( array( $dot, $thousands ) ) ) . ']+';
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		$format_args = $this->get_number_format_args( $options );
		$thousands   = $format_args['thousands'];
		$dot         = $format_args['dot'];

		$check = str_replace(
			array( $thousands, $dot, html_entity_decode( $thousands ) ),
			array( '', '.', '' ),
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
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		$format_args = $this->get_number_format_args( $options );
		$thousands   = $format_args['thousands'];
		$dot         = $format_args['dot'];
		$decimals    = $format_args['decimals'];

		if ( 'slider' !== pods_v( static::$type . '_format_type', $options ) ) {
			// Slider only supports `1234.00` format so no need for replacing characters.
			$value = str_replace(
				array( $thousands, html_entity_decode( $thousands ), $dot, html_entity_decode( $dot ) ),
				array( '', '', '.', '.' ),
				$value
			);
		}

		$value = trim( $value );

		$value = preg_replace( '/[^0-9\.\-]/', '', $value );

		if ( $this->is_empty( $value ) && ( ! is_numeric( $value ) || 0.0 !== (float) $value ) ) {
			// Don't enforce a default value here.
			return null;
		}

		$value = number_format( (float) $value, $decimals, '.', '' );

		// Optionally remove trailing decimal zero's.
		if ( pods_v( static::$type . '_format_soft', $options, false ) ) {
			$value = $this->trim_decimals( $value, '.' );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function format( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		if ( $this->is_empty( $value ) && ( ! is_numeric( $value ) || 0.0 !== (float) $value ) ) {
			// Don't enforce a default value here.
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

		// Optionally remove trailing decimal zero's.
		if ( pods_v( static::$type . '_format_soft', $options, false ) ) {
			$value = $this->trim_decimals( $value, $dot );
		}

		return $value;
	}

	/**
	 * Trim trailing 0 decimals from numbers.
	 *
	 * @since 2.7.15
	 *
	 * @param string $value
	 * @param string $dot
	 *
	 * @return string
	 */
	public function trim_decimals( $value, $dot ) {
		$parts = explode( $dot, $value );

		if ( isset( $parts[1] ) ) {
			$parts[1] = rtrim( $parts[1], '0' );

			if ( empty( $parts[1] ) ) {
				unset( $parts[1] );
			}
		}

		return implode( $dot, $parts );
	}

	/**
	 * Get the formatting arguments for numbers.
	 *
	 * @since 2.7.0
	 *
	 * @param array $options Field options.
	 *
	 * @return array {
	 * @type string $thousands
	 * @type string $dot
	 * @type int    $decimals
	 * }
	 */
	public function get_number_format_args( $options ) {

		$format = pods_v( static::$type . '_format', $options );
		$format = pods_unslash( $format );

		switch ( $format ) {
			case '9.999,99':
				$thousands = '.';
				$dot       = ',';
				break;
			case '9,999.99':
				$thousands = ',';
				$dot       = '.';
				break;
			case '9\'999.99':
				$thousands = '\'';
				$dot       = '.';
				break;
			case '9 999,99':
				$thousands = ' ';
				$dot       = ',';
				break;
			case '9999.99':
				$thousands = '';
				$dot       = '.';
				break;
			case '9999,99':
				$thousands = '';
				$dot       = ',';
				break;
			default:
				global $wp_locale;
				$thousands = $wp_locale->number_format['thousands_sep'];
				$dot       = $wp_locale->number_format['decimal_point'];
				break;
		}

		$decimals = $this->get_max_decimals( $options );

		return array(
			'thousands' => $thousands,
			'dot'       => $dot,
			'decimals'  => $decimals,
		);
	}

	/**
	 * Get the max allowed decimals.
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

		$decimals = (int) pods_v( static::$type . '_decimals', $options, 0 );

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
