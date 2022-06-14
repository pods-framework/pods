<?php

namespace Pods\Blocks\Types;

use WP_Block;

/**
 * Item Single block functionality class.
 *
 * @since 2.8.0
 */
class Item_Single extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-single';
	}

	/**
	 * Get block configuration to register with Pods.
	 *
	 * @since 2.8.0
	 *
	 * @return array Block configuration.
	 */
	public function block() {
		return [
			'internal'        => true,
			'label'           => __( 'Pods Single Item', 'pods' ),
			'description'     => __( 'Display a single Pod item.', 'pods' ),
			'namespace'       => 'pods',
			'category'        => 'pods',
			'icon'            => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'single',
				'item',
				'field',
			],
			'uses_context'    => [
				'postType',
				'postId',
			],
			'transforms'      => [
				'from' => [
					[
						'type'       => 'shortcode',
						'tag'        => 'pods',
						'attributes' => [
							'name'  => [
								'type'      => 'object',
								'source'    => 'shortcode',
								'attribute' => 'name',
							],
							'slug'  => [
								'type'      => 'string',
								'source'    => 'shortcode',
								'attribute' => 'slug',
							],
							'template'  => [
								'type'      => 'object',
								'source'    => 'shortcode',
								'attribute' => 'template',
							],
							'template_custom'  => [
								// Pull this from content or the attribute.
								'type'      => 'content',
								'source'    => 'shortcode',
								'attribute' => 'template_custom',
							],
						],
						'isMatchConfig' => [
							[
								'name'     => 'field',
								'excluded' => true,
							],
							[
								'name'     => 'form',
								'excluded' => true,
							],
							[
								'name'     => 'view',
								'excluded' => true,
							],
							[
								'name' => 'content_before',
								'excluded' => true,
							],
							[
								'name' => 'content_after',
								'excluded' => true,
							],
							[
								'name' => 'not_found',
								'excluded' => true,
							],
							[
								'name' => 'limit',
								'excluded' => true,
							],
							[
								'name' => 'orderby',
								'excluded' => true,
							],
							[
								'name' => 'where',
								'excluded' => true,
							],
							[
								'name' => 'pagination',
								'excluded' => true,
							],
							[
								'name' => 'pagination_location',
								'excluded' => true,
							],
							[
								'name' => 'pagination_type',
								'excluded' => true,
							],
							[
								'name' => 'filters',
								'excluded' => true,
							],
							[
								'name' => 'filters_label',
								'excluded' => true,
							],
							[
								'name' => 'filters_location',
								'excluded' => true,
							],
							[
								'name' => 'cache_mode',
								'excluded' => true,
							],
							[
								'name' => 'expires',
								'excluded' => true,
							],
						],
					],
				],
			],
		];
	}

	/**
	 * Get list of Field configurations to register with Pods for the block.
	 *
	 * @since 2.8.0
	 *
	 * @return array List of Field configurations.
	 */
	public function fields() {
		$api = pods_api();

		$all_pods = $api->load_pods( [ 'names' => true ] );
		$all_pods = array_merge( [
			'' => '- ' . __( 'Use Current Pod', 'pods' ) . ' -',
		], $all_pods );

		$all_templates = $api->load_templates( [ 'names' => true ] );
		$all_templates = array_merge( [
			'' => '- ' . __( 'Use Custom Template', 'pods' ) . ' -',
		], $all_templates );

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
				'name'    => 'template',
				'label'   => __( 'Template', 'pods' ),
				'type'    => 'pick',
				'data'    => $all_templates,
				'default' => '',
				'description' => __( 'You can choose a previously saved Pods Template here. We recommend saving your Pods Templates with our Templates component so you can enjoy the full editing experience.', 'pods' ),
			],
			[
				'name'  => 'template_custom',
				'label' => __( 'Custom Template', 'pods' ),
				'type'  => 'paragraph',
				'description' => __( 'You can specify a custom template to use, it accepts HTML and magic tags. Any content here will override whatever Template you may have chosen above.', 'pods' ),
			],
		];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since 2.8.0
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

		if (
			empty( $attributes['template'] )
			&& empty( $attributes['template_custom'] )
		) {
			if ( wp_is_json_request() && did_action( 'rest_api_init' ) ) {
				return $this->render_placeholder(
					'<i class="pods-block-placeholder_error"></i>' . esc_html__( 'Pods Single Item', 'pods' ),
					esc_html__( 'Please specify a "Template" or "Custom Template" under "More Settings" to configure this block.', 'pods' )
				);
			}

			return '';
		}

		// Check whether we should preload the block.
		if ( $this->is_preloading_block() && ! $this->should_preload_block( $attributes, $block ) ) {
			return '';
		}

		// Use current if no pod name / slug provided.
		if ( empty( $attributes['name'] ) || empty( $attributes['slug'] ) ) {
			$attributes['use_current'] = true;
		} elseif ( ! isset( $attributes['use_current'] ) ) {
			$attributes['use_current'] = false;
		}

		if ( $attributes['use_current'] && $block instanceof WP_Block && ! empty( $block->context['postType'] ) ) {
			// Detect post type / ID from context.
			$attributes['name'] = $block->context['postType'];

			if ( ! empty( $block->context['postId'] ) ) {
				$attributes['slug'] = $block->context['postId'];

				unset( $attributes['use_current'] );
			}
		} elseif (
			! empty( $attributes['use_current'] )
			&& ! empty( $_GET['post_id'] )
			&& wp_is_json_request()
			&& did_action( 'rest_api_init' )
		) {
			$attributes['slug'] = absint( $_GET['post_id'] );

			if ( empty( $attributes['name'] ) ) {
				$attributes['name'] = get_post_type( $attributes['slug'] );
			}

			unset( $attributes['use_current'] );
		}

		return pods_shortcode( $attributes, $attributes['template_custom'] );
	}
}
