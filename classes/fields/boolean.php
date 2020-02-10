<?php

/**
 * Handles boolean field type data and operations.
 *
 * @package Pods\Fields
 */
class PodsField_Boolean extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'boolean';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Yes / No';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		self::$label = __( 'Yes / No', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			static::$type . '_format_type' => array(
				'label'      => __( 'Input Type', 'pods' ),
				'default'    => 'checkbox',
				'type'       => 'pick',
				'data'       => array(
					'checkbox' => __( 'Checkbox', 'pods' ),
					'radio'    => __( 'Radio Buttons', 'pods' ),
					'dropdown' => __( 'Drop Down', 'pods' ),
				),
				'dependency' => true,
			),
			static::$type . '_yes_label'   => array(
				'label'   => __( 'Yes Label', 'pods' ),
				'default' => __( 'Yes', 'pods' ),
				'type'    => 'text',
			),
			static::$type . '_no_label'    => array(
				'label'   => __( 'No Label', 'pods' ),
				'default' => __( 'No', 'pods' ),
				'type'    => 'text',
			),
		);

		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$schema = 'BOOL DEFAULT 0';

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function is_empty( $value = null ) {

		$is_empty = false;

		$value = filter_var( $value, FILTER_VALIDATE_BOOLEAN );

		if ( ! $value ) {
			$is_empty = true;
		}

		return $is_empty;

	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		$yesno = array(
			1 => pods_v( static::$type . '_yes_label', $options ),
			0 => pods_v( static::$type . '_no_label', $options ),
		);

		// Deprecated handling for 1.x
		if ( ! parent::$deprecated && isset( $yesno[ (int) $value ] ) ) {
			$value = $yesno[ (int) $value ];
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = (array) $options;
		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) ) {
			if ( ! empty( $value ) ) {
				$value = true;
			} else {
				$value = false;
			}
		}

		$field_type = 'checkbox';

		if ( 'radio' === pods_v( static::$type . '_format_type', $options ) ) {
			$field_type = 'radio';
		} elseif ( 'dropdown' === pods_v( static::$type . '_format_type', $options ) ) {
			$field_type = 'select';
		}

		if ( isset( $options['name'] ) && false === PodsForm::permission( static::$type, $options['name'], $options, null, $pod, $id ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options['readonly'] = true;
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options['readonly'] = true;
		}

		if ( 1 === $value || '1' === $value || true === $value ) {
			$value = 1;
		} else {
			$value = 0;
		}

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function data( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {

		if ( 'checkbox' !== pods_v( static::$type . '_format_type', $options ) ) {
			$data = array(
				1 => pods_v( static::$type . '_yes_label', $options ),
				0 => pods_v( static::$type . '_no_label', $options ),
			);
		} else {
			$data = array(
				1 => pods_v( static::$type . '_yes_label', $options ),
			);
		}

		return $data;
	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
	
		$errors = array();
		$check  = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

		if ( 1 === (int) pods_v( 'required', $options ) && 0 === $check ) {
			$errors[] = __( 'This field is required.', 'pods' );
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

		$yes = strtolower( pods_v( static::$type . '_yes_label', $options, __( 'Yes', 'pods' ), true ) );
		$no  = strtolower( pods_v( static::$type . '_no_label', $options, __( 'No', 'pods' ), true ) );

		// Only allow 0 / 1
		if ( 'yes' === strtolower( $value ) || '1' === (string) $value ) {
			$value = 1;
		} elseif ( 'no' === strtolower( $value ) || '0' === (string) $value ) {
			$value = 0;
		} elseif ( strtolower( $value ) === $yes ) {
			$value = 1;
		} elseif ( strtolower( $value ) === $no ) {
			$value = 0;
		} elseif ( 0 !== (int) $value ) {
			$value = 1;
		} else {
			$value = 0;
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		$yesno = array(
			1 => pods_v( static::$type . '_yes_label', $options, __( 'Yes', 'pods' ), true ),
			0 => pods_v( static::$type . '_no_label', $options, __( 'No', 'pods' ), true ),
		);

		if ( isset( $yesno[ (int) $value ] ) ) {
			$value = strip_tags( $yesno[ (int) $value ], '<strong><a><em><span><img>' );
		}

		return $value;
	}
}
