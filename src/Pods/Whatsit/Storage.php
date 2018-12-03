<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Storage class.
 *
 * @since 2.8
 */
abstract class Storage {

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
	 * Storage constructor.
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
	 * @return Whatsit|null
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
	 * @return Whatsit[]
	 */
	public function find( array $args = array() ) {
		// @todo Find how?
		return array();
	}

	/**
	 * Add an object.
	 *
	 * @param Whatsit $object Object to add.
	 *
	 * @return string|int|false Object name, object ID, or false if not added.
	 */
	public function add( Whatsit $object ) {
		return false;
	}

	/**
	 * Save an object.
	 *
	 * @param Whatsit $object Object to save.
	 *
	 * @return string|int|false Object name, object ID, or false if not saved.
	 */
	public function save( Whatsit $object ) {
		return false;
	}

	/**
	 * Get object argument data.
	 *
	 * @param Whatsit $object Object with arguments to save.
	 *
	 * @return array
	 */
	public function get_args( Whatsit $object ) {
		return array();
	}

	/**
	 * Save object argument data.
	 *
	 * @param Whatsit $object Object with arguments to save.
	 *
	 * @return bool
	 */
	public function save_args( Whatsit $object ) {
		return false;
	}

	/**
	 * Duplicate an object.
	 *
	 * @param Whatsit $object Object to duplicate.
	 *
	 * @return string|int|false Duplicated object name, duplicated object ID, or false if not duplicated.
	 */
	public function duplicate( Whatsit $object ) {
		return false;
	}

	/**
	 * Delete an object.
	 *
	 * @param Whatsit $object Object to delete.
	 *
	 * @return bool
	 */
	public function delete( Whatsit $object ) {
		return false;
	}

	/**
	 * Reset an object's item data.
	 *
	 * @param Whatsit $object Object of items to reset.
	 *
	 * @return bool
	 */
	public function reset( Whatsit $object ) {
		return false;
	}

}
