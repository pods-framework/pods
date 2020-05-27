<?php

namespace Pods\Blocks\Types;

/**
 * Item_List block functionality class.
 *
 * @since 2.8
 */
class Item_List extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-list';
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
			'label'           => __( 'Pods Item List', 'pods' ),
			'description'     => __( 'List multiple Pod items.', 'pods' ),
			'namespace'       => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'item',
				'list',
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
				'name'  => 'template',
				'label' => __( 'Template (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'    => 'limit',
				'label'   => __( 'Limit', 'pods' ),
				'type'    => 'number',
				'default' => 15,
			],
			[
				'name'  => 'orderby',
				'label' => __( 'Order By (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'where',
				'label' => __( 'Where (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'    => 'expires',
				'label'   => __( 'Expires (optional)', 'pods' ),
				'type'    => 'number',
				'default' => ( MINUTE_IN_SECONDS * 5 ),
			],
			[
				'name'    => 'cache_mode',
				'label'   => __( 'Cache Mode (optional)', 'pods' ),
				'type'    => 'text',
				'default' => 'none',
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

		if ( empty( $attributes['name'] ) || ( empty( $attributes['template'] ) && empty( $attributes['template_custom'] ) ) ) {
			if ( is_admin() || wp_is_json_request() ) {
				return __( 'No preview available, please fill in more Block details.', 'pods' );
			}

			return '';
		}

		return pods_shortcode( $attributes, $attributes['template_custom'] );
	}
}
