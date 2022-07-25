<?php

use Tribe\Repository\Core_Read_Interface;

/**
 * Interface Tribe__Repository__Read_Interface
 *
 *
 * @since 4.7.19
 */
interface Tribe__Repository__Read_Interface extends Tribe__Repository__Setter_Interface, Core_Read_Interface {
	/**
	 * Sets the permission that should be used to get the posts.
	 *
	 * Mind that this implementation does not support a `by( 'perm', 'editable' )`
	 * filter to force more readable code.
	 *
	 * @param string $permission One of the two `self::PERMISSION` constants.
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function permission( $permission );

	/**
	 * Fetches a single instance of the post type handled by the repository by
	 * the primary key.
	 *
	 * By default the primary key is the post ID.
	 *
	 * @param mixed $primary_key
	 *
	 * @return WP_Post|null|mixed
	 */
	public function by_primary_key( $primary_key );

	/**
	 * Returns the Read repository built WP_Query object.
	 *
	 * @since 4.7.19
	 *
	 * @return WP_Query
	 */
	public function get_query();

	/**
	 * Whether the current READ query will apply a specific `by` (or `where`)
	 * filter or not.
	 *
	 * @since 4.7.19
	 *
	 * @param string $key
	 * @param null $value If provided an ulterior check will be made to see if
	 *                    the value of the filter that is being applied matches
	 *                    the specified one (w/ loose comparison).
	 *
	 * @return bool Whether the current query setup has the specified filter applied
	 *              or not.
	 */
	public function has_filter( $key, $value = null );

	/**
	 * What filter the current READ query is currently applying in a specific `by` (or `where`).
	 *
	 * @since 4.9.5
	 *
	 * @return string|null The current filter being applied.
	 */
	public function get_current_filter();

	/**
	 * Deletes a set of events fetched by using filters.

	 *
	 * @since 4.9.5
	 *
	 *
	 * @param bool $return_promise Whether to return the promise or just the deleted post IDs
	 *                             if the deletion happens in a background process; defaults
	 *                             to `false`.
	 *
	 * @return int[]|Tribe__Promise An array of deleted post IDs, or that will be deleted in asynchronous
	 *                              mode or a promise object if `$return_promise` is set to `true`. The
	 *                              promise object will immediately execute its resolved or rejected callback
	 *                              if in synchronous mode.
	 */
	public function delete(
		$return_promise = false );

	/**
	 * Executes the delete operation in asynchronous mode.
	 *
	 * This method will override any filtering that might deactivate or disable asynchronous
	 * deletion processes. The recommended way to delete events is by using the `delete` method
	 * and letting the filtering conditions take over.
	 *
	 * @since 4.9.5
	 *
	 * @param array $to_delete      The post IDs to delete.
	 * @param bool  $return_promise Whether to return the `Tribe__Promise` object created to
	 *                              handle the background deletion or not.
	 *
	 * @return array|Tribe__Promise The promise object created to handle the background deletion
	 *                              or the array of post IDs that will be, eventually, deleted.
	 */
	public function async_delete( array $to_delete, $return_promise = true );

	/**
	 * Executes the update operation in asynchronous mode.
	 *
	 * This method will override any filtering that might deactivate or disable asynchronous
	 * update processes. The recommended way to update events is by using the `update` method
	 * and letting the filtering conditions take over.
	 *
	 * @since 4.9.5
	 *
	 * @param array $to_update      The post IDs to update.
	 * @param bool  $return_promise Whether to return the `Tribe__Promise` object created to
	 *                              handle the background update or not.
	 *
	 * @return array|Tribe__Promise The promise object created to handle the background update
	 *                              or the array of post IDs that will be, eventually, updated.
	 */
	public function async_update( array $to_update, $return_promise = true );

	/**
	 * Sets the display context the read posts will be shown into.
	 *
	 * The display context identifies the format that the post will
	 * be in: e.g. in a month view or week view for events.
	 * Extending classes can support more display contexts.
	 *
	 * @since 4.9.5
	 *
	 * @param string $context A display context supported by the repository; defaults to `default`.
	 *
	 * @return Tribe__Repository__Read_Interface For chaining purposes.
	 */
	public function set_display_context( $context = 'default' );

	/**
	 * Sets the render context the posts will be shown into.
	 *
	 * The render context indicates where the event’s display
	 * context will be output. Default specifies that it is a
	 * standard loop context. Widget indicates that it will be
	 * rendered within a widget and so on.
	 * Extending classes can support more render contexts.
	 *
	 * @since 4.9.5
	 *
	 * @param string $context A display context supported by the repository; defaults to `default`.
	 *
	 * @return Tribe__Repository__Read_Interface For chaining purposes.
	 */
	public function set_render_context( $context = 'default' );

	/**
	 * A utility method to build and return a WP_Query object for the specified
	 * posts.
	 *
	 * This method will be used mainly to hydrate and return query objects with cached
	 * results in context where the expected return type is a `Wp_Query` object.
	 * The advantage over doing `$repository->where( 'post__in' , $ids )->get_query()` is
	 * to avoid all the overhead of a query that, probably did run already.
	 *
	 * @since 4.9.5
	 *
	 * @param array $posts An array of post objects or post IDs the query should return as if fetched.
	 *
	 * @return WP_Query A query object ready to return, and operate, on the posts.
	 */
	public function get_query_for_posts( array $posts );
}
