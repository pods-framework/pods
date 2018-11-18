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
	 * Register object to collection.
	 *
	 * @param Pods_Object $object Pods object.
	 */
	public function register_object( Pods_Object $object ) {
		$id         = $object->get_id();
		$identifier = $object->get_identifier();

		// Store ids for reference.
		if ( ! empty( $id ) ) {
			$this->object_ids[ $id ] = $identifier;
		}

		$this->objects[ $identifier ] =& $object;
	}

	/**
	 * Unregister object to collection.
	 *
	 * @param string|Pods_Object $identifier Object identifier, ID, or Pods object instance.
	 *
	 * @return boolean Whether object was successfully unregistered.
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
		return $this->objects;
	}

	/**
	 * Get object from collection.
	 *
	 * @param string $identifier Object identifier or ID.
	 *
	 * @return Pods_Object|null Object or null if not found.
	 */
	public function get_object( $identifier ) {
		// This might be an ID.
		$id = $identifier;

		if ( isset( $this->object_ids[ $id ] ) ) {
			$identifier = $this->object_ids[ $id ];
		}

		if ( isset( $this->objects[ $identifier ] ) ) {
			return $this->objects[ $identifier ];
		}

		return null;
	}

}
