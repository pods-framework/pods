<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Group class.
 *
 * @since 2.8.0
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
	public function get_fields( array $args = [] ) {
		if ( [] === $this->_fields ) {
			return [];
		}

		$object_collection = Store::get_instance();

		$has_custom_args = ! empty( $args );

		if ( null === $this->_fields || $has_custom_args ) {
			$filtered_args = [
				'parent'            => $this->get_parent_id(),
				'parent_id'         => $this->get_parent_id(),
				'parent_name'       => $this->get_parent_name(),
				'parent_identifier' => $this->get_parent_identifier(),
				'group'             => $this->get_name(),
				'group_id'          => $this->get_id(),
				'group_name'        => $this->get_name(),
				'group_identifier'  => $this->get_identifier(),
			];

			if ( empty( $filtered_args['parent_id'] ) || empty( $filtered_args['group_id'] ) ) {
				$filtered_args['bypass_post_type_find'] = true;
			}

			$filtered_args = array_filter( $filtered_args );

			$args = array_merge( [
				'orderby'           => 'menu_order title',
				'order'             => 'ASC',
			], $filtered_args, $args );

			try {
				$api = pods_api();

				if ( ! empty( $args['object_type'] ) ) {
					$objects = $api->_load_objects( $args );
				} else {
					$objects = $api->load_fields( $args );
				}
			} catch ( \Exception $exception ) {
				$objects = [];
			}

			if ( ! $has_custom_args ) {
				$this->_fields = wp_list_pluck( $objects, 'identifier' );
			}

			return $objects;
		}

		$objects = array_map( [ $object_collection, 'get_object' ], $this->_fields );
		$objects = array_filter( $objects );

		$names = wp_list_pluck( $objects, 'name' );

		return array_combine( $names, $objects );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_groups( array $args = [] ) {
		// Groups do not support groups.
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_arg( $arg, $default = null, $strict = false ) {
		$arg = (string) $arg;

		$special_args = [
			// Pod args.
			'pod_id'             => 'get_parent_id',
			'pod'                => 'get_parent_name',
			'pod_name'           => 'get_parent_name',
			'pod_identifier'     => 'get_parent_identifier',
			'pod_label'          => 'get_parent_label',
			'pod_description'    => 'get_parent_description',
			'pod_object'         => 'get_parent_object',
			'pod_object_type'    => 'get_parent_object_type',
			'pod_object_storage_type'   => 'get_parent_object_storage_type',
			'pod_type'           => 'get_parent_type',
		];

		if ( isset( $special_args[ $arg ] ) ) {
			return $this->{$special_args[ $arg ]}();
		}

		return parent::get_arg( $arg, $default );
	}

}
