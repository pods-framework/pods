<?php

namespace Pods\Blocks;

use WP_Post;

/**
 * Blocks abstract.
 *
 * @credit The Events Calendar team - https://github.com/the-events-calendar/tribe-common
 *
 * @since 3.0
 */
abstract class Blocks_Abstract implements Blocks_Interface {

	/**
	 * Namespace for Blocks.
	 *
	 * @since 3.0
	 *
	 * @var string
	 */
	private $namespace = 'pods';

	/**
	 * Builds the name of the Block
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function name() {
		if ( false === strpos( $this->slug(), $this->namespace . '/' ) ) {
			return $this->namespace . '/' . $this->slug();
		} else {
			return $this->slug();
		}
	}

	/**
	 * Return the namespace to child or external sources
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Return the block attributes
	 *
	 * @since 3.0
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function attributes( $params = [] ) {

		// get the default attributes
		$default_attributes = $this->default_attributes();

		// parse the attributes with the default ones
		$attributes = wp_parse_args(
			$params,
			$default_attributes
		);

		/**
		 * Filters the default attributes for the block
		 *
		 * @since 3.0
		 *
		 * @param array $attributes The attributes
		 * @param object $this The current object
		 */
		$attributes = apply_filters( 'pods_block_attributes_defaults_' . $this->slug(), $attributes, $this );

		return $attributes;
	}

	/**
	 * Return the block default attributes
	 *
	 * @since 3.0
	 *
	 * @param array $attributes
	 *
	 * @return array
	 */
	public function default_attributes() {

		$attributes = [];

		/**
		 * Filters the default attributes
		 *
		 * @param array $params The attributes
		 * @param object $this The current object
		 */
		$attributes = apply_filters( 'pods_block_attributes_defaults', $attributes, $this );

		return $attributes;
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since 3.0
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function render( $attributes = [] ) {
		$json_string = json_encode( $attributes, JSON_PRETTY_PRINT );

		return
			'<pre class="pods-placeholder-text-' . $this->name() . '">' .
			'Block Name: ' . $this->name() . "\n" .
			'Block Attributes: ' . "\n" . $json_string .
			'</pre>';
	}

	/**
	 * Sends a valid JSON response to the AJAX request for the block contents
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function ajax() {
		wp_send_json_error( esc_attr__( 'Problem loading the block, please remove this block to restart.', 'pods' ) );
	}

	/**
	 * Does the registration for PHP rendering for the Block, important due to been
	 * an dynamic Block
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function register() {
		$block_args = [
			'render_callback' => [ $this, 'render' ],
		];

		register_block_type( $this->name(), $block_args );

		add_action( 'wp_ajax_' . $this->get_ajax_action(), [ $this, 'ajax' ] );

		$this->assets();
		$this->hook();
	}

	/**
	 * Determine whether a post or content string has this block.
	 *
	 * This test optimizes for performance rather than strict accuracy, detecting
	 * the pattern of a block but not validating its structure. For strict accuracy
	 * you should use the block parser on post content.
	 *
	 * @since 3.0
	 *
	 * @see gutenberg_parse_blocks()
	 *
	 * @param int|string|WP_Post|null $post Optional. Post content, post ID, or post object. Defaults to global $post.
	 *
	 * @return bool Whether the post has this block.
	 */
	public function has_block( $post = null ) {
		if ( ! is_numeric( $post ) ) {
			$wp_post = get_post( $post );
			if ( $wp_post instanceof WP_Post ) {
				$post = $wp_post->post_content;
			}
		}

		return false !== strpos( (string) $post, '<!-- wp:' . $this->name() );
	}

	/**
	 * Fetches the name for the block we are working with and converts it to the
	 * correct `wp_ajax_{$action}` string for us to Hook.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_ajax_action() {
		return str_replace( 'pods/', 'pods_editor_block_', $this->name() );
	}

	/**
	 * Used to include any Assets for the Block we are registering.
	 *
	 * @since 3.0
	 *
	 * @return void
	 */
	public function assets() {
	}

	/**
	 * Attach any particular hook for the specif block.
	 *
	 * @since 3.0
	 */
	public function hook() {
	}

	/**
	 * Returns the block data for the block editor.
	 *
	 * @since 3.0
	 *
	 * @return array<string,mixed> The block editor data.
	 */
	public function block_data() {
		$block_data = [
			'id' => $this->slug(),
		];

		/**
		 * Filters the block data.
		 *
		 * @since 3.0
		 *
		 * @param array $block_data The block data.
		 * @param object $this The current object.
		 */
		$block_data = apply_filters( 'pods_block_block_data', $block_data, $this );

		/**
		 * Filters the block data for the block.
		 *
		 * @since 3.0
		 *
		 * @param array $block_data The block data.
		 * @param object $this The current object.
		 */
		$block_data = apply_filters( 'pods_block_block_data_' . $this->slug(), $block_data, $this );

		return $block_data;
	}
}
