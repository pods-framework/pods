<?php

namespace Pods\Whatsit;

use Pods\Whatsit;
use PodsForm;

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
		if ( null !== $this->_table_info ) {
			return $this->_table_info;
		}

		$related_type = $this->get_related_object_type();
		$related_name = $this->get_related_object_name();

		if ( null === $related_type || null === $related_name ) {
			return array();
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

	/**
	 * Get related object type from field.
	 *
	 * @return string|null The related object type, or null if not found.
	 */
	public function get_related_object_type() {
		$type = $this->get_type();

		$simple_tableless_objects = \PodsForm::simple_tableless_objects();

		$related_type = $this->get_arg( $type . '_object', $this->get_arg( 'pick_object' ) );

		if ( '__current__' === $related_type ) {
			$related_type = $this->get_object_type();
		}

		if ( empty( $related_type ) && 'avatar' === $type ) {
			$related_type = 'media';
		}

		if ( empty( $related_type ) || \in_array( $related_type, $simple_tableless_objects, true ) ) {
			return null;
		}

		return $related_type;
	}

	/**
	 * Get related object name from field.
	 *
	 * @return string|null The related object name, or null if not found.
	 */
	public function get_related_object_name() {
		$type = $this->get_type();

		$related_type = $this->get_related_object_type();

		if ( null === $related_type ) {
			return null;
		}

		$related_name = $this->get_arg( $type . '_val', $this->get_arg( 'pick_val', $related_type ) );

		if ( '__current__' === $related_name ) {
			$related_name = $this->get_name();
		}

		if ( 'table' === $related_type ) {
			$related_name = $this->get_arg( 'related_table', $related_name );
		} elseif ( \in_array( $related_type, array( 'user', 'media', 'comment' ), true ) ) {
			$related_name = $related_type;
		}

		return $related_name;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_fields() {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_groups() {
		return [];
	}

}
