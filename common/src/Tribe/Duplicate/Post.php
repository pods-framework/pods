<?php

/**
 * Class Tribe__Duplicate__Post
 *
 * Provides the functionality to find an existing post starting from the post data.
 *
 * @since 4.6
 */
class Tribe__Duplicate__Post {
	const AND_OPERATOR = 'AND';
	const OR_OPERATOR = 'OR';

	/**
	 * @var array The columns of the post table.
	 */
	public static $post_table_columns = [
		'ID',
		'post_author',
		'post_date',
		'post_date_gmt',
		'post_content',
		'post_title',
		'post_excerpt',
		'post_status',
		'comment_status',
		'ping_status',
		'post_password',
		'post_name',
		'to_ping',
		'pinged',
		'post_modified',
		'post_modified_gmt',
		'post_content_filtered',
		'post_parent',
		'guid',
		'menu_order',
		'post_type',
		'post_mime_type',
		'comment_count',
	];

	/**
	 * @var array The post fields that should be used to find a duplicate.
	 */
	protected $post_fields = [];

	/**
	 * @var array The custom fields that should be used to find a duplicate.
	 */
	protected $custom_fields = [];

	/**
	 * @var Tribe__Duplicate__Strategy_Factory
	 */
	protected $factory;

	/**
	 * @var string The SQL logic operator that should be used to join the WHERE queries frags.
	 */
	protected $where_operator = self::AND_OPERATOR;

	/**
	 * @var string The post type that should be used to find duplicates.
	 */
	protected $post_type = 'post';

	/**
	 * @var int The limit that should be applied to the number of JOIN in a single query.
	 */
	protected $join_limit = 2;

	/**
	 * Tribe__Duplicate__Post constructor.
	 *
	 * @param Tribe__Duplicate__Strategy_Factory|null $factory
	 *
	 * @since 4.6
	 */
	public function __construct( Tribe__Duplicate__Strategy_Factory $factory = null ) {
		$this->factory = null !== $factory ? $factory : tribe( 'post-duplicate.strategy-factory' );
	}

	/**
	 * Sets the post fields that should be used to find a duplicate in the database.
	 *
	 * Each entry should be in the [ <post field> => [ 'match' => <strategy> ]] format.
	 * If not the strategy will be set to the default one.
	 *
	 * @param array $post_fields
	 *
	 * @since 4.6
	 */
	public function use_post_fields( array $post_fields ) {
		if ( empty( $post_fields ) ) {
			$this->post_fields = [];

			return;
		}

		$cast = $this->cast_to_strategy( $post_fields );
		$this->post_fields = array_intersect_key( $cast, array_combine( self::$post_table_columns, self::$post_table_columns ) );
	}

	/**
	 * Converts an array of fields to the format required by the class.
	 *
	 * @param array $fields
	 *
	 * @return array
	 *
	 * @since 4.6
	 */
	protected function cast_to_strategy( array $fields ) {
		$cast = [];

		foreach ( $fields as $key => $value ) {
			if ( is_numeric( $key ) ) {
				$cast[ $value ] = [ 'match' => 'same' ];
			} elseif ( is_array( $value ) ) {
				if ( ! empty( $value['match'] ) ) {
					$cast[ $key ] = $value;
				} else {
					$cast[ $key ] = array_merge( $value, [ 'match' => 'same' ] );
				}
			}
		}

		return $cast;
	}

	/**
	 * Finds a duplicate with the data provided.
	 *
	 * The more post and custom fields are used to find a match the less likely it is to find one and the more
	 * likely it is for a duplicate to be a good match.
	 *
	 * @param array $postarr An array of post data, post fields and custom fields, that should be used to find the
	 *                       duplicate.
	 *
	 * @return bool|int `false` if a duplicate was not found, the post ID of the duplicate if found.
	 *
	 * @since 4.6
	 */
	public function find_for( array $postarr ) {
		if ( empty( $this->post_fields ) && empty( $this->custom_fields ) ) {
			return false;
		}

		$prepared = $this->prepare_queries( $postarr );

		if ( false === $prepared ) {
			return false;
		}

		$id = false;

		/** @var wpdb $wpdb */
		global $wpdb;
		foreach ( $prepared as $query ) {
			$this_id = $wpdb->get_var( $query );

			if ( self::AND_OPERATOR === $this->where_operator ) {
				if ( empty( $this_id ) ) {
					return false;
				}

				$id = empty( $id )
					? $this_id
					: $this_id == $id;

				if ( empty( $id ) ) {
					return false;
				}
			} else {
				if ( ! empty( $this_id ) ) {
					return $this_id;
				}
			}
		}

		return $id;
	}

	/**
	 * Finds all the duplicates with the data provided.
	 *
	 * The more post and custom fields are used to find a match the less likely it is to find one and the more
	 * likely it is for a duplicate to be a good match.
	 *
	 * @param array $postarr An array of post data, post fields and custom fields, that should be used to find the
	 *                       duplicate.
	 *
	 * @return bool|array `false` if a duplicate was not found, an array of the duplicate post IDs if any were found.
	 *
	 * @since 4.6
	 */
	public function find_all_for( array $postarr ) {
		if ( empty( $this->post_fields ) && empty( $this->custom_fields ) ) {
			return false;
		}

		$prepared = $this->prepare_queries( $postarr );

		if ( false === $prepared ) {
			return false;
		}

		$ids = false;

		/** @var wpdb $wpdb */
		global $wpdb;
		foreach ( $prepared as $query ) {
			$this_ids = $wpdb->get_results( $query );
			$this_ids = ! empty( $this_ids )
				? array_map( 'intval', wp_list_pluck( $this_ids, 'ID' ) )
				: false;

			if ( self::AND_OPERATOR === $this->where_operator ) {
				if ( empty( $this_ids ) ) {
					return false;
				}

				$ids = empty( $ids )
					? $this_ids
					: array_intersect( (array) $ids, (array) $this_ids );

				if ( empty( $ids ) ) {
					return false;
				}
			} else {
				$ids = empty( $ids )
					? $this_ids
					: array_unique( array_merge( (array) $ids, array_filter( (array) $this_ids ) ) );
			}
		}

		return $ids;
	}

	/**
	 * Sets the custom fields that should be used to find a duplicate in the database.
	 *
	 * Each entry should be in the [ <custom field> => [ 'match' => <strategy> ]] format.
	 * If not the strategy will be set to the default one.
	 *
	 * @param array $custom_fields
	 *
	 * @since 4.6
	 */
	public function use_custom_fields( array $custom_fields ) {
		$cast = $this->cast_to_strategy( $custom_fields );
		$this->custom_fields = $cast;
	}

	/**
	 * Gets the SQL logic operator that will be used to join the WHERE queries frags.
	 *
	 * @return string
	 *
	 * @since 4.6
	 */
	public function get_where_operator() {
		return $this->where_operator;
	}

	/**
	 * Sets the SQL logic operator that should be used to join the WHERE queries frags.
	 *
	 * @param string $where_operator
	 *
	 * @since 4.6
	 */
	public function set_where_operator( $where_operator ) {
		$this->where_operator = self::AND_OPERATOR === strtoupper( $where_operator )
			? self::AND_OPERATOR
			: self::OR_OPERATOR;
	}

	/**
	 * Prepares the query that should be used to query for duplicates according
	 * to the current post and custom fields.
	 *
	 * @param array $postarr
	 *
	 * @return bool|array An array of prepared queries or `false` on failure.
	 *
	 * @since 4.6
	 */
	protected function prepare_queries( array $postarr ) {
		/** @var wpdb $wpdb */
		global $wpdb;

		$where_frags               = [];
		$custom_fields_where_frags = [];
		$join                      = [];

		if ( ! empty( $this->post_fields ) ) {
			$queryable_post_fields = array_intersect_key( $postarr, $this->post_fields );
			if ( empty( $queryable_post_fields ) ) {
				return false;
			}
			foreach ( $queryable_post_fields as $key => $value ) {
				$match_strategy = $this->factory->make( $this->post_fields[ $key ]['match'] );
				$where_frags[] = $match_strategy->where( $key, $postarr[ $key ] );
			}
		}

		if ( ! empty( $this->custom_fields ) ) {
			// we had post fields and found a match
			$queryable_custom_fields = array_intersect_key( $postarr, $this->custom_fields );
			$i = 0;
			foreach ( $queryable_custom_fields as $key => $value ) {
				$match_strategy = $this->factory->make( $this->custom_fields[ $key ]['match'] );
				$meta_value = is_array( $value ) ? reset( $value ) : $value;
				$custom_fields_where_frags[] = $match_strategy->where_custom_field( $key, $meta_value, "pm{$i}" );
				$i ++;
			}
			$count = count( $custom_fields_where_frags );
			for ( $i = 0; $i < $count; $i ++ ) {
				$join[] = " \nLEFT JOIN {$wpdb->postmeta} pm{$i} ON pm{$i}.post_id = {$wpdb->posts}.ID ";
			}
		}

		/**
		 * Filters the JOIN limit.
		 *
		 * @param int    $join_limit  How many joins will be made per query at most.
		 * @param array  $where_frags The WHERE components for this duplicate search query
		 * @param string $post_type   The post type that's being used for this duplicate search query.
		 *
		 * @since 4.6
		 */
		$join_limit = apply_filters( 'tribe_duplicate_post_join_limit', $this->join_limit, $where_frags, $this->post_type );

		$excluded_status = [
			'trash',
			'autodraft',
		];

		/**
		 * Filters the excluded status.
		 *
		 * @param array  $excluded_status The list of post_status to exclude from the query.
		 * @param string $post_type       The post type that's being used for this duplicate search query.
		 *
		 * @since 4.6
		 */
		$excluded_status = apply_filters( 'tribe_duplicate_post_excluded_status', $excluded_status, $where_frags, $this->post_type );

		$post_type_conditional = $wpdb->prepare( "{$wpdb->posts}.post_type = %s", $this->post_type );

		$post_status_conditional = '';

		if ( $excluded_status ) {
			$in_string = array_fill( 0, count( $excluded_status ), '%s' );
			$in_string = implode( ', ', $in_string );

			// @codingStandardsIgnoreLine
			$post_status_conditional = $wpdb->prepare( "{$wpdb->posts}.post_status NOT IN ( {$in_string} )", $excluded_status );
		}

		$queries = [];

		if ( ! empty( $join_limit ) && ! empty( $join ) ) {
			while ( count( $join ) ) {
				$current_wheres = array_splice( $custom_fields_where_frags, 0, $join_limit );
				$current_joins = array_splice( $join, 0, $join_limit );

				$this_join = implode( "\n", $current_joins );

				$this_where = "\n" . implode( " \n{$this->where_operator} ", array_merge( $where_frags, $current_wheres ) );
				$this_where = sprintf( '%s AND (%s)', $post_type_conditional, $this_where );

				if ( '' !== $post_status_conditional ) {
					$this_where = sprintf( '%s AND %s', $post_status_conditional, $this_where );
				}

				$queries[] = "SELECT DISTINCT {$wpdb->posts}.ID from {$wpdb->posts} {$this_join} \nWHERE {$this_where}";
			}
		} else {
			$where = implode( " \n{$this->where_operator} ", $where_frags );
			$where = sprintf( '%s AND (%s)', $post_type_conditional, $where );

			if ( '' !== $post_status_conditional ) {
				$where = sprintf( '%s AND %s', $post_status_conditional, $where );
			}

			$queries[] = "SELECT DISTINCT {$wpdb->posts}.ID from {$wpdb->posts} \nWHERE {$where}";
		}


		return $queries;
	}

	/**
	 * Gets the post type that will be used to find duplicates.
	 *
	 * @return string
	 *
	 * @since 4.6
	 */
	public function get_post_type() {
		return $this->post_type;
	}

	/**
	 * Sets the post type that should be used to find duplicates.
	 *
	 * @param string $post_type
	 *
	 * @since 4.6
	 */
	public function set_post_type( $post_type ) {
		$this->post_type = $post_type;
	}

	/**
	 * Sets the limit that should be applied to the number of JOIN in a single query.
	 *
	 * Setting the limit to an empty value will remove the limit (very bad idea).
	 *
	 * @param int $join_limit
	 *
	 * @since 4.6
	 */
	public function set_join_limit( $join_limit ) {
		$this->join_limit = empty( $join_limit )
			? 999
			: intval( $join_limit );
	}

	/**
	 * Returns the limit that will be applied to the number of JOIN in a single query.
	 *
	 * @return int
	 *
	 * @since 4.6
	 */
	public function get_join_limit() {
		return $this->join_limit;
	}
}
