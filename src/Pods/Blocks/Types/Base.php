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
	 * Set the default attributes of this block.
	 *
	 * @since TBD
	 *
	 * @return array List of attributes.
	 */
	public function default_attributes() {
		$fields = $this->fields();

		$defaults = [];

		foreach ( $fields as $field ) {
			$defaults[ $field['name'] ] = $this->default_attribute( $field );
		}

		return $defaults;
	}

	/**
	 * Get the default attribute for a field.
	 *
	 * @since TBD
	 *
	 * @param array $field The field to get the default attribute for.
	 *
	 * @return mixed The default attribute for a field.
	 */
	public function default_attribute( $field ) {
		$default_value = isset( $field['default'] ) ? $field['default'] : '';

		if ( 'pick' === $field['type'] && isset( $field['data'] ) ) {
			foreach ( $field['data'] as $key => $value ) {
				if ( ! is_array( $value ) ) {
					$value = [
						'label' => $value,
						'value' => $key,
					];
				}

				if ( $default_value === $value['value'] ) {
					$default_value = $value;

					break;
				}
			}
		}

		return $default_value;
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

	/*
	 * {@inheritDoc}
	 *
	 * @since TBD
	*/
	public function attributes( $params = [] ) {
		// Convert any potential array values for pick/boolean.
		foreach ( $params as $param => $value ) {
			if ( is_array( $value ) ) {
				if ( isset( $value['label'], $value['value'] ) ) {
					$params[ $param ] = $value['value'];
				} elseif ( isset( $value[0]['label'], $value[0]['value'] ) ) {
					$params[ $param ] = array_values( wp_list_pluck( $value, 'value' ) );
				}
			}
		}

		return parent::attributes( $params );
	}
}
