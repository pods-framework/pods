<?php

/**
 * @package Pods\Fields
 */
class PodsField_Slug extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'slug';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Permalink (url-friendly)';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public static $pod_types = array(
		'pod',
		'table',
	);

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$label = __( 'Permalink (url-friendly)', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
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

		$schema = 'VARCHAR(200)';

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;
		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( '-', $value );
		}

		$field_type = 'slug';

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

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		$index = pods_v( 'pod_index', pods_v( 'options', $pod, $pod, true ), 'id', true );

		if ( empty( $value ) && isset( $fields[ $index ] ) ) {
			$value = $fields[ $index ]['value'];
		}

		$value = pods_unique_slug( $value, $name, $pod, 0, $params->id, null, false );

		return $value;
	}
}
