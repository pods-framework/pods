<?php

namespace Pods\Theme;

// Don't load directly.
if ( ! defined( 'ABSPATH' ) ) {
	die( '-1' );
}

use Exception;
use Pods\Whatsit\Field;
use Pods\Whatsit\Pod;
use WP_Query;

/**
 * WP_Query specific functionality.
 *
 * @since 2.8.0
 */
class WP_Query_Integration {

	/**
	 * Pod lookup cache keyed by post_type to avoid repeat load_pod() calls per request.
	 *
	 * @since 3.4.0
	 *
	 * @var array<string, Pod|null>
	 */
	protected static $pod_for_post_type_cache = [];

	/**
	 * Add the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function hook() {
		add_action( 'pre_get_posts', [ $this, 'show_cpt_on_core_taxonomy_archive' ] );
		add_action( 'pre_get_posts', [ $this, 'maybe_collect_table_meta_query' ], 20 );
		add_filter( 'posts_clauses', [ $this, 'rewrite_meta_query_clauses' ], 20, 2 );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function unhook() {
		remove_action( 'pre_get_posts', [ $this, 'show_cpt_on_core_taxonomy_archive' ] );
		remove_action( 'pre_get_posts', [ $this, 'maybe_collect_table_meta_query' ], 20 );
		remove_filter( 'posts_clauses', [ $this, 'rewrite_meta_query_clauses' ], 20, 2 );
	}

	/**
	 * Show Custom Post Type on core Taxonomy archive.
	 *
	 * @since 2.8.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 */
	public function show_cpt_on_core_taxonomy_archive( $query ) {
		// Skip on admin screens.
		if ( is_admin() ) {
			return;
		}

		// Skip if not on the archive we want.
		if (
			! empty( $query->query_vars['suppress_filters'] )
			|| ! $query->is_main_query()
			|| $query->is_404()
			|| (
				! $query->is_category()
				&& ! $query->is_tag()
			)
		) {
			return;
		}

		$object = $query->get_queried_object();

		if ( ! isset( $object->taxonomy ) ) {
			return;
		}

		$taxonomy = $object->taxonomy;

		// Find all CPT that have this taxonomy set.
		$api = pods_api();

		try {
			$post_types_to_show = $api->load_pods( [
				'args'  => [
					'archive_show_in_taxonomies_' . $taxonomy => 1,
				],
				'names' => true,
			] );
		} catch ( Exception $exception ) {
			pods_debug_log( $exception );

			return;
		}

		$post_types_to_show = array_keys( $post_types_to_show );

		$existing_post_types = $query->get( 'post_type' );

		if ( empty( $existing_post_types ) ) {
			$existing_post_types = [
				'post',
			];
		} elseif ( ! is_array( $existing_post_types ) ) {
			$existing_post_types = (array) $existing_post_types;
		}

		$post_types_to_show = array_unique( array_merge( $existing_post_types, $post_types_to_show ) );

		$query->set( 'post_type', $post_types_to_show );
	}

	/**
	 * Collect meta_query keys that reference table-based pod fields before WP_Query parses them.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 */
	public function maybe_collect_table_meta_query( $query ) {
		if ( is_admin() || ! empty( $query->query_vars['suppress_filters'] ) ) {
			return;
		}

		if ( function_exists( 'pods_tableless' ) && pods_tableless() ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' );

		if ( empty( $meta_query ) ) {
			return;
		}

		$post_types = $this->resolve_queried_post_types( $query );

		if ( empty( $post_types ) ) {
			return;
		}

		$pods_by_post_type = [];

		foreach ( $post_types as $post_type ) {
			$pod = $this->get_table_based_pod_for_post_type( $post_type );

			if ( null === $pod ) {
				continue;
			}

			$pods_by_post_type[ $post_type ] = $pod;
		}

		if ( empty( $pods_by_post_type ) ) {
			return;
		}

		$specs = $this->collect_table_meta_specs( $meta_query, $pods_by_post_type );

		if ( empty( $specs ) ) {
			return;
		}

		// Stash parsed specs on the query object so posts_clauses can act on them.
		$query->pods_table_meta = $specs;

		// Strip the consumed keys from the meta_query so WP_Meta_Query never builds postmeta joins/where for them.
		// We rebuild the meta_query here (the relation is recomposed by WP_Meta_Query again next time get_sql runs).
		$consumed_keys = array_map( static function ( $spec ) {
			return $spec['meta_query']['key'] ?? '';
		}, $specs );

		$query->set( 'meta_query', $this->remove_keys_from_meta_query( $meta_query, $consumed_keys ) );
	}

	/**
	 * Rebuild a meta_query, dropping clauses whose key we have absorbed.
	 *
	 * @since 3.4.0
	 *
	 * @param array             $meta_query    The original meta_query.
	 * @param array<string>     $consumed_keys Keys to drop.
	 *
	 * @return array The filtered meta_query.
	 */
	protected function remove_keys_from_meta_query( $meta_query, $consumed_keys ) {
		$consumed_lookup = array_flip( array_filter( $consumed_keys, 'strlen' ) );

		$filtered = [];

		foreach ( $meta_query as $key => $clause ) {
			if ( ! is_array( $clause ) ) {
				$filtered[ $key ] = $clause;
				continue;
			}

			if ( $this->is_meta_query_relation_group( $clause ) ) {
				$filtered[ $key ] = $this->remove_keys_from_meta_query( $clause, $consumed_keys );
				continue;
			}

			$clause_key = $clause['key'] ?? '';

			if ( isset( $consumed_lookup[ $clause_key ] ) ) {
				continue;
			}

			$filtered[ $key ] = $clause;
		}

		// Always carry the relation so WP_Meta_Query's grouping stays intact.
		if ( isset( $meta_query['relation'] ) && ! isset( $filtered['relation'] ) ) {
			$filtered['relation'] = $meta_query['relation'];
		}

		return $filtered;
	}

	/**
	 * Rewrite posts_clauses so table-based pod fields use JOINs instead of postmeta.
	 *
	 * @since 3.4.0
	 *
	 * @param array    $clauses The WP_Query SQL clauses.
	 * @param WP_Query $query   The WP_Query instance.
	 *
	 * @return array The possibly modified SQL clauses.
	 */
	public function rewrite_meta_query_clauses( $clauses, $query ) {
		if ( ! isset( $query->pods_table_meta ) || empty( $query->pods_table_meta ) ) {
			return $clauses;
		}

		global $wpdb;

		$specs         = $query->pods_table_meta;
		$alias_counter = 1;
		$join_clauses  = [];
		$where_clauses = [];

		$join  = (string) ( $clauses['join']  ?? '' );
		$where = (string) ( $clauses['where'] ?? '' );

		foreach ( $specs as $spec ) {
			$pod           = $spec['pod'];
			$field         = $spec['field'];
			$meta_query    = $spec['meta_query'];
			$table_info    = $pod->get_table_info();
			$pod_table     = $table_info['pod_table'] ?? null;
			$field_id_col  = $table_info['field_id']    ?? 'id';
			$column        = $field->get_name();

			if ( empty( $pod_table ) ) {
				continue;
			}

			$alias     = 'pdst' . $alias_counter;
			$rel_alias = 'pdstr' . $alias_counter;
			$alias_counter++;

			$is_relationship = $field->is_relationship();

			if ( $is_relationship ) {
				$join_clauses[] = "LEFT JOIN `{$wpdb->prefix}podsrel` AS `{$rel_alias}` ON "
					. "`{$rel_alias}`.`pod_id` = " . (int) $pod->get_id() . ' '
					. "AND `{$rel_alias}`.`field_id` = " . (int) $field->get_id() . ' '
					. "AND `{$rel_alias}`.`item_id` = `{$wpdb->posts}`.`ID`";
			}

			$join_clauses[] = "LEFT JOIN `{$pod_table}` AS `{$alias}` ON "
				. "`{$alias}`.`{$field_id_col}` = `{$wpdb->posts}`.`ID`";

			$where_clauses[] = $this->build_meta_query_where( $meta_query, $alias, $rel_alias, $column, $is_relationship );
		}

		if ( empty( $join_clauses ) ) {
			return $clauses;
		}

		$clauses['join']  = $join . ' ' . implode( ' ', $join_clauses );

		// Group our new WHERE clauses into a single parenthesized block to preserve meta_query relation semantics.
		if ( ! empty( $where_clauses ) ) {
			$combined = implode( ' AND ', array_filter( $where_clauses ) );
			if ( '' !== $combined ) {
				$where .= ' AND (' . $combined . ')';
			}
		}

		$clauses['where']    = $where;
		$clauses['distinct'] = 'DISTINCT';

		/**
		 * Allow third-party plugins to adjust the rewritten SQL clauses.
		 *
		 * @since 3.4.0
		 *
		 * @param array    $clauses The (possibly modified) SQL clauses.
		 * @param WP_Query $query   The WP_Query instance.
		 */
		return apply_filters( 'pods_wp_query_table_meta_clauses', $clauses, $query );
	}

	/**
	 * Resolve the post types targeted by the query, including the default 'post' fallback.
	 *
	 * @since 3.4.0
	 *
	 * @param WP_Query $query The WP_Query instance.
	 *
	 * @return string[] List of post type slugs.
	 */
	protected function resolve_queried_post_types( WP_Query $query ) {
		$post_type = $query->get( 'post_type' );

		if ( empty( $post_type ) ) {
			return [ 'post' ];
		}

		if ( 'any' === $post_type ) {
			return array_values( get_post_types( [ 'public' => true ] ) );
		}

		if ( ! is_array( $post_type ) ) {
			return [ $post_type ];
		}

		return array_values( array_filter( $post_type, 'is_string' ) );
	}

	/**
	 * Load and cache the Pod object for a post type if it is table-based.
	 *
	 * @since 3.4.0
	 *
	 * @param string $post_type The post type slug.
	 *
	 * @return Pod|null The Pod object, or null if not table-based or not registered.
	 */
	protected function get_table_based_pod_for_post_type( $post_type ) {
		if ( isset( self::$pod_for_post_type_cache[ $post_type ] ) ) {
			return self::$pod_for_post_type_cache[ $post_type ];
		}

		$pod = null;

		try {
			$loaded = pods_api()->load_pod( [ 'name' => $post_type ] );
		} catch ( Exception $exception ) {
			pods_debug_log( $exception );
			$loaded = null;
		}

		// ponytail: do not store a "missing pod" sentinel for every post type seen; cache the null once and move on.
		if ( $loaded instanceof Pod && $loaded->is_table_based() ) {
			$pod = $loaded;
		}

		self::$pod_for_post_type_cache[ $post_type ] = $pod;

		return $pod;
	}

	/**
	 * Walk a meta_query and produce specs for every key that resolves to a table-based pod field.
	 *
	 * @since 3.4.0
	 *
	 * @param array             $meta_query        The meta_query argument.
	 * @param array<string,Pod> $pods_by_post_type Map of post type to Pod.
	 *
	 * @return array List of specs, each with field, pod, meta_query data.
	 */
	protected function collect_table_meta_specs( $meta_query, $pods_by_post_type ) {
		$specs = [];

		// Top-level 'relation' is allowed but ignored here; WP_Meta_Query owns the relation composition.
		foreach ( $meta_query as $key => $clause ) {
			if ( ! is_array( $clause ) ) {
				continue;
			}

			// Nested meta_query (relation group).
			if ( $this->is_meta_query_relation_group( $clause ) ) {
				$specs = array_merge( $specs, $this->collect_table_meta_specs( $clause, $pods_by_post_type ) );
				continue;
			}

			$spec = $this->resolve_meta_clause_to_spec( $key, $clause, $pods_by_post_type );

			if ( null !== $spec ) {
				$specs[] = $spec;
			}
		}

		return $specs;
	}

	/**
	 * Determine whether a meta_query entry is a relation group rather than a single clause.
	 *
	 * @since 3.4.0
	 *
	 * @param array $clause The clause.
	 *
	 * @return bool True if this is a nested relation group.
	 */
	protected function is_meta_query_relation_group( $clause ) {
		if ( ! isset( $clause[0] ) ) {
			return false;
		}

		// Relation groups nest another array whose first element is itself an array.
		foreach ( $clause as $candidate ) {
			if ( is_array( $candidate ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Resolve a single meta_query clause to a table-field spec, if any.
	 *
	 * @since 3.4.0
	 *
	 * @param string|int        $key              The meta_query array key.
	 * @param array             $clause           The clause data.
	 * @param array<string,Pod> $pods_by_post_type Map of post type to Pod.
	 *
	 * @return array|null The spec, or null when not resolvable to a table field.
	 */
	protected function resolve_meta_clause_to_spec( $key, $clause, $pods_by_post_type ) {
		if ( ! isset( $clause['key'] ) ) {
			return null;
		}

		// Honor WP_Meta_Query-supported allow-list; bail on anything outside it (it stays on postmeta).
		$compare = strtoupper( (string) ( $clause['compare'] ?? '=' ) );
		if ( ! in_array( $compare, [ '=', '!=', 'LIKE', 'NOT LIKE', 'IN', 'NOT IN', 'BETWEEN', 'NOT BETWEEN', 'EXISTS', 'NOT EXISTS', 'REGEXP', 'NOT REGEXP', '>', '<', '>=', '<=' ], true ) ) {
			return null;
		}

		$field_name = $this->strip_pods_meta_alias( (string) $clause['key'] );

		foreach ( $pods_by_post_type as $pod ) {
			$field = $this->find_pod_field_by_name( $pod, $field_name );

			if ( $field instanceof Field ) {
				return [
					'pod'        => $pod,
					'field'      => $field,
					'meta_query' => $clause,
				];
			}
		}

		return null;
	}

	/**
	 * Strip Pods' meta key alias prefixes (`pods_`, `_pods_`) so users can pass either form.
	 *
	 * @since 3.4.0
	 *
	 * @param string $key The raw meta key.
	 *
	 * @return string The unwrapped field name.
	 */
	protected function strip_pods_meta_alias( $key ) {
		if ( 0 === strpos( $key, '_pods_' ) ) {
			return substr( $key, strlen( '_pods_' ) );
		}

		if ( 0 === strpos( $key, 'pods_' ) ) {
			return substr( $key, strlen( 'pods_' ) );
		}

		return $key;
	}

	/**
	 * Locate a Field by name on a Pod.
	 *
	 * @since 3.4.0
	 *
	 * @param Pod    $pod        The Pod.
	 * @param string $field_name The field name.
	 *
	 * @return Field|null The Field, or null if not found.
	 */
	protected function find_pod_field_by_name( Pod $pod, $field_name ) {
		static $pod_fields_cache = [];

		$cache_key = $pod->get_id() . ':' . $field_name;

		if ( array_key_exists( $cache_key, $pod_fields_cache ) ) {
			return $pod_fields_cache[ $cache_key ];
		}

		$field = null;

		foreach ( $pod->get_fields() as $candidate ) {
			if ( ! $candidate instanceof Field ) {
				continue;
			}

			if ( $candidate->get_name() === $field_name ) {
				$field = $candidate;
				break;
			}
		}

		$pod_fields_cache[ $cache_key ] = $field;

		return $field;
	}

	/**
	 * Build the WHERE clause for one parsed meta_query spec.
	 *
	 * @since 3.4.0
	 *
	 * @param array  $meta_query     The meta_query clause data.
	 * @param string $alias          Table JOIN alias for the Pod table.
	 * @param string $rel_alias      JOIN alias for the podsrel table (empty if not a relationship).
	 * @param string $column         Column name on the Pod table.
	 * @param bool   $is_relationship Whether the field is a relationship.
	 *
	 * @return string SQL fragment (empty when compare is unsupported and was skipped).
	 */
	protected function build_meta_query_where( $meta_query, $alias, $rel_alias, $column, $is_relationship ) {
		global $wpdb;

		$compare = strtoupper( (string) ( $meta_query['compare'] ?? '=' ) );
		$value   = $meta_query['value'] ?? '';
		$type    = $meta_query['type'] ?? 'CHAR';

		// Coerce value into the same shape WP_Meta_Query expects, then prepare() it via $wpdb.
		$prepared = $this->prepare_meta_query_value( $value, $type );

		// Relationship traversal matches in podsrel.related_item_id.
		if ( $is_relationship ) {
			$field_expr = "`{$rel_alias}`.`related_item_id`";
		} else {
			$field_expr = "CAST(`{$alias}`.`{$column}` AS {$type})";
		}

		switch ( $compare ) {
			case '=':
			case '!=':
			case '>':
			case '<':
			case '>=':
			case '<=':
				return "{$field_expr} {$compare} {$prepared}";

			case 'LIKE':
			case 'NOT LIKE':
				// LIKE compares strings; cast to CHAR even if WP asked for a numeric type so MySQL doesn't compare numbers.
				return "CAST({$field_expr} AS CHAR) {$compare} {$prepared}";

			case 'IN':
			case 'NOT IN':
				if ( ! is_array( $prepared ) ) {
					return '';
				}
				$list = implode( ', ', $prepared );
				return "{$field_expr} {$compare} ({$list})";

			case 'BETWEEN':
			case 'NOT BETWEEN':
				if ( ! is_array( $prepared ) || count( $prepared ) < 2 ) {
					return '';
				}
				list( $min, $max ) = array_values( $prepared );
				$between_op = ( 'NOT BETWEEN' === $compare ) ? 'NOT BETWEEN' : 'BETWEEN';
				return "{$field_expr} {$between_op} {$min} AND {$max}";

			case 'EXISTS':
				return "{$field_expr} IS NOT NULL";

			case 'NOT EXISTS':
				return "{$field_expr} IS NULL";

			case 'REGEXP':
			case 'NOT REGEXP':
				// WP_Meta_Query normally uses LIKE-style regex against CHAR; mirror that.
				return "CAST({$field_expr} AS CHAR) {$compare} {$prepared}";
		}

		return '';
	}

	/**
	 * Sanitize and prepare a meta_query value in the same style WP_Meta_Query does.
	 *
	 * @since 3.4.0
	 *
	 * @param mixed  $value The raw value.
	 * @param string $type  The cast type (CHAR, NUMERIC, etc.).
	 *
	 * @return string|array SQL-ready placeholder list fragment.
	 */
	protected function prepare_meta_query_value( $value, $type ) {
		global $wpdb;

		$is_string = ( 'CHAR' === $type );

		// For BETWEEN/IN, return a list of placeholders raw so the caller stays in control of grouping.
		if ( is_array( $value ) ) {
			return array_map( static function( $item ) use ( $wpdb, $is_string ) {
				return $is_string ? $wpdb->prepare( '%s', (string) $item ) : $wpdb->prepare( '%d', (int) $item );
			}, $value );
		}

		return $is_string ? $wpdb->prepare( '%s', (string) $value ) : $wpdb->prepare( '%d', (int) $value );
	}

}
