<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Pod class.
 *
 * @since 2.8
 */
class Pod extends Whatsit {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'pod';

	/**
	 * {@inheritdoc}
	 */
	public function get_object_fields() {
		if ( array() === $this->_object_fields ) {
			return array();
		}

		$api = pods_api();

		$object_fields = $api->get_wp_object_fields( $this->get_type(), $this );

		$object_collection = Collection::get_instance();

		$fields = array();

		foreach ( $object_fields as $object_field ) {
			$object_field['object_type'] = 'field';
			$object_field['parent']      = $this->get_id();

			$field = $object_collection->get_object( $object_field );

			if ( $field ) {
				$fields[ $field->get_name() ] = $field;
			}
		}

		$this->_object_fields = $fields;

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		if ( array() === $this->_table_info ) {
			return array();
		}

		$api = pods_api();

		$table_info = $api->get_table_info( $this->get_type(), $this->get_name() );

		if ( ! $table_info ) {
			$table_info = array();
		}

		$this->_table_info = $table_info;

		return $table_info;
	}

}
