<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Group class.
 *
 * @since 2.8
 */
class Group extends Whatsit {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'group';

	/**
	 * {@inheritdoc}
	 */
	public function get_fields() {
		if ( array() === $this->_fields ) {
			return array();
		}

		$object_collection = Store::get_instance();

		$storage_object = $object_collection->get_storage_object( $this->get_arg( 'storage_type' ) );

		if ( ! $storage_object ) {
			return array();
		}

		if ( null === $this->_fields ) {
			$args = array(
				'object_type'      => 'field',
				'orderby'          => 'menu_order title',
				'order'            => 'ASC',
				'group'            => $this->get_identifier(),
				'group_id'         => $this->get_id(),
				'group_name'       => $this->get_name(),
				'group_identifier' => $this->get_identifier(),
			);

			$fields = $storage_object->find( $args );

			$this->_fields = wp_list_pluck( $fields, 'id' );

			return $fields;
		}

		$fields = array_map( array( $object_collection, 'get_object' ), $this->_fields );
		$fields = array_filter( $fields );

		$names = wp_list_pluck( $fields, 'name' );

		$fields = array_combine( $names, $fields );

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_groups() {
		return array();
	}

}
