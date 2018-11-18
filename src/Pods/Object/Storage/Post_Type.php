<?php

/**
 * Pods_Object_Storage_Post_Type class.
 *
 * @since 2.8
 */
class Pods_Object_Storage_Post_Type extends Pods_Object_Storage {

	/**
	 * {@inheritdoc}
	 */
	protected $type = 'post_type';

	/**
	 * {@inheritdoc}
	 */
	public function get() {
		// @todo Get how?
		return null;
	}

	/**
	 * {@inheritdoc}
	 */
	public function find( array $args = array() ) {
		// @todo Find how?
		return array();
	}

	/**
	 * {@inheritdoc}
	 */
	public function add() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function duplicate() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete() {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function reset() {
		return false;
	}

}
