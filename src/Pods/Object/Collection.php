<?php

/**
 * Pods_Object_Collection class.
 *
 * @since 2.8
 */
class Pods_Object_Collection {

	/**
	 * @var Pods_Object_Collection
	 */
	protected static $instance;

	/**
	 * @var string[]
	 */
	protected $object_types = array();

	/**
	 * @var string[]
	 */
	protected $storage_types = array();

	/**
	 * @var Pods_Object_Storage[]
	 */
	protected $storage_engine = array();

	/**
	 * @var array
	 */
	protected $objects = array();

	/**
	 * @var array
	 */
	protected $object_ids = array();

	/**
	 * Pods_Object_Collection constructor.
	 */
	private function __construct() {
		// Nothing here.
	}

	/**
	 * Get instance of object.
	 *
	 * @return Pods_Object_Collection
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Destroy instance.
	 */
	public static function destroy() {
		self::$instance = null;
	}

	/**
	 * Register an object type to collection.
	 *
	 * @param string $object_type Pods object type.
	 * @param string $class_name  Object class name.
	 */
	public function register_object_type( $object_type, $class_name ) {
		$this->object_types[ $object_type ] = $class_name;
	}

	/**
	 * Unregister an object type to collection.
	 *
	 * @param string $object_type Pods object type.
	 *
	 * @return boolean Whether the object type was successfully unregistered.
	 */
	public function unregister_object_type( $object_type ) {
		if ( isset( $this->object_types[ $object_type ] ) ) {
			unset( $this->object_types[ $object_type ] );

			return true;
		}

		return false;
	}

	/**
	 * Remove all object types from collection.
	 */
	public function flush_object_types() {
		$this->object_types = array();
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
	 * Register an object storage type to collection.
	 *
	 * @param string                     $storage_type Pods object storage type.
	 * @param string|Pods_Object_Storage $class_name   Object storage class name or object.
	 */
	public function register_storage_type( $storage_type, $class_name ) {
		if ( $class_name instanceof Pods_Object_Storage ) {
			$this->storage_engine[ $storage_type ] = clone $class_name;

			$class_name = get_class( $class_name );
		}

		$this->storage_types[ $storage_type ] = $class_name;
	}

	/**
	 * Unregister an object storage type to collection.
	 *
	 * @param string $storage_type Pods object storage type.
	 *
	 * @return boolean Whether the object storage type was successfully unregistered.
	 */
	public function unregister_storage_type( $storage_type ) {
		if ( isset( $this->storage_types[ $storage_type ] ) ) {
			unset( $this->storage_types[ $storage_type ] );

			if ( isset( $this->storage_engine[ $storage_type ] ) ) {
				unset( $this->storage_engine[ $storage_type ] );
			}

			return true;
		}

		return false;
	}

	/**
	 * Remove all object storage types from collection.
	 */
	public function flush_storage_types() {
		$this->storage_types  = array();
		$this->storage_engine = array();
	}

	/**
	 * Get list of object storage types.
	 *
	 * @return array List of object storage types.
	 */
	public function get_storage_types() {
		return $this->storage_types;
	}

	/**
	 * Get object storage type class.
	 *
	 * @param string $storage_type Object storage type.
	 *
	 * @return string Storage type class.
	 */
	public function get_storage_type( $storage_type ) {
		if ( isset( $this->storage_types[ $storage_type ] ) ) {
			return $this->storage_types[ $storage_type ];
		}

		return null;
	}

	/**
	 * Get storage type object.
	 *
	 * @param string $storage_type Object storage type.
	 *
	 * @return Pods_Object_Storage Storage type object.
	 */
	public function get_storage_object( $storage_type ) {
		if ( isset( $this->storage_engine[ $storage_type ] ) ) {
			return $this->storage_engine[ $storage_type ];
		}

		$class_name = $this->get_storage_type( $storage_type );

		if ( ! $class_name || ! class_exists( $class_name ) ) {
			return null;
		}

		$storage_object = new $class_name;

		$this->storage_engine[ $storage_type ] = $storage_object;

		return $storage_object;
	}

	/**
	 * Register an object to collection.
	 *
	 * @param Pods_Object|array $object Pods object.
	 */
	public function register_object( $object ) {
		$id         = null;
		$identifier = null;

		if ( $object instanceof Pods_Object ) {
			$id         = $object->get_id();
			$identifier = $object->get_identifier();
		} elseif ( is_array( $object ) ) {
			if ( ! empty( $object['id'] ) ) {
				$id = $object['id'];
			}

			$identifier = Pods_Object::get_identifier_from_args( $object );
		} else {
			// Don't register this object.
			return;
		}

		// Store ids for reference.
		if ( '' !== $id && null !== $id ) {
			$this->object_ids[ $id ] = $identifier;
		}

		if ( $object instanceof Pods_Object ) {
			$object = clone $object;
		}

		$this->objects[ $identifier ] = $object;
	}

	/**
	 * Unregister an object to collection.
	 *
	 * @param string|Pods_Object|array $identifier Object identifier, ID, or Pods object instance.
	 *
	 * @return boolean Whether the object was successfully unregistered.
	 */
	public function unregister_object( $identifier ) {
		if ( $identifier instanceof Pods_Object ) {
			$id         = $identifier->get_id();
			$identifier = $identifier->get_identifier();
		} else {
			// This might be an ID.
			$id = $identifier;
		}

		if ( isset( $this->object_ids[ $id ] ) ) {
			// If this was an ID lookup, set the identifier for removal.
			if ( $identifier !== $this->object_ids[ $id ] ) {
				$identifier = $this->object_ids[ $id ];
			}

			unset( $this->object_ids[ $id ] );
		}

		if ( isset( $this->objects[ $identifier ] ) ) {
			// Ensure reference gets killed.
			$this->objects[ $identifier ] = null;

			unset( $this->objects[ $identifier ] );

			return true;
		}

		return false;
	}

	/**
	 * Remove all objects from collection.
	 */
	public function flush_objects() {
		// Ensure references get killed.
		$this->objects = array_map( '__return_null', $this->objects );

		$this->object_ids = array();
		$this->objects    = array();
	}

	/**
	 * Get objects from collection.
	 *
	 * @return Pods_Object[] List of objects.
	 */
	public function get_objects() {
		$objects = array_map( array( $this, 'get_object' ), $this->objects );
		$objects = array_filter( $objects );

		return $objects;
	}

	/**
	 * Get object from collection.
	 *
	 * @param string|null|Pods_Object|array $identifier Object identifier, ID, or the object/array itself.
	 *
	 * @return Pods_Object|null Object or null if not found.
	 */
	public function get_object( $identifier ) {
		// Is this already an object?
		if ( $identifier instanceof Pods_Object ) {
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
	 * @param Pods_Object|array $object Pods object or array.
	 *
	 * @return Pods_Object|null Pods object or null if not able to setup.
	 */
	public function setup_object( $object ) {
		if ( $object instanceof Pods_Object ) {
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

		/** @var Pods_Object $object */
		$object = new $class_name;
		$object->setup( $args );

		$this->objects[ $object->get_identifier() ] = $object;

		return $object;
	}

}
