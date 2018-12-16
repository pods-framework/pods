<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Field class.
 *
 * @since 2.8
 */
class Field extends Whatsit {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'field';

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		if ( array() === $this->_table_info ) {
			return array();
		}

		$type = $this->get_type();

		$simple_tableless_objects = \PodsForm::simple_tableless_objects();

		$related_type = $this->get_arg( $type . '_object' );

		if ( empty( $related_type ) || in_array( $related_type, $simple_tableless_objects, true ) ) {
			return array();
		}

		$related_name = $this->get_arg( $type . '_val' );

		if ( '__current__' === $related_name ) {
			$related_name = $this->get_name();
		}

		$api = pods_api();

		$table_info = $api->get_table_info( $related_type, $related_name );

		if ( ! $table_info ) {
			$table_info = array();
		}

		$this->_table_info = $table_info;

		return $table_info;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_arg( $arg, $default = null ) {
		if ( 'pod' === $arg ) {
			return $this->get_parent_name();
		}

		return parent::get_arg( $arg, $default );
	}

}
