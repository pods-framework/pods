<?php

namespace Pods\Blocks\Types;

/**
 * Form block functionality class.
 *
 * @since 2.8
 */
class Form extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-form';
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
			'label'           => __( 'Pods Form', 'pods' ),
			'description'     => __( 'Display a form for creating and editing Pod items.', 'pods' ),
			'namespace'       => 'pods',
			'category'        => 'pods',
			'icon'            => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'form',
				'input',
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
	 * @since TBD
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
			],
			[
				'name'        => 'slug',
				'label'       => __( 'Slug or ID (optional)', 'pods' ),
				'type'        => 'text',
				'description' => __( 'Use this to enable editing of an item.', 'pods' ),
			],
			[
				'name'  => 'fields',
				'label' => __( 'Field Names (comma-separated) (optional)', 'pods' ),
				'type'  => 'paragraph',
			],
			[
				'name'  => 'label',
				'label' => __( 'Submit Button Label (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'thank_you',
				'label' => __( 'Redirect URL (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'    => 'form_output_type',
				'label'   => __( 'Output Type', 'pods' ),
				'type'    => 'pick',
				'data'    => [
					'div'   => 'Div containers (<div>)',
					'ul'    => 'Unordered list (<ul>)',
					'p'     => 'Paragraph elements (<p>)',
					'table' => 'Table rows (<table>)',
				],
				'default' => 'div',
			],
		];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since TBD
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

		// Prevent any previews of this block.
		if ( is_admin() || wp_is_json_request() ) {
			return $this->render_placeholder(
				esc_html__( 'Form', 'pods' ),
				esc_html__( 'No preview is available for this Pods Form, you will see it when you view or preview this on the front of your site.', 'pods' ),
				'<img src="' . esc_url( PODS_URL . 'ui/images/pods-form-placeholder.svg' ) . '" alt="' . esc_attr__( 'Generic placeholder image depicting a common form layout', 'pods' ) . '" class="pods-block-placeholder_image">'
			);
		}

		// Detect post type / ID from context.
		if ( empty( $attributes['name'] ) && $block instanceof WP_Block && ! empty( $block->context['postType'] ) ) {
			$attributes['name'] = $block->context['postType'];

			if ( isset( $attributes['slug'] ) && '{@post.ID}' === $attributes['slug'] && ! empty( $block->context['postId'] ) ) {
				$attributes['slug'] = $block->context['postId'];
			}
		}

		return pods_shortcode_form( $attributes );
	}
}
