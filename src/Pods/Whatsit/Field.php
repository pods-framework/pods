<?php

namespace Pods\Whatsit;

use PodsForm;
use Pods\Whatsit;

/**
 * Field class.
 *
 * @since 2.8.0
 */
class Field extends Whatsit {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'field';

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		if ( null !== $this->_table_info ) {
			return $this->_table_info;
		}

		$related_type = $this->get_related_object_type();
		$related_name = $this->get_related_object_name();

		if ( null === $related_type || null === $related_name ) {
			return [];
		}

		$api = pods_api();

		$table_info = $api->get_table_info( $related_type, $related_name );

		if ( ! $table_info ) {
			$table_info = [];
		}

		$this->_table_info = $table_info;

		return $table_info;
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
			'pod_storage_type'   => 'get_parent_storage_type',
			'pod_type'           => 'get_parent_type',
			// Group args.
			'group_id'           => 'get_group_id',
			'group_name'         => 'get_group_name',
			'group_identifier'   => 'get_group_identifier',
			'group_label'        => 'get_group_label',
			'group_description'  => 'get_group_description',
			'group_object'       => 'get_group_object',
			'group_object_type'  => 'get_group_object_type',
			'group_storage_type' => 'get_group_storage_type',
			'group_type'         => 'get_group_type',
		];

		if ( isset( $special_args[ $arg ] ) ) {
			return $this->{$special_args[ $arg ]}();
		}

		return parent::get_arg( $arg, $default );
	}

	/**
	 * Get related object type from field.
	 *
	 * @since 2.8.0
	 *
	 * @return string|null The related object type, or null if not found.
	 */
	public function get_related_object_type() {
		$type = $this->get_type();

		$simple_tableless_objects = PodsForm::simple_tableless_objects();

		// File field types are always related to the media object type.
		if ( 'file' === $type ) {
			return 'media';
		}

		$related_type = $this->get_arg( $type . '_object', $this->get_arg( 'pick_object' ) );

		if ( '__current__' === $related_type ) {
			$related_type = $this->get_object_type();
		}

		if ( empty( $related_type ) && 'avatar' === $type ) {
			$related_type = 'media';
		}

		if ( empty( $related_type ) || in_array( $related_type, $simple_tableless_objects, true ) ) {
			return null;
		}

		return $related_type;
	}

	/**
	 * Get related object name from field.
	 *
	 * @since 2.8.0
	 *
	 * @return string|null The related object name, or null if not found.
	 */
	public function get_related_object_name() {
		$type = $this->get_type();

		$related_type = $this->get_related_object_type();

		if ( null === $related_type ) {
			return null;
		}

		$related_name = $this->get_arg( $type . '_val', $this->get_arg( 'pick_val', $related_type ) );

		if ( '__current__' === $related_name ) {
			$related_name = $this->get_name();
		}

		if ( 'table' === $related_type ) {
			$related_name = $this->get_arg( 'related_table', $related_name );
		} elseif ( \in_array( $related_type, array( 'user', 'media', 'comment' ), true ) ) {
			$related_name = $related_type;
		}

		return $related_name;
	}

	/**
	 * Get related object data from field.
	 *
	 * @since 2.8.0
	 *
	 * @return array|null The related object data, or null if not found.
	 */
	public function get_related_object_data() {
		return PodsForm::field_method( $this->args['type'], 'data', $this->args['name'], null, $this->args, null, null, true );
	}

	/**
	 * Get field value limit from field.
	 *
	 * @since 2.8.0
	 *
	 * @return int The field value limit.
	 */
	public function get_limit() {
		$type   = $this->get_type();
		$format = $this->get_arg( $type .'_format_type', 'single' );

		if ( 'multi' === $format ) {
			return (int) $this->get_arg( $type . '_limit', 0 );
		}

		return 1;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_fields( array $args = [] ) {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_groups( array $args = [] ) {
		return [];
	}

}
