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
		$blocks = $this->get_blocks();

		// Pods Blocks API.
		$pods_blocks_options_file = file_get_contents( PODS_DIR . 'ui/js/blocks/pods-blocks-api.min.asset.json' );

		$pods_blocks_options = json_decode( $pods_blocks_options_file, true );

		wp_register_script( 'pods-blocks-api', PODS_URL . 'ui/js/blocks/pods-blocks-api.min.js', $pods_blocks_options['dependencies'], $pods_blocks_options['version'], true );

		wp_set_script_translations( 'pods-blocks-api', 'pods' );

		wp_localize_script( 'pods-blocks-api', 'podsBlocksConfig', [
			'blocks' => array_map( static function( $block ) {
				$js_block = $block;

				unset( $js_block['render_callback'] );

				return $js_block;
			}, $blocks ),
		] );

		foreach ( $blocks as $block ) {
			$block_name = $block['blockName'];

			unset( $block['blockName'], $block['fields'] );

			register_block_type( $block_name, $block );
		}
	}

	/**
	 * Setup core blocks.
	 *
	 * @since TBD
	 */
	public function setup_core_blocks() {
		tribe( 'pods.blocks.field' );
		tribe( 'pods.blocks.form' );
		tribe( 'pods.blocks.list' );
		tribe( 'pods.blocks.single' );
		tribe( 'pods.blocks.view' );

		do_action( 'pods_blocks_api_setup_core_blocks' );
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
}
