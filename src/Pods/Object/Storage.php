<?php

/**
 * Pods_Object_Storage class.
 *
 * @since 2.8
 */
abstract class Pods_Object_Storage {

	/**
	 * @var string
	 */
	protected $type = '';

	/**
	 * Pods_Object_Storage constructor.
	 */
	public function __construct() {
		// @todo Bueller?
	}

	/**
	 * Get storage type.
	 *
	 * @return string
	 */
	public function get_storage_type() {
		return $this->type;
	}

	/**
	 * Get object from storage.
	 *
	 * @return Pods_Object|null
	 */
	public function get() {
		// @todo Get how?
		return null;
	}

	/**
	 * Find objects in storage.
	 *
	 * @param array $args Arguments to use.
	 *
	 * @return Pods_Object[]
	 */
	public function find( array $args = array() ) {
		// @todo Find how?
		return array();
	}

	/**
	 * Add an object.
	 *
	 * @param Pods_Object $object Object to add.
	 *
	 * @return string|int|false Object name, object ID, or false if not added.
	 */
	public function add( Pods_Object $object ) {
		return false;
	}

	/**
	 * Save an object.
	 *
	 * @param Pods_Object $object Object to save.
	 *
	 * @return string|int|false Object name, object ID, or false if not saved.
	 */
	public function save( Pods_Object $object ) {
		return false;
	}

	/**
	 * Save object argument data.
	 *
	 * @param Pods_Object $object Object with arguments to save.
	 *
	 * @return bool
	 */
	public function save_args( Pods_Object $object ) {
		return false;
	}

	/**
	 * Duplicate an object.
	 *
	 * @param Pods_Object $object Object to duplicate.
	 *
	 * @return string|int|false Duplicated object name, duplicated object ID, or false if not duplicated.
	 */
	public function duplicate( Pods_Object $object ) {
		return false;
	}

	/**
	 * Delete an object.
	 *
	 * @param Pods_Object $object Object to delete.
	 *
	 * @return bool
	 */
	public function delete( Pods_Object $object ) {
		return false;
	}

	/**
	 * Reset an object's item data.
	 *
	 * @param Pods_Object $object Object of items to reset.
	 *
	 * @return bool
	 */
	public function reset( Pods_Object $object ) {
		return false;
	}

}
