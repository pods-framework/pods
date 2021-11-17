<?php

namespace Pods\Blocks\Collections;

/**
 * Block collection functionality class.
 *
 * @since 2.8.0
 */
class Pods extends Base {

	/**
	 * Get the name/slug of this block collection.
	 *
	 * @since 2.8.0
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods';
	}

	/**
	 * Get block collection configuration to register with Pods.
	 *
	 * @since 2.8.0
	 *
	 * @return array Block collection configuration.
	 */
	public function block_collection() {
		return [
			'internal'  => true,
			'label'     => __( 'Pods Blocks', 'pods' ),
			'namespace' => $this->slug(),
			'icon'      => 'pods',
		];
	}
}
