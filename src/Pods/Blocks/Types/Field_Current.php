<?php

namespace Pods\Blocks\Types;

/**
 * Field_Current block functionality class.
 *
 * @since 2.8
 */
class Field_Current extends Base {

	/**
	 * {@inheritDoc}
	 *
	 * @since TBD
	 */
	public function slug() {
		return 'pods-block-field-current';
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since TBD
	 */
	public function block() {
		return [
			'internal'        => true,
			'label'           => __( 'Pods Field Value for Current Object', 'pods' ),
			'description'     => __( 'Display the current pod item\'s field value. This requires the current post to be setup as a Pod.', 'pods' ),
			'namespace'       => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'current',
				'field',
				'value',
			],
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since TBD
	 */
	public function fields() {
		return [
			[
				'name'  => 'field',
				'label' => __( 'Field name', 'pods' ),
				'type'  => 'text',
			],
		];
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since TBD
	 */
	public function render( $attributes = [] ) {
		$attributes = $this->attributes( $attributes );
		$attributes = array_map( 'trim', $attributes );

		if ( empty( $attributes['field'] ) ) {
			if ( is_admin() || wp_is_json_request() ) {
				return __( 'No preview available, please specify "Field name".', 'pods' );
			}

			return '';
		}

		return pods_shortcode( $attributes );
	}

	/**
	 * {@inheritDoc}
	 *
	 * @since TBD
	 */
	public function hook() {
		// allowed_block_types doesn't have everything we need to exclude this block when we getting registered blocks.
		//add_filter( 'allowed_block_types', [ $this, 'filter_allowed_block_types_for_post' ], 10, 2 );
	}

	/**
	 * Filters the allowed block types for the editor, defaulting to true (all
	 * block types supported).
	 *
	 * @since TBD
	 *
	 * @param bool|array $allowed_block_types Array of block type slugs, or
	 *                                        boolean to enable/disable all.
	 * @param WP_Post    $post                The post resource data.
	 *
	 * @return bool|array Array of block type slugs, or boolean to enable/disable all.
	 */
	public function filter_allowed_block_types_for_post( $allowed_block_types, $post ) {
		try {
			$pod = pods_api()->load_pod( [ 'name' => $post->post_type ] );
		} catch ( \Exception $exception ) {
			$pod = null;
		}

		if ( $pod && 'post_type' === $pod['type'] ) {
			//return $allowed_block_types;
		}

		if ( ! is_array( $allowed_block_types ) ) {
			// Get list of all blocks.
			$block_types = \WP_Block_Type_Registry::get_instance()->get_all_registered();

			$allowed_block_types = array_values( wp_list_pluck( $block_types, 'name' ) );
		}

		echo '<pre>';
		var_dump( $pod, $block_types, $allowed_block_types );

		if ( isset( $allowed_block_types[ $this->slug() ] ) ) {
			unset( $allowed_block_types[ $this->slug() ] );
		}
		var_dump( $allowed_block_types );
		echo '</pre>';
		die();

		return $allowed_block_types;
	}
}
