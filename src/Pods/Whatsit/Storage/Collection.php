<?php

namespace Pods\Whatsit\Storage;

use PodsAPI;
use Pods\Whatsit;
use Pods\Whatsit\Storage;
use Pods\Whatsit\Store;

/**
 * Collection class.
 *
 * @since 2.8.0
 */
class Collection extends Storage {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'collection';

	/**
	 * @var array
	 */
	protected static $compatible_types = [
		'collection' => 'collection',
		'file'       => 'file',
	];

	/**
	 * @var array
	 */
	protected $secondary_args = [];

	/**
	 * {@inheritdoc}
	 */
	public function get_label() {
		return __( 'Code', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get( array $args = [] ) {
		// Object type is required.
		if ( empty( $args['object_type'] ) ) {
			return null;
		}

		if ( ! empty( $args['name'] ) ) {
			$find_args = [
				'object_type' => $args['object_type'],
				'name'        => $args['name'],
				'limit'       => 1,
			];

			$objects = $this->find( $find_args );

			if ( $objects ) {
				return reset( $objects );
			}
		}

		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_by_identifier( $identifier, $parent = null ) {
		if ( $identifier instanceof Whatsit ) {
			return $identifier;
		}

		if ( ! is_string( $identifier ) || false === strpos( $identifier, '/' ) ) {
			return null;
		}

		$object_collection = Store::get_instance();

		// Check if we already have an object registered and available.
		$object = $object_collection->get_object( $identifier );

		if ( $object ) {
			return $object;
		}

		$identifier_parts = explode( '/', $identifier );

		$total_parts = count( $identifier_parts );

		$parent_object_id = 0;

		if ( 3 === $total_parts ) {
			$object_type = $identifier_parts[0];

			if ( is_numeric( $identifier_parts[1] ) ) {
				$parent_object_id = (int) $identifier_parts[1];
			}

			$object_name = $identifier_parts[2];
		} elseif ( 2 === $total_parts ) {
			$object_type = $identifier_parts[0];
			$object_name = $identifier_parts[1];
		} else {
			return null;
		}

		$get_args = [
			'object_type' => $object_type,
			'name'        => $object_name,
		];

		if ( $parent instanceof Whatsit ) {
			$get_args['parent']            = $parent->get_id();
			$get_args['parent_id']         = $parent->get_id();
			$get_args['parent_name']       = $parent->get_name();
			$get_args['parent_identifier'] = $parent->get_identifier();
		} elseif ( is_numeric( $parent ) ) {
			$get_args['parent']    = $parent;
			$get_args['parent_id'] = $parent;
		} elseif ( is_string( $parent ) ) {
			if ( false === strpos( $parent, '/' ) ) {
				$get_args['parent_name'] = $parent;
			} else {
				$get_args['parent_identifier'] = $parent;
			}
		} elseif ( 0 < $parent_object_id ) {
			$get_args['parent']    = $parent_object_id;
			$get_args['parent_id'] = $parent_object_id;
		}

		return $this->get( $get_args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function find( array $args = [] ) {
		// Object type OR parent is required.
		if ( empty( $args['object_type'] ) && empty( $args['object_types'] ) && empty( $args['parent'] ) ) {
			return [];
		}

		/**
		 * Filter the maximum number of posts to get for post type storage.
		 *
		 * @since 2.8.0
		 *
		 * @param int $limit
		 *
		 */
		$limit = apply_filters( 'pods_whatsit_storage_post_type_find_limit', 300 );

		if ( empty( $args['limit'] ) ) {
			$args['limit'] = $limit;
		}

		if ( ! isset( $args['args'] ) ) {
			$args['args'] = [];
		}

		$args['args'] = (array) $args['args'];

		$secondary_object_args = [
			'parent',
			'group',
		];

		foreach ( $secondary_object_args as $arg ) {
			$args      = $this->setup_arg( $args, $arg );
			$arg_value = $this->get_arg_value( $args, $arg );

			if ( '_null' === $arg_value ) {
				continue;
			}

			$args['args'][ $arg ] = $arg_value;
		}

		foreach ( $this->secondary_args as $arg ) {
			if ( ! isset( $args[ $arg ] ) ) {
				continue;
			}

			$args['args'][ $arg ] = $args[ $arg ];
		}

		$object_collection = Store::get_instance();

		$cache_key = wp_json_encode( $args ) . $object_collection->get_salt();

		$use_cache = did_action( 'init' ) && empty( $args['bypass_cache'] );

		$found_objects = null;

		if ( $use_cache ) {
			$found_objects = pods_static_cache_get( $cache_key, self::class . '/find_objects' );
		}

		// Cached objects found, don't process again.
		if ( is_array( $found_objects ) ) {
			return $found_objects;
		}

		$collection_args = [
			'object_storage_types' => static::$compatible_types,
		];

		if ( ! empty( $args['object_storage_type'] ) ) {
			$collection_args['object_storage_types'] = $args['object_storage_type'];
		} elseif ( ! empty( $args['object_storage_types'] ) ) {
			$collection_args['object_storage_types'] = $args['object_storage_types'];
		}

		if ( ! empty( $args['object_type'] ) ) {
			$collection_args['object_types'] = $args['object_type'];
		} elseif ( ! empty( $args['object_types'] ) ) {
			$collection_args['object_types'] = $args['object_types'];
		}

		if ( ! empty( $args['id'] ) ) {
			$collection_args['ids'] = $args['id'];
		} elseif ( ! empty( $args['ids'] ) ) {
			$collection_args['ids'] = $args['ids'];
		} elseif ( ! empty( $args['identifier'] ) ) {
			$collection_args['identifiers'] = $args['identifier'];
		} elseif ( ! empty( $args['identifiers'] ) ) {
			$collection_args['identifiers'] = $args['identifiers'];
		}

		if ( ! empty( $args['name'] ) ) {
			$collection_args['names'] = $args['name'];
		} elseif ( ! empty( $args['names'] ) ) {
			$collection_args['names'] = $args['names'];
		}

		if ( isset( $args['internal'] ) ) {
			$collection_args['internal'] = $args['internal'];
		}

		$objects = $object_collection->get_objects( $collection_args );

		if ( empty( $objects ) ) {
			return [];
		}

		foreach ( $args['args'] as $arg => $value ) {
			if ( null === $value ) {
				foreach ( $objects as $k => $object ) {
					if ( $value === $object->get_arg( $arg ) ) {
						continue;
					}

					unset( $objects[ $k ] );
				}

				if ( empty( $objects ) ) {
					return [];
				}

				continue;
			}

			if ( ! is_array( $value ) ) {
				$value = trim( $value );

				foreach ( $objects as $k => $object ) {
					if ( $value === (string) $object->get_arg( $arg ) ) {
						continue;
					}

					unset( $objects[ $k ] );
				}

				if ( empty( $objects ) ) {
					return [];
				}

				continue;
			}

			$value = (array) $value;
			$value = array_map( 'trim', $value );
			$value = array_unique( $value );
			$value = array_filter( $value );

			if ( $value ) {
				foreach ( $objects as $k => $object ) {
					$arg_value = $object->get_arg( $arg );

					if ( null !== $arg_value && ! is_scalar( $arg_value ) ) {
						$arg_value = serialize( $arg_value );
					}

					if ( in_array( (string) $arg_value, $value, true ) ) {
						continue;
					}

					unset( $objects[ $k ] );
				}

				if ( empty( $objects ) ) {
					return [];
				}
			}
		}//end foreach

		if ( ! empty( $args['limit'] ) ) {
			$objects = array_slice( $objects, 0, $args['limit'], true );
		}

		if ( $use_cache ) {
			pods_static_cache_set( $cache_key, $objects, self::class . '/find_objects' );
		}

		$names = wp_list_pluck( $objects, 'name' );

		return array_combine( $names, $objects );
	}

	/**
	 * {@inheritdoc}
	 */
	protected function save_object( Whatsit $object ) {
		$storage_type = $object->get_object_storage_type();

		if ( empty( $storage_type ) ) {
			$object->set_arg( 'object_storage_type', $this->get_object_storage_type() );
		}

		$object_collection = Store::get_instance();
		$object_collection->register_object( $object );

		// Clear caches that may be missing this object.
		pods_static_cache_clear( true, self::class . '/find_objects' );
		pods_static_cache_clear( true, static::class . '/find_objects/any' );
		pods_static_cache_clear( true, static::class . '/find_objects/' . $object->get_type() );

		// We can't do this because it'll mess with all other caches every time a group/field gets registered.
		// pods_cache_clear( true, PodsAPI::class . '/_load_objects' );

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function delete_object( Whatsit $object ) {
		// If this object has fields or groups, delete them.
		$objects = array_merge( $object->get_all_fields(), $object->get_groups() );

		// Delete child objects.
		array_map( [ $this, 'delete' ], $objects );

		$object_collection = Store::get_instance();
		$object_collection->unregister_object( $object );

		$object->set_arg( 'id', null );

		return true;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save_args( Whatsit $object ) {
		return true;
	}

	/**
	 * Setup object from an identifier.
	 *
	 * @param string $value         The identifier.
	 * @param bool   $force_refresh Whether to force the refresh of the object.
	 *
	 * @return Whatsit|null
	 */
	public function to_object( $value, $force_refresh = false ) {
		if ( empty( $value ) ) {
			return null;
		}

		if ( is_wp_error( $value ) ) {
			return null;
		}

		$object_collection = Store::get_instance();

		// Check if we already have an object registered and available.
		return $object_collection->get_object( $value );
	}

}
