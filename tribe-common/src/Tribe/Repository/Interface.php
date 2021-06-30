<?php

/**
 * Interface Tribe__Repository__Interface
 *
 * @since 4.7.19
 *
 */
interface Tribe__Repository__Interface
	extends Tribe__Repository__Read_Interface,
	Tribe__Repository__Update_Interface {

	const PERMISSION_EDITABLE = 'editable';
	const PERMISSION_READABLE = 'readable';

	/**
	 * Returns the current default query arguments of the repository.
	 *
	 * @since 4.7.19
	 *
	 * @return array
	 */
	public function get_default_args();

	/**
	 * Sets the default arguments of the repository.
	 *
	 * @since 4.7.19
	 *
	 * @param array $default_args
	 *
	 * @return mixed
	 */
	public function set_default_args( array $default_args );

	/**
	 * Sets the dynamic part of the filter tag that will be used to filter
	 * the query arguments and object.
	 *
	 * @param string $filter_name
	 *
	 * @return Tribe__Repository__Read_Interface
	 */
	public function filter_name( $filter_name );

	/**
	 * Returns the repository filter name.
	 *
	 * @since 4.9.5
	 *
	 * @return string
	 */
	public function get_filter_name(  );

	/**
	 * Sets the formatter in charge of formatting items to the correct format.
	 *
	 * @since 4.7.19
	 *
	 * @param Tribe__Repository__Formatter_Interface $formatter
	 */
	public function set_formatter( Tribe__Repository__Formatter_Interface $formatter );


	/**
	 * Build, without initializing it, the query.
	 *
	 * @since 4.7.19
	 *
	 * @param bool $use_query_builder Whether to use the query builder, if set, or not.
	 *
	 * @return WP_Query
	 */
	public function build_query( $use_query_builder = true );

	/**
	 * Adds a custom JOIN clause to the query.
	 *
	 * @since 4.7.19
	 *
	 * @param string $join
	 */
	public function join_clause( $join );

	/**
	 * Adds a custom WHERE clause to the query.
	 *
	 * @since 4.7.19
	 *
	 * @param string $where
	 */
	public function where_clause( $where );

	/**
	 * Sets the object in charge of building and returning the query.
	 *
	 * @since 4.7.19
	 *
	 * @param mixed $query_builder
	 *
	 * @return mixed
	 */
	public function set_query_builder( $query_builder );

	/**
	 * Builds a fenced group of WHERE clauses that will be used with OR logic.
	 *
	 * Mind that this is a lower level implementation of WHERE logic that requires
	 * each callback method to add, at least, one WHERE clause using the repository
	 * own `where_clause` method.
	 *
	 * @param array $callbacks       One or more WHERE callbacks that will be called
	 *                                this repository. The callbacks have the shape
	 *                                [ <method>, <...args>]
	 *
	 * @return $this
	 * @throws Tribe__Repository__Usage_Error If one of the callback methods does
	 *                                        not add any WHERE clause.
	 *
	 * @see Tribe__Repository::where_clause()
	 * @see Tribe__Repository__Query_Filters::where()
	 */
	public function where_or( $callbacks );

	/**
	 * Filters the query to return posts that have got a number or posts
	 * related to them by meta at least equal to a value.
	 *
	 * @since 4.7.19
	 *
	 * @param string|array $by_meta_keys One or more `meta_keys` relating
	 *                                   another post TO this post type.
	 * @param int          $min          The minimum number of posts of another type that should
	 *                                   be related to the queries post type(s).
	 * @param string|array $keys         One or more meta_keys to check on the post type in relation
	 *                                   with the query post type(s); if the `$values` parameter is
	 *                                   not provided then this will trigger an EXISTS check.
	 * @param string|array $values       One or more value the meta_key specified with `$keys` should
	 *                                   match.
	 *
	 * @return $this
	 */
	public function by_related_to_min( $by_meta_keys, $min, $keys = null, $values = null );

	/**
	 * Filters the query to return posts that have got a number or posts
	 * related to them by meta at most equal to a value.
	 *
	 * @since 4.7.19
	 *
	 * @param string|array $by_meta_keys One or more `meta_keys` relating
	 *                                   another post TO this post type.
	 *                                   be related to the queries post type(s).
	 * @param int          $max          The maximum number of posts of another type that should
	 *                                   be related to the queries post type(s).
	 * @param string|array $keys         One or more meta_keys to check on the post type in relation
	 *                                   with the query post type(s); if the `$values` parameter is
	 *                                   not provided then this will trigger an EXISTS check.
	 * @param string|array $values       One or more value the meta_key specified with `$keys` should
	 *                                   match.
	 *
	 * @return $this
	 */
	public function by_related_to_max( $by_meta_keys, $max, $keys = null, $values = null );

	/**
	 * Filters the query to return posts that have got a number or posts
	 * related to them by meta between two values.
	 *
	 * @since 4.7.19
	 *
	 * @param string|array $by_meta_keys One or more `meta_keys` relating
	 *                                   another post TO this post type.
	 * @param int          $min          The minimum number of posts of another type that should
	 *                                   be related to the queries post type(s).
	 * @param int          $max          The maximum number of posts of another type that should
	 *                                   be related to the queries post type(s).
	 *
	 * @param string|array $keys         One or more meta_keys to check on the post type in relation
	 *                                   with the query post type(s); if the `$values` parameter is
	 *                                   not provided then this will trigger an EXISTS check.
	 * @param string|array $values       One or more value the meta_key specified with `$keys` should
	 *                                   match.
	 *
	 * @return $this
	 */
	public function by_related_to_between( $by_meta_keys, $min, $max, $keys = null, $values = null );

	/**
	 * Adds an entry to the repository filter schema.
	 *
	 * @since 4.9.5
	 *
	 * @param string   $key      The filter key, the one that will be used in `by` and `where`
	 *                           calls.
	 * @param callable $callback The function that should be called to apply this filter.
	 */
	public function add_schema_entry( $key, $callback );

	/**
	 * Returns an hash string for this repository instance filters and, optionally, a generated query.
	 *
	 * By default all applied filters, and query vars, will be included but specific filters can
	 * be excluded, or included, from the hash generation.
	 * The possibility to include the query in the hash generation is required as the query vars could
	 * be further modified after the repository filters are applied and the query is built.
	 *
	 * @since 4.9.5
	 *
	 * @param array          $settings An array of settings to define how the hash should be produced in the shape
	 *                                 `[ 'exclude' => [ 'ex_1', ... ], 'include' => [ 'inc_1', ... ] ]`. This array
	 *                                 will apply both to the Repository filters and the query vars.
	 * @param WP_Query|null $query An optional query object to include in the hashing.
	 *
	 * @return string The generated hash string.
	 *
	 */
	public function hash( array $settings = [], WP_Query $query = null );

	/**
	 * Returns the data the repository would use to build the hash.
	 *
	 * @since 4.9.5
	 *
	 * @param array          $settings An array of settings to define how the hash should be produced in the shape
	 *                                 `[ 'exclude' => [ 'ex_1', ... ], 'include' => [ 'inc_1', ... ] ]`. This array
	 *                                 will apply both to the Repository filters and the query vars.
	 * @param WP_Query|null $query An optional query object to include in the hashing.
	 *
	 * @return array An array of hash data components.
	 */
	public function get_hash_data( array $settings, WP_Query $query = null );

	/**
	 * Returns the last built query from the repository instance.
	 *
	 * @since 4.9.6
	 *
	 * @return WP_Query|null The last built query instance if any.
	 */
	public function get_last_built_query();

	/**
	 * Builds, and adds to the query, a WHERE clause to the query on multiple fields.
	 *
	 * @since 4.9.6
	 *
	 * @param array  $fields         The fields to add WHERE clauses for. The fields can be post fields, custom fields or
	 *                               taxonomy terms.
	 * @param string $compare        The comparison operator to use, e.g. 'LIKE' or '>'.
	 * @param mixed  $value          The value, or values, to compare with; the format will be set depending on the type of
	 *                               each value.
	 * @param string $where_relation The relation to join the WHERE clauses with, either 'OR' or 'AND'; default to 'OR'.
	 * @param string $value_relation The relation to join the value clauses in case the value is an array, either 'OR'
	 *                               or 'AND'; defaults to 'OR'.
	 *
	 * @return $this This repository instance to allow chain calls.
	 *
	 * @throws \Tribe__Repository__Usage_Error If the comparison operator or the relation are not valid.
	 */
	public function where_multi( array $fields, $compare, $value, $where_relation = 'OR', $value_relation = 'OR' );

	/**
	 * Sets the query instance the repository will use.
	 *
	 * Setting a query explicitly
	 *
	 * @since 4.9.9
	 *
	 * @param  \WP_Query  $query An query instance.
	 *
	 * @return \Tribe__Repository__Interface The repository instance, for chaining.
	 * @throws \Tribe__Repository__Usage_Error If trying to set the query after a fetching operation is done.
	 */
	public function set_query( WP_Query $query );

	/**
	 * Returns a cloned instance of the repository that will yield the next page results.
	 *
	 * Mind that this method will always return a Repository instance, no matter if a next page exists or not.
	 * If a next page does not exist then the instance returned by this method will yield no posts and a count of `0`.
	 *
	 * @since 4.9.11
	 *
	 * @return \Tribe__Repository__Interface The repository instance that will yield the next page results.
	 */
	public function next();

	/**
	 * Returns a cloned instance of the repository that will yield the previous page results.
	 *
	 * Mind that this method will always return a Repository instance, no matter if a previous page exists or not.
	 * If a previous page does not exist then the instance returned by this method will yield no posts and a count
	 * of `0`.
	 *
	 * @since 4.9.11
	 *
	 * @return \Tribe__Repository__Interface The repository instance that will yield the previous page results.
	 */
	public function prev();

	/**
	 * Sets the found rows calculation to be enabled for queries.
	 *
	 * @since 4.9.10
	 *
	 * @param bool $found_rows Whether found rows calculation should be enabled.
	 *
	 * @return \Tribe__Repository__Interface The repository instance, for chaining.
	 */
	public function set_found_rows( $found_rows );

	/**
	 * Voids the repositories queries preventing the repository from running any query.
	 *
	 * @since 4.9.14
	 *
	 * @param bool $void Whether to void the repository queries or not.
	 *
	 * @return Tribe__Repository__Interface $this The repository instance.
	 */
	public function void_query( $void_query = true );
}
