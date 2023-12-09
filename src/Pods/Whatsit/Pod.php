<?php

namespace Pods\Whatsit;

use Pods\Static_Cache;
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
	 * @param boolean $strict Whether to only get the argument, otherwise the default will be returned.
	 *
	 * @return string The storage used for the Pod data (meta, table, etc).
	 */
	public function get_storage( $strict = false ) {
		$storage = parent::get_arg( 'storage' );

		if ( ! $strict && empty( $storage ) ) {
			$storage = $this->get_default_storage();
		}

		return $storage;
	}

	/**
	 * Get the default storage used for the Pod data (meta, table, etc) based on the current Pod type.
	 *
	 * @since 2.9.16
	 *
	 * @return string The default storage used for the Pod data (meta, table, etc).
	 */
	public function get_default_storage() {
		$type    = $this->get_type();
		$storage = 'none';

		if ( in_array( $type, [ 'post_type', 'taxonomy', 'user', 'comment', 'media' ], true ) ) {
			$storage = 'meta';
		} elseif ( in_array( $type, [ 'pod', 'table' ], true ) ) {
			$storage = 'table';
		} elseif ( 'settings' === $type )  {
			$storage = 'option';
		}

		return $storage;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_args() {
		$args = parent::get_args();

		$args['storage'] = $this->get_arg( 'storage' );

		// Pods generally have no parent, group, or order.
		unset( $args['parent'], $args['group'], $args['weight'] );

		return $args;
	}

	/**
	 * {@inheritdoc}
	 */
	public function export( array $args = [] ) {
		$exported = parent::export( $args );

		// Always make sure we have a storage arg set.
		$exported['storage'] = $this->get_storage();

		return $exported;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_arg( $arg, $default = null, $strict = false, $raw = false ) {
		if ( $raw ) {
			return parent::get_arg( $arg, $default, $strict, $raw );
		}

		if ( 'storage' === $arg ) {
			return $this->get_storage();
		}

		if ( 'type' === $arg && null === $default ) {
			$default = 'post_type';
		}

		$value = parent::get_arg( $arg, $default, $strict );

		// Better handle object for extended objects.
		if ( 'object' === $arg && 'table' !== $this->get_type() && ( did_action( 'init' ) || doing_action( 'init' ) ) ) {
			if ( $this->is_extended() ) {
				return $this->get_name();
			}

			return '';
		}

		return $value;
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

	/**
	 * Determine whether this is a table-based Pod.
	 *
	 * @since 2.9.0
	 *
	 * @return bool Whether this is a table-based Pod.
	 */
	public function is_table_based() {
		return 'table' === $this->get_storage() || 'pod' === $this->get_type();
	}

	/**
	 * Determine whether this is a meta-based Pod.
	 *
	 * @since 2.9.0
	 *
	 * @return bool Whether this is a meta-based Pod.
	 */
	public function is_meta_based() {
		return 'meta' === $this->get_storage();
	}

	/**
	 * Determine whether the Pod is an extending an existing content type.
	 *
	 * @since 2.8.4
	 *
	 * @return bool Whether the Pod is an extending an existing content type.
	 */
	public function is_extended() {
		$type = $this->get_type();
		$name = $this->get_name();

		// Simple content type checks.
		if ( 'user' === $type ) {
			return true;
		} elseif ( 'media' === $type ) {
			return true;
		} elseif ( 'comment' === $type ) {
			return true;
		} elseif ( $type === $name ) {
			return true;
		} elseif ( 'post_type' !== $type && 'taxonomy' !== $type ) {
			return false;
		}

		$cached_var = '';

		// Simple checks for post types.
		if ( 'post_type' === $type ) {
			if ( 'post' === $name || 'page' === $name ) {
				return true;
			}

			$cached_var = 'existing_post_types_cached';
		}

		// Simple checks for taxonomies.
		if ( 'taxonomy' === $type ) {
			if ( 'category' === $name || 'post_tag' === $name ) {
				return true;
			}

			$cached_var = 'existing_taxonomies_cached';
		}

		$existing_cached = pods_init()->refresh_existing_content_types_cache();

		return ! empty( $existing_cached[ $cached_var ] ) && array_key_exists( $this->get_name(), $existing_cached[ $cached_var ] );
	}

	/**
	 * Count the total rows for the pod.
	 *
	 * @since 2.8.9
	 *
	 * @return int The total rows for the Pod.
	 */
	public function count_rows() {
		$pod = pods( $this );

		if ( ! $pod || ! $pod->valid() ) {
			return 0;
		}

		return $pod->total_all_rows();
	}

	/**
	 * Count the total row meta for the pod.
	 *
	 * @since 2.8.9
	 *
	 * @return int The total row meta for the Pod.
	 */
	public function count_row_meta() {
		if ( 'meta' !== $this->get_storage() && ! in_array( $this->get_type(), [ 'post_type', 'taxonomy', 'user', 'comment' ], true ) ) {
			return 0;
		}

		$pod = pods( $this );

		if ( ! $pod || ! $pod->valid() ) {
			return 0;
		}

		$field_id      = $this->get_arg( 'field_id' );
		$meta_field_id = $this->get_arg( 'meta_field_id' );
		$meta_table    = $this->get_arg( 'meta_table' );

		if ( empty( $meta_field_id ) || empty( $meta_table ) ) {
			return 0;
		}

		// Make a simple request so we can perform a total_found() SQL request.
		$params = [
			'distinct' => false,
			'select'   => 'meta.' . $meta_field_id,
			'join'     => "LEFT JOIN {$meta_table} AS meta ON meta.{$meta_field_id} = t.{$field_id}",
			'limit'    => 1,
		];

		$pod->find( $params );

		return $pod->total_found();
	}

	/**
	 * Count the total wp_podsrel rows for the pod.
	 *
	 * @since 2.8.9
	 *
	 * @return int The total wp_podsrel rows for the pod.
	 */
	public function count_podsrel_rows() {
		if ( pods_tableless() ) {
			return 0;
		}

		$pod = pods( $this );

		if ( ! $pod || ! $pod->valid() ) {
			return 0;
		}

		$fields = $this->get_fields();

		if ( empty( $fields ) ) {
			return 0;
		}

		$pod_id    = (int) $this->get_id();
		$field_ids = wp_list_pluck( $fields, 'id' );
		$field_ids = array_map( 'absint', $field_ids );
		$field_ids = array_filter( $field_ids );

		if ( empty( $field_ids ) ) {
			return 0;
		}

		$field_ids = implode( ', ', $field_ids );

		$data = pods_data();

		global $wpdb;

		// Make a simple request so we can perform a total_found() SQL request.
		$params = [
			'distinct' => false,
			'select'   => 't.id',
			'table'    => $wpdb->prefix . 'podsrel',
			'where'    => "
				(
					t.pod_id = {$pod_id}
					AND t.field_id IN ( {$field_ids} )
				)
				OR (
					t.related_pod_id = {$pod_id}
					AND t.related_field_id IN ( {$field_ids} )
				)
			",
			'limit'    => 1,
		];

		$data->select( $params );

		return $data->total_found();
	}

}
