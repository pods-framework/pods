<?php

namespace Tribe\Utils;

/**
 * Class Taxonomy.
 *
 * @since   4.13.0
 *
 * @package Tribe\Utils
 */
class Taxonomy {
	/**
	 * Match any operand.
	 *
	 * @since 4.13.0
	 * @since 4.13.0
	 *
	 * @var string
	 */
	const OPERAND_OR = 'OR';

	/**
	 * Match all operand.
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	const OPERAND_AND = 'AND';

	/**
	 * Default operand for taxonomy filters.
	 *
	 * @since 4.13.0
	 *
	 * @var string
	 */
	const DEFAULT_OPERAND = self::OPERAND_OR;

	/**
	 * Translates a given argument to repository arguments.
	 *
	 * @since 4.13.0
	 *
	 * @param string       $taxonomy Which taxonomy we are using to setup.
	 * @param string|array $terms    Which terms we are going to use here.
	 * @param string       $operand  Which is the operand we should use.
	 *
	 * @return array A fully qualified `tax_query` array, merge using `array_merge_recursively`.
	 */
	public static function translate_to_repository_args( $taxonomy, $terms, $operand = self::OPERAND_OR ) {
		$tax_query = [];
		// Prevent empty values from even trying.
		if ( empty( $taxonomy ) ) {
			return $tax_query;
		}

		// Prevent empty values from even trying.
		if ( empty( $terms ) ) {
			return $tax_query;
		}

		$repo = tribe_events();

		$operation = static::OPERAND_AND === $operand ? 'term_and' : 'term_in';

		$repo->by( $operation, $taxonomy, $terms );

		// This will only build the query not execute it.
		$built_query = $repo->build_query();

		if ( ! empty( $built_query->query_vars['tax_query'] ) ) {
			$tax_query = $built_query->query_vars['tax_query'];
		}

		return $tax_query;
	}

	/**
	 * Transform all Term IDs and Slugs into IDs of existing terms in a given taxonomy.
	 *
	 * @since 4.13.0
	 *
	 * @param string|int|array $terms    Terms to be cleaned up.
	 * @param string           $taxonomy Which taxonomy we are querying for.
	 *
	 * @return array List of IDs of terms.
	 */
	public static function normalize_to_term_ids( $terms, $taxonomy ) {
		if ( empty( $terms ) ) {
			return $terms;
		}

		/**
		 * Allow filtering of the needle for splitting terms, by default it will be a comma.
		 *
		 * @since 4.13.0
		 *
		 * @param string       $needle   Defaults to a comma. Which character that we will split terms by.
		 * @param string|array $terms    Terms string that we will split by the needle filtered.
		 * @param string       $taxonomy Which taxonomy this will be for.
		 */
		$needle = apply_filters( 'tribe_normalize_to_term_ids_needle', ',', $terms, $taxonomy );
		if ( is_string( $terms ) && false !== strpos( $terms, $needle ) ) {
			$terms = array_map( 'trim', explode( $needle, $terms ) );
		}

		$terms = array_map( static function ( $param ) use ( $taxonomy ) {
			$param   = preg_replace( '/^#/', '', $param );
			$term_by = is_numeric( $param ) ? 'ID' : 'slug';
			$term    = get_term_by( $term_by, $param, $taxonomy );

			if ( ! $term instanceof \WP_Term ) {
				return false;
			}

			return $term->term_id;
		}, (array) $terms );

		$terms = array_filter( $terms );
		$terms = array_unique( $terms );

		return $terms;
	}


	/**
	 * When dealing with templates that make use of `get_post_class` the taxonomy + terms queries are very inefficient
	 * so this method primes the caching by doing a single query that will build the cache for all Posts involved on
	 * the template we are about to render, reducing about 2 queries for each Post that we prime the cache for.
	 *
	 * Important to note that
	 *
	 * @since 5.0.0
	 *
	 * @param array $posts
	 * @param array $taxonomies
	 * @param bool  $prime_term_meta
	 *
	 * @return array<int, array>
	 */
	public static function prime_term_cache( array $posts = [], array $taxonomies = [ 'post_tag', \Tribe__Events__Main::TAXONOMY ], bool $prime_term_meta = false ): array {
		$first = reset( $posts );
		$is_numeric = ( ! $first instanceof \WP_Post );
		if ( $is_numeric ) {
			$ids = $posts;
		} else {
			$ids = wp_list_pluck( $posts, 'ID' );
		}
		$cache = [];

		// Build the base cache.
		foreach ( $ids as $id ) {
			foreach ( $taxonomies as $taxonomy ) {
				$cache[ $id ][ $taxonomy ] = [];
			}
		}

		$args  = [
			'fields'     => 'all_with_object_id',
			'object_ids' => $ids,
			'taxonomy'   => $taxonomies,
		];
		$terms = get_terms( $args );

		// Drop invalid results.
		$valid_terms = array_filter( $terms, static function ( $term ) {
			return $term instanceof \WP_Term;
		} );

		$term_ids = wp_list_pluck( $valid_terms, 'term_id' );

		foreach ( $valid_terms as $term ) {
			$cache[ $term->object_id ][ $term->taxonomy ][] = $term->term_id;
		}

		foreach ( $cache as $id => $object_taxonomies ) {
			// Skip when invalid object id is passed.
			if ( empty( $id ) ) {
				continue;
			}

			foreach ( $object_taxonomies as $taxonomy => $term_ids ) {
				// Skip when invalid taxonomy is passed.
				if ( empty( $taxonomy ) ) {
					continue;
				}

				// Do not skip when `term_ids` are empty.
				wp_cache_add( $id, $term_ids, $taxonomy . '_relationships' );
			}
		}

		_prime_term_caches( $term_ids, $prime_term_meta );

		return $cache;
	}
}