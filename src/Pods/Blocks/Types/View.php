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
				return $this->render_placeholder(
					'<i class="pods-block-placeholder_error"></i>' . esc_html__( 'Pods View - Block Error', 'pods' ),
					esc_html__( 'This block is not configured properly, please specify a "View" to use.', 'pods' )
				);
			}

			return '';
		}

		// Prevent any previews of this block.
		if ( is_admin() || wp_is_json_request() ) {
			return $this->render_placeholder(
				esc_html__( 'View', 'pods' ),
				esc_html__( 'No preview is available for this Pods View, you will see it when you view or preview this on the front of your site.', 'pods' ),
				'<img src="' . esc_url( PODS_URL . 'ui/images/pods-view-placeholder.svg' ) . '" alt="' . esc_attr__( 'Generic placeholder image depicting a common view layout', 'pods' ) . '" class="pods-block-placeholder_image">'
			);
		}

		return pods_shortcode( $attributes );
	}
}
