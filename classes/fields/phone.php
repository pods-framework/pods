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

		static::$group = __( 'Text', 'pods' );
		static::$label = __( 'Phone', 'pods' );
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
				'pick_show_select_text' => 0,
			),
			static::$type . '_options'     => array(
				'label' => __( 'Phone Options', 'pods' ),
				'type'  => 'boolean_group',
				'boolean_group' => array(
					static::$type . '_enable_phone_extension' => array(
						'label'   => __( 'Enable Phone Extension', 'pods' ),
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
				'label'   => __( 'Enable HTML5 Input Field', 'pods' ),
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

		$options         = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;
		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( ' ', $value );
		}

		$field_type = 'phone';

		if ( isset( $options['name'] ) && ! pods_permission( $options ) ) {
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

		if ( ! empty( $options['disable_dfv'] ) ) {
			return pods_view( PODS_DIR . 'ui/fields/phone.php', compact( array_keys( get_defined_vars() ) ) );
		}

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
		$validate = parent::validate( $value, $name, $options, $fields, $pod, $id, $params );

		$errors = array();

		if ( is_array( $validate ) ) {
			$errors = $validate;
		}

		$label = strip_tags( pods_v( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) ) );

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( is_array( $check ) ) {
			$errors = $check;
		} elseif ( '' === $check && 0 < strlen( $value ) ) {
			if ( $this->is_required( $options ) ) {
				$errors[] = sprintf( __( 'The %s field is required.', 'pods' ), $label );
			} else {
				$errors[] = sprintf( __( 'Invalid phone number provided for the field %s.', 'pods' ), $label );
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
		$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

		$phone_format = pods_v( static::$type . '_format', $options, '999-999-9999 x999', true );

		if ( 'international' !== $phone_format ) {
			// Clean input
			$number = preg_replace( '/(\+\d+)/', '', $value );
			$number = preg_replace( '/([^0-9ext])/', '', $number );

			$number = str_replace(
				[
					'ext',
					'x',
					't',
					'e'
				],
				[
					'|',
					'|',
					'',
					'',
				],
				$number
			);

			$extension = '';

			// Get extension
			$extension_data = explode( '|', $number );

			if ( 1 < count( $extension_data ) ) {
				$number    = $extension_data[0];
				$extension = $extension_data[1];
			}

			// Build number array
			$numbers = str_split( $number, 3 );

			// Split up the numbers: 123-456-7890: 123[0] 456[1] 789[2]0[3]
			if ( isset( $numbers[3] ) ) {
				$numbers[2] .= $numbers[3];
				$numbers     = array( $numbers[0], $numbers[1], $numbers[2] );
			} elseif ( isset( $numbers[1] ) ) {
				$numbers = array( $numbers[0], $numbers[1] );
			}

			// Format number
			if ( '(999) 999-9999 x999' === $phone_format ) {
				$number_count = count( $numbers );

				if ( 1 === $number_count ) {
					// Invalid number.
					$value = '';
				} elseif ( 2 === $number_count ) {
					// Basic number, no area code!
					$value = implode( '-', $numbers );
				} else {
					// Full number.
					$value = '(' . $numbers[0] . ') ' . $numbers[1] . '-' . $numbers[2];
				}
			} elseif ( '999.999.9999 x999' === $phone_format ) {
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
