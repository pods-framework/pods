<?php

namespace Pods\Blocks;

use Pods\Whatsit\Block;

/**
 * Blocks functionality class.
 *
 * @since 2.8
 */
class API {

	/**
	 * Register blocks for the Pods Blocks API.
	 *
	 * @since TBD
	 */
	public function register_blocks() {
		static $registered = false;

		if ( $registered ) {
			return;
		}

		$blocks = $this->get_blocks();

		// Pods Blocks API.
		$pods_blocks_options_file = file_get_contents( PODS_DIR . 'ui/js/blocks/pods-blocks-api.min.asset.json' );

		$pods_blocks_options = json_decode( $pods_blocks_options_file, true );

		wp_register_script( 'pods-blocks-api', PODS_URL . 'ui/js/blocks/pods-blocks-api.min.js', $pods_blocks_options['dependencies'], $pods_blocks_options['version'], true );

		wp_set_script_translations( 'pods-blocks-api', 'pods' );

		wp_localize_script( 'pods-blocks-api', 'podsBlocksConfig', [
			'blocks'      => array_map( static function ( $block ) {
				$js_block = $block;

				unset( $js_block['render_callback'] );

				return $js_block;
			}, $blocks ),
			// No custom collections to register directly with JS right now.
			'collections' => [],
		] );

		add_filter( 'block_categories', [ $this, 'register_block_collections' ] );

		foreach ( $blocks as $block ) {
			$block_name = $block['blockName'];

			unset( $block['blockName'], $block['fields'] );

			register_block_type( $block_name, $block );
		}

		$registered = true;
	}

	/**
	 * Setup core blocks.
	 *
	 * @since TBD
	 */
	public function setup_core_blocks() {
		static $setup = false;

		if ( $setup ) {
			return;
		}

		/**
		 * Allow any integrations to be set up before core blocks and collections are called.
		 *
		 * @since TBD
		 */
		do_action( 'pods_blocks_api_pre_init' );

		tribe( 'pods.blocks.collection.pods' );
		tribe( 'pods.blocks.field' );
		tribe( 'pods.blocks.form' );
		tribe( 'pods.blocks.list' );
		tribe( 'pods.blocks.single' );
		tribe( 'pods.blocks.view' );

		/**
		 * Allow custom blocks to be registered with Pods.
		 *
		 * @since TBD
		 */
		do_action( 'pods_blocks_api_init' );

		$setup = true;
	}

	/**
	 * Get list of registered blocks for the Pods Blocks API.
	 *
	 * @since TBD
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

		/** @var Block[] $blocks */
		$blocks = $api->_load_objects( [
			'object_type' => 'block',
		] );

		// Ensure the response is an array.
		$blocks = array_values( $blocks );

		$blocks = array_map( static function ( $block ) {
			return $block->get_block_args();
		}, $blocks );

		return $blocks;
	}

	/**
	 * Get list of registered block collections for the Pods Blocks API.
	 *
	 * @since TBD
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
	 * @since 2.8
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
}
