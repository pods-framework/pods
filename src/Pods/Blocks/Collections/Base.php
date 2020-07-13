<?php

namespace Pods\Blocks\Collections;

use Pods\Whatsit\Store;

/**
 * Block Collection functionality class.
 *
 * @since 2.8
 */
abstract class Base {

	/**
	 * Register the block collection with Pods.
	 *
	 * @since TBD
	 */
	public function register_with_pods() {
		$block_collection = $this->block_collection();

		if ( empty( $block_collection ) ) {
			return;
		}

		$block_collection['object_type']  = 'block-collection';
		$block_collection['storage_type'] = 'collection';
		$block_collection['name']         = $this->slug();

		$object_collection = Store::get_instance();
		$object_collection->register_object( $block_collection );
	}

	/**
	 * Get the name/slug of this block collection.
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return '';
	}

	/**
	 * Get block collection configuration to register with Pods.
	 *
	 * @since TBD
	 *
	 * @return array Block collection configuration.
	 */
	public function block_collection() {
		return [];
	}
}
