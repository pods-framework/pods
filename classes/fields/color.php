<?php

/**
 * @package Pods\Fields
 */
class PodsField_Color extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'color';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Color Picker';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$label = __( 'Color Picker', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			static::$type . '_repeatable' => array(
				'label'             => __( 'Repeatable Field', 'pods' ),
				'default'           => 0,
				'type'              => 'boolean',
				'help'              => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency'        => true,
				'developer_mode'    => true,
			),
			static::$type . '_select_label'   => array(
				'label'       => __( 'Select Color Label', 'pods' ),
				'placeholder' => __( 'Select Color', 'pods' ),
				'default'     => '',
				'type'        => 'text',
			),
			static::$type . '_clear_label'   => array(
				'label'       => __( 'Clear Label', 'pods' ),
				'placeholder' => __( 'Clear', 'pods' ),
				'default'     => '',
				'type'        => 'text',
			),
		);

		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$schema = 'VARCHAR(7)';

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

		// WP Color Picker for 3.5+
		$field_type = 'color';

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
			return pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
		}

		// Default labels.
		if ( empty( $options[ static::$type . '_select_label' ] ) ) {
			$options[ static::$type . '_select_label' ] = __( 'Select Color', 'pods' );
		}
		if ( empty( $options[ static::$type . '_clear_label' ] ) ) {
			$options[ static::$type . '_clear_label' ] = __( 'Clear', 'pods' );
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
			$color = str_replace( '#', '', $check );

			if ( 0 < strlen( $value ) && '' === $check ) {
				if ( $this->is_required( $options ) ) {
					$errors[] = __( 'This field is required.', 'pods' );
				} else {
					// @todo Ask for a specific format in error message
					$errors[] = __( 'Invalid value provided for this field.', 'pods' );
				}
			} elseif ( ! empty( $color ) && ! in_array( strlen( $color ), array( 3, 6 ), true ) ) {
				$errors[] = __( 'Invalid Hex Color value provided for this field.', 'pods' );
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

		$value = str_replace( '#', '', $value );

		if ( 0 < strlen( $value ) ) {
			$value = '#' . $value;
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		if ( ! empty( $value ) ) {
			$value = $value . ' <span style="display:inline-block;width:25px;height:25px;border:1px solid #333;background-color:' . $value . '"></span>';
		}

		return $value;
	}
}
