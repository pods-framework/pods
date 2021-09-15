<?php

namespace Pods\Blocks\Types;

/**
 * Item_Single block functionality class.
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
			],
			[
				'name'        => 'slug',
				'label'       => __( 'Slug or ID (optional)', 'pods' ),
				'type'        => 'text',
				'description' => __( 'Defaults to using the current pod item.', 'pods' ),
			],
			[
				'name'    => 'template',
				'label'   => __( 'Template (optional)', 'pods' ),
				'type'    => 'pick',
				'data'    => $all_templates,
				'default' => '',
			],
			[
				'name'  => 'template_custom',
				'label' => __( 'Custom Template (optional)', 'pods' ),
				'type'  => 'paragraph',
			],
		];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
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
		$attributes = array_map( 'trim', $attributes );

		if (
			empty( $attributes['template'] )
			&& empty( $attributes['template_custom'] )
		) {
			if ( is_admin() || wp_is_json_request() ) {
				return $this->render_placeholder(
					'<i class="pods-block-placeholder_error"></i>' . esc_html__( 'Pods Single Item', 'pods' ),
					esc_html__( 'Please specify a "Template" or "Custom Template" under "More Settings" to configure this block.', 'pods' )
				);
			}

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
			&& (
				is_admin()
				|| wp_is_json_request()
			)
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
