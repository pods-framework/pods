<?php

namespace Pods\REST\Interfaces;

/**
 * Post Repository interface.
 *
 * @credit The Events Calendar team - https://github.com/the-events-calendar/tribe-common
 *
 * @since 2.8.0
 */
interface Post_Repository_Interface {
	/**
	 * Retrieves an array representation of the post.
	 *
	 * @since 3.0
	 *
	 * @param int $id The post ID.
	 *
	 * @return array An array representation of the post.
	 */
	public function get_data( $id );
}
