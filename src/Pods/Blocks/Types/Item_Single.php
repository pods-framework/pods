<?php

namespace Pods\Blocks\Types;

/**
 * Item_Single block functionality class.
 *
 * @since 2.8
 */
class Item_Single extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-single';
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
			'label'           => __( 'Pods Single Item', 'pods' ),
			'description'     => __( 'Display a single Pod item.', 'pods' ),
			'namespace'       => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'single',
				'item',
				'field',
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
				'label' => __( 'Slug / ID (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'template',
				'label' => __( 'Template (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'use_current',
				'label' => __( 'Use Current', 'pods' ),
				'type'  => 'boolean',
			],
			[
				'name'  => 'template_custom',
				'label' => __( 'Custom Template', 'pods' ),
				'type'  => 'paragraph',
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

		if (
			(
				(
					empty( $args['name'] )
					&& empty( $args['slug'] )
				)
				|| empty( $args['use_current'] )
			)
			&& empty( $attributes['template'] )
			&& empty( $attributes['template_custom'] )
		) {
			if ( is_admin() || wp_is_json_request() ) {
				return __( 'No preview available, please fill in more Block details.', 'pods' );
			}

			return '';
		}

		return pods_shortcode( $attributes, $attributes['template_custom'] );
	}
}
