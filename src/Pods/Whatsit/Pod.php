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
	public function get_args() {
		$args = parent::get_args();

		// Pods generally have no parent, group, or order.
		unset( $args['parent'], $args['group'], $args['weight'] );

		return $args;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_object_fields() {
		if ( array() === $this->_object_fields ) {
			return array();
		}

		$api = pods_api();

		$object_fields = $api->get_wp_object_fields( $this->get_type(), $this );

		$object_collection = Store::get_instance();

		$objects = array();

		foreach ( $object_fields as $object_field ) {
			$object_field['object_type']  = 'object-field';
			$object_field['storage_type'] = 'collection';
			$object_field['parent']       = $this->get_id();

			$object = $object_collection->get_object( $object_field );

			if ( $object ) {
				$objects[ $object->get_name() ] = $object;
			}
		}

		$this->_object_fields = $objects;

		return $objects;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		if ( null !== $this->_table_info ) {
			return $this->_table_info;
		}

		$api = pods_api();

		$table_info = $api->get_table_info( $this->get_type(), $this->get_name(), null, $this );

		if ( empty( $table_info ) ) {
			$table_info = array();
		}

		$this->_table_info = $table_info;

		return $table_info;
	}

}
