<?php

/**
 * Class Tribe__Repository__Decorator
 *
 * This is the base repository decorator class to ease the decoration
 * of repositories.
 *
 * @since 4.7.19
 */
abstract class Tribe__Repository__Decorator implements Tribe__Repository__Interface {
	/**
	 * @var Tribe__Repository__Interface|Tribe__Repository__Read_Interface|Tribe__Repository__Update_Interface
	 */
	protected $decorated;

	/**
	 * {@inheritdoc}
	 */
	public function get_default_args() {
		return $this->decorated->get_default_args();
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_default_args( array $default_args ) {
		return $this->decorated->set_default_args( $default_args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter_name( $filter_name ) {
		$this->decorated->filter_name( $filter_name );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_args( array $args ) {
		$this->decorated->by_args( $args );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function by( $key, $value = null ) {
		$call_args = func_get_args();
		call_user_func_array( [ $this->decorated, 'by' ], $call_args );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function where( $key, $value = null ) {
		$call_args = func_get_args();
		call_user_func_array( [ $this->decorated, 'where' ], $call_args );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function page( $page ) {
		$this->decorated->page( $page );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function per_page( $per_page ) {
		$this->decorated->per_page( $per_page );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function found() {
		return $this->decorated->found();
	}

	/**
	 * {@inheritdoc}
	 */
	public function all() {
		return $this->decorated->all();
	}

	/**
	 * {@inheritdoc}
	 */
	public function offset( $offset, $increment = false ) {
		$this->decorated->offset( $offset );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function order( $order = 'ASC' ) {
		$this->decorated->order( $order );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function order_by( $order_by, $order = 'DESC' ) {
		$this->decorated->order_by( $order_by, $order );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function fields( $fields ) {
		$this->decorated->fields( $fields );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function permission( $permission ) {
		$this->decorated->permission( $permission );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function in( $post_ids ) {
		$this->decorated->in( $post_ids );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function not_in( $post_ids ) {
		$this->decorated->not_in( $post_ids );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parent( $post_id ) {
		$this->decorated->parent( $post_id );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parent_in( $post_ids ) {
		$this->decorated->parent_in( $post_ids );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parent_not_in( $post_ids ) {
		$this->decorated->parent_not_in( $post_ids );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function search( $search ) {
		$this->decorated->search( $search );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function count() {
		return $this->decorated->count();
	}

	/**
	 * {@inheritdoc}
	 */
	public function first() {
		return $this->decorated->first();
	}

	/**
	 * {@inheritdoc}
	 */
	public function last() {
		return $this->decorated->last();
	}

	/**
	 * {@inheritdoc}
	 */
	public function nth( $n ) {
		return $this->decorated->nth( $n );
	}

	/**
	 * {@inheritdoc}
	 */
	public function take( $n ) {
		return $this->decorated->take( $n );
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_primary_key( $primary_key ) {
		return $this->decorated->by_primary_key( $primary_key );
	}

	/**
	 * {@inheritdoc}
	 */
	public function set( $key, $value ) {
		$this->decorated->set( $key, $value );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_query() {
		return $this->decorated->get_query();
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_args( array $update_map ) {
		$this->decorated->set_args( $update_map );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( $return_promise = true ) {
		$this->decorated->save( $return_promise );
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_formatter( Tribe__Repository__Formatter_Interface $formatter ) {
		$this->decorated->set_formatter( $formatter );
	}

	/**
	 * {@inheritdoc}
	 */
	public function join_clause( $join ) {
		$this->decorated->join_clause( $join );
	}

	/**
	 * {@inheritdoc}
	 */
	public function where_clause( $where ) {
		$this->decorated->where_clause( $where );
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_query_builder( $query_builder ) {
		$this->decorated->set_query_builder( $query_builder );
	}

	/**
	 * Sets the repository to be decorated.
	 *
	 * @since 4.7.19
	 *
	 * @param Tribe__Repository__Interface $decorated
	 */
	protected function set_decorated_repository( Tribe__Repository__Interface $decorated ) {
		$this->decorated = $decorated;
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_query( $use_query_builder = true ) {
		return $this->decorated->build_query( $use_query_builder );
	}

	/**
	 * {@inheritdoc}
	 */
	public function where_or( $callbacks ) {
		$call_args = func_get_args();
		call_user_func_array( [ $this->decorated, 'where_or' ], $call_args );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_related_to_min( $by_meta_keys, $min, $keys = null, $values = null ) {
		$this->decorated->by_related_to_min( $by_meta_keys, $min, $keys, $values );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_related_to_max( $by_meta_keys, $max, $keys = null, $values = null ) {
		$this->decorated->by_related_to_max( $by_meta_keys, $max, $keys, $values );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_related_to_between( $by_meta_keys, $min, $max, $keys = null, $values = null ) {
		$this->decorated->by_related_to_between( $by_meta_keys, $min, $max, $keys, $values );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_not_related_to( $by_meta_keys, $keys = null, $values = null ) {
		$this->decorated->by_not_related_to( $by_meta_keys, $keys, $values );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_filter( $key, $value = null ) {
		return $this->decorated->has_filter( $key, $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_current_filter() {
		return $this->decorated->get_current_filter();
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_ids() {
		return $this->decorated->get_ids();
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_schema_entry( $key, $callback ) {
		$this->decorated->add_schema_entry( $key, $callback );
	}

	/**
	 * {@inheritdoc}
	 */
	public function prepare_interval( $values, $format = '%s' ) {
		return $this->decorated->prepare_interval( $values, $format );
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete( $return_promise = false ) {
		return $this->decorated->delete( $return_promise );
	}

	/**
	 * {@inheritdoc}
	 */
	public function async_delete( array $to_delete, $return_promise = true ) {
		return $this->decorated->async_delete( $to_delete, $return_promise );
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_update_field_alias( $alias, $field_name ) {
		$this->decorated->add_update_field_alias( $alias, $field_name );
	}

	/**
	 * {@inheritdoc}
	 */
	public function async_update( array $to_update, $return_promise = true ) {
		return $this->decorated->async_update( $to_update, $return_promise );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_update_fields_aliases() {
		return $this->decorated->get_update_fields_aliases();
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_update_fields_aliases( array $update_fields_aliases ) {
		$this->decorated->set_update_fields_aliases( $update_fields_aliases );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_filter_name() {
		return $this->decorated->get_filter_name();
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter_postarr_for_update( array $postarr, $post_id ) {
		return $this->decorated->filter_postarr_for_update( $postarr, $post_id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_postarr( $id = null ) {
		return $this->decorated->build_postarr();
	}

	/**
	 * {@inheritdoc}
	 */
	public function create() {
		return $this->decorated->create();
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter_postarr_for_create( array $postarr ) {
		return $this->decorated->filter_postarr_for_create( $postarr, $post_id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_create_args( array $create_args ) {
		$this->decorated->set_create_args( $create_args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_create_args() {
		return $this->decorated->get_create_args();
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_display_context( $context = 'default' ) {
		$this->decorated->set_display_context( $context );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_render_context( $context = 'default' ) {
		$this->decorated->set_render_context( $context );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_query_for_posts( array $posts ) {
		return $this->decorated->get_query_for_posts( $posts );
	}

	/**
	 * Whether the decorator is decorating an instance of a specific repository class or not.
	 *
	 * The check is made recursively for decorators to get to the first repository implementation.
	 *
	 * @since 4.9.5
	 *
	 * @param string $class The class to check for.
	 *
	 * @return bool Whether the decorator is decorating an instance of a specific repository class or not.
	 */
	public function decorates_an_instance_of( $class ) {
		return $this->decorated instanceof Tribe__Repository__Decorator
			? $this->decorated->decorates_an_instance_of( $class )
			: $this->decorated instanceof $class;
	}

	/**
	 * Returns the concrete repository implementation that's "hidden" under the decorator(s).
	 *
	 * @since 4.9.5
	 *
	 * @return \Tribe__Repository__Interface The concrete repository instance.
	 */
	public function get_decorated_repository() {
		return $this->decorated instanceof Tribe__Repository__Decorator
			? $this->decorated->get_decorated_repository()
			: $this->decorated;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pluck( $field ) {
		return $this->decorated->pluck( $field );
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter( $orderby = [], $order = 'ASC', $preserve_keys = false ) {
		return $this->decorated->filter( $orderby, $order, $preserve_keys );
	}

	/**
	 * {@inheritdoc}
	 */
	public function sort( $orderby = [], $order = 'ASC', $preserve_keys = false ) {
		return $this->decorated->sort( $orderby, $order, $preserve_keys );
	}

	/**
	 * {@inheritdoc}
	 */
	public function collect() {
		return $this->decorated->collect();
	}

	/**
	 * {@inheritdoc}
	 */
	public function hash( array $settings = [], WP_Query $query = null ) {
		return $this->decorated->hash( $settings );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_hash_data( array $settings, WP_Query $query = null ) {
		return $this->decorated->get_hash_data( $settings, $query );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_last_built_query() {
		return $this->decorated->last_built_query;
	}

	/**
	 * {@inheritDoc}
	 */
	public function where_multi( array $fields, $compare, $value, $where_relation = 'OR', $value_relation = 'OR' ) {
		$this->decorated->where_multi( $fields, $compare, $value, $where_relation, $value_relation );

		return $this;
	}

	/**
	 * Handle getting additional property from decorated object.
	 *
	 * @since 4.9.6.1
	 *
	 * @param string $name Property name.
	 *
	 * @return mixed
	 */
	public function __get( $name ) {
		return $this->decorated->{$name};
	}

	/**
	 * Handle setting additional property on decorated object.
	 *
	 * @since 4.9.6.1
	 *
	 * @param string $name  Property name.
	 * @param mixed  $value Property value.
	 */
	public function __set( $name, $value ) {
		$this->decorated->{$name} = $value;
	}

	/**
	 * Check if additional property on decorated object exists.
	 *
	 * @since 4.9.6.1
	 *
	 * @param string $name Property name.
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return isset( $this->decorated->{$name} );
	}

	/**
	 * Call methods on decorated object.
	 *
	 * @since 4.9.6.1
	 *
	 * @param string $name      Method name.
	 * @param array  $arguments Method arguments.
	 *
	 * @return mixed
	 */
	public function __call( $name, $arguments ) {
		return call_user_func_array( [ $this->decorated, $name ], $arguments );
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_query( WP_Query $query ) {
		$this->decorated->set_query( $query );

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function next() {
		return $this->decorated->next();
	}

	/**
	 * {@inheritDoc}
	 */
	public function prev() {
		return $this->decorated->prev();
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_found_rows( $found_rows ) {
		$this->decorated->set_found_rows( $found_rows );

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function void_query( $void_query = true ) {
		$this->decorated->void_query( $void_query );

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_last_sql(): ?string {
		return $this->decorated->get_last_sql();
	}
}
