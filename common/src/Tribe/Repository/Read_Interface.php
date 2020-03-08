<?php

/**
 * Interface Tribe__Repository__Read_Interface
 *
 * @since 4.7.19
 */
interface Tribe__Repository__Read_Interface extends Tribe__Repository__Setter_Interface {
	/**
	 * Batch filter application method.
	 *
	 * This is the same as calling `by` multiple times with different arguments.
	 *
	 * @since 4.7.19
	 *
	 * @param array $args An associative array of arguments to filter
	 *                    the posts by in the shape [ <key>, <value> ]. * * @return Tribe__Repository__Read_Interface */
	public function by_args( array $args );

	/**
	 * Batch filter application method.
	 *
	 * This is the same as calling `where` multiple times with different arguments.
	 *
	 * T

	/**
	 * Applies a filter to the query.
	 *
	 * While the signature only shows 2 arguments additional arguments will be passed
	 * to the schema filters.
	 *
	 * @since 4.7.19
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param mixed  ...$args Additional, optional, call arguments that will be passed to
	 *                        the schema.
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function by( $key, $value = null );

	/**
	 * Just an alias of the `by` method to allow for easier reading.
	 *
	 * @since 4.7.19
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function where( $key, $value = null );

	/**
	 * Sets the page of posts to fetch.
	 *
	 * Mind that this implementation does not support a `by( 'page', 2 )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param int $page
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function page( $page );

	/**
	 * Sets the number of posts to retrieve per page.
	 *
	 * Mind that this implementation does not support a `by( 'per_page', 5 )`
	 * filter to force more readable code; by default posts per page is set to
	 * the pagination defaults for the post type.
	 *
	 * @param int $per_page
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function per_page( $per_page );

	/**
	 * Returns the number of posts found matching the query.
	 *
	 * Mind that this value ignores the offset returning the
	 * number of results if limits where not applied.
	 *
	 * @since 4.7.19
	 *
	 * @return int
	 */
	public function found();

	/**
	 * Returns all posts matching the query.
	 *
	 * Mind that "all" means "all the posts matching all the filters" so pagination applies.
	 *
	 * @return array
	 */
	public function all();

	/**
	 * Sets the offset on the query.
	 *
	 * Mind that this implementation does not support a `by( 'offset', 2 )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param int  $offset
	 * @param bool $increment Whether to increment the offset by the value
	 *                        or replace it.
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function offset( $offset, $increment = false );

	/**
	 * Sets the order on the query.
	 *
	 * Mind that this implementation does not support a `by( 'order', 2 )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param string $order
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function order( $order = 'ASC' );

	/**
	 * Sets the order criteria results should be fetched by.
	 *
	 * Mind that this implementation does not support a `by( 'order_by', 'title' )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param string $order_by The post field, custom field or alias key to order posts by.
	 * @param string $order The order direction; optional; shortcut for the `order` method; defaults
	 *                      to `DESC`.
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function order_by( $order_by, $order = 'DESC' );

	/**
	 * Sets the fields that should be returned by the query.
	 *
	 * Mind that this implementation does not support a `by( 'fields', 'ids' )`
	 * filter to force more readable code.
	 *
	 * @since 4.7.19
	 *
	 * @param string $fields
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function fields( $fields );

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
	 * Sugar method to set the `post__in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array|int $post_ids
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function in( $post_ids );

	/**
	 * Sugar method to set the `post__not_in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array|int $post_ids
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function not_in( $post_ids );

	/**
	 * Sugar method to set the `post_parent__in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array|int $post_id
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function parent( $post_id );

	/**
	 * Sugar method to set the `post_parent__in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array $post_ids
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function parent_in( $post_ids );

	/**
	 * Sugar method to set the `post_parent__not_in` argument.
	 *
	 * Successive calls will stack, not replace each one.
	 *
	 * @since 4.7.19
	 *
	 * @param array $post_ids
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function parent_not_in( $post_ids );

	/**
	 * Sugar method to set the `s` argument.
	 *
	 * Successive calls will replace the search string.
	 * This is the default WordPress searh, to search by title,
	 * content or excerpt only use the `title`, `content`, `excerpt` filters.
	 *
	 * @param $search
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function search( $search );

	/**
	 * Returns the number of posts found matching the query in the current page.
	 *
	 * While the `found` method will return the number of posts found
	 * across all pages this method will only return the number of
	 * posts found in the current page.
	 * Differently from the `found` method this method will apply the
	 * offset if set.
	 *
	 * @since 4.7.19
	 *
	 * @return int
	 */
	public function count();

	/**
	 * Returns the first post of the page matching the current query.
	 *
	 * If, by default or because set with the `per_page` method, all
	 * posts matching the query should be returned then this will be
	 * the first post of all those matching the query.
	 *
	 * @since 4.7.19
	 *
	 * @return WP_Post|mixed|null
	 *
	 * @see   Tribe__Repository__Read_Interface::per_page()
	 */
	public function first();

	/**
	 * Returns the last post of the page matching the current query.
	 *
	 * If, by default or because set with the `per_page` method, all
	 * posts matching the query should be returned then this will be
	 * the last post of all those matching the query.
	 *
	 * @since 4.7.19
	 *
	 * @return WP_Post|mixed|null
	 *
	 * @see   Tribe__Repository__Read_Interface::per_page()
	 */
	public function last();

	/**
	 * Returns the nth post (1-based) of the page matching the current query.
	 *
	 * Being 1-based the second post can be fetched using `nth( 2 )`.
	 * If, by default or because set with the `per_page` method, all
	 * posts matching the query should be returned then this will be
	 * the nth post of all those matching the query.
	 *
	 * @since 4.7.19
	 *
	 * @param int $n
	 *
	 * @return WP_Post|mixed|null
	 *
	 * @see   Tribe__Repository__Read_Interface::per_page()
	 */
	public function nth( $n );

	/**
	 * Returns the first n posts of the page matching the current query.
	 *
	 * If, by default or because set with the `per_page` method, all
	 * posts matching the query should be returned then this method will
	 * return the first n posts of all those matching the query.
	 *
	 * @since 4.7.19
	 *
	 * @return array An array of posts matching the query.
	 *
	 * @see   Tribe__Repository__Read_Interface::per_page()
	 */
	public function take( $n );

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

	/**
	 * Plucks a field from all results and returns it.
	 *
	 * This method will implicitly build and use a `WP_List_Util` instance on the return
	 * value of a call to the `all` method.
	 *
	 * @since 4.9.5
	 *
	 * @param string $field The field to pluck from each result.
	 *
	 * @return array An array of the plucked results.
	 *
	 * @see \wp_list_pluck()
	 */
	public function pluck( $field );

	/**
	 * Filters the results according to the specified criteria.
	 *
	 * This method will implicitly build and use a `WP_List_Util` instance on the return
	 * value of a call to the `all` method.
	 *
	 * @since 4.9.5
	 *
	 * @param array  $args     Optional. An array of key => value arguments to match
	 *                         against each object. Default empty array.
	 * @param string $operator Optional. The logical operation to perform. 'AND' means
	 *                         all elements from the array must match. 'OR' means only
	 *                         one element needs to match. 'NOT' means no elements may
	 *                         match. Default 'AND'.
	 *
	 * @return array An array of the filtered results.
	 *
	 * @see \wp_list_filter()
	 */
	public function filter( $args = array(), $operator = 'AND' );

	/**
	 * Sorts the results according to the specified criteria.
	 *
	 * This method will implicitly build and use a `WP_List_Util` instance on the return
	 * value of a call to the `all` method.
	 *
	 * @since 4.9.5
	 *
	 * @param string|array $orderby       Optional. Either the field name to order by or an array
	 *                                    of multiple orderby fields as $orderby => $order.
	 * @param string       $order         Optional. Either 'ASC' or 'DESC'. Only used if $orderby
	 *                                    is a string.
	 * @param bool         $preserve_keys Optional. Whether to preserve keys. Default false.
	 *
	 * @return array An array of the sorted results.
	 *
	 * @see \wp_list_sort()
	 */
	public function sort( $orderby = array(), $order = 'ASC', $preserve_keys = false  );

	/**
	 * Builds a collection on the result of the `all()` method call.
	 *
	 * @since 4.9.5
	 *
	 * @return \Tribe__Utils__Post_Collection
	 */
	public function collect();
}
