<?php

namespace Pods\Whatsit;

use Pods\Whatsit;

/**
 * Block Collection class.
 *
 * @since 2.8.0
 */
class Block_Collection extends Pod {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'block-collection';

	/**
	 * Get list of Block Collection API arguments to use.
	 *
	 * @since 2.8.0
	 *
	 * @return array List of Block Collection API arguments.
	 */
	public function get_block_collection_args() {
		$namespace = $this->get_arg( 'namespace', 'pods' );

		// Block collections are only allowed A-Z0-9- characters, no underscores.
		$namespace = str_replace( '_', '-', sanitize_title_with_dashes( $namespace ) );

		return [
			'namespace' => $namespace,
			'title'     => $this->get_arg( 'title', $this->get_arg( 'label' ) ),
			'icon'      => $this->get_arg( 'icon', '' ),
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_args() {
		$args = Whatsit::get_args();

		// Pods generally have no parent, group, or order.
		unset( $args['parent'], $args['group'] );

		return $args;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_fields( array $args = [] ) {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		return [];
	}
}
