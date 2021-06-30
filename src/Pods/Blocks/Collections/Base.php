<?php

namespace Pods\Blocks\Collections;

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
		$collection = $this->block_collection();

		if ( empty( $collection ) ) {
			return;
		}

		$collection['name'] = $this->slug();

		pods_register_block_collection( $collection );
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
