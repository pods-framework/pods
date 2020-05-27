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
	public function get_args() {
		$args = parent::get_args();

		// Groups have no group.
		unset( $args['group'] );

		return $args;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_fields() {
		if ( [] === $this->_fields ) {
			return [];
		}

		$object_collection = Store::get_instance();

		if ( null === $this->_fields ) {
			$args = [
				'orderby'          => 'menu_order title',
				'order'            => 'ASC',
				'group'            => $this->get_name(),
				'group_id'         => $this->get_id(),
				'group_name'       => $this->get_name(),
				'group_identifier' => $this->get_identifier(),
			];

			$args = array_filter( $args );

			$objects = pods_api()->load_fields( $args );

			$this->_fields = wp_list_pluck( $objects, 'identifier' );

			return $objects;
		}

		$objects = array_map( [ $object_collection, 'get_object' ], $this->_fields );
		$objects = array_filter( $objects );

		$names = wp_list_pluck( $objects, 'name' );

		$objects = array_combine( $names, $objects );

		return $objects;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_groups() {
		return array();
	}

}
