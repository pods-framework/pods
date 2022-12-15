<?php

/**
 * Initialize Gutenberg editor blocks
 *
 * @since 4.8
 */
class Tribe__Editor {

	/**
	 * Key we store the toggle under in the tribe_events_calendar_options array.
	 *
	 * @since 4.14.13
	 *
	 * @var string
	 */
	public static $blocks_editor_key = 'toggle_blocks_editor';

	/**
	 * Meta key for flagging if a post is from Classic Editor
	 *
	 * @since 4.8
	 *
	 * @var string
	 */
	public $key_flag_classic_editor = '_tribe_is_classic_editor';

	/**
	 * Utility function to check if we should load the blocks or not.
	 *
	 * @since 4.8
	 *
	 * @return bool
	 */
	public function should_load_blocks() {
		$should_load_blocks = (boolean) $this->are_blocks_enabled();

		/**
		 * Filters whether the Blocks Editor should be activated or not for events.
		 *
		 * @since 4.12.0
		 *
		 * @param bool $should_load_blocks Whether the blocks editor should be activated or not for events.
		 */
		$should_load_blocks= (bool) apply_filters( 'tribe_editor_should_load_blocks', $should_load_blocks );

		return $should_load_blocks;
	}

	/**
	 * Utility function to check if blocks are enabled based on two assumptions
	 *
	 * a) Is gutenberg active?
	 *     1) Via plugin or WP version
	 * b) Is the blocks editor active?
	 *      1) Based on the enqueue_block_assets action.
	 *
	 * @since 4.14.13
	 *
	 * @return bool
	 */
	public function are_blocks_enabled() {
		$gutenberg      = $this->is_gutenberg_active() || $this->is_wp_version();
		$blocks_enabled = $gutenberg && $this->is_blocks_editor_active();

		/**
		 * Filters whether the Blocks Editor is enabled or not.
		 *
		 * @since 4.14.13
		 *
		 * @param bool $should_load_blocks Whether the Blocks Editor is enabled or not.
		 */
		return (bool) apply_filters( 'tribe_editor_are_blocks_enabled', $blocks_enabled );
	}

	/**
	 * Checks if we are on version 5.0-alpha or higher where we no longer have
	 * Gutenberg Project, but the Blocks Editor
	 *
	 * @since 4.8
	 *
	 * @return boolean
	 */
	public function is_wp_version() {
		global $wp_version;

		return version_compare( $wp_version, '5.0-alpha', '>=' );
	}

	/**
	 * Checks if we have the Gutenberg Project plugin active.
	 *
	 * @since 4.8
	 *
	 * @return boolean
	 */
	private function is_gutenberg_active() {
		return function_exists( 'gutenberg_register_scripts_and_styles' );
	}

	/**
	 * Checks if we have Editor Block active.
	 *
	 * @since 4.8
	 * @since 4.14.13 Switch to using the `enqueue_block_assets` check that the Classic Editor plugin uses
	 *
	 * @return boolean
	 */
	public function is_blocks_editor_active() {
		return has_action( 'enqueue_block_assets' );
	}

	/**
	 * Adds the required fields into the Post Type so that we can the Rest API to update it
	 *
	 * @since 4.8
	 *
	 * @param  array $args Arguments used to setup the Post Type
	 *
	 * @return array
	 */
	public function add_rest_support( $args = [] ) {
		// Blocks Editor requires REST support
		$args['show_in_rest'] = true;

		// Make sure we have the Support argument and it's an array
		if ( ! isset( $args['supports'] ) || ! is_array( $args['supports'] ) ) {
			$args['supports'] = [];
		}

		if ( ! in_array( 'revisions', $args['supports'] ) ) {
			$args['supports'][] = 'revisions';
		}

		// Add Custom Fields (meta) Support
		if ( ! in_array( 'custom-fields', $args['supports'] ) ) {
			$args['supports'][] = 'custom-fields';
		}

		// Add Post Title Support
		if ( ! in_array( 'title', $args['supports'] ) ) {
			$args['supports'][] = 'title';
		}

		// Add Post Excerpt Support
		if ( ! in_array( 'excerpt', $args['supports'] ) ) {
			$args['supports'][] = 'excerpt';
		}

		// Add Post Content Support
		if ( ! in_array( 'editor', $args['supports'] ) ) {
			$args['supports'][] = 'editor';
		}

		// Add Post Author Support
		if ( ! in_array( 'author', $args['supports'] ) ) {
			$args['supports'][] = 'author';
		}

		// Add Thumbnail Support
		if ( ! in_array( 'thumbnail', $args['supports'] ) ) {
			$args['supports'][] = 'thumbnail';
		}

		return $args;
	}

	/**
	 * Detect if the Classic Editor is force-activated via plugin or if it comes from a request.
	 *
	 * @since 4.8
	 * @todo Deprecate before 6.0.
	 *
	 * @return bool
	 */
	public function is_classic_editor() {
		return ! $this->should_load_blocks();

		_deprecated_function( __FUNCTION__, '4.14.13', 'should_load_blocks' );
		/**
		 * Allow other addons to disable Classic Editor based on options.
		 *
		 * @since  4.8.5
		 * @deprecated 4.14.13
		 *
		 * @param bool $classic_is_active Whether the Classic Editor should be used.
		 */
		return apply_filters_deprecated(
			'tribe_editor_classic_is_active',
			[false],
			'4.14.13',
			'tribe_editor_should_load_blocks',
			'This has been deprecated in favor of the filter in should_load_blocks(). Note however that the logic is inverted!'
		);
	}

	/* DEPRECATED FUNCTIONS */

	/**
	 * Adds the required fields into the Events Post Type so that we can use Block Editor
	 *
	 * @since 4.8
	 * @deprecated 4.14.13 This is not used anywhere.
	 *
	 * @param  array $args Arguments used to setup the Post Type
	 *
	 * @return array
	 */
	public function add_support( $args = [] ) {
		_deprecated_function( __FUNCTION__, '4.14.13' );
		// Make sure we have the Support argument and it's an array
		if ( ! isset( $args['supports'] ) || ! is_array( $args['supports'] ) ) {
			$args['supports'] = [];
		}

		// Add Editor Support
		if ( ! in_array( 'editor', $args['supports'] ) ) {
			$args['supports'][] = 'editor';
		}

		return $args;
	}

	/**
	 * classic_editor_replace is function that is created by the plugin:
	 * used in ECP recurrence and TEC Meta
	 *
	 * @see https://wordpress.org/plugins/classic-editor/
	 *
	 * prior 1.3 version the Classic Editor plugin was bundled inside of a unique function:
	 * `classic_editor_replace` now all is bundled inside of a class `Classic_Editor`
	 *
	 * @since 4.8
	 * @deprecated 4.14.13
	 *
	 * @return bool
	 */
	public function is_classic_plugin_active() {
		_deprecated_function( __FUNCTION__, '4.14.13', 'Tribe\Editor\Compatibility\Classic_Editor::is_classic_plugin_active' );

		return Tribe\Editor\Compatibility\Classic_Editor::is_classic_plugin_active();
	}

	/**
	 * Check if the setting `classic-editor-replace` is set to `replace` that option means to
	 * replace the gutenberg editor with the Classic Editor.
	 *
	 * Prior to 1.3 on Classic Editor plugin the value to identify if is on classic the value
	 * was `replace`, now the value is `classic`
	 *
	 * @since 4.8
	 * @deprecated 4.14.13
	 *
	 * @return bool
	 */
	public function is_classic_option_active() {
		// _deprecated_function( __FUNCTION__, '4.14.13', 'Tribe\Editor\Compatibility\Classic_Editor::is_classic_option_active' );

		return Tribe\Editor\Compatibility\Classic_Editor::is_classic_option_active();
	}

	/**
	 * Whether the TEC setting dictates Blocks or the Classic Editor.
	 * used in ET, ET+ and TEC
	 *
	 * @since 4.12.0
	 * @todo Deprecate before 6.0.
	 *
	 * @return bool True if using Blocks. False if using the Classic Editor.
	 */
	public function is_events_using_blocks() {
		return $this->should_load_blocks();

		_deprecated_function( __FUNCTION__, '4.14.13', 'should_load_blocks');
		/**
		 * Whether the event is being served through blocks
		 * or the Classic Editor.
		 *
		 * @since 4.12.0
		 *
		 * @param bool $is_using_blocks True if using blocks. False if using the Classic Editor.
		 */
		$is_using_blocks = apply_filters_deprecated( 'tribe_is_using_blocks', null, '4.14.13', 'tribe_editor_should_load_blocks', 'Function is slated for deprecation. Please use should_load_blocks, above.' );

		// Early bail: The filter was overridden to return either true or false.
		if ( null !== $is_using_blocks ) {
			return (bool) $is_using_blocks;
		}

		// Early bail: The site itself is not using blocks.
		if ( ! $this->should_load_blocks() ) {
			return false;
		}

		return tribe_is_truthy( tribe_get_option( 'toggle_blocks_editor', false ) );
	}
}
