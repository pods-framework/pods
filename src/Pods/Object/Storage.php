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
	 * Get object from storage.
	 *
	 * @return null
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
	 * @return array
	 */
	public function find( array $args = array() ) {
		// @todo Find how?
		return array();
	}

	/**
	 * Add an object.
	 *
	 * @return bool
	 */
	public function add() {
		return false;
	}

	/**
	 * Save an object.
	 *
	 * @return bool
	 */
	public function save() {
		return false;
	}

	/**
	 * Duplicate an object.
	 *
	 * @return bool
	 */
	public function duplicate() {
		return false;
	}

	/**
	 * Delete an object.
	 *
	 * @return bool
	 */
	public function delete() {
		return false;
	}

	/**
	 * Reset an object's item data.
	 *
	 * @return bool
	 */
	public function reset() {
		return false;
	}

}
