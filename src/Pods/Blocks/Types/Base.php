<?php

namespace Pods\Blocks\Types;

use Pods\Whatsit\Store;
use Tribe__Editor__Blocks__Abstract;

/**
 * Field block functionality class.
 *
 * @since 2.8
 */
abstract class Base extends Tribe__Editor__Blocks__Abstract {

	/**
	 * Set the default attributes of this block
	 *
	 * @since TBD
	 *
	 * @return array List of attributes.
	 */
	public function default_attributes() {
		$fields = $this->fields();

		$defaults = [];

		foreach ( $fields as $field ) {
			$defaults[ $field['name'] ] = isset( $field['default'] ) ? $field['default'] : '';
		}

		return $defaults;
	}

	/**
	 * Get list of Field configurations to register with Pods for the block.
	 *
	 * @since TBD
	 *
	 * @return array List of Field configurations.
	 */
	public function fields() {
		return [];
	}

	/**
	 * Register the block with Pods.
	 *
	 * @since TBD
	 */
	public function register_with_pods() {
		$block = $this->block();

		if ( empty( $block ) ) {
			return;
		}

		$block['object_type']  = 'block';
		$block['storage_type'] = 'collection';
		$block['name']         = $this->slug();

		$this->assets();
		$this->hook();

		$object_collection = Store::get_instance();
		$object_collection->register_object( $block );

		$fields = $this->fields();

		foreach ( $fields as $field ) {
			$field['object_type']  = 'block-field';
			$field['storage_type'] = 'collection';
			$field['parent']       = 'block/' . $block['name'];

			$object_collection->register_object( $field );
		}
	}

	/**
	 * Get block configuration to register with Pods.
	 *
	 * @since TBD
	 *
	 * @return array Block configuration.
	 */
	public function block() {
		return [];
	}
}
