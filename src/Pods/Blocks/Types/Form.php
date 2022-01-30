<?php

namespace Pods\Blocks\Types;

use WP_Block;

/**
 * Form block functionality class.
 *
 * @since 2.8.0
 */
class Form extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-form';
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
			'transforms'      => [
				'from' => [
					[
						'type'       => 'shortcode',
						'tag'        => 'pods-form',
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
							'fields' => [
								'type'      => 'string',
								'source'    => 'shortcode',
								'attribute' => 'fields',
							],
							'label'  => [
								'type'      => 'string',
								'source'    => 'shortcode',
								'attribute' => 'label',
							],
							'thank_you' => [
								'type'      => 'string',
								'source'    => 'shortcode',
								'attribute' => 'thank_you',
							],
							'form_output_type'  => [
								'type'      => 'object',
								'source'    => 'shortcode',
								'attribute' => 'form_output_type',
							],
						],
						'isMatchConfig' => [
							[
								'name'     => 'name',
								'required' => false,
							],
						],
					],
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
							'fields' => [
								'type'      => 'string',
								'source'    => 'shortcode',
								'attribute' => 'fields',
							],
							'label'  => [
								'type'      => 'string',
								'source'    => 'shortcode',
								'attribute' => 'label',
							],
							'thank_you' => [
								'type'      => 'string',
								'source'    => 'shortcode',
								'attribute' => 'thank_you',
							],
							'form_output_type'  => [
								'type'      => 'object',
								'source'    => 'shortcode',
								'attribute' => 'form_output_type',
							],
						],
						'isMatchConfig' => [
							[
								'name'     => 'form',
								'required' => true,
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

		return [
			[
				'name'        => 'name',
				'label'       => __( 'Pod Name', 'pods' ),
				'type'        => 'pick',
				'data'        => $all_pods,
				'default'     => '',
				'description' => __( 'Choose the pod to reference, or reference the Pod in the current context of this block.', 'pods' ),
			],
			[
				'name'        => 'slug',
				'label'       => __( 'Slug or ID', 'pods' ),
				'type'        => 'text',
				'description' => __( 'Use this to enable editing of an item.', 'pods' ),
			],
			[
				'name'        => 'fields',
				'label'       => __( 'Form Fields', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'Comma-separated list of the Pod Fields you want to include. Default is to show all.', 'pods' ),
			],
			[
				'name'        => 'label',
				'label'       => __( 'Submit Button Label', 'pods' ),
				'type'        => 'text',
				'description' => __( 'The label to show in the submit button of the form.', 'pods' ),
			],
			[
				'name'        => 'thank_you',
				'label'       => __( 'Redirect URL', 'pods' ),
				'type'        => 'text',
				'description' => __( 'After someone submits the form, they can be redirected anywhere you would like. You can reference the saved item ID by using "X_ID_X" in the URL. The default is to not redirect.', 'pods' ),
			],
			[
				'name'        => 'form_output_type',
				'label'       => __( 'Output Type', 'pods' ),
				'type'        => 'pick',
				'data'        => [
					'div'   => 'Div containers (<div>)',
					'ul'    => 'Unordered list (<ul>)',
					'p'     => 'Paragraph elements (<p>)',
					'table' => 'Table rows (<table>)',
				],
				'default'     => 'div',
				'description' => __( 'Choose how you want your form HTML to be set up. This allows you flexibility to build and style your forms with any CSS customizations you would like. Some output types are naturally laid out better in certain themes.', 'pods' ),
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

		// Prevent any previews of this block.
		if ( wp_is_json_request() && did_action( 'rest_api_init' ) ) {
			return $this->render_placeholder(
				esc_html__( 'Form', 'pods' ),
				esc_html__( 'No preview is available for this Pods Form, you will see it when you view or preview this on the front of your site.', 'pods' ),
				'<img src="' . esc_url( PODS_URL . 'ui/images/pods-form-placeholder.svg' ) . '" alt="' . esc_attr__( 'Generic placeholder image depicting a common form layout', 'pods' ) . '" class="pods-block-placeholder_image">'
			);
		}

		// Check whether we should preload the block.
		if ( $this->is_preloading_block() && ! $this->should_preload_block( $attributes, $block ) ) {
			return '';
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
