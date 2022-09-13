<?php

namespace Pods\Blocks\Types;

use WP_Block;

/**
 * Item Single List Fields block functionality class.
 *
 * @since 2.9.4
 */
class Item_Single_List_Fields extends Item_Single {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 2.9.4
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-single-all-fields';
	}

	/**
	 * Get block configuration to register with Pods.
	 *
	 * @since 2.9.4
	 *
	 * @return array Block configuration.
	 */
	public function block() {
		return [
			'internal'        => true,
			'label'           => __( 'Pods Single Item - List Fields', 'pods' ),
			'description'     => __( 'Display fields for a single Pod item.', 'pods' ),
			'namespace'       => 'pods',
			'category'        => 'pods',
			'icon'            => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'single',
				'item',
				'list',
				'fields',
			],
			'uses_context'    => [
				'postType',
				'postId',
			],
		];
	}

	/**
	 * Get list of Field configurations to register with Pods for the block.
	 *
	 * @since 2.9.4
	 *
	 * @return array List of Field configurations.
	 */
	public function fields() {
		$api = pods_api();

		$all_pods = $api->load_pods( [ 'names' => true ] );
		$all_pods = array_merge( [
			'' => '- ' . __( 'Use Current Pod', 'pods' ) . ' -',
		], $all_pods );

		return [
			[
				'name'    => 'name',
				'label'   => __( 'Pod Name', 'pods' ),
				'type'    => 'pick',
				'data'    => $all_pods,
				'default' => '',
				'description' => __( 'Choose the pod to reference, or reference the Pod in the current context of this block.', 'pods' ),
			],
			[
				'name'        => 'slug',
				'label'       => __( 'Slug or ID', 'pods' ),
				'type'        => 'text',
				'description' => __( 'Defaults to using the current pod item.', 'pods' ),
			],
			[
				'name'        => 'display_fields',
				'label'       => __( 'Display Fields', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'Comma-separated list of the Pod Fields you want to display. Default is to show all.', 'pods' ),
			],
			[
				'name'        => 'display_include_title',
				'label'       => __( 'Include Title Field', 'pods' ),
				'type'        => 'boolean',
				'description' => __( 'Whether to include the Title field (default off) when showing all fields.', 'pods' ),
			],
			[
				'name'        => 'display_output_type',
				'label'       => __( 'Output Type', 'pods' ),
				'type'        => 'pick',
				'data'        => [
					'ul'    => 'Unordered list (<ul>)',
					'dl'    => 'Description list (<dl>)',
					'p'     => 'Paragraph elements (<p>)',
					'div'   => 'Div containers (<div>)',
					'table' => 'Table rows (<table>)',
				],
				'default'     => 'ul',
				'description' => __( 'Choose how you want your output HTML to be set up. This allows you flexibility to build and style your output with any CSS customizations you would like. Some output types are naturally laid out better in certain themes.', 'pods' ),
			],
		];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since 2.9.4
	 *
	 * @param array         $attributes The block attributes.
	 * @param string        $content    The block default content.
	 * @param WP_Block|null $block      The block instance.
	 *
	 * @return string The block content to render.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		$attributes = $this->attributes( $attributes );
		$attributes = array_map( 'pods_trim', $attributes );

		if ( empty( $attributes['display_output_type'] ) ) {
			$attributes['display_output_type'] = 'ul';
		}

		if ( empty( $attributes['display_include_title'] ) ) {
			$attributes['display_include_title'] = 'no_index';
		} else {
			$attributes['display_include_title'] = 'include_index';
		}

		$_all_fields = [
			$attributes['display_output_type'],
			$attributes['display_include_title'],
		];

		if ( ! empty( $attributes['display_fields'] ) ) {
			$display_fields = $attributes['display_fields'];

			$display_fields = preg_replace( '/[^a-z0-9_\-]/', '|', $display_fields );
			$display_fields = preg_replace( '/[\s\,\|]+/', '|', $display_fields );
			$display_fields = explode( '|', $display_fields );
			$display_fields = array_unique( array_filter( $display_fields ) );
			$display_fields = implode( '|', $display_fields );

			if ( '' !== $display_fields ) {
				$_all_fields[] = $display_fields;
			}
		}

		$attributes['template_custom'] = '{@_display_fields.' . implode( '.', $_all_fields ) . '}';

		return parent::render( $attributes, $content, $block );
	}
}
