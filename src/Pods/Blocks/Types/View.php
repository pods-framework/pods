<?php

namespace Pods\Blocks\Types;

/**
 * View block functionality class.
 *
 * @since 2.8.0
 */
class View extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-view';
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
			'transforms'      => [
				'from' => [
					[
						'type'       => 'shortcode',
						'tag'        => 'pods',
						'attributes' => [
							'view'  => [
								'type'      => 'string',
								'source'    => 'shortcode',
								'attribute' => 'view',
							],
						],
						'isMatchConfig' => [
							[
								'name'     => 'view',
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
		 * @since 2.8.0
		 *
		 * @param string $default_cache_mode Default cache mode.
		 */
		$default_cache_mode = apply_filters( 'pods_shortcode_default_cache_mode', 'none' );

		return [
			[
				'name'  => 'view',
				'label' => __( 'File to include from theme', 'pods' ),
				'type'  => 'text',
				'description' => __( 'This is the file location relative to your theme or child theme folder. For example: my-text.php or parts/ad-spot.php', 'pods' ),
			],
			[
				'name'    => 'cache_mode',
				'label'   => __( 'Cache Mode', 'pods' ),
				'type'    => 'pick',
				'data'    => $cache_modes,
				'default' => $default_cache_mode,
				'description' => __( 'The mode to cache the output with.', 'pods' ),
			],
			[
				'name'    => 'expires',
				'label'   => __( 'Expires', 'pods' ),
				'type'    => 'number',
				'default' => ( MINUTE_IN_SECONDS * 5 ),
				'description' => __( 'Set how long to cache the output for in seconds.', 'pods' ),
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
		$attributes = array_map( 'pods_trim', $attributes );

		if ( empty( $attributes['view'] ) ) {
			if ( $this->in_editor_mode( $attributes ) ) {
				return $this->render_placeholder(
					'<i class="pods-block-placeholder_error"></i>' . esc_html__( 'Pods View', 'pods' ),
					esc_html__( 'Please specify a "View" under "More Settings" to configure this block.', 'pods' )
				);
			}

			return '';
		}

		// Prevent any previews of this block.
		if ( $this->in_editor_mode( $attributes ) ) {
			return $this->render_placeholder(
				esc_html__( 'View', 'pods' ),
				esc_html__( 'No preview is available for this Pods View, you will see it when you view or preview this on the front of your site.', 'pods' ),
				'<img src="' . esc_url( PODS_URL . 'ui/images/pods-view-placeholder.svg' ) . '" alt="' . esc_attr__( 'Generic placeholder image depicting a common view layout', 'pods' ) . '" class="pods-block-placeholder_image">'
			);
		}

		// Check whether we should preload the block.
		if ( $this->is_preloading_block() && ! $this->should_preload_block( $attributes, $block ) ) {
			return '';
		}

		return pods_shortcode( $attributes );
	}
}
