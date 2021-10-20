<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Pod class.
 *
 * @since 2.8.0
 */
class Pod extends Whatsit {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'pod';

	/**
	 * Get the storage used for the Pod data (meta, table, etc).
	 *
	 * @since 2.8.1
	 *
	 * @return string The storage used for the Pod data (meta, table, etc).
	 */
	public function get_storage() {
		return $this->get_arg( 'storage' );
	}

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
		if ( [] === $this->_object_fields ) {
			return [];
		}

		$api = pods_api();

		$object_fields = $api->get_wp_object_fields( $this->get_type(), $this );

		$object_collection = Store::get_instance();

		$objects = [];

		foreach ( $object_fields as $object_field ) {
			$object_field['object_type']  = 'object-field';
			$object_field['object_storage_type'] = 'collection';
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
	public function count_object_fields() {
		if ( [] === $this->_object_fields ) {
			return 0;
		}

		$api = pods_api();

		$object_fields = $api->get_wp_object_fields( $this->get_type(), $this );

		return count( $object_fields );
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
			$table_info = [];
		}

		$this->_table_info = $table_info;

		return $table_info;
	}

}
