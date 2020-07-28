<?php

namespace Pods\Blocks\Types;

/**
 * View block functionality class.
 *
 * @since 2.8
 */
class View extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-view';
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
			'label'           => __( 'Pods View', 'pods' ),
			'description'     => __( 'Include a file from a theme, with caching options', 'pods' ),
			'namespace'       => 'pods',
			'category'        => 'pods',
			'icon'            => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'view',
				'include',
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
		$cache_modes = [
			[
				'label' => 'Disable Caching',
				'value' => 'none',
			],
			[
				'label' => 'Object Cache',
				'value' => 'cache',
			],
			[
				'label' => 'Transient',
				'value' => 'transient',
			],
			[
				'label' => 'Site Transient',
				'value' => 'site-transient',
			],
		];

		/**
		 * Allow filtering of the default cache mode used for the Pods shortcode.
		 *
		 * @since TBD
		 *
		 * @param string $default_cache_mode Default cache mode.
		 */
		$default_cache_mode = apply_filters( 'pods_shortcode_default_cache_mode', 'none' );

		return [
			[
				'name'  => 'view',
				'label' => __( 'File to include from theme', 'pods' ),
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
				'type'    => 'pick',
				'data'    => $cache_modes,
				'default' => $default_cache_mode,
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

		if ( empty( $attributes['view'] ) ) {
			if ( is_admin() || wp_is_json_request() ) {
				return __( 'No preview available, please specify "View".', 'pods' );
			}

			return '';
		}

		// Prevent any previews of this block.
		if ( is_admin() || wp_is_json_request() ) {
			ob_start();
			?>
				<div class="pods-block-placeholder_container">
					<div class="pods-block-placeholder_content-container">
						<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-logo-green.svg" alt="Pods logo" class="pods-logo">
						<div class="pods-block-placeholder_content">
							<h2 class="pods-block-placeholder_title">View</h2>
							<p><?php echo __( 'No preview is available for this Pods Form, you will see it on the frontend.', 'pods' ); ?></p>
						</div>
					</div>
					<img src="<?php echo esc_url( PODS_URL ); ?>ui/images/pods-view-placeholder.svg" alt="Generic placeholder image depicting a common form layout" class="pods-block-placeholder_image">
				</div>
			<?
			return ob_get_clean();
		}

		return pods_shortcode( $attributes );
	}
}
