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
				'label'   => __( 'Pod name', 'pods' ),
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

		// Prevent any previews of this block.
		if ( is_admin() || wp_is_json_request() ) {
			ob_start();
			?>
				<div class="pods-block-placeholder_container">
					<div class="pods-block-placeholder_content-container">
						<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-green.svg" alt="Pods logo" class="pods-logo">
						<div class="pods-block-placeholder_content">
							<h2 class="pods-block-placeholder_title">Form</h2>
							<p><?php echo __( 'No preview is available for this Pods Form, you will see it on the frontend.', 'pods' ); ?></p>
						</div>
					</div>
					<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-form-placeholder.svg" alt="Generic placeholder image depicting a common form layout" class="pods-block-placeholder_image">
				</div>
			<?
			return ob_get_clean();
		}

		return pods_shortcode_form( $attributes );
	}
}
