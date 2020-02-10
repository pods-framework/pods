<?php

/**
 * @package Pods\Fields
 */
class PodsField_Phone extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Text';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'phone';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Phone';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		self::$label = __( 'Phone', 'pods' );
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
			static::$type . '_format'      => array(
				'label'   => __( 'Format', 'pods' ),
				'default' => '999-999-9999 x999',
				'type'    => 'pick',
				'data'    => array(
					__( 'US', 'pods' )            => array(
						'999-999-9999 x999'   => '123-456-7890 x123',
						'(999) 999-9999 x999' => '(123) 456-7890 x123',
						'999.999.9999 x999'   => '123.456.7890 x123',
					),
					__( 'International', 'pods' ) => array(
						'international' => __( 'Any (no validation available)', 'pods' ),
					),
				),
			),
			static::$type . '_options'     => array(
				'label' => __( 'Phone Options', 'pods' ),
				'group' => array(
					static::$type . '_enable_phone_extension' => array(
						'label'   => __( 'Enable Phone Extension?', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
					),
				),
			),
			static::$type . '_max_length'  => array(
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => 25,
				'type'    => 'number',
				'help'    => __( 'Set to -1 for no limit', 'pods' ),
			),
			static::$type . '_html5'       => array(
				'label'   => __( 'Enable HTML5 Input Field?', 'pods' ),
				'default' => apply_filters( 'pods_form_ui_field_html5', 0, static::$type ),
				'type'    => 'boolean',
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

		$length = (int) pods_v( static::$type . '_max_length', $options, 25, true );

		$schema = 'VARCHAR(' . $length . ')';

		if ( 255 < $length || $length < 1 ) {
			$schema = 'LONGTEXT';
		}

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = (array) $options;
		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( ' ', $value );
		}

		$field_type = 'phone';

		if ( isset( $options['name'] ) && false === PodsForm::permission( static::$type, $options['name'], $options, null, $pod, $id ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options['readonly'] = true;

				$field_type = 'text';
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options['readonly'] = true;

			$field_type = 'text';
		}

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		$errors = array();

		$label = strip_tags( pods_v( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) ) );

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( is_array( $check ) ) {
			$errors = $check;
		} else {
			if ( 0 < strlen( $value ) && '' === $check ) {
				if ( 1 === (int) pods_v( 'required', $options ) ) {
					$errors[] = sprintf( __( 'The %s field is required.', 'pods' ), $label );
				} else {
					$errors[] = sprintf( __( 'Invalid phone number provided for the field %s.', 'pods' ), $label );
				}
			}
		}

		if ( ! empty( $errors ) ) {
			return $errors;
		}

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		$options = (array) $options;

		if ( 'international' !== pods_v( static::$type . '_format', $options ) ) {
			// Clean input
			$number = preg_replace( '/([^0-9ext])/', '', $value );

			$number = str_replace(
				array( '-', '.', 'ext', 'x', 't', 'e', '(', ')' ), array(
					'',
					'',
					'|',
					'|',
					'',
					'',
					'',
					'',
				), $number
			);

			// Get extension
			$extension = explode( '|', $number );
			if ( 1 < count( $extension ) ) {
				$number    = preg_replace( '/([^0-9])/', '', $extension[0] );
				$extension = preg_replace( '/([^0-9])/', '', $extension[1] );
			} else {
				$extension = '';
			}

			// Build number array
			$numbers = str_split( $number, 3 );

			if ( isset( $numbers[3] ) ) {
				$numbers[2] .= $numbers[3];
				$numbers     = array( $numbers[0], $numbers[1], $numbers[2] );
			} elseif ( isset( $numbers[1] ) ) {
				$numbers = array( $numbers[0], $numbers[1] );
			}

			// Format number
			if ( '(999) 999-9999 x999' === pods_v( static::$type . '_format', $options ) ) {
				$number_count = count( $numbers );

				if ( 1 === $number_count ) {
					$value = '';
				} elseif ( 2 === $number_count ) {
					$value = implode( '-', $numbers );
				} else {
					$value = '(' . $numbers[0] . ') ' . $numbers[1] . '-' . $numbers[2];
				}
			} elseif ( '999.999.9999 x999' === pods_v( static::$type . '_format', $options ) ) {
				$value = implode( '.', $numbers );
			} else {
				$value = implode( '-', $numbers );
			}

			// Add extension
			if ( 1 === (int) pods_v( static::$type . '_enable_phone_extension', $options ) && 0 < strlen( $extension ) ) {
				$value .= ' x' . $extension;
			}
		}//end if

		$length = (int) pods_v( static::$type . '_max_length', $options, 25 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

		return $value;
	}
}
