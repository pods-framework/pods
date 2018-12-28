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
	public function get_groups() {
		if ( array() === $this->_groups ) {
			return array();
		}

		$object_collection = Store::get_instance();
		$storage_object    = $object_collection->get_storage_object( $this->get_storage_type() );

		if ( ! $storage_object ) {
			return array();
		}

		if ( null === $this->_groups ) {
			$args = array(
				'object_type'       => 'group',
				'orderby'           => 'menu_order title',
				'order'             => 'ASC',
				'parent'            => $this->get_id(),
				'parent_id'         => $this->get_id(),
				'parent_name'       => $this->get_name(),
				'parent_identifier' => $this->get_identifier(),
			);

			/** @var Group[] $objects */
			$objects = $storage_object->find( $args );

			$this->_groups = wp_list_pluck( $objects, 'id' );

			return $objects;
		}

		$objects = array_map( array( $object_collection, 'get_object' ), $this->_groups );
		$objects = array_filter( $objects );

		$names = wp_list_pluck( $objects, 'name' );

		$objects = array_combine( $names, $objects );

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
