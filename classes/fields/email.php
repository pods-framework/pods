<?php

/**
 * @package Pods\Fields
 */
class PodsField_Email extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Text';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'email';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'E-mail';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$group = __( 'Text', 'pods' );
		static::$label = __( 'E-mail', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			static::$type . '_max_length'  => array(
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => 255,
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

		$length = (int) pods_v( static::$type . '_max_length', $options, 255 );

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

		$value = $this->normalize_value_for_input( $value, $options );

		$field_type = 'email';

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
			return pods_view( PODS_DIR . 'ui/fields/email.php', compact( array_keys( get_defined_vars() ) ) );
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

		$check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( is_array( $check ) ) {
			$errors = $check;
		} else {
			if ( 0 < strlen( $value ) && '' === $check ) {
				$label = pods_v( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) );

				if ( $this->is_required( $options ) ) {
					$errors[] = sprintf( __( '%s is required', 'pods' ), $label );
				} else {
					$errors[] = sprintf( __( 'Invalid e-mail provided for %s', 'pods' ), $label );
				}
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

		if ( ! is_email( $value ) ) {
			$value = '';
		}

		$length = (int) pods_v( static::$type . '_max_length', $options, 255 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		return '<a href="mailto:' . esc_attr( $value ) . '">' . $value . '</a>';
	}
}
