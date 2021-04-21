<?php

use Tribe\Traits\With_Meta_Updates_Handling;
use Tribe\Traits\With_Post_Attribute_Detection;
use Tribe__Utils__Array as Arr;

abstract class Tribe__Repository
	implements Tribe__Repository__Interface {
	use With_Meta_Updates_Handling;
	use With_Post_Attribute_Detection;

	const MAX_NUMBER_OF_POSTS_PER_PAGE = 99999999999;

	/**
	 * @var  array An array of keys that cannot be updated on this repository.
	 */
	protected static $blocked_keys = [
		'ID',
		'post_type',
		'post_modified',
		'post_modified_gmt',
		'guid',
		'comment_count',
	];

	/**
	 * @var array A list of the default filters supported and implemented by the repository.
	 */
	protected static $default_modifiers = [
		'p',
		'author',
		'author_name',
		'author__in',
		'author__not_in',
		'has_password',
		'post_password',
		'cat',
		'category__and',
		'category__in',
		'category__not_in',
		'category_name',
		'comment_count',
		'comment_status',
		'title',
		'title_like',
		'name',
		'post_name__in',
		'ping_status',
		'post__in',
		'post__not_in',
		'post_parent',
		'post_parent__in',
		'post_parent__not_in',
		'post_mime_type',
		's',
		'search',
		'tag',
		'tag__and',
		'tag__in',
		'tag__not_in',
		'tag_id',
		'tag_slug__and',
		'tag_slug__in',
		'ID',
		'id',
		'date',
		'after_date',
		'before_date',
		'date_gmt',
		'after_date_gmt',
		'before_date_gmt',
		'post_title',
		'post_content',
		'post_excerpt',
		'post_status',
		'to_ping',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'guid',
		'perm',
		'menu_order',
		'meta',
		'meta_equals',
		'meta_not_equals',
		'meta_gt',
		'meta_greater_than',
		'meta_gte',
		'meta_greater_than_or_equal',
		'meta_like',
		'meta_not_like',
		'meta_lt',
		'meta_less_than',
		'meta_lte',
		'meta_less_than_or_equal',
		'meta_in',
		'meta_not_in',
		'meta_between',
		'meta_not_between',
		'meta_exists',
		'meta_not_exists',
		'meta_regexp',
		'meta_equals_regexp',
		'meta_not_regexp',
		'meta_not_equals_regexp',
		'meta_regexp_or_like',
		'meta_equals_regexp_or_like',
		'meta_not_regexp_or_like',
		'meta_not_equals_regexp_or_like',
		'taxonomy_exists',
		'taxonomy_not_exists',
		'term_id_in',
		'term_id_not_in',
		'term_id_and',
		'term_name_in',
		'term_name_not_in',
		'term_name_and',
		'term_slug_in',
		'term_slug_not_in',
		'term_slug_and',
		'term_in',
		'term_not_in',
		'term_and',
	];

	/**
	 * @var array An array of default arguments that will be applied to all queries.
	 */
	protected static $common_args = [
		'post_type'        => 'post',
		'suppress_filters' => false,
		'posts_per_page'   => -1,
	];

	/**
	 * @var array A list of query modifiers that will trigger a overriding merge, thus
	 *            replacing previous values, when set multiple times.
	 */
	protected static $replacing_modifiers = [
		'p',
		'author',
		'author_name',
		'author__in',
		'author__not_in',
		'has_password',
		'post_password',
		'cat',
		'category__and',
		'category__in',
		'category__not_in',
		'category_name',
		'comment_count',
		'comment_status',
		'menu_order',
		'title',
		'title_like',
		'name',
		'post_name__in',
		'ping_status',
		'post__in',
		'post__not_in',
		'post_parent',
		'post_parent__in',
		'post_parent__not_in',
		'post_mime_type',
		's',
		'search',
		'tag',
		'tag__and',
		'tag__in',
		'tag__not_in',
		'tag_id',
		'tag_slug__and',
		'tag_slug__in',
		'ID',
		'id',
		'date',
		'after_date',
		'before_date',
		'date_gmt',
		'after_date_gmt',
		'before_date_gmt',
		'post_title',
		'post_content',
		'post_excerpt',
		'post_status',
		'to_ping',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'guid',
		'perm',
		'order',
	];

	/**
	 * @var int
	 */
	protected static $meta_alias = 0;

	/**
	 * @var array A list of keys that denote the value to check should be cast to array.
	 */
	protected static $multi_value_keys = [ 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ];

	/**
	 * @var array A map of SQL comparison operators to their human-readable counterpart.
	 */
	protected static $comparison_operators = [
		'='           => 'equals',
		'!='          => 'not-equals',
		'>'           => 'gt',
		'>='          => 'gte',
		'<'           => 'lt',
		'<='          => 'lte',
		'LIKE'        => 'like',
		'NOT LIKE'    => 'not-like',
		'IN'          => 'in',
		'NOT IN'      => 'not-in',
		'BETWEEN'     => 'between',
		'NOT BETWEEN' => 'not-between',
		'EXISTS'      => 'exists',
		'NOT EXISTS'  => 'not-exists',
		'REGEXP'      => 'regexp',
		'NOT REGEXP'  => 'not-regexp',
	];

	/**
	 * A counter to keep track, on the class level, of the aliases generated for the terms table
	 * while building multi queries.
	 *
	 * @var int
	 */
	protected static $alias_counter = 1;

	/**
	 * @var string
	 */
	protected $filter_name = 'default';
	/**
	 * @var array The post IDs that will be updated.
	 */
	protected $ids = [];
	/**
	 * @var bool Whether the post IDs to update have already been fetched or not.
	 */
	protected $has_ids = false;
	/**
	 * @var array The updates that will be saved to the database.
	 */
	protected $updates = [];

	/**
	 * @var array A list of taxonomies this repository will recognize.
	 */
	protected $taxonomies = [];

	/**
	 * @var array A map detailing which fields should be converted from a
	 *            GMT time and date to a local one.
	 */
	protected $to_local_time_map = [
		'post_date_gmt' => 'post_date',
	];

	/**
	 * @var array A map detailing which fields should be converted from a
	 *            localized time and date to a GMT one.
	 */
	protected $to_gmt_map = [
		'post_date' => 'post_date_gmt',
	];

	/**
	 * @var array
	 */
	protected $default_args = [ 'post_type' => 'post' ];

	/**
	 * @var array An array of query modifying callbacks populated while applying
	 *            the filters.
	 */
	protected $query_modifiers = [];

	/**
	 * @var bool Whether the current query is void or not.
	 */
	protected $void_query = false;

	/**
	 * @var array An array of query arguments that will be populated while applying
	 *            filters.
	 */
	protected $query_args = [
		'meta_query' => [ 'relation' => 'AND' ],
		'tax_query'  => [ 'relation' => 'AND' ],
		'date_query' => [ 'relation' => 'AND' ],
	];

	/**
	 * @var array An array of query arguments that support 'relation'.
	 */
	protected $relation_query_args = [
		'meta_query',
		'tax_query',
		'date_query',
	];

	/**
	 * @var WP_Query The current query object built and modified by the instance.
	 */
	protected $current_query;

	/**
	 * @var array An associative array of the filters that will be applied and the used values.
	 */
	protected $current_filters = [];

	/**
	 * @var string|null The current filter being applied.
	 */
	protected $current_filter;

	/**
	 * @var Tribe__Repository__Query_Filters
	 */
	public $filter_query;

	/**
	 * @var string The filter that should be used to get a post by its primary key.
	 */
	protected $primary_key = 'p';

	/**
	 * @var array A map of callbacks in the shape [ <slug> => <callback|primitive> ]
	 */
	protected $schema = [];

	/**
	 * @var array A map of schema slugs and their meta keys to be queried.
	 */
	protected $simple_meta_schema = [];

	/**
	 * @var array A map of schema slugs and their taxonomies to be queried.
	 */
	protected $simple_tax_schema = [];

	/**
	 * @var Tribe__Repository__Interface
	 */
	protected $main_repository;

	/**
	 * @var Tribe__Repository__Formatter_Interface
	 */
	protected $formatter;

	/**
	 * @var bool
	 */
	protected $skip_found_rows = true;

	/**
	 * @var Tribe__Repository__Interface
	 */
	protected $query_builder;

	/**
	 * A map relating aliases to their real update field name.
	 *
	 * E.g. the `title` alias might be an alias of `post_title` in update/save operations.
	 * This is done to allow using set-like methods with human-readable names.
	 * Extending classes should pre-fill this with default aliases.
	 *
	 * @var array
	 */
	protected $update_fields_aliases = [
		'title'       => 'post_title',
		'content'     => 'post_content',
		'description' => 'post_content',
		'slug'        => 'post_name',
		'excerpt'     => 'post_excerpt',
		'status'      => 'post_status',
		'parent'      => 'post_parent',
		'author'      => 'post_author',
		'date'        => 'post_date',
		'date_gmt'    => 'post_date_gmt',
		'date_utc'    => 'post_date_gmt',
		'tag'         => 'post_tag',
		'image'       => '_thumbnail_id',
	];

	/**
	 * The default create args that will be used by the repository
	 * to create posts of the managed type.
	 *
	 * @var
	 */
	protected $create_args;

	/**
	 * Indicates the current display context if any.
	 * Extending classes can support and use this property to know the
	 * display context.
	 *
	 * @var string
	 */
	protected $display_context = 'default';

	/**
	 * Indicates the current render context if any.
	 * Extending classes can support and use this property to know the
	 * render context.
	 *
	 * @var string
	 */
	protected $render_context = 'default';

	/**
	 * The query last built from the repository instance.
	 *
	 * @var WP_Query|null
	 */
	protected $last_built_query;

	/**
	 * The hash of the last built query.
	 *
	 * @var string
	 */
	protected $last_built_hash = '';

	/**
	 * Tribe__Repository constructor.
	 *
	 * @since 4.7.19
	 */
	public function __construct() {
		$this->filter_query = new Tribe__Repository__Query_Filters();
		$this->default_args = array_merge( [ 'posts_per_page' => -1 ], $this->default_args );
		$post_types         = (array) Tribe__Utils__Array::get( $this->default_args, 'post_type', [] );
		$this->taxonomies   = get_taxonomies( [ 'object_type' => $post_types ], 'names' );

		/**
		 * Allow plugins to init their classes and setup hooks at the initial setup of a repository.
		 *
		 * @param Tribe__Repository $this This repository instance
		 *
		 * @since 4.9.5
		 */
		do_action( "tribe_repository_{$this->filter_name}_init", $this );
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_default_args() {
		return $this->default_args;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_default_args( array $default_args ) {
		$this->default_args = $default_args;
	}

	/**
	 * Returns the value of a protected property.
	 *
	 * @since 4.7.19
	 *
	 * @param string $name
	 *
	 * @return mixed|null
	 * @throws Tribe__Repository__Usage_Error If trying to access a non defined property.
	 */
	public function __get( $name ) {
		if ( ! property_exists( $this, $name ) ) {
			throw Tribe__Repository__Usage_Error::because_property_is_not_defined( $name, $this );
		}

		return $this->{$name};
	}

	/**
	 * Magic method to set protected properties.
	 *
	 * @since 4.7.19
	 *
	 * @param string $name
	 * @param mixed  $value
	 *
	 * @throws Tribe__Repository__Usage_Error As properties have to be set extending
	 * the class, using setter methods or via constructor injection
	 */
	public function __set( $name, $value ) {
		throw Tribe__Repository__Usage_Error::because_properties_should_be_set_correctly( $name, $this );
	}

	/**
	 * Whether the class has a property with the specific name or not.
	 *
	 * @since 4.7.19
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function __isset( $name ) {
		return property_exists( $this, $name ) && isset( $this->{$name} );
	}

	/**
	 * {@inheritdoc}
	 */
	public function where( $key, $value = null ) {
		$call_args = func_get_args();

		return call_user_func_array( [ $this, 'by' ], $call_args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function page( $page ) {
		$this->query_args['paged'] = absint( $page );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function per_page( $per_page ) {
		// we allow for `-1` here
		$this->query_args['posts_per_page'] = $per_page;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function count() {
		if ( $this->void_query ) {
			return 0;
		}

		$query = $this->build_query();

		// The request property will be set during the `get_posts` method and empty before it.
		if ( ! empty( $query->request ) ) {
			return (int) $query->post_count;
		}

		$original_fields_value = $query->get( 'fields', '' );

		$query->set( 'fields', 'ids' );

		/**
		 * Filters the query object by reference before counting found posts in the current page.
		 *
		 * @since 4.7.19
		 *
		 * @param WP_Query $query
		 */
		do_action( "tribe_repository_{$this->filter_name}_pre_count_posts", $query );

		$ids = $query->get_posts();

		$query->set( 'fields', $original_fields_value );

		return is_array( $ids ) ? count( $ids ) : 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_query( $use_query_builder = true ) {
		$query = null;

		if ( array_key_exists( 'void_query', $this->query_args ) && false !== $this->query_args['void_query'] ) {
			$this->void_query = true;
		}

		// We'll let the query builder decide if the query has to be rebuilt or not.
		if ( $use_query_builder && null !== $this->query_builder ) {
			$query = $this->build_query_with_builder();
		}

		if ( null !== $this->last_built_query && $this->last_built_hash === $this->hash()) {
			return $this->last_built_query;
		}

		if ( null === $query ) {
			$query = $this->build_query_internally();
		}

		/**
		 * Fires after the query has been built and before it's returned.
		 *
		 * @since 4.9.5
		 *
		 * @param WP_Query $query The built query.
		 * @param array $query_args An array of query arguments used to build the query.
		 * @param Tribe__Repository $this This repository instance.
		 * @param bool $use_query_builder Whether a query builder was used to build this query or not.
		 * @param Tribe__Repository__Interface $query_builder The query builder in use, if any.
		 */
		do_action( "tribe_repository_{$this->filter_name}_query",
			$query,
			$this,
			$use_query_builder,
			$this->query_builder
		);

		$this->last_built_query = $query;
		$this->last_built_hash = $this->hash();

		return $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function found() {
		if ( $this->void_query ) {
			return 0;
		}

		$query = $this->build_query();

		$original_no_found_rows_value = $query->get( 'no_found_rows' );

		// The request property will be set during the `get_posts` method and empty before it.
		if ( ! empty( $query->request ) && ( false === (boolean) $original_no_found_rows_value || ! $this->skip_found_rows ) ) {
			return (int) $query->found_posts;
		}

		$original_fields_value = $query->get( 'fields' );

		$query->set( 'fields', 'ids' );
		$query->set( 'no_found_rows', false );

		/**
		 * Filters the query object by reference before counting found posts.
		 *
		 * @since 4.7.19
		 *
		 * @param WP_Query $query
		 */
		do_action( "tribe_repository_{$this->filter_name}_pre_found_posts", $query );

		$query->get_posts();

		$query->set( 'fields', $original_fields_value );
		$query->set( 'no_found_rows', $original_no_found_rows_value );

		return (int) $query->found_posts;
	}

	/**
	 * {@inheritdoc}
	 */
	public function all() {
		if ( $this->void_query ) {
			return [];
		}

		$query = $this->build_query();

		// The request property will be set during the `get_posts` method and empty before it.
		if ( ! empty( $query->request ) ) {
			return array_map( [ $this, 'format_item' ], $query->posts );
		}

		$original_fields_value = $query->get( 'fields', '' );

		$return_ids = 'ids' === $original_fields_value;

		/**
		 * Do not skip counting the rows if we have some filtering to do on
		 * `found_posts`.
		 */
		$query->set( 'no_found_rows', $this->skip_found_rows );

		// We'll let the class build the items later.
		$query->set( 'fields', 'ids' );

		/**
		 * Filters the query object by reference before getting the posts.
		 *
		 * @since 4.7.19
		 *
		 * @param WP_Query $query
		 */
		do_action( "tribe_repository_{$this->filter_name}_pre_get_posts", $query );

		$results = $query->get_posts();

		/**
		 * Allow extending classes to customize the return value.
		 * Since we are filtering the array returning empty values while formatting
		 * the item will exclude it from the return values.
		 */
		$formatted = $return_ids
			? $results
			: array_filter( array_map( [ $this, 'format_item' ], $results ) );

		// Reset the fields if required.
		$query->set( 'fields', $original_fields_value );

		return $formatted;
	}

	/**
	 * {@inheritdoc}
	 */
	public function offset( $offset, $increment = false ) {
		/**
		 * The `offset` argument will only be used when `posts_per_page` is not -1
		 * and will ignore pagination.
		 * So we filter to apply a real SQL OFFSET; we also leave in place the `offset`
		 * query var to have a fallback should the LIMIT cause proving difficult to filter.
		 */
		$this->query_args['offset'] = $increment
			? absint( $offset ) + (int) Tribe__Utils__Array::get( $this->query_args, 'offset', 0 )
			: absint( $offset );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function order( $order = 'ASC' ) {
		$order = strtoupper( $order );

		if ( ! in_array( $order, [ 'ASC', 'DESC' ], true ) ) {
			return $this;
		}

		$this->query_args['order'] = $order;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function order_by( $order_by, $order = 'DESC' ) {
		$this->query_args['orderby'] = $order_by;

		// Based on `WP_Query->parse_orderby` we should ignore the global order passed, and use the value on for each item in array.
		if ( ! is_array( $order_by ) ) {
			$this->query_args['order'] = $order;
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function fields( $fields ) {
		$this->query_args['fields'] = $fields;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function permission( $permission ) {
		if ( ! in_array( $permission, [ self::PERMISSION_READABLE, self::PERMISSION_EDITABLE ], true ) ) {
			return $this;
		}

		$this->query_args['perm'] = $permission;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function in( $post_ids ) {
		$this->add_args( 'post__in', $post_ids );

		return $this;
	}

	/**
	 * Merges arguments into a query arg.
	 *
	 * @since 4.7.19
	 *
	 * @param string    $key
	 * @param array|int $value
	 */
	protected function add_args( $key, $value ) {
		$this->query_args[ $key ] = (array) $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function not_in( $post_ids ) {
		$this->add_args( 'post__not_in', $post_ids );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parent( $post_id ) {
		$this->add_args( 'post_parent__in', $post_id );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parent_in( $post_ids ) {
		$this->add_args( 'post_parent__in', $post_ids );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function parent_not_in( $post_ids ) {
		$this->add_args( 'post_parent__not_in', $post_ids );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function search( $search ) {
		$this->query_args['s'] = $search;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function first() {
		$query     = $this->build_query();

		$original_fields_value = $query->get( 'fields', '' );

		$return_id = 'ids' === $original_fields_value;

		// The request property will be set during the `get_posts` method and empty before it.
		if ( ! empty( $query->request ) ) {
			$ids = $this->get_ids();

			if ( empty( $ids ) ) {
				return null;
			}

			return $return_id ? reset( $ids ) : $this->format_item( reset( $ids ) );
		}

		$query->set( 'fields', 'ids' );
		$ids = $query->get_posts();

		$query->set( 'fields', $original_fields_value );

		if ( empty( $ids ) ) {
			return null;
		}

		return $return_id ? reset( $ids ) : $this->format_item( reset( $ids ) );
	}

	/**
	 * Formats a post handled by the repository to the expected
	 * format.
	 *
	 * Extending classes should use this method to format return values to the expected format.
	 *
	 * @since 4.7.19
	 *
	 * @param int|WP_Post $id
	 *
	 * @return WP_Post
	 */
	protected function format_item( $id ) {
		$formatted =  null === $this->formatter
			? get_post( $id )
			: $this->formatter->format_item( $id );

		/**
		 * Filters a single formatted result.
		 *
		 * @since 4.9.11
		 *
		 * @param mixed|WP_Post                $formatted The formatted post result, usually a post object.
		 * @param int                          $id        The formatted post ID.
		 * @param Tribe__Repository__Interface $this      The current repository object.
		 */
		$formatted = apply_filters( "tribe_repository_{$this->filter_name}_format_item", $formatted, $id, $this );

		return $formatted;
	}

	/**
	 * {@inheritdoc}
	 */
	public function last() {
		$query     = $this->build_query();

		$original_fields_value = $query->get('fields', '');

		$return_id = 'ids' === $original_fields_value;

		// The request property will be set during the `get_posts` method and empty before it.
		if ( ! empty( $query->request ) ) {
			$ids = $this->get_ids();

			if ( empty( $ids ) ) {
				return null;
			}

			return $return_id ? end( $ids ) : $this->format_item( end( $ids ) );
		}

		$query->set( 'fields', 'ids' );
		$ids = $query->get_posts();

		$query->set( 'fields', $original_fields_value );

		if ( empty( $ids ) ) {
			return null;
		}

		return $return_id ? end( $ids ) : $this->format_item( end( $ids ) );
	}

	/**
	 * {@inheritdoc}
	 */
	public function nth( $n ) {
		$per_page = (int) Tribe__Utils__Array::get_in_any( [
			$this->query_args,
			$this->default_args,
		], 'posts_per_page', get_option( 'posts_per_page' ) );

		if ( - 1 !== $per_page && $n > $per_page ) {
			return null;
		}

		$query = $this->build_query();

		$return_ids = 'ids' === $query->get( 'fields', '' );

		$i = absint( $n ) - 1;

		$ids = $this->get_ids();

		if ( empty( $ids[ $i ] ) ) {
			return null;
		}

		return $return_ids ? $ids[ $i ] : $this->format_item( $ids[ $i ] );
	}

	/**
	 * Applies and returns a schema entry.
	 *
	 * @since 4.7.19
	 *
	 * @param string $key
	 * @param mixed  $value
	 * @param mixed  ...$args Additional arguments for the application.
	 *
	 * @return mixed A scalar value or a callable.
	 */
	public function apply_modifier( $key, $value = null ) {
		$call_args = func_get_args();

		$application = Tribe__Utils__Array::get( $this->schema, $key, null );

		/**
		 * Return primitives, including `null`, as they are.
		 */
		if ( ! is_callable( $application ) ) {
			return $application;
		}

		/**
		 * Allow for callbacks to fire immediately and return more complex values.
		 * This also means that callbacks meant to run on the next step, the one
		 * where args are applied, will need to be "wrapped" in callbacks themselves.
		 * The `$key` is removed from the args to get the value first and avoid
		 * unused args.
		 */
		$args_without_key = array_splice( $call_args, 1 );

		$schema_entry = call_user_func_array( $application, $args_without_key );

		/**
		 * Filters the applied modifier schema entry response.
		 *
		 * @param mixed             $schema_entry A scalar value or a callable.
		 * @param Tribe__Repository $this         This repository instance
		 *
		 * @since 4.9.5
		 */
		return apply_filters( "tribe_repository_{$this->filter_name}_apply_modifier_schema_entry", $schema_entry, $this );
	}

	/**
	 * {@inheritdoc}
	 */
	public function take( $n ) {
		$query     = $this->build_query();

		$return_ids = 'ids' === $query->get( 'fields', '' );

		$matching_ids = $this->get_ids();

		if ( empty( $matching_ids ) ) {
			return [];
		}

		$spliced = array_splice( $matching_ids, 0, $n );

		return $return_ids ? $spliced : array_map( [ $this, 'format_item' ], $spliced );
	}

	/**
	 * Fetches a single instance of the post type handled by the repository.
	 *
	 * Similarly to the `get_post` function permissions are not taken into account when returning
	 * an instance by its primary key; extending classes can refine this behaviour to suit.
	 *
	 * @param mixed $primary_key
	 *
	 * @return WP_Post|null|mixed
	 */
	public function by_primary_key( $primary_key ) {
		return $this->by( $this->primary_key, $primary_key )->first();
	}

	/**
	 * Filters posts by simple meta schema value.
	 *
	 * @since 4.9.5
	 *
	 * @param mixed $value Meta value.
	 */
	public function filter_by_simple_meta_schema( $value ) {
		$filter = $this->get_current_filter();

		if ( ! array_key_exists( $filter, $this->simple_meta_schema ) ) {
			return;
		}

		$simple_meta = $this->simple_meta_schema[ $filter ];

		$by = Tribe__Utils__Array::get( $simple_meta, 'by', 'meta_regexp_or_like' );

		$this->by( $by, $simple_meta['meta_key'], $value );
	}

	/**
	 * Filters posts by simple tax schema value.
	 *
	 * @since 4.9.5
	 *
	 * @param int|string|array $value Term value(s).
	 */
	public function filter_by_simple_tax_schema( $value ) {
		$filter = $this->get_current_filter();

		if ( ! array_key_exists( $filter, $this->simple_tax_schema ) ) {
			return;
		}

		$simple_tax = $this->simple_tax_schema[ $filter ];

		$by = Tribe__Utils__Array::get( $simple_tax, 'by', 'term_in' );

		$this->by( $by, $simple_tax['taxonomy'], $value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function by( $key, $value = null ) {
		if ( $this->void_query || ( 'void_query' === $key && false !== $value ) ) {
			$this->void_query = true;

			// No point in doing more computations if the query is void.
			return $this;
		}

		$call_args = func_get_args();

		$this->current_filters[ $key ] = array_slice( $call_args, 1 );

		try {
			// Set current filter as which one we are running.
			$this->current_filter = $key;

			$query_modifier = $this->modify_query( $key, $call_args );

			// Set current filter as no longer active, we aren't running it anymore.
			$this->current_filter = null;

			/**
			 * Here we allow the repository to call one of its own methods and return `null`.
			 * A repository might have a `where` or `by` that is just building
			 * a more complex query using a base `where` or `by`.
			 */
			if ( null === $query_modifier ) {
				return $this;
			}

			/**
			 * Primitives are just merged in.
			 * Since we are using `array_merge_recursive` we expect them to be arrays.
			 */
			if ( ! ( is_object( $query_modifier ) || is_callable( $query_modifier ) ) ) {

				if ( ! is_array( $query_modifier ) ) {
					throw new InvalidArgumentException( 'Query modifier should be an array!' );
				}

				$replace_modifiers = in_array( $key, $this->replacing_modifiers(), true );
				if ( $replace_modifiers ) {
					/**
					 * We do a merge to make sure new values will override and replace the old
					 * ones.
					 */
					$this->query_args = array_merge( $this->query_args, $query_modifier );
				} else {
					/**
					 * We do a recursive merge to allow "stacking" of same kind of queries;
					 * e.g. two or more `tax_query` or `meta_query` entries should merge into one.
					 */
					$this->query_args = Arr::merge_recursive_query_vars( $this->query_args, $query_modifier );
				}
			} else {
				/**
				 * If we get back something that is not an array then we add it to
				 * the stack of query modifying callbacks we'll call on the query
				 * after building it.
				 */
				$this->query_modifiers[] = $query_modifier;
			}
		} catch ( Exception $e ) {
			/**
			 * We allow for the `apply` method to orderly fail to micro-optimize.
			 * If applying one parameter would yield no results then let's immediately bail.
			 * Schema should throw t
			 * his Exception if a light-weight on the filters would already
			 * deem a query as yielding nothing.
			 */
			$this->void_query = true;

			return $this;
		}

		/**
		 * Catching other type of exceptions is something the client code should handle!
		 */

		return $this;
	}

	/**
	 * Returns the query modifier for a key.
	 *
	 * @since 4.7.19
	 *
	 * @param string $key
	 * @param array  $call_args
	 *
	 * @return mixed
	 *
	 * @throws Tribe__Repository__Usage_Error If the required filter is not defined by the class.
	 * @throws Tribe__Repository__Void_Query_Exception To signal the query would yield no results.
	 */
	protected function modify_query( $key, $call_args ) {
		if ( ! $this->schema_has_modifier_for( $key ) ) {
			if ( $this->has_default_modifier( $key ) ) {
				// let's use the default filters normalizing the key first
				$call_args[0]   = $this->normalize_key( $key );
				$query_modifier = call_user_func_array( [ $this, 'apply_default_modifier' ], $call_args );
			} elseif ( 2 === count( $call_args ) ) {
				// Pass query argument $key with the single value argument.
				$query_modifier = [
					$key => $call_args[1],
				];
			} else {
				// More than two $call_args were sent (key, value), assume it was meant for a filter that was not defined yet.
				throw Tribe__Repository__Usage_Error::because_the_read_filter_is_not_defined( $key, $this );
			}
		} else {
			$query_modifier = call_user_func_array( [ $this, 'apply_modifier' ], $call_args );
		}

		return $query_modifier;
	}

	/**
	 * Whether the current schema defines an application for the key or not.
	 *
	 * @since 4.7.19
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	protected function schema_has_modifier_for( $key ) {
		return isset( $this->schema[ $key ] );
	}

	/**
	 * Whether a filter defined and handled by the repository exists or not.
	 *
	 * @since 4.7.19
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function has_default_modifier( $key ) {
		$normalized_key = $this->normalize_key( $key );

		return in_array( $normalized_key, self::$default_modifiers, true );
	}

	/**
	 * Normalizes the filter key to allow broad matching of the `by` filters.
	 *
	 * @since 4.7.19
	 *
	 * E.g. `by( 'id', 23 )` is the same as `by( 'ID', 23 ).
	 * E.g. `by( 'parent', 23 )` is the same as `by( `post_parent`, 23 )`
	 *
	 * @param string $key
	 *
	 * @return string The normalized filter key
	 */
	protected function normalize_key( $key ) {
		// `ID` to `id`
		$normalized = strtolower( $key );

		$post_prefixed = [
			'password',
			'name__in',
			'_in',
			'_not_in',
			'parent',
			'parent__in',
			'parent__not_in',
			'mime_type',
			'content',
			'excerpt',
			'status',
			'modified',
			'modified_gmt',
			'content_filtered',
		];

		if ( in_array( $key, $post_prefixed, true ) ) {
			$normalized = 'post_' . $key;
		}

		return $normalized;
	}

	/**
	 * Returns a list of modifiers that, when applied multiple times,
	 * will replace the previous value.
	 *
	 * This behaviour is in opposition to "stackable" modifiers that will,
	 * instead, be composed and stacked.
	 *
	 * @since 4.7.19
	 *
	 * @return array
	 */
	protected function replacing_modifiers() {
		return self::$replacing_modifiers;
	}

	/**
	 * Batch filter application method.
	 *
	 * This is the same as calling `where` multiple times with different arguments.
	 *
	 * @since 4.7.19
	 *
	 * @param array $args An associative array of arguments to filter
	 *                    the posts by in the shape [ <key>, <value> ].
	 *
	 * @return Tribe__Repository__Read_Interface|Tribe__Repository__Update_Interface
	 */
	public function where_args( array $args ) {
		return $this->by_args( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_args( array $args ) {
		foreach ( $args as $key => $value ) {
			$this->by( $key, $value );
		}

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function save( $return_promise = false ) {
		$to_update = $this->get_ids();

		if ( empty( $to_update ) ) {
			return $return_promise ? new Tribe__Promise() : [];
		}

		$exit     = [];
		$postarrs = [];

		foreach ( $to_update as $id ) {
			$postarrs[ $id ] = $this->filter_postarr_for_update( $this->build_postarr( $id ), $id );
		}

		// If any `filter_postarr_for_update` call returned a falsy value then drop it.
		$postarrs = array_filter( $postarrs );

		if (
			$this->is_background_update_active( $to_update )
			&& count( $to_update ) > $this->get_background_update_threshold( $to_update )
		) {
			return $this->async_update( $postarrs, true );
		}

		$update_callback = $this->get_update_callback( $to_update, false );

		foreach ( $postarrs as $id => $postarr ) {
			$this_exit   = $update_callback( $postarr );
			$exit[ $id ] = $id === $this_exit ? true : $this_exit;
		}

		return $return_promise ? new Tribe__Promise : $exit;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_ids() {
		if ( $this->void_query ) {
			return [];
		}


		try {
			/** @var WP_Query $query */
			$query = $this->get_query();

			// The request property will be set during the `get_posts` method and empty before it.
			if ( empty( $query->request ) ) {
				$query->set( 'fields', 'ids' );

				return $query->get_posts();
			}

			return array_map(
				static function ( $post ) {
					if ( is_int( $post ) ) {
						return $post;
					}
					$post_arr = (array) $post;

					return Arr::get( $post_arr, 'ID', Arr::get( $post_arr, 'id', 0 ) );
				},
				$query->posts
			);

		} catch ( Tribe__Repository__Void_Query_Exception $e ) {
			/*
			 * Extending classes might use this method to run sub-queries
			 * and signal a void query; let's return an empty array.
			 */
			return [];
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_query() {
		return $this->build_query();
	}

	/**
	 * Whether the current key can be updated by this repository or not.
	 *
	 * @since 4.7.19
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function can_be_updated( $key ) {
		return ! in_array( $key, self::$blocked_keys, true );
	}

	/**
	 * Whether the current key is a date one requiring a converted key pair too or not.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	protected function requires_converted_date( $key ) {
		return array_key_exists( $key, $this->to_local_time_map ) || array_key_exists( $key, $this->to_gmt_map );
	}

	/**
	 * Updates the update post payload to add dates that should be provided in GMT
	 * and localized version.
	 *
	 * @since 4.7.19
	 *
	 * @param       string     $key
	 * @param       string|int $value
	 * @param array            $postarr
	 */
	protected function update_postarr_dates( $key, $value, array &$postarr ) {
		if ( array_key_exists( $key, $this->to_gmt_map ) ) {
			$postarr[ $this->to_gmt_map[ $key ] ] = Tribe__Timezones::to_tz( $value, 'UTC' );
		} elseif ( array_key_exists( $key, $this->to_local_time_map ) ) {
			$postarr[ $this->to_local_time_map[ $key ] ] = Tribe__Timezones::to_tz( $value, Tribe__Timezones::wp_timezone_string() );
		}
		$postarr[ $key ] = $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_args( array $update_map ) {
		foreach ( $update_map as $key => $value ) {
			$this->set( $key, $value );
		}

		return $this;
	}

	/**
	 * Sets the args to be updated during save process.
	 *
	 * @param string $key   Argument key.
	 * @param mixed  $value Argument value.
	 *
	 * @throws Tribe__Repository__Usage_Error
	 *
	 * @return $this
	 */
	public function set( $key, $value ) {
		if ( ! is_string( $key ) ) {
			throw Tribe__Repository__Usage_Error::because_update_key_should_be_a_string( $this );
		}

		$this->updates[ $key ] = $value;

		return $this;
	}

	/**
	 * Sets the create args the repository will use to create posts.
	 *
	 * @since 4.9.5
	 *
	 * @param string|int $image The path to an image file, an image URL, or an attachment post ID.
	 *
	 * @return $this
	 */
	public function set_featured_image( $image ) {
		if ( '' === $image || false === $image ) {
			$thumbnail_id = false;
		} elseif ( 0 === $image || null === $image ) {
			$thumbnail_id = '';
		} else {
			$thumbnail_id = tribe_upload_image( $image );
		}

		if ( false === $thumbnail_id ) {
			return $this;
		}

		return $this->set( '_thumbnail_id', $thumbnail_id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter_name( $filter_name ) {
		$this->filter_name = trim( $filter_name );

		return $this;

	}

	/**
	 * {@inheritdoc}
	 */
	public function set_formatter( Tribe__Repository__Formatter_Interface $formatter ) {
		$this->formatter = $formatter;
	}

	/**
	 * Filters the query to only return posts that are related, via a meta key, to posts
	 * that satisfy a condition.
	 *
	 * @param string|array $meta_keys One or more `meta_keys` relating the queried post type(s)
	 *                                to another post type.
	 * @param string       $compare   The SQL comparison operator.
	 * @param string       $field     One (a column in the `posts` table) that should match
	 *                                the comparison criteria; required if the comparison operator is not `EXISTS` or
	 *                                `NOT EXISTS`.
	 * @param string|array $values    One or more values the post field(s) should be compared to;
	 *                                required if the comparison operator is not `EXISTS` or `NOT EXISTS`.
	 *
	 * @return $this
	 * @throws Tribe__Repository__Usage_Error If the comparison operator requires
	 */
	public function where_meta_related_by( $meta_keys, $compare, $field = null, $values = null ) {
		$meta_keys = Tribe__Utils__Array::list_to_array( $meta_keys );

		if ( ! in_array( $compare, [ 'EXISTS', 'NOT EXISTS' ], true ) ) {
			if ( empty( $field ) || empty( $values ) ) {
				throw Tribe__Repository__Usage_Error::because_this_comparison_operator_requires_fields_and_values( $meta_keys, $compare, $this );
			}
		}

		$field = esc_sql( $field );

		/** @var wpdb $wpdb */
		global $wpdb;
		$p  = $this->sql_slug( 'meta_related_post', $compare, $meta_keys );
		$pm = $this->sql_slug( 'meta_related_post_meta', $compare, $meta_keys );

		$this->filter_query->join( "LEFT JOIN {$wpdb->postmeta} {$pm} ON {$wpdb->posts}.ID = {$pm}.post_id" );
		$this->filter_query->join( "LEFT JOIN {$wpdb->posts} {$p} ON {$pm}.meta_value = {$p}.ID" );

		$keys_in = $this->prepare_interval( $meta_keys );

		if ( 'EXISTS' === $compare ) {
			$this->filter_query->where( "{$pm}.meta_key IN {$keys_in} AND {$pm}.meta_id IS NOT NULL" );
		} elseif ( 'NOT EXISTS' === $compare ) {
			$this->filter_query->where( "{$pm}.meta_id IS NULL" );
		} else {
			if ( in_array( $compare, self::$multi_value_keys, true ) ) {
				$values = $this->prepare_interval( $values );
			} else {
				$values = $this->prepare_value( $values );
			}
			$this->filter_query->where( "{$pm}.meta_key IN {$keys_in} AND {$p}.{$field} {$compare} {$values}" );
		}

		return $this;
	}

	/**
	 * Filters the query to only return posts that are related, via a meta key, to posts
	 * that satisfy a condition.
	 *
	 * @since 4.10.3
	 *
	 * @throws Tribe__Repository__Usage_Error If the comparison operator requires and no value provided.
	 *
	 * @param string|array $meta_keys     One or more `meta_keys` relating the queried post type(s)
	 *                                    to another post type.
	 * @param string       $compare       The SQL comparison operator.
	 * @param string       $meta_field    One (a column in the `postmeta` table) that should match
	 *                                    the comparison criteria; required if the comparison operator is not `EXISTS` or
	 *                                    `NOT EXISTS`.
	 * @param string|array $meta_values   One or more values the post field(s) should be compared to;
	 *                                    required if the comparison operator is not `EXISTS` or `NOT EXISTS`.
	 * @param boolean      $or_not_exists Whether or not to also include a clause to check if value IS NULL.
	 *                                    Example with this as true: `value = X OR value IS NULL`.
	 *
	 * @return $this
	 */
	public function where_meta_related_by_meta( $meta_keys, $compare, $meta_field = null, $meta_values = null, $or_not_exists = false ) {
		$meta_keys = Tribe__Utils__Array::list_to_array( $meta_keys );

		if ( ! in_array( $compare, [ 'EXISTS', 'NOT EXISTS' ], true ) ) {
			if ( empty( $meta_field ) || empty( $meta_values ) ) {
				throw Tribe__Repository__Usage_Error::because_this_comparison_operator_requires_fields_and_values( $meta_keys, $compare, $this );
			}
		}

		$meta_field = esc_sql( $meta_field );

		/** @var wpdb $wpdb */
		global $wpdb;

		$pm  = $this->sql_slug( 'post_meta_related_post_meta', $compare, $meta_keys );
		$pmm = $this->sql_slug( 'meta_post_meta_related_post_meta', $compare, $meta_keys );

		$this->filter_query->join( "LEFT JOIN {$wpdb->postmeta} {$pm} ON {$pm}.post_id = {$wpdb->posts}.ID" );
		$this->filter_query->join( "
			LEFT JOIN {$wpdb->postmeta} {$pmm}
				ON {$pmm}.post_id = {$pm}.meta_value
					AND {$pmm}.meta_key = '{$meta_field}'
		" );

		$keys_in = $this->prepare_interval( $meta_keys );

		if ( 'EXISTS' === $compare ) {
			$this->filter_query->where( "
				{$pm}.meta_key IN {$keys_in}
				AND {$pmm}.meta_id IS NOT NULL
			" );
		} elseif ( 'NOT EXISTS' === $compare ) {
			$this->filter_query->where( "
				{$pm}.meta_key IN {$keys_in}
				AND {$pmm}.meta_id IS NULL
			" );
		} else {
			if ( in_array( $compare, static::$multi_value_keys, true ) ) {
				$meta_values = $this->prepare_interval( $meta_values );
			} else {
				$meta_values = $this->prepare_value( $meta_values );
			}

			$clause = "{$pmm}.meta_value {$compare} {$meta_values}";

			if ( $or_not_exists ) {
				$clause = "
					(
						{$clause}
						OR {$pmm}.meta_id IS NULL
					)
				";
			}

			$this->filter_query->where( "
				{$pm}.meta_key IN {$keys_in}
				AND {$clause}
			" );
		}

		return $this;
	}

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
	public function where_or( $callbacks ) {
		$all_callbacks = func_get_args();
		$buffered      = $this->filter_query->get_buffered_where_clauses( true );

		$this->filter_query->buffer_where_clauses( true );

		$buffered_count = count( $buffered );

		foreach ( $all_callbacks as $c ) {
			call_user_func_array( [ $this, $c[0] ], array_slice( $c, 1 ) );

			if ( $buffered_count === count( $this->filter_query->get_buffered_where_clauses() ) ) {
				throw Tribe__Repository__Usage_Error::because_where_or_should_only_be_used_with_methods_that_add_where_clauses( $c, $this );
			}

			$buffered_count ++;
		}

		$buffered = $this->filter_query->get_buffered_where_clauses( true );

		$fenced = sprintf( '( %s )', implode( ' OR ', $buffered ) );

		$this->where_clause( $fenced );

		return $this;
	}

	/**
	 * Adds an entry to the repository filter schema.
	 *
	 * @since 4.9.5
	 *
	 * @param string   $key      The filter key, the one that will be used in `by` and `where`
	 *                           calls.
	 * @param callable $callback The function that should be called to apply this filter.
	 */
	public function add_schema_entry( $key, $callback ) {
		$this->schema[ $key ] = $callback;
	}

	/**
	 * Adds a simple meta entry to the repository filter schema.
	 *
	 * @since 4.9.5
	 *
	 * @param string       $key      The filter key, the one that will be used in `by` and `where` calls.
	 * @param string|array $meta_key The meta key(s) to use for the meta lookup.
	 * @param string|null  $by       The ->by() lookup to use (defaults to meta_regexp_or_like).
	 */
	public function add_simple_meta_schema_entry( $key, $meta_key, $by = null ) {
		$this->schema[ $key ] = [ $this, 'filter_by_simple_meta_schema' ];

		$this->simple_meta_schema[ $key ] = [
			'meta_key' => $meta_key,
			'by'       => $by,
		];
	}

	/**
	 * Adds a simple taxonomy entry to the repository filter schema.
	 *
	 * @since 4.9.5
	 *
	 * @param string       $key      The filter key, the one that will be used in `by` and `where` calls.
	 * @param string|array $taxonomy The taxonomy/taxonomies to use for the tax lookup.
	 * @param string|null  $by       The ->by() lookup to use (defaults to term_in).
	 */
	public function add_simple_tax_schema_entry( $key, $taxonomy, $by = null ) {
		$this->schema[ $key ] = [ $this, 'filter_by_simple_tax_schema' ];

		$this->simple_tax_schema[ $key ] = [
			'taxonomy' => $taxonomy,
			'by'       => $by,
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function add_update_field_alias( $alias, $field_name ) {
		$this->update_fields_aliases[ $alias ] = $field_name;
	}

	/**
	 * Returns modified query arguments after applying a default filter.
	 *
	 * @since 4.7.19
	 *
	 * @param      string $key
	 * @param      mixed  $value
	 *
	 * @return array
	 * @throws Tribe__Repository__Usage_Error If a filter is called with wrong arguments.
	 */
	protected function apply_default_modifier( $key, $value ) {
		$args = [];

		$call_args = func_get_args();
		$arg_1     = isset( $call_args[2] ) ? $call_args[2] : null;
		$arg_2     = isset( $call_args[3] ) ? $call_args[3] : null;

		/** @var wpdb $wpdb */
		global $wpdb;

		switch ( $key ) {
			default:
				// leverage built-in WP_Query filters
				$args = [ $key => $value ];
				break;
			case 'ID':
			case 'id':
				$args = [ 'p' => $value ];
				break;
			case 'search':
				$args = [ 's' => $value ];
				break;
			case 'post_status':
				$this->query_args['post_status'] = (array) $value;
				break;
			case 'date':
			case 'after_date':
				$args = $this->get_posts_after( $value, 'post_date' );
				break;
			case 'before_date':
				$args = $this->get_posts_before( $value, 'post_date' );
				break;
			case 'date_gmt':
			case 'after_date_gmt':
				$args = $this->get_posts_after( $value, 'post_date_gmt' );
				break;
			case 'before_date_gmt':
				$args = $this->get_posts_before( $value, 'post_date_gmt' );
				break;
			case 'title_like':
				$this->filter_query->to_get_posts_with_title_like( $value );
				break;
			case 'post_content':
				$this->filter_query->to_get_posts_with_content_like( $value );
				break;
			case 'post_excerpt':
				$this->filter_query->to_get_posts_with_excerpt_like( $value );
				break;
			case 'to_ping':
				$this->filter_query->to_get_posts_to_ping( $value );
				$args = [ 'to_ping' => $value ];
				break;
			case 'post_modified':
				$args = $this->get_posts_after( $value, 'post_modified' );
				break;
			case 'post_modified_gmt':
				$args = $this->get_posts_after( $value, 'post_modified_gmt' );
				break;
			case 'post_content_filtered':
				$this->filter_query->to_get_posts_with_filtered_content_like( $value );
				break;
			case 'guid':
				$this->filter_query->to_get_posts_with_guid_like( $value );
				break;
			case 'menu_order':
				$args = [ 'menu_order' => $value ];
				break;
			case 'meta':
			case 'meta_equals':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, '=', $format = $arg_2 );
				break;
			case 'meta_not_equals':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, '!=', $format = $arg_2 );
				break;
			case 'meta_gt':
			case 'meta_greater_than':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, '>', $format = $arg_2 );
				break;
			case 'meta_gte':
			case 'meta_greater_than_or_equal':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, '>=', $format = $arg_2 );
				break;
			case 'meta_like':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'LIKE' );
				break;
			case 'meta_not_like':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'NOT LIKE' );
				break;
			case 'meta_lt':
			case 'meta_less_than':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, '<', $format = $arg_2 );
				break;
			case 'meta_lte':
			case 'meta_less_than_or_equal':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, '<=', $format = $arg_2 );
				break;
			case 'meta_in':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'IN', $format = $arg_2 );
				break;
			case 'meta_not_in':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'NOT IN', $format = $arg_2 );
				break;
			case 'meta_between':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'BETWEEN', $format = $arg_2 );
				break;
			case 'meta_not_between':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'NOT BETWEEN', $format = $arg_2 );
				break;
			case 'meta_exists':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'EXISTS' );
				break;
			case 'meta_not_exists':
				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'NOT EXISTS' );
				break;
			case 'meta_regexp':
			case 'meta_equals_regexp':
				// Check if Regexp is fenced.
				if ( tribe_is_regex( $arg_1 ) ) {
					// Unfence the Regexp.
					$arg_1 = tribe_unfenced_regex( $arg_1 );
				}

				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'REGEXP' );
				break;
			case 'meta_not_regexp':
			case 'meta_not_equals_regexp':
				// Check if Regexp is fenced.
				if ( tribe_is_regex( $arg_1 ) ) {
					// Unfence the Regexp.
					$arg_1 = tribe_unfenced_regex( $arg_1 );
				}

				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, 'NOT REGEXP' );
				break;
			case 'meta_regexp_or_like':
			case 'meta_equals_regexp_or_like':
				$compare = 'LIKE';

				// Check if Regexp is fenced (the only way for Regexp to be supported in this context).
				if ( tribe_is_regex( $arg_1 ) ) {
					$compare = 'REGEXP';

					// Unfence the Regexp.
					$arg_1 = tribe_unfenced_regex( $arg_1 );
				}

				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, $compare );
				break;
			case 'meta_not_regexp_or_like':
			case 'meta_not_equals_regexp_or_like':
				$compare = 'NOT LIKE';

				// Check if Regexp is fenced (the only way for Regexp to be supported in this context).
				if ( tribe_is_regex( $arg_1 ) ) {
					$compare = 'NOT REGEXP';

					// Unfence the Regexp.
					$arg_1 = tribe_unfenced_regex( $arg_1 );
				}

				$args = $this->build_meta_query( $meta_key = $value, $meta_value = $arg_1, $compare );
				break;
			case 'taxonomy_exists':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'term_id', 'EXISTS' );
				break;
			case 'taxonomy_not_exists':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'term_id', 'NOT EXISTS' );
				break;
			case 'term_id_in':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'term_id', 'IN' );
				break;
			case 'term_id_not_in':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'term_id', 'NOT IN' );
				break;
			case 'term_id_and':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'term_id', 'AND' );
				break;
			case 'term_name_in':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'name', 'IN' );
				break;
			case 'term_name_not_in':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'name', 'NOT IN' );
				break;
			case 'term_name_and':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'name', 'AND' );
				break;
			case 'term_slug_in':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'slug', 'IN' );
				break;
			case 'term_slug_not_in':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'slug', 'NOT IN' );
				break;
			case 'term_slug_and':
				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'slug', 'AND' );
				break;
			case 'term_in':
				$arg_1 = Tribe__Terms::translate_terms_to_ids( $arg_1, $value, false );

				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'term_id', 'IN' );
				break;
			case 'term_not_in':
				$arg_1 = Tribe__Terms::translate_terms_to_ids( $arg_1, $value, false );

				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'term_id', 'NOT IN' );
				break;
			case 'term_and':
				$arg_1 = Tribe__Terms::translate_terms_to_ids( $arg_1, $value, false );

				$args = $this->build_tax_query( $taxonomy = $value, $terms = $arg_1, 'term_id', 'AND' );
				break;
		}


		return $args;
	}

	/**
	 * Builds a date query entry to get posts after a date.
	 *
	 * @since 4.7.19
	 *
	 * @param string $value
	 * @param string $column
	 *
	 * @return array
	 */
	protected function get_posts_after( $value, $column = 'post_date' ) {
		$timezone = in_array( $column, [ 'post_date_gmt', 'post_modified_gmt' ], true )
			? 'UTC'
			: Tribe__Timezones::generate_timezone_string_from_utc_offset( Tribe__Timezones::wp_timezone_string() );

		if ( is_numeric( $value ) ) {
			$value = "@{$value}";
		}

		$date = new DateTime( $value, new DateTimeZone( $timezone ) );

		$array_key = sprintf( '%s-after', $column );

		return [
			'date_query' => [
				'relation' => 'AND',
				$array_key => [
					'inclusive' => true,
					'column'    => $column,
					'after'     => $date->format( 'Y-m-d H:i:s' ),
				],
			],
		];
	}

	/**
	 * Builds a date query entry to get posts before a date.
	 *
	 * @since 4.7.19
	 *
	 * @param string $value
	 * @param string $column
	 *
	 * @return array
	 */
	protected function get_posts_before( $value, $column = 'post_date' ) {
		$timezone = in_array( $column, [ 'post_date_gmt', 'post_modified_gmt' ], true )
			? 'UTC'
			: Tribe__Timezones::generate_timezone_string_from_utc_offset( Tribe__Timezones::wp_timezone_string() );

		if ( is_numeric( $value ) ) {
			$value = "@{$value}";
		}

		$date = new DateTime( $value, new DateTimeZone( $timezone ) );

		$array_key = sprintf( '%s-before', $column );

		return [
			'date_query' => [
				'relation' => 'AND',
				$array_key => [
					'inclusive' => true,
					'column'    => $column,
					'before'    => $date->format( 'Y-m-d H:i:s' ),
				],
			],
		];
	}

	/**
	 * Builds a meta query entry.
	 *
	 * @since 4.7.19
	 *
	 * @param string       $meta_key
	 * @param string|array $meta_value
	 * @param string       $compare
	 * @param string       $type_or_format The type of value to compare
	 *
	 * @return array|null
	 * @throws Tribe__Repository__Usage_Error If trying to compare multiple values with a single
	 *                                        comparison operator.
	 */
	protected function build_meta_query( $meta_key, $meta_value = 'value', $compare = '=', $type_or_format = null ) {
		$meta_keys = Tribe__Utils__Array::list_to_array( $meta_key );

		$postfix = Tribe__Utils__Array::get( self::$comparison_operators, $compare, '' );

		if ( count( $meta_keys ) === 1 ) {
			$array_key = $this->sql_slug( $meta_keys[0], $postfix );

			$args = [
				'meta_query' => [
					$array_key => [
						'key'     => $meta_keys[0],
						'compare' => strtoupper( $compare ),
					],
				],
			];

			if ( ! in_array( $compare, [ 'EXISTS', 'NOT EXISTS' ], true ) ) {
				$args['meta_query'][ $array_key ]['value'] = $meta_value;
			}

			if ( 0 === strpos( $type_or_format, '%' ) ) {
				throw Tribe__Repository__Usage_Error::because_the_type_is_a_wpdb_prepare_format( $meta_key, $type_or_format, $this );
			}

			if ( null !== $type_or_format ) {
				$args['meta_query'][ $array_key ]['type'] = $type_or_format;
			}

			return $args;
		}


		if ( null === $type_or_format ) {
			$type_or_format = '%s';
		} elseif ( 0 !== strpos( $type_or_format, '%' ) ) {
			throw Tribe__Repository__Usage_Error::because_the_format_is_not_a_wpdb_prepare_one( $meta_key, $type_or_format, $this );
		}

		/** @var wpdb $wpdb */
		global $wpdb;

		// Build custom WHERE and JOINS to reduce the JOIN clauses
		$pm_alias     = $this->sql_slug( 'meta', $postfix, ++ self::$meta_alias );
		$meta_keys_in = sprintf( "('%s')", implode( "','", array_map( 'esc_sql', $meta_keys ) ) );

		$this->validate_operator_and_values( $compare, $meta_keys, $meta_value );

		if ( in_array( $compare, self::$multi_value_keys, true ) ) {
			$meta_values = $this->prepare_interval( Tribe__Utils__Array::list_to_array( $meta_value ), $type_or_format );
		} else {
			$meta_values = $this->prepare_value( $meta_value, $type_or_format );
		}

		$this->filter_query->join( "JOIN {$wpdb->postmeta} {$pm_alias} ON {$wpdb->posts}.ID = {$pm_alias}.post_id" );

		if ( 'EXISTS' === $compare ) {
			$this->filter_query->where( "{$pm_alias}.meta_key IN {$meta_keys_in} AND {$pm_alias}.meta_id IS NOT NULL" );
		} elseif ( 'NOT EXISTS' === $compare ) {
			$this->filter_query->where( "{$pm_alias}.meta_key NOT IN {$meta_keys_in} AND {$pm_alias}.meta_id IS NOT NULL" );
		} else {
			$this->filter_query->where( "{$pm_alias}.meta_key IN {$meta_keys_in} AND {$pm_alias}.meta_value {$compare} {$meta_values}" );
		}
	}

	/**
	 * Generates a SQL friendly slug from the provided, variadic, fragments.
	 *
	 * @since 4.7.19
	 *
	 * @param ...string $frag
	 *
	 * @return string
	 */
	protected function sql_slug( $frag ) {
		$frags = func_get_args();

		foreach ( $frags as &$frag ) {
			if ( is_string( $frag ) ) {
				Tribe__Utils__Array::get( self::$comparison_operators, $frag, $frag );
			} elseif ( is_array( $frag ) ) {
				$frag = implode( '_', $frag );
			}
		}


		$frags = array_filter( $frags );

		return strtolower( str_replace( '-', '_', sanitize_title( implode( '_', $frags ) ) ) );
	}

	/**
	 * Builds a taxonomy query entry.
	 *
	 * @since 4.7.19
	 *
	 * @param string           $taxonomy
	 * @param int|string|array $terms
	 * @param string           $field
	 * @param string           $operator
	 *
	 * @return array
	 */
	protected function build_tax_query( $taxonomy, $terms, $field, $operator ) {
		if ( in_array( $operator, [ 'EXISTS', 'NOT EXISTS' ], true ) ) {
			$array_key = $this->sql_slug( $taxonomy, $operator );
		} else {
			$array_key = $this->sql_slug( $taxonomy, $field, $operator );
		}

		return [
			'tax_query' => [
				$array_key => [
					'taxonomy' => $taxonomy,
					'field'    => $field,
					'terms'    => $terms,
					'operator' => strtoupper( $operator ),
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function join_clause( $join ) {
		$this->filter_query->join( $join );
	}

	/**
	 * {@inheritdoc}
	 */
	public function where_clause( $where ) {
		$this->filter_query->where( $where );
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_query_builder( $query_builder ) {
		$this->query_builder = $query_builder;
	}

	/**
	 * Builds and escapes an interval of strings.
	 *
	 * The return string includes opening and closing braces.
	 *
	 * @since 4.7.19
	 *
	 * @param string|array $values One or more values to use to build
	 *                             the interval
	 *                             .
	 * @param string       $format The format that should be used to escape
	 *                             the values; default to '%s'.
	 * @param string       $operator The operator the interval is being prepared for;
	 *                               defaults to `IN`.
	 *
	 * @return string
	 */
	public function prepare_interval( $values, $format = '%s', $operator = 'IN' ) {
		$values = Tribe__Utils__Array::list_to_array( $values );

		$prepared = [];
		foreach ( $values as $value ) {
			$prepared[] = $this->prepare_value( $value, $format );
		}

		return in_array( $operator, [ 'BETWEEN', 'NOT BETWEEN' ] )
			? sprintf( '%s AND %s', $prepared[0], $prepared[1] )
			: sprintf( '(%s)', implode( ',', $prepared ) );
	}

	/**
	 * Prepares a single value to be used in a SQL query.
	 *
	 * @since 4.7.19
	 *
	 * @param mixed  $value
	 * @param string $format
	 *
	 * @return string
	 */
	public function prepare_value( $value, $format = '%s' ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		return $wpdb->prepare( $format, $value );
	}

	/**
	 * Validates that a comparison operator is used with the correct type of values.
	 *
	 * This is just a wrap to signal this kind of code error not in bad SQL error but
	 * with a visible exception.
	 *
	 * @since 4.7.19
	 *
	 * @param string       $compare A SQL comparison operator
	 * @param string|array $meta_key
	 * @param mixed        $meta_value
	 *
	 * @throws Tribe__Repository__Usage_Error
	 */
	protected function validate_operator_and_values( $compare, $meta_key, $meta_value ) {
		if ( is_array( $meta_value ) && ! in_array( $compare, self::$multi_value_keys, true ) ) {
			throw Tribe__Repository__Usage_Error::because_single_value_comparisons_should_be_used_with_one_value(
				$meta_key,
				$meta_value,
				$compare,
				$this
			);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_related_to_min( $by_meta_keys, $min, $keys = null, $values = null ) {
		$min = $this->prepare_value( $min, '%d' );

		/** @var wpdb $wpdb */
		global $wpdb;

		$by_meta_keys = $this->prepare_interval( $by_meta_keys );

		$join      = '';
		$and_where = '';
		if ( ! empty( $keys ) || ! empty( $values ) ) {
			$join = "\nJOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id\n";
		}
		if ( ! empty( $keys ) ) {
			$keys       = $this->prepare_interval( $keys );
			$and_where .= "\nAND pm2.meta_key IN {$keys}\n";
		}
		if ( ! empty( $values ) ) {
			$values     = $this->prepare_interval( $values );
			$and_where .= "\nAND pm2.meta_value IN {$values}\n";
		}

		$this->where_clause( "{$wpdb->posts}.ID IN (
			SELECT pm1.meta_value
			FROM {$wpdb->postmeta} pm1 {$join}
			WHERE pm1.meta_key IN {$by_meta_keys} {$and_where}
			GROUP BY( pm1.meta_value )
			HAVING COUNT(DISTINCT pm1.post_id) >= {$min}
		)" );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_related_to_max( $by_meta_keys, $max, $keys = null, $values = null ) {
		$max = $this->prepare_value( $max, '%d' );

		/** @var wpdb $wpdb */
		global $wpdb;

		$join      = '';
		$and_where = '';
		if ( ! empty( $keys ) || ! empty( $values ) ) {
			$join = "\nJOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id\n";
		}
		if ( ! empty( $keys ) ) {
			$keys       = $this->prepare_interval( $keys );
			$and_where .= "\nAND pm2.meta_key IN {$keys}\n";
		}
		if ( ! empty( $values ) ) {
			$values     = $this->prepare_interval( $values );
			$and_where .= "\nAND pm2.meta_value IN {$values}\n";
		}

		$by_meta_keys = $this->prepare_interval( $by_meta_keys );

		$this->where_clause( "{$wpdb->posts}.ID IN (
			SELECT pm1.meta_value
			FROM {$wpdb->postmeta} pm1 {$join}
			WHERE pm1.meta_key IN {$by_meta_keys} {$and_where}
			GROUP BY( pm1.meta_value )
			HAVING COUNT(DISTINCT pm1.post_id) <= {$max}
		)" );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function by_related_to_between( $by_meta_keys, $min, $max, $keys = null, $values = null ) {
		$min = $this->prepare_value( $min, '%d' );
		$max = $this->prepare_value( $max, '%d' );

		/** @var wpdb $wpdb */
		global $wpdb;

		$by_meta_keys = $this->prepare_interval( $by_meta_keys );

		$join      = '';
		$and_where = '';
		if ( ! empty( $keys ) || ! empty( $values ) ) {
			$join = "\nJOIN {$wpdb->postmeta} pm2 ON pm1.post_id = pm2.post_id\n";
		}
		if ( ! empty( $keys ) ) {
			$keys       = $this->prepare_interval( $keys );
			$and_where .= "\nAND pm2.meta_key IN {$keys}\n";
		}
		if ( ! empty( $values ) ) {
			$values     = $this->prepare_interval( $values );
			$and_where .= "\nAND pm2.meta_value IN {$values}\n";
		}

		$this->where_clause( "{$wpdb->posts}.ID IN (
			SELECT pm1.meta_value
			FROM {$wpdb->postmeta} pm1 {$join}
			WHERE pm1.meta_key IN {$by_meta_keys} {$and_where}
			GROUP BY( pm1.meta_value )
			HAVING COUNT(DISTINCT pm1.post_id) BETWEEN {$min} AND {$max}
		)" );

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function has_filter( $key, $value = null ) {
		$args   = func_get_args();
		$values = array_slice( $args, 1 );

		if ( null === $value ) {
			// We just want to check if a filter is applied.
			return array_key_exists( $key, $this->current_filters );
		}

		// We check if the filter exists and the arguments match; inline to prevent "Undefined index" errors.
		return array_key_exists( $key, $this->current_filters ) && array_slice(
			$this->current_filters[ $key ],
			0,
			min( count( $this->current_filters[ $key ] ), count( $values ) )
		) === $values;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_current_filter() {
		return $this->current_filter;
	}

	/**
	 * Returns a map relating comparison operators to their "pretty" name.
	 *
	 * @since 4.9.5
	 *
	 * @return array
	 */
	public static function get_comparison_operators() {
		return self::$comparison_operators;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete( $return_promise = false ) {
		$to_delete = $this->get_ids();

		if ( empty( $to_delete ) ) {
			return $return_promise ? new Tribe__Promise() : [];
		}


		/**
		 * Filters the post delete operation allowing third party code to bail out of
		 * the process completely.
		 *
		 * @since 4.9.5
		 *
		 * @param array|null $deleted An array containing the the IDs of the deleted posts.
		 * @param self       $this    This repository instance.
		 */
		$deleted = apply_filters( "tribe_repository_{$this->filter_name}_delete", null, $to_delete );
		if ( null !== $deleted ) {
			return $deleted;
		}

		if (
			$this->is_background_delete_active( $to_delete )
			&& count( $to_delete ) > $this->get_background_delete_threshold( $to_delete )
		) {
			return $this->async_delete( $to_delete, $return_promise );
		}

		$delete_callback = $this->get_delete_callback( $to_delete );

		foreach ( $to_delete as $id ) {
			$done = $delete_callback( $id );

			if ( empty( $done ) ) {
				tribe( 'logger' )->log(
					__( 'Could not delete post with ID ' . $id, 'tribe-common' ),
					Tribe__Log::WARNING,
					$this->filter_name
				);
				continue;
			}
			$deleted[] = $id;
		}

		return $return_promise ? new Tribe__Promise() : $deleted;
	}

	/**
	 * Whether background delete is activated for the repository or not.
	 *
	 * @since 4.9.5
	 *
	 * @param array $to_delete An array of post IDs to delete.
	 *
	 * @return bool Whether background delete is activated for the repository or not.
	 */
	protected function is_background_delete_active( $to_delete ) {
		/**
		 * Whether background, asynchronous, deletion of posts is active or not for all repositories.
		 *
		 * If active then if the number of posts to delete is over the threshold, defined
		 * by the `tribe_repository_delete_background_threshold` filter, then the deletion will happen
		 * in background in other requests.
		 *
		 * @since 4.9.5
		 *
		 * @param bool  $background_active Whether background deletion is active or not.
		 * @param array $to_delete         The array of post IDs to delete.
		 */
		$background_active = (bool) apply_filters( 'tribe_repository_delete_background_activated', true, $to_delete );

		/**
		 * Whether background, asynchronous, deletion of posts is active or not for this specific repository.
		 *
		 * If active then if the number of posts to delete is over the threshold, defined
		 * by the `tribe_repository_delete_background_threshold` filter, then the deletion will happen
		 * in background in other requests.
		 *
		 * @since 4.9.5
		 *
		 * @param bool  $background_active Whether background deletion is active or not.
		 * @param array $to_delete         The array of post IDs to delete.
		 */
		$background_active = (bool) apply_filters(
			"tribe_repository_{$this->filter_name}_delete_background_activated",
			$background_active,
			$to_delete
		);

		return $background_active;
	}

	/**
	 * Returns the threshold above which posts will be deleted in background.
	 *
	 * @since 4.9.5
	 *
	 * @param array $to_delete An array of post IDs to delete.
	 *
	 * @return int The threshold above which posts will be deleted in background.
	 */
	protected function get_background_delete_threshold( $to_delete ) {
		/**
		 * The number of posts above which the deletion will happen in background.
		 *
		 * This filter will be ignored if background delete is deactivated with the `tribe_repository_delete_background_activated`
		 * or `tribe_repository_{$this->filter_name}_delete_background_activated` filter.
		 *
		 * @since 4.9.5
		 *
		 * @param int The threshold over which posts will be deleted in background.
		 * @param array $to_delete The post IDs to delete.
		 */
		$background_threshold = (int) apply_filters( 'tribe_repository_delete_background_threshold', 20, $to_delete );

		/**
		 * The number of posts above which the deletion will happen in background.
		 *
		 * This filter will be ignored if background delete is deactivated with the `tribe_repository_delete_background_activated`
		 * or `tribe_repository_{$this->filter_name}_delete_background_activated` filter.
		 *
		 * @since 4.9.5
		 *
		 * @param int The threshold over which posts will be deleted in background.
		 * @param array $to_delete The post IDs to delete.
		 */
		$background_threshold = (int) apply_filters(
			"tribe_repository_{$this->filter_name}_delete_background_threshold",
			$background_threshold,
			$to_delete
		);

		return $background_threshold;
	}

	/**
	 * Whether background update is activated for the repository or not.
	 *
	 * @since 4.9.5
	 *
	 * @param array $to_update An array of post IDs to update.
	 *
	 * @return bool Whether background update is activated for the repository or not.
	 */
	protected function is_background_update_active( $to_update ) {
		/**
		 * Whether background, asynchronous, update of posts is active or not for all repositories.
		 *
		 * If active then if the number of posts to update is over the threshold, defined
		 * by the `tribe_repository_update_background_threshold` filter, then the update will happen
		 * in background in other requests.
		 *
		 * @since 4.9.5
		 *
		 * @param bool  $background_active Whether background update is active or not.
		 * @param array $to_update         The array of post IDs to update.
		 */
		$background_active = (bool) apply_filters( 'tribe_repository_update_background_activated', true, $to_update );

		/**
		 * Whether background, asynchronous, update of posts is active or not for this specific repository.
		 *
		 * If active then if the number of posts to update is over the threshold, defined
		 * by the `tribe_repository_update_background_threshold` filter, then the update will happen
		 * in background in other requests.
		 *
		 * @since 4.9.5
		 *
		 * @param bool  $background_active Whether background update is active or not.
		 * @param array $to_update         The array of post IDs to update.
		 */
		$background_active = (bool) apply_filters(
			"tribe_repository_{$this->filter_name}_update_background_activated",
			$background_active,
			$to_update
		);

		return $background_active;
	}

	/**
	 * Returns the threshold above which posts will be updated in background.
	 *
	 * @since 4.9.5
	 *
	 * @param array $to_update An array of post IDs to update.
	 *
	 * @return int The threshold above which posts will be updated in background.
	 */
	protected function get_background_update_threshold( $to_update ) {
		/**
		 * The number of posts above which the update will happen in background.
		 *
		 * This filter will be ignored if background update is deactivated with the `tribe_repository_update_background_activated`
		 * or `tribe_repository_{$this->filter_name}_update_background_activated` filter.
		 *
		 * @since 4.9.5
		 *
		 * @param int The threshold over which posts will be updated in background.
		 * @param array $to_update The post IDs to update.
		 */
		$background_threshold = (int) apply_filters( 'tribe_repository_update_background_threshold', 20, $to_update );

		/**
		 * The number of posts above which the update will happen in background.
		 *
		 * This filter will be ignored if background update is deactivated with the `tribe_repository_update_background_activated`
		 * or `tribe_repository_{$this->filter_name}_update_background_activated` filter.
		 *
		 * @since 4.9.5
		 *
		 * @param int The threshold over which posts will be updated in background.
		 * @param array $to_update The post IDs to update.
		 */
		$background_threshold = (int) apply_filters(
			"tribe_repository_{$this->filter_name}_update_background_threshold",
			$background_threshold,
			$to_update
		);

		return $background_threshold;
	}


	/**
	 * {@inheritdoc}
	 */
	public function async_delete( array $to_delete, $return_promise = true ) {
		$promise = new Tribe__Promise( $this->get_delete_callback( $to_delete, true ), $to_delete );
		if ( ! $return_promise ) {
			// Dispatch it immediately and return the IDs that will be deleted.
			$promise->save()->dispatch();

			return $to_delete;
		}

		// Return the promise and let the client do the dispatching.
		return $promise;
	}

	/**
	 * Returns the delete callback function or method to use to delete posts.
	 *
	 * @since 4.9.5
	 *
	 * @param      int|array $to_delete  The post ID to delete or an array of post IDs to delete.
	 * @param bool           $background Whether the callback will be used in background delete operations or not.
	 *
	 * @return callable The callback to use.
	 */
	protected function get_delete_callback( $to_delete, $background = false ) {
		/**
		 * Filters the callback that all repositories should use to delete posts.
		 *
		 * @since 4.9.5
		 *
		 * @param callable  $callback   The callback that should be used to delete each post; defaults
		 *                              to `wp_delete_post`; falsy return values will be interpreted as
		 *                              failures to delete.
		 * @param array|int $to_delete  An array of post IDs to delete.
		 * @param bool      $background Whether the delete operation will happen in background or not.
		 */
		$callback = apply_filters( 'tribe_repository_delete_callback', 'wp_delete_post', (array) $to_delete, (bool) $background );

		/**
		 * Filters the callback that all repositories should use to delete posts.
		 *
		 * @since 4.9.5
		 *
		 * @param callable  $callback   The callback that should be used to delete each post; defaults
		 *                              to `wp_delete_post`; falsy return values will be interpreted as
		 *                              failures to delete.
		 * @param array|int $to_delete  An array of post IDs to delete.
		 * @param bool      $background Whether the delete operation will happen in background or not.
		 */
		$callback = apply_filters(
			"tribe_repository_{$this->filter_name}_delete_callback",
			$callback,
			(array) $to_delete,
			(bool) $background
		);

		return $callback;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_filter_name() {
		return $this->filter_name;
	}

	/**
	 * Returns the update callback function or method to use to update posts.
	 *
	 * @since 4.9.5
	 *
	 * @param      int|array $to_update  The post ID to update or an array of post IDs to update.
	 * @param bool           $background Whether the callback will be used in background update operations or not.
	 *
	 * @return callable The callback to use.
	 */
	protected function get_update_callback( $to_update, $background = false ) {
		/**
		 * Filters the callback that all repositories should use to update posts.
		 *
		 * @since 4.9.5
		 *
		 * @param callable  $callback   The callback that should be used to update each post; defaults
		 *                              to `wp_update_post`; falsy return values will be interpreted as
		 *                              failures to update.
		 * @param array|int $to_update  An array of post IDs to update.
		 * @param bool      $background Whether the update operation will happen in background or not.
		 */
		$callback = apply_filters( 'tribe_repository_update_callback', 'wp_update_post', (array) $to_update, (bool) $background );

		/**
		 * Filters the callback that all repositories should use to update posts.
		 *
		 * @since 4.9.5
		 *
		 * @param callable  $callback   The callback that should be used to update each post; defaults
		 *                              to `wp_update_post`; falsy return values will be interpreted as
		 *                              failures to update.
		 * @param array|int $to_update  An array of post IDs to update.
		 * @param bool      $background Whether the update operation will happen in background or not.
		 */
		$callback = apply_filters(
			"tribe_repository_{$this->filter_name}_update_callback",
			$callback,
			(array) $to_update,
			(bool) $background
		);

		return $callback;
	}

	/**
	 * {@inheritdoc}
	 */
	public function async_update( array $to_update, $return_promise = true ) {
		$promise = new Tribe__Promise( $this->get_update_callback( $to_update, true ), $to_update );
		if ( ! $return_promise ) {
			// Dispatch it immediately and return the IDs that will be deleted.
			$promise->save()->dispatch();

			return $to_update;
		}

		// Return the promise and let the client do the dispatching.
		return $promise;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_update_fields_aliases() {
		return $this->update_fields_aliases;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_update_fields_aliases( array $update_fields_aliases ) {
		$this->update_fields_aliases = $update_fields_aliases;
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter_postarr_for_update( array $postarr, $post_id ) {
		/**
		 * Filters the post array that will be used for an update.
		 *
		 * @since 4.9.5
		 *
		 * @param array $postarr The post array that will be sent to the update callback.
		 * @param int The post ID if set.
		 */
		return apply_filters( "tribe_repository_{$this->filter_name}_update_postarr", $postarr, $post_id );
	}

	/**
	 * A utility method to cast any PHP error into an exception proper.
	 *
	 * Usage: `set_error_handler( array( $repository, 'cast_error_to_exception' ) );
	 *
	 * @since 4.9.5
	 *
	 * @param int $code The error code.
	 * @param string $message The error message.
	 */
	public function cast_error_to_exception( $code, $message ) {
		throw new RuntimeException( $message, $code );
	}

	/**
	 * {@inheritdoc}
	 */
	public function create() {
		$postarr = $this->filter_postarr_for_create( array_merge( $this->build_postarr(), $this->create_args ) );

		// During the filtering allow extending classes or filters to prevent the create completely.
		if ( false === ( bool ) $postarr ) {
			return false;
		}

		$created = call_user_func( $this->get_create_callback( $postarr ), $postarr );

		$post = $this->format_item( $created );

		return $post instanceof WP_Post && $post->ID === $created ? $post : false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter_postarr_for_create( array $postarr ) {
		 /**
		  * Filters the post array that will be used for the creation of a post
		  * of the type managed by the repository.
		  *
		  * @since 4.9.5
		  *
		  * @param array $postarr The post array that will be sent to the create callback.
		  */
		 return apply_filters( "tribe_repository_{$this->filter_name}_update_postarr", $postarr );
	}

	/**
	 * {@inheritdoc}
	 */
	public function build_postarr( $id = null ) {
		$postarr = [
			'tax_input'  => [],
			'meta_input' => [],
		];

		/*
		 * The check is lax here by design: we leave space for the client code
		 * to use this method to build post arrays; when this is used by the
		 * repository the integrity of `$id` is granted.
		 */
		$is_update = null !== $id && is_numeric( $id );

		// But still let's provide values that make sense.
		if ( $is_update ) {
			$postarr['ID'] = (int) $id;
		}

		foreach ( $this->updates as $key => $value ) {
			if ( is_callable( $value ) ) {
				$value = $value( $id, $key, $this );
			}

			// Allow fields to be aliased
			$key = Tribe__Utils__Array::get( $this->update_fields_aliases, $key, $key );

			if ( ! $this->can_be_updated( $key ) ) {
				throw Tribe__Repository__Usage_Error::because_this_field_cannot_be_updated( $key, $this );
			}

			if ( $this->is_a_post_field( $key ) ) {
				if ( $this->requires_converted_date( $key ) ) {
					$this->update_postarr_dates( $key, $value, $postarr );
				} else {
					$postarr[ $key ] = $value;
				}
			} elseif ( $this->is_a_taxonomy( $key ) ) {
				$taxonomy = get_taxonomy( $key );
				if ( $taxonomy instanceof WP_Taxonomy ) {
					$postarr['tax_input'][ $key ] = Tribe__Utils__Array::list_to_array( $value );
				}
			} else {
				// it's a custom field
				$postarr['meta_input'][ $key ] = $value;
			}
		}

		return $postarr;
	}

	/**
	 * Returns the create callback function or method to use to create posts.
	 *
	 * @since 4.9.5
	 *
	 * @param array    $postarr     The post array that will be used for the creation.
	 *
	 * @return callable The callback to use.
	 */
	protected function get_create_callback( array $postarr ) {
		/**
		 * Filters the callback that all repositories should use to create posts.
		 *
		 * @since 4.9.5
		 *
		 * @param callable $callback    The callback that should be used to create posts; defaults
		 *                              to `wp_insert_post`; non numeric and existing post ID return
		 *                              values will be interpreted as failures to create the post.
		 * @param array    $postarr     The post array that will be used for the creation.
		 */
		$callback = apply_filters( 'tribe_repository_create_callback', 'wp_insert_post', $postarr );

		/**
		 * Filters the callback that all repositories should use to create posts.
		 *
		 * @since 4.9.5
		 *
		 * @param callable $callback    The callback that should be used to create posts; defaults
		 *                              to `wp_insert_post`; non numeric and existing post ID return
		 *                              values will be interpreted as failures to create the post.
		 * @param array    $postarr     The post array that will be used for the creation.
		 */
		$callback = apply_filters(
			"tribe_repository_{$this->filter_name}_create_callback",
			$callback,
			$postarr
		);

		return $callback;
	}

	/**
	 * Returns the create args the repository will use to create posts.
	 *
	 * @since 4.9.5
	 *
	 * @return array The create args the repository will use to create posts.
	 */
	public function get_create_args() {
		return $this->create_args;
	}

	/**
	 * Sets the create args the repository will use to create posts.
	 *
	 * @since 4.9.5
	 *
	 * @param array $create_args The create args the repository will use to create posts.
	 */
	public function set_create_args( array $create_args ) {
		$this->create_args = $create_args;
	}

	/**
	 * Returns a value trying to fetch it from an array first and then
	 * reading it from the meta.
	 *
	 * @since 4.9.5
	 *
	 * @param array    $postarr The array to look into.
	 * @param string   $key     The key to retrieve.
	 * @param int|null $post_id The post ID to fetch the value for.
	 * @param mixed $default The default value to return if nothing was found.
	 *
	 * @return mixed The found value if any.
	 */
	protected function get_from_postarr_or_meta( array $postarr, $key, $post_id = null, $default = null ) {
		$default_value = get_post_meta( $post_id, $key, true );
		if ( '' === $default_value || null === $post_id ) {
			$default_value = $default;
		}

		return Tribe__Utils__Array::get( $postarr['meta_input'], $key, $default_value );
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_display_context( $context = 'default' ) {
		$this->display_context = $context;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function set_render_context( $context = 'default' ) {
		$this->render_context = $context;

		return $this;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_query_for_posts( array $posts ) {
		$posts = array_filter( array_map( 'get_post', $posts ) );
		$query = new \WP_Query();
		// Let's make it look like the posts are the result of a query using `post__in`.
		$query->set( 'post__in', wp_list_pluck( $posts, 'ID' ) );
		$query->found_posts  = count( $posts );
		$query->posts        = $posts;
		$query->post_count   = count( $posts );
		$query->current_post = - 1;

		return $query;
	}

	/**
	 * {@inheritdoc}
	 */
	public function pluck( $field ) {
		$list = new WP_List_Util( $this->all() );

		return $list->pluck( $field );
	}

	/**
	 * {@inheritdoc}
	 */
	public function filter( $args = [], $operator = 'AND' ) {
		$list = new WP_List_Util( $this->all() );

		return $list->filter( $args, $operator );
	}

	/**
	 * {@inheritdoc}
	 */
	public function sort( $orderby = [], $order = 'ASC', $preserve_keys = false ) {
		$list = new WP_List_Util( $this->all() );

		return $list->sort( $orderby, $order, $preserve_keys );
	}

	/**
	 * {@inheritdoc}
	 */
	public function collect() {
		return new Tribe__Utils__Post_Collection( $this->all() );
	}

	/**
	 * Builds the ORM query with the query builder.
	 *
	 * Allow classes extending or decorating the repository to act before
	 * the query is built or replace its building completely.
	 *
	 * @since 4.9.5
	 *
	 * @return WP_Query|null A built query object or `null` if the builder failed or bailed.
	 */
	protected function build_query_with_builder() {
		$built = $this->query_builder->build_query();

		$built->builder = $this->query_builder;

		if ( null !== $built ) {
			$query = $built;
		}

		return $query;
	}

	/**
	 * Builds the ORM query internally, without a query builder.
	 *
	 * @since 4.9.5
	 *
	 * @return WP_Query The built query object.
	 */
	protected function build_query_internally() {
		$query = new WP_Query();

		$query->builder = $this;

		$this->filter_query->set_query( $query );

		/**
		 * Here we merge, not recursively, to allow user-set query arguments
		 * to override the default ones.
		 */
		$query_args = array_merge( $this->default_args, $this->query_args );

		$default_post_status = [ 'publish' ];
		if ( current_user_can( 'read_private_posts' ) ) {
			$default_post_status[] = 'private';
		}

		$query_args['post_status'] = Tribe__Utils__Array::get( $query_args, 'post_status', $default_post_status );

		/**
		 * Filters the query arguments that will be used to fetch the posts.
		 *
		 * @param array    $query_args An array of the query arguments the query will be
		 *                             initialized with.
		 * @param WP_Query $query      The query object, the query arguments have not been parsed yet.
		 * @param          $this       $this This repository instance
		 */
		$query_args = apply_filters( "tribe_repository_{$this->filter_name}_query_args", $query_args, $query, $this );

		/**
		 * Provides a last-ditch effort to override the filtered offset.
		 *
		 * This should only be used if doing creating pagination for performance purposes.
		 *
		 * @since 4.11.0
		 *
		 * @param null|int $filtered_offset Offset parameter setting.
		 * @param array    $query_args      List of query arguments.
		 */
		$filtered_offset = apply_filters( 'tribe_repository_query_arg_offset_override', null, $query_args );

		if ( $filtered_offset || isset( $query_args['offset'] ) ) {
			$per_page = (int) Tribe__Utils__Array::get( $query_args, 'posts_per_page', get_option( 'posts_per_page' ) );

			if ( $filtered_offset ) {
				$query_args['offset'] = $filtered_offset;
			} elseif ( isset( $query_args['offset'] ) ) {
				$offset = absint( $query_args['offset'] );
				$page   = (int) Tribe__Utils__Array::get( $query_args, 'paged', 1 );

				$real_offset          = $per_page === -1 ? $offset : ( $per_page * ( $page - 1 ) ) + $offset;
				$query_args['offset'] = $real_offset;

				/**
				 * Unset the `offset` query argument to avoid applying it multiple times when this method
				 * is used, on the same repository, more than once.
				 */
				unset( $this->query_args['offset'] );
			}

			$query_args['posts_per_page'] = $per_page === -1 ? self::MAX_NUMBER_OF_POSTS_PER_PAGE : $per_page;
		}

		foreach ( $query_args as $key => $value ) {
			$query->set( $key, $value );
		}

		/**
		 * Here process the previously set query modifiers passing them the
		 * query object before it executes.
		 * The query modifiers should modify the query by reference.
		 */
		foreach ( $this->query_modifiers as $arg ) {
			if ( is_object( $arg ) && method_exists( $arg, '__invoke' ) ) {
				// __invoke, assume changes are made by reference
				$arg( $query );
			} elseif ( is_callable( $arg ) ) {
				// assume changes are made by reference
				$arg( $query );
			}
		}

		return $query;
	}

	/**
	 * {@inheritDoc}
	 */
	public function hash( array $settings = [], WP_Query $query = null ) {
		return md5( json_encode( $this->get_hash_data( $settings, $query ) ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_hash_data( array $settings, WP_Query $query = null ) {
		$filters    = $this->current_filters;
		$query_vars = null !== $query
			? $query->query
			: array_merge( $this->default_args, $this->query_args );

		if ( isset( $settings['exclude'] ) ) {
			$filters = array_diff_key(
				$filters,
				array_combine( $settings['exclude'], $settings['exclude'] )
			);
			$query_vars = array_diff_key(
				$query_vars,
				array_combine( $settings['exclude'], $settings['exclude'] )
			);
		}

		if ( isset( $settings['include'] ) ) {
			$filters = array_intersect_key(
				$filters,
				array_combine( $settings['include'], $settings['include'] )
			);
			$query_vars = array_intersect_key(
				$query_vars,
				array_combine( $settings['include'], $settings['include'] )
			);
		}

		Tribe__Utils__Array::recursive_ksort( $filters );
		Tribe__Utils__Array::recursive_ksort( $query_vars );

		return [ 'filters' => $filters, 'query_vars' => $query_vars ];
	}

	/**
	 * {@inheritDoc}
	 */
	public function get_last_built_query() {
		return $this->last_built_query;
	}

	/**
	 * Checks a SQL relation is valid.
	 *
	 * Allowed values are 'OR' and 'AND'.
	 *
	 * @since 4.9.6
	 *
	 * @param string $relation The relation to check.
	 *
	 * @throws \Tribe__Repository__Usage_Error If the relation is not a valid one.
	 */
	protected function validate_relation( $relation ) {
		if ( ! in_array( $relation, [ 'OR', 'AND' ], true ) ) {
			throw Tribe__Repository__Usage_Error::because_this_relation_is_not_valid( $relation );
		}
	}

	/**
	 * Sanitizes and prepares string to be used in a LIKE comparison.
	 *
	 * If no leading and trailing `%` was found it will be added at the start and end of the string.
	 *
	 * @since 4.9.6
	 *
	 * @param string|array $value The string to prepare or an array of strings to prepare.
	 *
	 * @return string|array The sanitized string, or strings.
	 */
	protected function prepare_like_string( $value ) {
		$original_value = $value;
		$values = (array) $value;
		$prepared = [];
		$pattern = '/^(?<pre>%{0,1})(?<string>.*?)(?<post>%{0,1})$/u';

		global $wpdb;

		foreach ( $values as $v ) {
			preg_match( $pattern, $v, $matches );
			$pre = $matches['pre'] ?: '';
			$post = $matches['post'] ?: '';
			$string = $wpdb->esc_like( $matches['string'] );

			if ( '' === $pre && '' === $post ) {
				// If the string does not contain any starting and ending placeholder we'll add all combinations.
				$prepared[] = '%' . $string;
				$prepared[] = $string . '%';
				$prepared[] = $string;
				$pre = $post = '%';
			}

			$prepared[] = $pre . $string . $post;
		}

		return is_array( $original_value ) ? $prepared : reset( $prepared );
	}

	/**
	 * Builds the WHERE clause for a set of fields.
	 *
	 * This method is table-agnostic. While flexible it will also require some care to be used.
	 *
	 * @since 4.9.6
	 *
	 * @param string|array $fields  One or more fields to build the clause for.
	 * @param string       $compare The comparison operator to use to build the
	 * @param string|array $values One or more values to build the WHERE clause for.
	 * @param string       $value_format The format, a `$wpdb::prepare()` compatible one, to use to format the values.
	 * @param string       $where_relation The relation to apply between each WHERE fragment.
	 * @param string       $value_relation The relation to apply between each value fragment.
	 *
	 * @return string The built WHERE clause.
	 *
	 * @throws \Tribe__Repository__Usage_Error If the relations are not valid or another WHERE building issue happens.
	 */
	protected function build_fields_where_clause(
		$fields,
		$compare,
		$values,
		$value_format = '%s',
		$where_relation = 'OR',
		$value_relation = 'OR'
	) {
		$this->validate_relation( $where_relation );
		$this->validate_relation( $value_relation );
		global $wpdb;
		$fields_where_clauses = [];
		$fields = (array) $fields;
		$values = (array) $values;
		foreach ( $fields as $field ) {
			$value_clauses = [];
			foreach ( $values as $compare_value ) {
				if ( ! is_array( $compare_value ) || count( $compare_value ) === 1 ) {
					$value_clauses[] = $wpdb->prepare(
						"({$field} {$compare} {$value_format})",
						$compare_value
					);
				} else {
					$value_format = implode(
						',',
						array_fill( 0, count( $compare_value ), $value_format )
					);
					$value_clauses[] = $wpdb->prepare(
						"({$field} {$compare} ({$value_format}))",
						$compare_value
					);
				}
			}
			$fields_where_clauses[] = '(' . implode( " {$value_relation} ", $value_clauses ) . ')';
		}

		$fields_where = $wpdb->remove_placeholder_escape(
			implode( " {$where_relation} ", $fields_where_clauses )
		);

		return $fields_where;
	}

	/**
	 * Returns the term IDs of terms matching a criteria, the match is made on the terms slug and name.
	 *
	 * This should be used to break-down a query and fetch term IDs, to then use in a "lighter" join, later.
	 *
	 * @since 4.9.6
	 *
	 * @param string|array $taxonomy The taxonomy, or taxonomies, to fetch the terms for.
	 * @param string $compare The comparison operator to use, e.g. 'LIKE' or '=>'.
	 * @param string|array $value An array of values to compare the terms slug or names with.
	 * @param string $relation The relation, either 'OR' or 'AND', to apply to the matching.
	 * @param string $format The format, a `$wpdb::prepare()` supported one, to use to format the values for the query.
	 *
	 * @return array An array of term IDs matching the query, if any.
	 */
	protected function fetch_taxonomy_terms_matches( $taxonomy, $compare, $value, $relation = 'OR', $format = '%s' ) {
		global $wpdb;
		$taxonomies = (array) $taxonomy;
		$values = (array) $value;

		$compare_target = count( $values ) > 1
			? '(' . $this->filter_query->create_interval_of_strings( $values ) . ')'
			: $wpdb->prepare( $format, reset( $values ) );

		$taxonomies_interval = $this->filter_query->create_interval_of_strings( $taxonomies );

		$query = "SELECT  tt.term_taxonomy_id FROM {$wpdb->terms} AS t
			INNER JOIN {$wpdb->term_taxonomy} AS tt ON t.term_id = tt.term_id
			WHERE tt.taxonomy IN ({$taxonomies_interval}) AND
			( t.slug {$compare} {$compare_target} {$relation} t.name {$compare} {$compare_target} )";

		return $wpdb->get_col( $wpdb->remove_placeholder_escape( $query ) );
	}

	/**
	 * {@inheritDoc}
	 */
	public function where_multi( array $fields, $compare, $value, $where_relation = 'OR', $value_relation = 'OR' ) {
		$compare = strtoupper( trim( $compare ) );

		// Check each value is compatible with the comparison operator.
		$values = (array) $value;
		foreach ( $values as $v ) {
			$this->validate_operator_and_values( $compare, 'where_multi', $v );
		}

		global $wpdb;

		if ( in_array( $compare, [ 'LIKE', 'NOT LIKE' ], true ) ) {
			$values = $this->prepare_like_string( $values );
		}

		$where_relation = strtoupper( trim( $where_relation ) );
		$this->validate_relation( $where_relation );
		$value_relation = strtoupper( trim( $value_relation ) );
		$this->validate_relation( $value_relation );

		$post_fields = [];
		$taxonomies = [];

		foreach ( $fields as $field ) {
			if ( $this->is_a_post_field( $field ) ) {
				$post_fields[] = $field;
			} elseif ( array_key_exists( $field, $this->simple_tax_schema ) ) {
				// Handle simple tax schema aliases.
				$schema = $this->simple_tax_schema[ $field ]['taxonomy'];

				if ( ! is_array( $schema ) ) {
					$taxonomies[] = $schema;

					continue;
				}

				// If doing an AND where relation, pass all taxonomies in to be grouped with OR.
				if ( 'AND' === $where_relation ) {
					$this->where_multi( $schema, $compare, $value, 'OR', $value_relation );

					continue;
				}

				foreach ( $schema as $taxonomy ) {
					$taxonomies[] = $taxonomy;
				}
			} elseif ( array_key_exists( $field, $this->simple_meta_schema ) ) {
				// Handle simple meta schema aliases.
				$schema = $this->simple_meta_schema[ $field ]['meta_key'];

				if ( ! is_array( $schema ) ) {
					$custom_fields[] = $schema;

					continue;
				}

				// If doing an AND where relation, pass all meta keys in to be grouped with OR.
				if ( 'AND' === $where_relation ) {
					$this->where_multi( $schema, $compare, $value, 'OR', $value_relation );

					continue;
				}

				foreach ( $schema as $meta_key ) {
					$custom_fields[] = $meta_key;
				}
			} elseif ( $this->is_a_taxonomy( $field ) ) {
				$taxonomies[] = $field;
			} else {
				$custom_fields[] = $field;
			}
		}

		$value_formats = [];

		foreach ( $values as $v ) {
			$value_format = '%d';
			if ( is_string( $v ) ) {
				$value_format = '%s';
			} elseif ( (int) $v !== (float) $v ) {
				$value_format = '%f';
			}
			$value_formats[] = $value_format;
		}

		// If the value formats differ then treat all of them as strings.
		if ( count( array_unique( $value_formats ) ) > 1 ) {
			$value_format = '%s';
		} else {
			$value_format = reset( $value_formats );
		}

		$where = [];

		if ( ! empty( $post_fields ) ) {
			$post_fields = array_map( static function ( $post_field ) use ( $wpdb ) {
				return "{$wpdb->posts}.$post_field";
			}, $post_fields );

			$post_fields_where = $this->build_fields_where_clause(
				$post_fields,
				$compare,
				$values,
				$value_format,
				$where_relation,
				$value_relation
			);

			$wheres[] = $post_fields_where;
		}

		if ( ! empty( $taxonomies ) ) {
			$all_matching_term_ids = [];
			$taxonomy_values = $values;

			if ( in_array( $compare, [ 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN' ], true ) ) {
				// We can use multiple values in the same query.
				$taxonomy_values = [ $values ];
			}

			foreach ( $taxonomy_values as $taxonomy_value ){
				$matching_term_ids = $this->fetch_taxonomy_terms_matches(
					$taxonomies,
					$compare,
					$taxonomy_value,
					$where_relation,
					$value_format
				);

				if ( empty( $matching_term_ids ) ) {
					if ( 'AND' === $value_relation ) {
						// No reason to waste any more time.
						$this->void_query = true;

						return $this;
					}

					continue;
				}

				$all_matching_term_ids[] = $matching_term_ids;
			}

			$intersection = count( $all_matching_term_ids ) > 1
				? array_intersect( ...$all_matching_term_ids )
				: reset( $all_matching_term_ids );

			if ( 'AND' === $where_relation && 0 === count( $intersection ) ) {
				// Let's not waste any more time.
				$this->void_query = true;

				return $this;
			}

			$merge = count( $all_matching_term_ids ) > 1
				? array_unique( array_merge( ...$all_matching_term_ids ) )
				: (array) reset( $all_matching_term_ids );
			$matching_term_ids = $where_relation === 'OR' ? array_filter( $merge ) : array_filter( $intersection );

			if ( 'AND' === $where_relation || ! empty( $matching_term_ids ) ) {
				// Let's not add WHERE and JOIN clauses if there is nothing to add.
				$tt_alias = 'tribe_tt_' . self::$alias_counter ++;
				$this->filter_query->join(
					"JOIN {$wpdb->term_relationships} {$tt_alias} ON {$wpdb->posts}.ID = {$tt_alias}.object_id"
				);
				$matching_term_ids_interval = implode( ',', $matching_term_ids );
				$wheres[] = "{$tt_alias}.term_taxonomy_id IN ({$matching_term_ids_interval})";
			}
		}

		if ( ! empty( $custom_fields ) ) {
			$meta_alias = 'tribe_meta_' . self::$alias_counter ++;

			$custom_fields = array_map( static function ( $custom_field ) use ( $wpdb, $meta_alias ) {
				return $wpdb->prepare(
					"{$meta_alias}.meta_key = %s AND {$meta_alias}.meta_value",
					$custom_field
				);
			}, $custom_fields );

			$meta_where = $this->build_fields_where_clause(
				$custom_fields,
				$compare,
				$values,
				$value_format,
				$where_relation,
				$value_relation
			);

			$this->filter_query->join(
				"JOIN {$wpdb->postmeta} {$meta_alias} ON {$wpdb->posts}.ID = {$meta_alias}.post_id"
			);

			$wheres[] = $meta_where;
		}

		$this->filter_query->where( implode( " {$where_relation} ", $wheres ) );

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_query( WP_Query $query ) {
		if (
			$this->last_built_query instanceof WP_Query
			&& !empty($this->last_built_query->request)
		){
			throw Tribe__Repository__Usage_Error::because_query_cannot_be_set_after_it_ran();
		}
		$this->last_built_query = $query;
		$this->last_built_hash  = $this->hash();

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function set_found_rows( $found_rows ) {
		$this->skip_found_rows = ! $found_rows;

		return $this;
	}

	/**
	 * Flush current filters and query information.
	 *
	 * @since 4.9.10
	 *
	 * @return self
	 */
	public function flush() {
		$this->current_query    = null;
		$this->current_filters  = [];
		$this->current_filter   = null;
		$this->last_built_query = null;
		$this->last_built_hash  = '';

		return $this;
	}

	/**
	 * {@inheritDoc}
	 */
	public function next() {
		$next         = clone $this;
		$current_page = isset( $this->query_args['paged'] )
			? (int) $this->query_args['paged']
			: 1;
		$next->page( $current_page + 1 );

		// Let's try to avoid running a query if we already know if a next page will yield any result or not.
		$query_ran = ! empty( $this->last_built_query ) && ! empty( $this->last_built_query->request );
		if ( $query_ran && ( false === (bool) $this->last_built_query->get( 'no_found_rows' ) ) ) {
			$found             = $this->last_built_query->found_posts;
			$posts_per_page    = $this->last_built_query->get( 'posts_per_page' );
			$this_is_last_page = ( $current_page * $posts_per_page ) >= $found;
			if ( $this_is_last_page ) {
				$next->void_query = true;
			}
		}

		$next->last_built_query = null;

		return $next;
	}

	/**
	 * {@inheritDoc}
	 */
	public function prev() {
		$prev         = clone $this;
		$current_page = isset( $this->query_args['paged'] )
			? (int) $this->query_args['paged']
			: 1;

		if ( $current_page === 1 ) {
			$prev->void_query = true;

			return $prev;
		}

		// If we're on page 1 we know there will be previous posts.
		$prev->page( $current_page - 1 );
		$prev->last_built_query = null;

		return $prev;
	}

	/**
	 * {@inheritDoc}
	 */
	public function void_query( $void_query = true ) {
		$this->void_query = (bool) $void_query;

		return $this;
	}
}
