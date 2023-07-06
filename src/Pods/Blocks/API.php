<?php

namespace Pods\Blocks;

use Pods;
use Pods\Pod_Manager;
use Pods\Whatsit\Block;

/**
 * Blocks functionality class.
 *
 * @since 2.8.0
 */
class API {

	/**
	 * Register blocks for the Pods Blocks API.
	 *
	 * @since 2.8.0
	 */
	public function register_blocks() {
		static $registered = false;

		if ( $registered ) {
			return;
		}

		// The 'block_categories' filter has been deprecated in WordPress 5.8+ and replaced by 'block_categories_all'.
		if ( pods_version_check( 'wp', '5.8-beta0' ) ) {
			add_filter( 'block_categories_all', [ $this, 'register_block_collections' ] );
		} else {
			add_filter( 'block_categories', [ $this, 'register_block_collections' ] );
		}

		$blocks = $this->get_blocks();

		foreach ( $blocks as $block ) {
			$block_name = $block['blockName'];

			unset( $block['blockName'], $block['fields'] );

			register_block_type( $block_name, $block );
		}

		add_action( 'admin_enqueue_scripts', [ $this, 'register_assets' ], 15 );

		$registered = true;
	}

	/**
	 * @return void
	 */
	public function register_assets() {
		$js_blocks = $this->get_js_blocks();

		// The Pods Blocks JS API.
		$pods_blocks_options_file = file_get_contents( PODS_DIR . 'ui/js/blocks/pods-blocks-api.min.asset.json' );

		$pods_blocks_options = null;

		if ( $pods_blocks_options_file ) {
			$pods_blocks_options = json_decode( $pods_blocks_options_file, true );
		}

		if ( ! is_array( $pods_blocks_options ) ) {
			$pods_blocks_options = [
				'dependencies' => [],
				'version'      => false,
			];
		}

		wp_register_script( 'pods-blocks-api', PODS_URL . 'ui/js/blocks/pods-blocks-api.min.js', $pods_blocks_options['dependencies'], $pods_blocks_options['version'], true );

		wp_set_script_translations( 'pods-blocks-api', 'pods' );

		$blocks_config = [
			'blocks'      => $js_blocks,
			'commands'    => [],
			// No custom collections to register directly with JS right now.
			'collections' => [],
		];

		$is_admin = is_admin();
		$screen   = ( $is_admin && function_exists( 'get_current_screen' ) ) ? get_current_screen() : null;

		// Maybe add commands if the person has the right access.
		if ( $screen && 'post' === $screen->base && $screen->post_type && pods_is_admin( 'pods' ) ) {
			// Check if this is a Pod or not.
			$api = pods_api();

			$pod = false;

			try {
				$pod = $api->load_pod( [
					'name'       => $screen->post_type,
					'auto_setup' => false,
				] );

				// Check if this was auto-setup before and isn't a full pod.
				if ( $pod && true === $pod->get_arg( 'adhoc' ) ) {
					$pod = false;
				}
			} catch ( \Exception $exception ) {
				// Nothing to do here.
			}

			if ( $pod ) {
				$blocks_config['commands'][] = [
					'name'         => 'pods/edit',
					'label'        => __( 'Edit this Pod configuration', 'pods' ),
					'searchLabel'  => __( 'Edit this Pod configuration > Manage Field Groups, Custom Fields, and other Custom Post Type settings in the Pods Admin', 'pods' ),
					'icon'         => 'pods',
					'callbackUrl'  => admin_url(
						sprintf(
							'admin.php?page=pods&action=edit&id=%d',
							$pod->get_id()
						)
					),
				];
			} else {
				$nonce = wp_create_nonce( 'pods_extend_post_type_' . $screen->post_type );

				$blocks_config['commands'][] = [
					'name'         => 'pods/extend',
					'label'        => __( 'Extend this Post Type with Pods to add custom fields', 'pods' ),
					'icon'         => 'pods',
					'callbackUrl'  => admin_url(
						sprintf(
							'admin.php?page=pods-add-new&pods_extend_post_type=%1$s&pods_extend_post_type_nonce=%2$s',
							$screen->post_type,
							$nonce
						)
					),
				];
			}
		}

		/**
		 * Allow filtering the blocks API config data.
		 *
		 * @since 3.0.0
		 *
		 * @param array $blocks_config The blocks API config data.
		 */
		$blocks_config = (array) apply_filters( 'pods_blocks_api_config', $blocks_config );

		wp_localize_script( 'pods-blocks-api', 'podsBlocksConfig', $blocks_config );
	}

	/**
	 * Setup core blocks.
	 *
	 * @since 2.8.0
	 */
	public function setup_core_blocks() {
		static $setup = false;

		if ( $setup ) {
			return;
		}

		/**
		 * Allow any integrations to be set up before core blocks and collections are called.
		 *
		 * @since 2.8.0
		 */
		do_action( 'pods_blocks_api_pre_init' );

		pods_container( 'pods.blocks.collection.pods' );
		pods_container( 'pods.blocks.field' );
		pods_container( 'pods.blocks.form' );
		pods_container( 'pods.blocks.list' );
		pods_container( 'pods.blocks.single' );
		pods_container( 'pods.blocks.single-list-fields' );
		pods_container( 'pods.blocks.view' );

		/**
		 * Allow custom blocks to be registered with Pods.
		 *
		 * @since 2.8.0
		 */
		do_action( 'pods_blocks_api_init' );

		$setup = true;
	}

	/**
	 * Get list of registered blocks for the Pods Blocks API.
	 *
	 * @since 2.8.0
	 *
	 * @return array List of registered blocks.
	 */
	public function get_blocks() {
		static $blocks = [];

		if ( ! empty( $blocks ) ) {
			return $blocks;
		}

		$this->setup_core_blocks();

		$api = pods_api();

		/**
		 * Allow filtering whether to bypass the post type find queries for blocks.
		 *
		 * @since 2.9.14
		 *
		 * @param bool $bypass_post_type_find Whether to bypass the post type find queries for blocks.
		 */
		$bypass_post_type_find = apply_filters( 'pods_blocks_api_get_blocks_bypass_post_type_find', true );

		/** @var Block[] $blocks */
		$blocks = $api->_load_objects( [
			'object_type' => 'block',
			'bypass_cache' => true,
			// Disable DB queries for now.
			'bypass_post_type_find' => $bypass_post_type_find,
		] );

		// Ensure the response is an array.
		$blocks = array_values( $blocks );

		$blocks = array_map( static function ( $block ) {
			return $block->get_block_args();
		}, $blocks );

		return $blocks;
	}

	/**
	 * Get list of registered blocks for the Pods Blocks API and prepare them for JavaScript registerBlockType().
	 *
	 * @since 2.8.0
	 *
	 * @return array List of registered blocks prepared for JavaScript registerBlockType().
	 */
	public function get_js_blocks() {
		static $js_blocks = [];

		if ( ! empty( $js_blocks ) ) {
			return $js_blocks;
		}

		$cached = pods_transient_get( 'pods_blocks_js' );

		if ( is_array( $cached ) ) {
			return $cached;
		}

		$blocks = $this->get_blocks();

		foreach ( $blocks as $block_key => $block ) {
			$js_block = [];

			// Remove render options.
			unset( $block['render_callback'], $block['render_custom_callback'], $block['render_template'], $block['render_template_path'] );

			// Remove assets options.
			unset( $block['enqueue_assets'], $block['enqueue_script'], $block['enqueue_style'] );

			foreach ( $block as $key => $value ) {
				// Prepare the keys as camelCase.
				$key = pods_js_camelcase_name( $key );

				// Skip if the value is null.
				if ( null === $value ) {
					continue;
				}

				$js_block[ $key ] = $value;
			}

			if ( ! isset( $js_block['usesContext'] ) ) {
				$js_block['usesContext'] = [];
			}

			$js_blocks[ $block_key ] = $js_block;
		}

		pods_transient_set( 'pods_blocks_js', $js_blocks, DAY_IN_SECONDS * 7 );

		return $js_blocks;
	}

	/**
	 * Get list of registered block collections for the Pods Blocks API.
	 *
	 * @since 2.8.0
	 *
	 * @return array List of registered block collections.
	 */
	public function get_block_collections() {
		static $collections = [];

		if ( ! empty( $collections ) ) {
			return $collections;
		}

		$this->setup_core_blocks();

		$api = pods_api();

		/** @var Block_Collection[] $block_collections */
		$block_collections = $api->_load_objects( [
			'object_type' => 'block-collection',
		] );

		// Ensure the response is an array.
		$block_collections = array_values( $block_collections );

		$block_collections = array_map( static function ( $block_collection ) {
			return $block_collection->get_block_collection_args();
		}, $block_collections );

		return $block_collections;
	}

	/**
	 * Register block collections by adding them to the list of 'categories'.
	 *
	 * @since 2.8.0
	 *
	 * @param array $collections List of block 'categories' from WordPress.
	 *
	 * @return array List of block 'categories' with custom block collections added.
	 */
	public function register_block_collections( array $collections ) {
		$block_collections = $this->get_block_collections();

		if ( empty( $block_collections ) ) {
			return $collections;
		}

		foreach ( $block_collections as $collection ) {
			$collections[] = [
				'slug'  => $collection['namespace'],
				'title' => $collection['title'],
				'icon'  => $collection['icon'],
			];
		}

		return $collections;
	}

	/**
	 * Remove our legacy Pods widgets from the Legacy Widget block.
	 *
	 * @since 2.8.0
	 *
	 * @param array $widgets An array of excluded widget-type IDs.
	 *
	 * @return array An array of excluded widget-type IDs.
	 */
	public function remove_from_legacy_widgets( $widgets ) {
		$widgets[] = 'pods_widget_field';
		$widgets[] = 'pods_widget_form';
		$widgets[] = 'pods_widget_list';
		$widgets[] = 'pods_widget_single';
		$widgets[] = 'pods_widget_view';

		return $widgets;
	}
}
