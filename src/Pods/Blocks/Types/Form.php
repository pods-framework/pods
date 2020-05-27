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
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'form',
				'input',
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
				'label' => __( 'Slug or ID (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'fields',
				'label' => __( 'Field names (comma-separated) (optional)', 'pods' ),
				'type'  => 'paragraph',
			],
			[
				'name'  => 'label',
				'label' => __( 'Submit button label (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'thank_you',
				'label' => __( 'Redirect URL (optional)', 'pods' ),
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

		if ( empty( $attributes['name'] ) ) {
			if ( is_admin() || wp_is_json_request() ) {
				return __( 'No preview available, please specify "Pod name".', 'pods' );
			}

			return '';
		}

		// Prevent any previews of this block.
		if ( is_admin() || wp_is_json_request() ) {
			return __( 'No preview is available for this Pods Form, you will see it on the frontend.', 'pods' );
		}

		return pods_shortcode_form( $attributes );
	}
}
