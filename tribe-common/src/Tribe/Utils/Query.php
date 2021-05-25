<?php
/**
 * Provides utility method related to the creation and manipulation of queries and query objects.
 *
 * @since   4.9.21
 *
 * @package Tribe\Utils
 */

namespace Tribe\Utils;

/**
 * Class Query
 *
 * @since   4.9.21
 *
 * @package Tribe\Utils
 */
class Query {

	/**
	 * Builds a new `WP_Query` object and sets the post, and accessory flags, on it.
	 *
	 * The query is built to yield to run a query that will yield no result and to have a `request` property that
	 * will never yield results; calls on the `WP_Query::get_posts` method are filtered to always return the post set.
	 * Queries built by this function can be spotted by looking for the `tribe_mock_query` property.
	 *
	 * @since 4.9.21
	 *
	 * @param array $posts The array of posts that should be used to build the query.
	 *
	 * @return \WP_Query The new WP_Query object, built to reflect the posts passed to it.
	 */
	public static function for_posts( array $posts = [] ) {
		if ( empty( $posts ) ) {
			$posts = [];
		}

		$query                   = new \WP_Query();
		$query->posts            = $posts;
		$query->found_posts      = count( $posts );
		$query->post             = reset( $posts );
		$query->query            = [ 'p' => 0 ];
		$query->tribe_mock_query = true;
		global $wpdb;
		// Use a query that will never yield results.
		$query->request = "SELECT ID FROM {$wpdb->posts} WHERE 1=0";

		// Return the same set of posts on each method requiring posts.
		$filter_posts_pre_query = static function ( $the_posts, $the_query ) use ( $posts, $query ) {
			if ( $the_query !== $query ) {
				return $the_posts;
			}

			$fields = $query->get( 'fields', false );
			// We assume some uniformity here.
			$posts_are_objects = ! is_numeric( reset( $posts ) );

			switch ( $fields ) {
				case 'ids':
					return $posts_are_objects ? wp_list_pluck( $posts, 'ID' ) : $posts;
				case 'id=>parent':
				default:
					return $posts_are_objects ? $posts : array_map( 'get_post', $posts );
			}
		};

		add_filter( 'posts_pre_query', $filter_posts_pre_query, 10, 2 );

		return $query;
	}
}
