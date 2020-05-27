<?php

namespace Pods\Blocks\Types;

/**
 * Field block functionality class.
 *
 * @since 2.8
 */
class Field extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-field';
	}

	/**
	 * Get block configuration to register with Pods.
	 *
	 * @since TBD
	 *
	 * @return array Block configuration.
	 */
	public function block() {
		return [
			'internal'        => true,
			'label'           => __( 'Pods Field Value', 'pods' ),
			'description'     => __( 'Display a single Pod item\'s field value.', 'pods' ),
			'namespace'       => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'field',
				'value',
			],
		];
	}

	/**
	 * Get list of Field configurations to register with Pods for the block.
	 *
	 * @since TBD
	 *
	 * @return array List of Field configurations.
	 */
	public function fields() {
		//$all_pods = pods_api()->load_pods( [ 'names' => true ] );

		return [
			[
				'name'  => 'name',
				'label' => __( 'Pod name', 'pods' ),
				'type'  => 'text',
				//'type'     => 'pick',
				//'pick_val' => $all_pods,
			],
			[
				'name'  => 'slug',
				'label' => __( 'Slug or ID', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'field',
				'label' => __( 'Field name', 'pods' ),
				'type'  => 'text',
			],
		];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since TBD
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function render( $attributes = [] ) {
		$attributes = $this->attributes( $attributes );
		$attributes = array_map( 'trim', $attributes );

		if ( empty( $attributes['name'] ) || empty( $attributes['slug'] ) || empty( $attributes['field'] ) ) {
			if ( is_admin() || wp_is_json_request() ) {
				return __( 'No preview available, please fill in more Block details.', 'pods' );
			}

			return '';
		}

		return pods_shortcode( $attributes );
	}
}
