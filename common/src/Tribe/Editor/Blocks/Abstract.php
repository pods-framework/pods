<?php

abstract class Tribe__Editor__Blocks__Abstract
implements Tribe__Editor__Blocks__Interface {

	/**
	 * Namespace for Blocks from tribe
	 *
	 * @since 4.8
	 *
	 * @var string
	 */
	private $namespace = 'tribe';

	/**
	 * Builds the name of the Block
	 *
	 * @since 4.8
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
	 * @since 4.8
	 *
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/*
	 * Return the block attributes
	 *
	 * @since 4.8
	 *
	 * @param  array $attributes
	 *
	 * @return array
	*/
	public function attributes( $params = array() ) {

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
		 * @param array  $attributes    The attributes
		 * @param object $this      The current object
		 */
		$attributes = apply_filters( 'tribe_block_attributes_defaults_' . $this->slug(), $attributes, $this );

		return $attributes;
	}

	/*
	 * Return the block default attributes
	 *
	 * @since 4.8
	 *
	 * @param  array $attributes
	 *
	 * @return array
	*/
	public function default_attributes() {

		$attributes = array();

		/**
		 * Filters the default attributes
		 *
		 * @param array  $params    The attributes
		 * @param object $this      The current object
		 */
		$attributes = apply_filters( 'tribe_block_attributes_defaults', $attributes, $this );

		return $attributes;
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since 4.8
	 *
	 * @param  array $attributes
	 *
	 * @return string
	 */
	public function render( $attributes = array() ) {
		if ( version_compare( phpversion(), '5.4', '>=' ) ) {
			$json_string = json_encode( $attributes, JSON_PRETTY_PRINT );
		} else {
			$json_string = json_encode( $attributes );
		}

		return
		'<pre class="tribe-placeholder-text-' . $this->name() . '">' .
			'Block Name: ' . $this->name() . "\n" .
			'Block Attributes: ' . "\n" . $json_string .
		'</pre>';
	}

	/**
	 * Sends a valid JSON response to the AJAX request for the block contents
	 *
	 * @since 4.8
	 *
	 * @return void
	 */
	public function ajax() {
		wp_send_json_error( esc_attr__( 'Problem loading the block, please remove this block to restart.', 'tribe-common' ) );
	}

	/**
	 * Does the registration for PHP rendering for the Block, important due to been
	 * an dynamic Block
	 *
	 * @since 4.8
	 *
	 * @return void
	 */
	public function register() {
		$block_args = array(
			'render_callback' => array( $this, 'render' ),
		);

		register_block_type( $this->name(), $block_args );

		add_action( 'wp_ajax_' . $this->get_ajax_action(), array( $this, 'ajax' ) );

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
	 * @since 4.8
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
	 * correct `wp_ajax_{$action}` string for us to Hook
	 *
	 * @since 4.8
	 *
	 * @return string
	 */
	public function get_ajax_action() {
		return str_replace( 'tribe/', 'tribe_editor_block_', $this->name() );
	}

	/**
	 * Used to include any Assets for the Block we are registering
	 *
	 * @since 4.8
	 *
	 * @return void
	 */
	public function assets() {
	}

	/**
	 * Attach any particular hook for the specif block.
	 *
	 * @since 4.8
	 */
	public function hook() {
	}
}

