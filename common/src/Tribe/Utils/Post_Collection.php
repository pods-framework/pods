<?php
/**
 * An extension of the base collection implementation to handle posts.
 *
 * @since 4.9.5
 */

/**
 * Class Tribe__Utils__Post_Collection
 *
 * @since 4.9.5
 */
class Tribe__Utils__Post_Collection extends Tribe__Utils__Collection {

	/**
	 * Tribe__Utils__Post_Collection constructor.
	 *
	 * Overrides the base constructor to ensure all elements in the collection are, in fact, posts.
	 * Elements that do not resolve to a post are discarded.
	 *
	 * @param array $items
	 */
	public function __construct( array $items ) {
		parent::__construct( array_filter( array_map( 'get_post', $items ) ) );
	}

	/**
	 * Plucks a meta key for all elements in the collection.
	 *
	 * Elements that are not posts or do not have the meta set will have an
	 * empty string value.
	 *
	 * @since 4.9.5
	 *
	 * @param string $meta_key The meta key to pluck.
	 * @param bool   $single   Whether to fetch the meta key as single or not.
	 *
	 * @return array An array of meta values for each item in the collection; items that
	 *               do not have the meta set or that are not posts, will have an empty
	 *               string value.
	 */
	public function pluck_meta( $meta_key, $single = true ) {
		$plucked = array();

		foreach ( $this as $item ) {
			$plucked[] = get_post_meta( $item->ID, $meta_key, $single );
		}

		return $plucked;
	}
}