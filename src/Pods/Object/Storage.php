<?php

/**
 * Pods__Object__Storage class.
 *
 * @since 2.8
 */
abstract class Pods__Object__Storage {

	/**
	 * @var string
	 */
	protected static $type = '';

	/**
	 * @var array
	 */
	protected $primary_args = array();

	/**
	 * @var array
	 */
	protected $secondary_args = array();

	/**
	 * Pods__Object__Storage constructor.
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
		return static::$type;
	}

	/**
	 * Get object from storage.
	 *
	 * @return Pods__Object|null
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
	 * @return Pods__Object[]
	 */
	public function find( array $args = array() ) {
		// @todo Find how?
		return array();
	}

	/**
	 * Add an object.
	 *
	 * @param Pods__Object $object Object to add.
	 *
	 * @return string|int|false Object name, object ID, or false if not added.
	 */
	public function add( Pods__Object $object ) {
		return false;
	}

	/**
	 * Save an object.
	 *
	 * @param Pods__Object $object Object to save.
	 *
	 * @return string|int|false Object name, object ID, or false if not saved.
	 */
	public function save( Pods__Object $object ) {
		return false;
	}

	/**
	 * Get object argument data.
	 *
	 * @param Pods__Object $object Object with arguments to save.
	 *
	 * @return array
	 */
	public function get_args( Pods__Object $object ) {
		return array();
	}

	/**
	 * Save object argument data.
	 *
	 * @param Pods__Object $object Object with arguments to save.
	 *
	 * @return bool
	 */
	public function save_args( Pods__Object $object ) {
		return false;
	}

	/**
	 * Duplicate an object.
	 *
	 * @param Pods__Object $object Object to duplicate.
	 *
	 * @return string|int|false Duplicated object name, duplicated object ID, or false if not duplicated.
	 */
	public function duplicate( Pods__Object $object ) {
		return false;
	}

	/**
	 * Delete an object.
	 *
	 * @param Pods__Object $object Object to delete.
	 *
	 * @return bool
	 */
	public function delete( Pods__Object $object ) {
		return false;
	}

	/**
	 * Reset an object's item data.
	 *
	 * @param Pods__Object $object Object of items to reset.
	 *
	 * @return bool
	 */
	public function reset( Pods__Object $object ) {
		return false;
	}

}
