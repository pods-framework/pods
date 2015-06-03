<?php
/**
 * @package Pods
 * @category Field Types
 */
class Pods_Field_Slug extends Pods_Field {

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'slug';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Permalink (url-friendly)';

	/**
	 * Field Type Preparation
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $prepare = '%s';

	/**
	 * Pod Types supported on (true for all, false for none, or give array of specific types supported)
	 *
	 * @var array|bool
	 * @since 2.1
	 */
	public static $pod_types = array( 'pod', 'table' );

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {

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
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return $value;

	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = array(), $pod = null, $id = null ) {

		$form_field_type = Pods_Form::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( '-', $value );
		}

		$field_type = 'slug';

		if ( isset( $options[ 'name' ] ) && false === Pods_Form::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options[ 'readonly' ] = true;

				$field_type = 'text';
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options[ 'readonly' ] = true;

			$field_type = 'text';
		}

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * {@inheritdoc}
	 */
	public function regex( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		return false;

	}

	/**
	 * {@inheritdoc}
	 */
	public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {

		return true;

	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

		$index = pods_var( 'pod_index', pods_v( 'options', $pod, $pod, true ), 'id', null, true );

		if ( empty( $value ) && isset( $fields[ $index ] ) ) {
			$value = $fields[ $index ][ 'value' ];
		}

		$value = pods_unique_slug( $value, $name, $pod, 0, $params->id, null, false );

		return $value;

	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		return $this->display( $value, $name, $options, $pod, $id );

	}

}