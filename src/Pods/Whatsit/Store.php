<?php

namespace Pods\Whatsit;

use Pods\Whatsit;
use Pods\Whatsit\Storage\Collection;
use Pods\Whatsit\Storage\File;
use Pods\Whatsit\Storage\Post_Type;

/**
 * Store class.
 *
 * @since 2.8.0
 */
class Store {

	/**
	 * @var string
	 */
	protected $salt = '';

	/**
	 * @var Store[]
	 */
	protected static $instances = [];

	/**
	 * @var string[]
	 */
	protected $object_types = [];

	/**
	 * @var string[]
	 */
	protected $storage_types = [];

	/**
	 * @var Storage[]
	 */
	protected $storage_engine = [];

	/**
	 * @var Whatsit[]
	 */
	protected $objects = [];

	/**
	 * @var string[]
	 */
	protected $object_ids = [];

	/**
	 * @var array[]
	 */
	protected $objects_in_storage = [];

	/**
	 * Store constructor.
	 */
	protected function __construct() {
		$this->object_types  = $this->get_default_object_types();
		$this->storage_types = $this->get_default_storage_types();
		$this->objects       = $this->get_default_objects();

		$this->rebuild_index();
	}

	/**
	 * Get list of default object type classes.
	 *
	 * @return string[] List of object type classes.
	 */
	public function get_default_object_types() {
		return [
			'pod'              => Pod::class,
			'field'            => Field::class,
			'object-field'     => Object_Field::class,
			'group'            => Group::class,
			'template'         => Template::class,
			'page'             => Page::class,
			'block'            => Block::class,
			'block-field'      => Block_Field::class,
			'block-collection' => Block_Collection::class,
		];
	}

	/**
	 * Get list of default storage type classes.
	 *
	 * @return string[] List of storage type classes.
	 */
	public function get_default_storage_types() {
		return [
			'collection' => Collection::class,
			'file'       => File::class,
			'post_type'  => Post_Type::class,
		];
	}

	/**
	 * Get list of default objects.
	 *
	 * @return array List of objects.
	 */
	public function get_default_objects() {
		return [
			'pod/_pods_pod'   => [
				'internal'     => true,
				'object_type'  => 'pod',
				'object_storage_type' => 'collection',
				'name'         => '_pods_pod',
				'label'        => __( 'Pod', 'pods' ),
				'description'  => __( 'Pod configuration', 'pods' ),
				'type'         => 'post_type',
			],
			'pod/_pods_group' => [
				'internal'     => true,
				'object_type'  => 'pod',
				'object_storage_type' => 'collection',
				'name'         => '_pods_group',
				'label'        => __( 'Pod Group', 'pods' ),
				'description'  => __( 'Pod Group configuration', 'pods' ),
				'type'         => 'post_type',
			],
			'pod/_pods_field' => [
				'internal'     => true,
				'object_type'  => 'pod',
				'object_storage_type' => 'collection',
				'name'         => '_pods_field',
				'label'        => __( 'Pod Field', 'pods' ),
				'description'  => __( 'Pod Field configuration', 'pods' ),
				'type'         => 'post_type',
			],
			'pod/_pods_template' => [
				'internal'     => true,
				'object_type'  => 'pod',
				'object_storage_type' => 'collection',
				'name'         => '_pods_template',
				'label'        => __( 'Pod Template', 'pods' ),
				'description'  => __( 'Pod Template configuration', 'pods' ),
				'type'         => 'post_type',
			],
			'pod/_pods_page' => [
				'internal'     => true,
				'object_type'  => 'pod',
				'object_storage_type' => 'collection',
				'name'         => '_pods_page',
				'label'        => __( 'Pod Page', 'pods' ),
				'description'  => __( 'Pod Page configuration', 'pods' ),
				'type'         => 'post_type',
			],
		];
	}

	/**
	 * Get the Store instance.
	 *
	 * @param int|null $blog_id The blog ID for the Store instance.
	 *
	 * @return self The Store instance.
	 */
	public static function get_instance( $blog_id = null ) {
		if ( null === $blog_id ) {
			$blog_id = get_current_blog_id();
		}

		if ( ! isset( self::$instances[ $blog_id ] ) ) {
			self::$instances[ $blog_id ] = new self();
		}

		return self::$instances[ $blog_id ];
	}

	/**
	 * Destroy the Store instance.
	 *
	 * @param int|null $blog_id The blog ID for the Store instance.
	 */
	public static function destroy( $blog_id = null ) {
		if ( null === $blog_id ) {
			self::$instances = [];
		} elseif ( isset( self::$instances[ $blog_id ] ) ) {
			unset( self::$instances[ $blog_id ] );
		}
	}

	/**
	 * Register an object type to collection.
	 *
	 * @param string $object_type Pods object type.
	 * @param string $class_name  Object class name.
	 */
	public function register_object_type( $object_type, $class_name ) {
		$this->object_types[ $object_type ] = $class_name;

		$this->refresh_salt();
	}

	/**
	 * Unregister an object type to collection.
	 *
	 * @param string $object_type Pods object type.
	 *
	 * @return boolean Whether the object type was successfully unregistered.
	 */
	public function unregister_object_type( $object_type ) {
		$defaults = $this->get_default_object_types();

		if ( isset( $defaults[ $object_type ] ) ) {
			return false;
		}

		if ( isset( $this->object_types[ $object_type ] ) ) {
			unset( $this->object_types[ $object_type ] );

			$this->refresh_salt();

			return true;
		}

		return false;
	}

	/**
	 * Remove all object types from collection.
	 */
	public function flush_object_types() {
		$this->object_types = $this->get_default_object_types();

		$this->refresh_salt();
	}

	/**
	 * Get list of object types.
	 *
	 * @return array List of object types.
	 */
	public function get_object_types() {
		return $this->object_types;
	}

	/**
	 * Register an object storage type to collection.
	 *
	 * @param string         $storage_type Pods object storage type.
	 * @param string|Storage $class_name   Object storage class name or object.
	 */
	public function register_storage_type( $storage_type, $class_name ) {
		if ( $class_name instanceof Storage ) {
			$this->storage_engine[ $storage_type ] = clone $class_name;

			$class_name = get_class( $class_name );
		}

		$this->storage_types[ $storage_type ] = $class_name;

		$this->refresh_salt();
	}

	/**
	 * Unregister an object storage type to collection.
	 *
	 * @param string $storage_type Pods object storage type.
	 *
	 * @return boolean Whether the object storage type was successfully unregistered.
	 */
	public function unregister_storage_type( $storage_type ) {
		$defaults = $this->get_default_storage_types();

		if ( isset( $defaults[ $storage_type ] ) ) {
			return false;
		}

		if ( isset( $this->storage_types[ $storage_type ] ) ) {
			unset( $this->storage_types[ $storage_type ] );

			if ( isset( $this->storage_engine[ $storage_type ] ) ) {
				unset( $this->storage_engine[ $storage_type ] );
			}

			$this->refresh_salt();

			return true;
		}

		return false;
	}

	/**
	 * Remove all object storage types from collection.
	 */
	public function flush_storage_types() {
		$this->storage_types = $this->get_default_storage_types();

		// Remove all storage engines that have been flushed exist.
		foreach ( $this->storage_engine as $storage_type => $engine ) {
			if ( isset( $this->storage_types[ $storage_type ] ) ) {
				continue;
			}

			unset( $this->storage_engine[ $storage_type ] );

			$this->refresh_salt();
		}
	}

	/**
	 * Get list of object storage types.
	 *
	 * @return array List of object storage types.
	 */
	public function get_object_storage_types() {
		return $this->storage_types;
	}

	/**
	 * Register an object to collection.
	 *
	 * @param Whatsit|array $object Pods object.
	 */
	public function register_object( $object ) {
		$id                  = null;
		$identifier          = null;
		$object_storage_type = 'collection';

		if ( $object instanceof Whatsit ) {
			$id                  = $object->get_id();
			$identifier          = $object->get_identifier();
			$object_storage_type = $object->get_object_storage_type();
		} elseif ( is_array( $object ) ) {
			if ( ! empty( $object['id'] ) ) {
				$id = $object['id'];
			}

			if ( empty( $object['name'] ) && ! empty( $object['namespace'] ) && ! empty( $object['object_type'] ) && 'block-collection' === $object['object_type'] ) {
			    $object['name'] = $object['namespace'];
			}

			if ( ! empty( $object['object_storage_type'] ) ) {
				$object_storage_type = $object['object_storage_type'];
			}

			$identifier = Whatsit::get_identifier_from_args( $object );
		} else {
			// Don't register this object.
			return;
		}

		$this->index( $identifier, [
			'id' => $id,
			'object_storage_type' => $object_storage_type,
		] );

		if ( $object instanceof Whatsit ) {
			$object = clone $object;
		}

		$this->objects[ $identifier ] = $object;

		$this->refresh_salt();
	}

	/**
	 * Remove all objects from collection.
	 */
	public function flush_objects() {
		$default_objects = $this->get_default_objects();

		foreach ( $this->objects as $identifier => $object ) {
			if ( isset( $default_objects[ $identifier ] ) ) {
				continue;
			}

			$this->unregister_object( $object );
		}
	}

	/**
	 * Unregister an object to collection.
	 *
	 * @param string|Whatsit|array $identifier Object identifier, ID, or Pods object instance.
	 *
	 * @return boolean Whether the object was successfully unregistered.
	 */
	public function unregister_object( $identifier ) {
		$defaults = $this->get_default_objects();

		if ( $identifier instanceof Whatsit ) {
			$id         = $identifier->get_id();
			$identifier = $identifier->get_identifier();
		} else {
			// This might be an ID.
			$id = $identifier;
		}

		if ( isset( $this->objects[ $identifier ] ) ) {
			if ( isset( $defaults[ $identifier ] ) ) {
				return false;
			}

			$object = $this->objects[ $identifier ];

			$object_storage_type = 'collection';

			if ( is_array( $object ) ) {
				if ( ! empty( $object['object_storage_type'] ) ) {
					$object_storage_type = $object['object_storage_type'];
				}
			} elseif ( $object instanceof Whatsit ) {
				$object_storage_type = $object->get_object_storage_type();
			} else {
				return false;
			}

			// Ensure reference gets killed.
			$object                       = null;
			$this->objects[ $identifier ] = null;

			unset( $this->objects[ $identifier ] );

			$this->deindex( $identifier, [
				'id'                  => $id,
				'object_storage_type' => $object_storage_type,
			] );

			$this->refresh_salt();

			return true;
		}//end if

		return false;
	}

	/**
	 * Flatten objects so that PHP objects are removed but are still registered.
	 */
	public function flatten_objects() {
		foreach ( $this->objects as $identifier => $object ) {
			if ( ! $object instanceof Whatsit ) {
				continue;
			}

			// Ensure reference gets killed.
			$object = null;

			$this->flatten_object( $identifier );
		}
	}

	/**
	 * Flatten objects so that PHP objects are removed but are still registered.
	 */
	public function flatten_object( $identifier ) {
		if ( $identifier instanceof Whatsit ) {
			$identifier = $identifier->get_identifier();
		}

		if ( ! isset( $this->objects[ $identifier ] ) ) {
			return;
		}

		if ( ! $this->objects[ $identifier ] instanceof Whatsit ) {
			return;
		}

		$this->objects[ $identifier ] = $this->objects[ $identifier ]->get_args();
	}

	/**
	 * Delete all objects and then flush them from collection.
	 */
	public function delete_objects() {
		$default_objects = $this->get_default_objects();

		foreach ( $this->objects as $identifier => $object ) {
			if ( isset( $default_objects[ $identifier ] ) ) {
				continue;
			}

			if ( ! $object instanceof Whatsit ) {
				$object = $this->get_object( $object );
			}

			// Delete from storage.
			$storage_type = $object->get_object_storage_type();

			if ( empty( $storage_type ) ) {
				$storage_type = 'collection';
			}

			$storage_object = $this->get_storage_object( $storage_type );

			if ( $storage_object ) {
				$storage_object->delete( $object );
			}

			$this->unregister_object( $object );
		}
	}

	/**
	 * Get storage type object.
	 *
	 * @param string $storage_type Object storage type.
	 *
	 * @return Storage Storage type object.
	 */
	public function get_storage_object( $storage_type ) {
		if ( isset( $this->storage_engine[ $storage_type ] ) ) {
			return $this->storage_engine[ $storage_type ];
		}

		$class_name = $this->get_object_storage_type( $storage_type );

		if ( ! $class_name || ! class_exists( $class_name ) ) {
			return null;
		}

		$storage_object = new $class_name();

		$this->storage_engine[ $storage_type ] = $storage_object;

		return $storage_object;
	}

	/**
	 * Get object storage type class.
	 *
	 * @param string $storage_type Object storage type.
	 *
	 * @return string Storage type class.
	 */
	public function get_object_storage_type( $storage_type ) {
		if ( isset( $this->storage_types[ $storage_type ] ) ) {
			return $this->storage_types[ $storage_type ];
		}

		return null;
	}

	/**
	 * Get objects from collection.
	 *
	 * @param array|null $storage_types The storage types to retrieve.
	 *
	 * @return Whatsit[] List of objects.
	 */
	public function get_objects( array $args = [] ) {
		$objects = null;

		if ( isset( $args['ids'] ) && ! is_bool( $args['ids'] ) ) {
			// Filter objects by IDs (for faster lookups).
			$args['ids'] = (array) $args['ids'];
			$args['ids'] = array_map( 'absint', $args['ids'] );
			$args['ids'] = array_filter( $args['ids'] );

			$objects = [];

			foreach ( $args['ids'] as $id ) {
				if ( isset( $this->object_ids[ $id ] ) ) {
					$identifier = $this->object_ids[ $id ];

					$objects[ $identifier ] = $this->objects[ $identifier ];
				}
			}
		} elseif ( isset( $args['identifiers'] ) && ! is_bool( $args['identifiers'] ) ) {
			// Filter objects by identifiers (for faster lookups).
			$args['identifiers'] = (array) $args['identifiers'];

			$objects = [];

			foreach ( $args['identifiers'] as $identifier ) {
				if ( isset( $this->objects[ $identifier ] ) ) {
					$objects[ $identifier ] = $this->objects[ $identifier ];
				}
			}
		}

		// Filter objects by object storage type.
		if ( isset( $args['object_storage_types'] ) && ! is_bool( $args['object_storage_types'] ) ) {
			$args['object_storage_types'] = (array) $args['object_storage_types'];

			// If no objects were filtered by ID, build by the index we have.
			if ( null === $objects ) {
				$objects = [];

				foreach ( $args['object_storage_types'] as $object_storage_type ) {
					if ( ! isset( $this->objects_in_storage[ $object_storage_type ] ) ) {
						continue;
					}

					foreach ( $this->objects_in_storage[ $object_storage_type ] as $identifier ) {
						if ( ! isset( $this->objects[ $identifier ] ) ) {
							continue;
						}

						$objects[ $identifier ] = $this->objects[ $identifier ];
					}
				}
			} else {
				// Filter the $objects by object storage type if we have them.

				// Maybe use isset() instead of in_array() for the comparisons.
				if ( isset( $args['object_storage_types'][0] ) ) {
					$args['object_storage_types'] = array_flip( $args['object_storage_types'] );
				}

				$objects = array_filter( $objects, static function( $object ) use ( $args ) {
					$current_object_storage_type = null;

					if ( $object instanceof Whatsit ) {
						$current_object_storage_type = $object->get_object_storage_type();
					} elseif ( is_array( $object ) && isset( $object['object_storage_type'] ) ) {
						$current_object_storage_type = $object['object_storage_type'];
					}

					return (
						$current_object_storage_type
						&& isset( $args['object_storage_types'][ $current_object_storage_type ] )
					);
				} );
			}
		}

		// If no objects were filtered by ID, we'll have to reference the current known objects and filter them out.
		if ( null === $objects ) {
			$objects = $this->objects;
		}

		// Filter objects by object type.
		if ( isset( $args['object_types'] ) && ! is_bool( $args['object_types'] ) ) {
			$args['object_types'] = (array) $args['object_types'];

			// Maybe use isset() instead of in_array() for the comparisons.
			if ( isset( $args['object_types'][0] ) ) {
				$args['object_types'] = array_flip( $args['object_types'] );
			}

			$objects = array_filter( $objects, static function( $object ) use ( $args ) {
				$current_object_type = null;

				if ( $object instanceof Whatsit ) {
					$current_object_type = $object->get_object_type();
				} elseif ( is_array( $object ) && isset( $object['object_type'] ) ) {
					$current_object_type = $object['object_type'];
				}

				return (
					$current_object_type
					&& isset( $args['object_types'][ $current_object_type ] )
				);
			} );
		}

		// Filter objects by name.
		if ( isset( $args['names'] ) && ! is_bool( $args['names'] ) ) {
			$args['names'] = (array) $args['names'];
			$args['names'] = array_map( 'trim', $args['names'] );
			$args['names'] = array_filter( $args['names'] );

			// Maybe use isset() instead of in_array() for the comparisons.
			if ( isset( $args['names'][0] ) ) {
				$args['names'] = array_flip( $args['names'] );
			}

			$objects = array_filter( $objects, static function( $object ) use ( $args ) {
				$current_name = null;

				if ( $object instanceof Whatsit ) {
					$current_name = $object->get_name();
				} elseif ( is_array( $object ) && isset( $object['name'] ) ) {
					$current_name = $object['name'];
				}

				return (
					$current_name
					&& isset( $args['names'][ $current_name ] )
				);
			} );
		}

		// Filter objects by internal.
		if ( isset( $args['internal'] ) ) {
			$args['internal'] = (boolean) $args['internal'];

			$objects = array_filter( $objects, static function( $object ) use ( $args ) {
				$internal = false;

				if ( $object instanceof Whatsit ) {
					$internal = $object->get_arg( 'internal', false );
				} elseif ( is_array( $object ) && isset( $object['internal'] ) ) {
					$internal = $object['internal'];
				}

				return $args['internal'] === (boolean) $internal;
			} );
		}

		// Build the objects.
		$objects = array_map( [ $this, 'get_object' ], $objects );

		return array_filter( $objects );
	}

	/**
	 * Get object from a specific object storage type.
	 *
	 * @param string                    $object_storage_type The object storage type.
	 * @param string|null|Whatsit|array $identifier          Object identifier, ID, or the object/array itself.
	 *
	 * @return Whatsit|null Object or null if not found.
	 */
	public function get_object_from_storage( $object_storage_type, $identifier ) {
		$object = $this->get_object( $identifier );

		if ( $object ) {
			return $object;
		}

		$storage = $this->get_storage_object( $object_storage_type );

		$args = [
			'limit' => 1,
		];

		if ( is_int( $identifier ) || is_numeric( $identifier ) ) {
			$args['id'] = $identifier;
		} elseif ( is_string( $identifier ) ) {
			$args['identifier'] = $identifier;
		} elseif ( $identifier instanceof Whatsit ) {
			$args['identifier'] = $identifier->get_identifier();
		} else {
			return null;
		}

		$objects = $storage->find( $args );

		if ( empty( $objects ) ) {
			return null;
		}

		return current( $objects );
	}

	/**
	 * Get object from collection.
	 *
	 * @param string|null|Whatsit|array $identifier Object identifier, ID, or the object/array itself.
	 *
	 * @return Whatsit|null Object or null if not found.
	 */
	public function get_object( $identifier ) {
		// Is this already an object?
		if ( $identifier instanceof Whatsit ) {
			return $this->setup_object( $identifier );
		}

		// Is this an object config array?
		if ( is_array( $identifier ) ) {
			return $this->setup_object( $identifier );
		}

		// This might be an ID.
		$id = $identifier;

		if ( isset( $this->object_ids[ $id ] ) ) {
			$identifier = $this->object_ids[ $id ];
		}

		if ( isset( $this->objects[ $identifier ] ) ) {
			$object = $this->objects[ $identifier ];

			return $this->setup_object( $object );
		}

		return null;
	}

	/**
	 * Setup object if it needs to be.
	 *
	 * @param Whatsit|array $object Pods object or array.
	 *
	 * @return Whatsit|null Pods object or null if not able to setup.
	 */
	public function setup_object( $object ) {
		if ( $object instanceof Whatsit ) {
			return $object;
		}

		if ( ! is_array( $object ) ) {
			return null;
		}

		$args = $object;

		if ( ! isset( $args['object_type'] ) ) {
			return null;
		}

		$class_name = $this->get_object_type( $args['object_type'] );

		if ( ! $class_name || ! class_exists( $class_name ) ) {
			return null;
		}

		/** @var Whatsit $object */
		$object = new $class_name();
		$object->setup( $args );

		$this->objects[ $object->get_identifier() ] = $object;

		return $object;
	}

	/**
	 * Get object type class.
	 *
	 * @param string $object_type Object type.
	 *
	 * @return string Object type class.
	 */
	public function get_object_type( $object_type ) {
		if ( isset( $this->object_types[ $object_type ] ) ) {
			return $this->object_types[ $object_type ];
		}

		return null;
	}

	/**
	 * Get the current salt for the store.
	 *
	 * @since 2.9.10
	 *
	 * @return string
	 */
	public function get_salt() {
		return $this->salt;
	}

	/**
	 * Refresh the salt for the store to indicate a change.
	 *
	 * @since 2.9.10
	 */
	public function refresh_salt() {
		$this->salt = md5( microtime() );
	}

	/**
	 * Rebuild the index of objects.
	 *
	 * @since 2.9.10
	 */
	public function rebuild_index() {
		foreach ( $this->objects as $identifier => $object ) {
			$args = [];

			if ( $object instanceof Whatsit ) {
				$args['id']                  = $object->get_id();
				$args['object_storage_type'] = $object->get_object_storage_type();
			} elseif ( is_array( $object ) ) {
				if ( isset( $object['id'] ) ) {
					$args['id'] = $object['id'];
				}

				if ( isset( $object['object_storage_type'] ) ) {
					$args['object_storage_type'] = $object['object_storage_type'];
				}
			} else {
				continue;
			}

			$this->index( $identifier, $args );
		}
	}

	/**
	 * Index an object identifier based on args.
	 *
	 * @since 2.9.10
	 *
	 * @param string $identifier The object identifier to index.
	 * @param array  $args       The list of indexable arguments.
	 */
	public function index( $identifier, array $args ) {
		$id                  = ! empty( $args['id'] ) ? $args['id'] : null;
		$object_storage_type = ! empty( $args['object_storage_type'] ) ? $args['object_storage_type'] : null;

		if ( empty( $identifier ) ) {
			return;
		}

		if ( $id === $identifier ) {
			$id = null;
		}

		// Build the storage index.
		if ( null !== $object_storage_type ) {
			if ( ! isset( $this->objects_in_storage[ $object_storage_type ] ) ) {
				$this->objects_in_storage[ $object_storage_type ] = [];
			}

			$this->objects_in_storage[ $object_storage_type ][] = $identifier;
		}

		// Build the ID index.
		if ( null !== $id ) {
			$this->object_ids[ $id ] = $identifier;
		}
	}

	/**
	 * Deindex an object identifier based on args.
	 *
	 * @since 2.9.10
	 *
	 * @param string $identifier The object identifier to index.
	 * @param array  $args       The list of indexable arguments.
	 */
	public function deindex( $identifier, array $args ) {
		$id                  = ! empty( $args['id'] ) ? $args['id'] : null;
		$object_storage_type = ! empty( $args['object_storage_type'] ) ? $args['object_storage_type'] : null;

		if ( empty( $identifier ) ) {
			return;
		}

		if ( $id === $identifier ) {
			$id = null;
		}

		// Remove from the storage index.
		if ( null !== $object_storage_type && isset( $this->objects_in_storage[ $object_storage_type ] ) ) {
			$key = array_search( $identifier, $this->objects_in_storage[ $object_storage_type ], true );

			if ( false !== $key ) {
				unset( $this->objects_in_storage[ $object_storage_type ][ $key ] );
			}
		}

		// Remove from the ID index.
		if ( null !== $id ) {
			if ( isset( $this->object_ids[ $id ] ) ) {
				unset( $this->object_ids[ $id ] );
			}
		} else {
			$key = array_search( $identifier, $this->object_ids, true );

			if ( false !== $key ) {
				unset( $this->object_ids[ $key ] );
			}
		}
	}

}
