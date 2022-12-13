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
		return 'pods-block-single-list-fields';
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
		return [
			[
				'name'    => 'name',
				'label'   => __( 'Pod Name', 'pods' ),
				'type'    => 'pick',
				'data'    => [ $this, 'callback_get_all_pods' ],
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
				'name'        => 'display_output_type',
				'label'       => __( 'Output Type', 'pods' ),
				'type'        => 'pick',
				'data'        => [
					'ul'    => __( 'Unordered list', 'pods' ) . ' (<ul>)',
					'dl'    => __( 'Description list', 'pods' ) . ' (<dl>)',
					'p'     => __( 'Paragraph elements', 'pods' ) . ' (<p>)',
					'div'   => __( 'Div containers', 'pods' ) . ' (<div>)',
					'table' => __( 'Table rows', 'pods' ) . ' (<table>)',
				],
				'default'     => 'ul',
				'description' => __( 'Choose how you want your output HTML to be set up. This allows you flexibility to build and style your output with any CSS customizations you would like. Some output types are naturally laid out better in certain themes.', 'pods' ),
			],
			[
				'name'        => 'display_fields',
				'label'       => __( 'Display Fields', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'Comma-separated list of the Pod Fields you want to display. Default is to show all. Use this OR the Exclude Fields option.', 'pods' ),
			],
			[
				'name'        => 'exclude_fields',
				'label'       => __( 'Exclude Fields', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'Comma-separated list of the Pod Fields you want to exclude from display. Default is to show all. Use this OR the Display Fields option.', 'pods' ),
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

		$magic_tag_data = [
			'_all_fields',
			$attributes['display_output_type'],
		];

		if ( ! empty( $attributes['display_fields'] ) ) {
			$magic_tag_data[0] = '_display_fields';

			$display_fields = $this->prepare_formatted_fields_by_pipe( $attributes['display_fields'] );

			if ( '' !== $display_fields ) {
				$magic_tag_data[] = $display_fields;
			}
		} elseif ( ! empty( $attributes['exclude_fields'] ) ) {
			$magic_tag_data[0] = '_display_fields';

			$exclude_fields = $this->prepare_formatted_fields_by_pipe( $attributes['exclude_fields'] );

			if ( '' !== $exclude_fields ) {
				$magic_tag_data[] = 'exclude=' . $exclude_fields;
			}
		}

		$attributes['template_custom'] = '{@' . implode( '.', $magic_tag_data ) . '}';

		return parent::render( $attributes, $content, $block );
	}

	/**
	 * Prepare the list of formatted fields separated by pipe.
	 *
	 * @param string $fields The list of fields.
	 *
	 * @return string The list of formatted fields separated by pipe.
	 */
	private function prepare_formatted_fields_by_pipe( $fields ) {
		$fields = str_replace( '.', ':', $fields );
		$fields = preg_replace( '/[^a-zA-Z0-9\:\_\-]/', '|', $fields );
		$fields = preg_replace( '/[\s\,\|]+/', '|', $fields );
		$fields = explode( '|', $fields );
		$fields = array_unique( array_filter( $fields ) );

		return implode( '|', $fields );
	}
}
