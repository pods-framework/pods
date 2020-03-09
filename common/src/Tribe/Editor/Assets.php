<?php
/**
 * Events Gutenberg Assets
 *
 * @since 4.8
 */
class Tribe__Editor__Assets {
	/**
	 *
	 * @since 4.8
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'tribe_plugins_loaded', array( $this, 'register' ) );
	}

	/**
	 * Registers and Enqueues the assets
	 *
	 * @since 4.8
	 *
	 * @param string $key Which key we are checking against
	 *
	 * @return boolean
	 */
	public function register() {

		$plugin = Tribe__Main::instance();

		tribe_asset(
			$plugin,
			'tribe-common-gutenberg-data',
			'app/data.js',
			/**
			 * @todo revise this dependencies
			 */
			array( 'react', 'react-dom', 'wp-components', 'wp-api', 'wp-api-request', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize' => array(
					array(
						'name' => 'tribe_editor_config',
						/**
						 * Array used to setup the FE with custom variables from the BE
						 *
						 * @since 4.8
						 *
						 * @param array An array with the variables to be localized
						 */
						'data' => tribe_callback( 'common.editor.configuration', 'localize' ),
					),
				),
				'priority'  => 11,
			)
		);

		tribe_asset(
			$plugin,
			'tribe-common-gutenberg-utils',
			'app/utils.js',
			/**
			 * @todo revise this dependencies
			 */
			array( 'react', 'react-dom', 'wp-components', 'wp-api', 'wp-api-request', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'priority'  => 12,
			)
		);
		tribe_asset(
			$plugin,
			'tribe-common-gutenberg-store',
			'app/store.js',
			/**
			 * @todo revise this dependencies
			 */
			array( 'react', 'react-dom', 'wp-components', 'wp-api', 'wp-api-request', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'priority'  => 13,
			)
		);
		tribe_asset(
			$plugin,
			'tribe-common-gutenberg-icons',
			'app/icons.js',
			/**
			 * @todo revise this dependencies
			 */
			array( 'react', 'react-dom', 'wp-components', 'wp-api', 'wp-api-request', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'priority'  => 14,
			)
		);
		tribe_asset(
			$plugin,
			'tribe-common-gutenberg-hoc',
			'app/hoc.js',
			/**
			 * @todo revise this dependencies
			 */
			array(
				'react',
				'react-dom',
				'wp-components',
				'wp-api',
				'wp-api-request',
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-editor',
			),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'priority'  => 15,
			)
		);
		tribe_asset(
			$plugin,
			'tribe-common-gutenberg-components',
			'app/components.js',
			/**
			 * @todo revise this dependencies
			 */
			array( 'react', 'react-dom', 'wp-components', 'wp-api', 'wp-api-request', 'wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor' ),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'priority'  => 16,
			)
		);
		tribe_asset(
			$plugin,
			'tribe-common-gutenberg-elements',
			'app/elements.js',
			/**
			 * @todo revise this dependencies
			 */
			array(
				'react',
				'react-dom',
				'wp-components',
				'wp-api',
				'wp-api-request',
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-editor',
			),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'priority'  => 17,
			)
		);
		/**
		 * @todo: figure out why element styles are loading for tickets but not events.
		 */
		tribe_asset(
			$plugin,
			'tribe-common-gutenberg-components',
			'app/components.js',
			/**
			 * @todo revise this dependencies
			 */
			array(
				'react',
				'react-dom',
				'wp-components',
				'wp-api',
				'wp-api-request',
				'wp-blocks',
				'wp-i18n',
				'wp-element',
				'wp-editor',
			),
			'enqueue_block_editor_assets',
			array(
				'in_footer' => false,
				'localize'  => array(),
				'priority'  => 17,
			)
		);
		tribe_asset(
			$plugin,
			'tribe-common-gutenberg-elements-styles',
			'app/elements.css',
			array(),
			'enqueue_block_editor_assets',
			array(
				'in_footer'    => false,
			)
		);
	}
}
