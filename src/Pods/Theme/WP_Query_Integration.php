<?php

namespace Pods\Theme;

use Exception;
use WP_Query;

/**
 * WP_Query specific functionality.
 *
 * @since 2.8.0
 */
class WP_Query_Integration {

	/**
	 * Add the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function hook() {
		add_action( 'pre_get_posts', [ $this, 'show_cpt_on_core_taxonomy_archive' ] );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 2.8.0
	 */
	public function unhook() {
		remove_action( 'pre_get_posts', [ $this, 'show_cpt_on_core_taxonomy_archive' ] );
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

}
