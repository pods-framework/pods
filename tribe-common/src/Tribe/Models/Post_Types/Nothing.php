<?php
/**
 * Models a non existing post.
 *
 * The reason for this class existence is to allow method chaining to happen without errors and to return a consistent
 * model type from methods.
 *
 * @since   4.9.18
 *
 * @package Tribe\Models\Post_Types
 */

namespace Tribe\Models\Post_Types;

/**
 * Class Nothing
 *
 * @since   4.9.18
 *
 * @package Tribe\Models\Post_Types
 */
class Nothing extends Base {

	/**
	 * {@inheritDoc}
	 */
	protected function get_cache_slug() {
		return '';
	}

	/**
	 * {@inheritDoc}
	 */
	protected function build_properties( $filter ) {
		return [];
	}

	/**
	 * {@inheritDoc}
	 */
	public function to_post( $output = OBJECT, $filter = 'raw' ) {
		return null;
	}
}