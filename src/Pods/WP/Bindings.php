<?php

namespace Pods\WP;

use Pods\Blocks\Types\Field;
use WP_Block;

/**
 * Bindings specific functionality.
 *
 * @since 3.2.0
 */
class Bindings {

	/**
	 * Add the class hooks.
	 *
	 * @since 3.2.0
	 */
	public function hook() {
		$this->register_block_bindings();
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 3.2.0
	 */
	public function unhook() {
		$this->unregister_block_bindings();
	}

	/**
	 * Register the block bindings.
	 *
	 * @since 3.2.0
	 */
	public function register_block_bindings() {
		if ( ! function_exists( 'register_block_bindings_source' ) || ! pods_can_use_dynamic_feature( 'display' ) ) {
			return;
		}

		register_block_bindings_source( 'pods/bindings-field', [
			'label'              => __( 'Pods Field', 'pods' ),
			'get_value_callback' => [ $this, 'get_value' ],
			'uses_context'       => [ 'postId', 'postType' ],
		] );
	}

	/**
	 * Unregister the block bindings.
	 *
	 * @since 3.2.0
	 */
	public function unregister_block_bindings() {
		if ( ! function_exists( 'unregister_block_bindings_source' ) || ! pods_can_use_dynamic_feature( 'display' ) ) {
			return;
		}

		unregister_block_bindings_source( 'pods/bindings-field' );
	}

	/**
	 * Get the bound value for a bound block.
	 *
	 * @since 3.2.0
	 *
	 * @param array    $source_args    List of source arguments from the block.
	 * @param WP_Block $block_instance The block instance.
	 * @param string   $attribute_name The name of the block attribute.
	 *
	 * @return string The bound value.
	 */
	public function get_value( $source_args, $block_instance, $attribute_name ) {
		if ( empty( $source_args['field'] ) ) {
			if ( is_admin() || wp_is_rest_endpoint() || pods_is_admin() ) {
				return __( 'You must provide the "field" of the field to bind.', 'pods' );
			}

			return '';
		}

		/** @var Field $field_block */
		$field_block = pods_container( 'pods.blocks.field' );

		if ( ! $field_block ) {
			if ( is_admin() || wp_is_rest_endpoint() || pods_is_admin() ) {
				return __( 'Pods blocks are not enabled.', 'pods' );
			}

			return '';
		}

		$value = $field_block->render( $source_args, '', $block_instance );

		// Only support full HTML for the content attribute.
		if ( 'content' !== $attribute_name ) {
			$value = wp_strip_all_tags( $value );
		}

		return $value;
	}

}
