<?php

/**
 * Pods_Object_Field class.
 *
 * @since 2.8
 */
class Pods_Object_Field extends Pods_Object {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'field';

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		return array();

		// @todo Handle logic to get table info for related object.
		if ( array() === $this->_table_info ) {
			return array();
		}

		$api = pods_api();

		$table_info = $api->get_table_info( $this->get_type(), $this->get_name() );

		if ( ! $table_info ) {
			$table_info = array();
		}

		$this->_table_info = $table_info;

		return $table_info;
	}

}
