<?php

namespace Pods\Whatsit;

use Pods\Whatsit;
use Pods\Whatsit\Pod;
use Pods\Whatsit\Field;
use Pods\Whatsit\Group;
use Pods\Whatsit\Storage;
use Pods\Whatsit\Storage\Post_Type;

/**
 * Collection class.
 *
 * @since 2.8
 */
class Collection {

	/**
	 * @var Collection
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
	 * @var Storage[]
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
	 * Collection constructor.
	 */
	protected function __construct() {
		$this->object_types  = $this->get_default_object_types();
		$this->storage_types = $this->get_default_storage_types();
		$this->objects       = $this->get_default_objects();
	}

	/**
	 * Get list of default object type classes.
	 *
	 * @return array List of object type classes.
	 */
	public function get_default_object_types() {
		return array(
			'pod'   => __NAMESPACE__ . '\Pod',
			'field' => __NAMESPACE__ . '\Field',
			'group' => __NAMESPACE__ . '\Group',
		);
	}

	/**
	 * Get list of default storage type classes.
	 *
	 * @return array List of storage type classes.
	 */
	public function get_default_storage_types() {
		return array(
			'post_type' => __NAMESPACE__ . '\Storage\Post_Type',
		);
	}

	/**
	 * Get list of default objects.
	 *
	 * @return array List of objects.
	 */
	public function get_default_objects() {
		return array(
			'pod/_pods_pod'   => array(
				'internal'    => true,
				'object_type' => 'pod',
				'name'        => '_pods_pod',
				'label'       => __( 'Pod', 'pods' ),
				'description' => __( 'Pod configuration', 'pods' ),
			),
			'pod/_pods_field' => array(
				'internal'    => true,
				'object_type' => 'pod',
				'name'        => '_pods_field',
				'label'       => __( 'Pod Field', 'pods' ),
				'description' => __( 'Pod Field configuration', 'pods' ),
			),
			'pod/_pods_group' => array(
				'internal'    => true,
				'object_type' => 'pod',
				'name'        => '_pods_group',
				'label'       => __( 'Pod Group', 'pods' ),
				'description' => __( 'Pod Group configuration', 'pods' ),
			),
		);
	}

	/**
	 * Get instance of object.
	 *
	 * @return Collection
	 */
	public static function get_instance() {
		if ( ! self::$instance ) {
			self::$instance = new self();
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
		$defaults = $this->get_default_object_types();

		if ( isset( $defaults[ $object_type ] ) ) {
			return false;
		}

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
		$this->object_types = $this->get_default_object_types();
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
	 * @param string         $storage_type Pods object storage type.
	 * @param string|Storage $class_name   Object storage class name or object.
	 */
	public function register_storage_type( $storage_type, $class_name ) {
		if ( $class_name instanceof Storage ) {
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
		$defaults = $this->get_default_storage_types();

		if ( isset( $defaults[ $storage_type ] ) ) {
			return false;
		}

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
		$this->storage_types = $this->get_default_storage_types();

		// Remove all storage engines that have been flushed exist.
		foreach ( $this->storage_engine as $storage_type => $engine ) {
			if ( isset( $this->storage_types[ $storage_type ] ) ) {
				continue;
			}

			unset( $this->storage_engine[ $storage_type ] );
		}
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
	 * @return Storage Storage type object.
	 */
	public function get_storage_object( $storage_type ) {
		if ( isset( $this->storage_engine[ $storage_type ] ) ) {
			return $this->storage_engine[ $storage_type ];
		}

		$class_name = $this->get_storage_type( $storage_type );

		if ( ! $class_name || ! class_exists( $class_name ) ) {
			return null;
		}

		$storage_object = new $class_name();

		$this->storage_engine[ $storage_type ] = $storage_object;

		return $storage_object;
	}

	/**
	 * Register an object to collection.
	 *
	 * @param Whatsit|array $object Pods object.
	 */
	public function register_object( $object ) {
		$id         = null;
		$identifier = null;

		if ( $object instanceof Whatsit ) {
			$id         = $object->get_id();
			$identifier = $object->get_identifier();
		} elseif ( is_array( $object ) ) {
			if ( ! empty( $object['id'] ) ) {
				$id = $object['id'];
			}

			$identifier = Whatsit::get_identifier_from_args( $object );
		} else {
			// Don't register this object.
			return;
		}

		// Store ids for reference.
		if ( '' !== $id && null !== $id ) {
			$this->object_ids[ $id ] = $identifier;
		}

		if ( $object instanceof Whatsit ) {
			$object = clone $object;
		}

		$this->objects[ $identifier ] = $object;
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

		if ( isset( $this->object_ids[ $id ] ) ) {
			// If this was an ID lookup, set the identifier for removal.
			if ( $identifier !== $this->object_ids[ $id ] ) {
				$identifier = $this->object_ids[ $id ];
			}

			unset( $this->object_ids[ $id ] );
		}

		if ( isset( $this->objects[ $identifier ] ) ) {
			if ( isset( $defaults[ $identifier ] ) ) {
				return false;
			}

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
		$default_objects = $this->get_default_objects();

		foreach ( $this->objects as $identifier => $object ) {
			if ( isset( $default_objects[ $identifier ] ) ) {
				continue;
			}

			// Ensure references get killed.
			$object = null;

			$this->objects[ $identifier ] = null;

			unset( $this->objects[ $identifier ] );
		}

		$this->object_ids = array();
	}

	/**
	 * Get objects from collection.
	 *
	 * @return Whatsit[] List of objects.
	 */
	public function get_objects() {
		$objects = array_map( array( $this, 'get_object' ), $this->objects );
		$objects = array_filter( $objects );

		return $objects;
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

}
