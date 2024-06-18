<?php

namespace Pods\WP\UI;

use Pods\Whatsit\Pod;
use WP_Query;
use WP_Taxonomy;

/**
 * Bindings specific functionality.
 *
 * @since 3.2.2
 */
class Taxonomy_Filter {

	/**
	 * Add the class hooks.
	 *
	 * @since 3.2.2
	 */
	public function hook(): void {
		add_action( 'restrict_manage_posts', [ $this, 'render_filters' ], 10, 2 );
		add_action( 'pre_get_posts', [ $this, 'filter_query' ] );
	}

	/**
	 * Remove the class hooks.
	 *
	 * @since 3.2.2
	 */
	public function unhook(): void {
		remove_action( 'restrict_manage_posts', [ $this, 'render_filters' ] );
		remove_action( 'pre_get_posts', [ $this, 'filter_query' ] );
	}

	/**
	 * Render the filters for any associated taxonomies.
	 *
	 * @since 3.2.2
	 *
	 * @param string $post_type  The post type slug.
	 * @param string $which      The location of the extra table nav markup:
	 *                           'top' or 'bottom' for WP_Posts_List_Table,
	 *                           'bar' for WP_Media_List_Table.
	 */
	public function render_filters( string $post_type, string $which = '' ): void {
		if ( 'top' !== $which ) {
			return;
		}

		$taxonomies = get_object_taxonomies( $post_type, 'objects' );

		$api = pods_api();

		foreach ( $taxonomies as $taxonomy ) {
			$pod = $api->load_pod( [
				'name' => $taxonomy->name,
			] );

			if ( ! $pod || ! $this->should_use_filter( $pod ) ) {
				continue;
			}

			$this->render_filter( $taxonomy );
		}
	}

	/**
	 * Render the filters for any associated taxonomies.
	 *
	 * @since 3.2.2
	 *
	 * @param WP_Taxonomy $taxonomy The taxonomy object.
	 */
	public function render_filter( WP_Taxonomy $taxonomy ): void {
		$filter_name = $this->get_filter_name( $taxonomy );

		$dropdown_options = array(
			'name'            => $filter_name,
			'taxonomy'        => $taxonomy->name,
			'show_option_all' => $taxonomy->labels->all_items,
			'hide_empty'      => 0,
			'hierarchical'    => (int) $taxonomy->hierarchical,
			'show_count'      => 0,
			'orderby'         => 'name',
			'selected'        => (int) pods_v( $filter_name ),
		);

		printf(
			'<label class="screen-reader-text" for="%s">%s</label>',
			esc_attr( $filter_name ),
			$taxonomy->labels->filter_by_item
		);

		wp_dropdown_categories( $dropdown_options );
	}

	/**
	 * Filter the query based on the taxonomy filters.
	 *
	 * @since 3.2.2
	 *
	 * @param WP_Query $query The query object.
	 */
	public function filter_query( WP_Query $query ): void {
		if ( ! $query->is_main_query() || ! is_admin() ) {
			return;
		}

		$screen = get_current_screen();

		if ( ! $screen || 'edit' !== $screen->base ) {
			return;
		}

		$post_type = $screen->post_type;

		$taxonomies = get_object_taxonomies( $post_type, 'objects' );

		$api = pods_api();

		foreach ( $taxonomies as $taxonomy ) {
			$pod = $api->load_pod( [
				'name' => $taxonomy->name,
			] );

			if ( ! $pod || ! $this->should_use_filter( $pod ) ) {
				continue;
			}

			$this->filter_query_for_taxonomy( $query, $taxonomy );
		}
	}

	/**
	 * Filter the query based on the taxonomy filters.
	 *
	 * @since 3.2.2
	 *
	 * @param WP_Query    $query    The query object.
	 * @param WP_Taxonomy $taxonomy The taxonomy object.
	 */
	public function filter_query_for_taxonomy( WP_Query $query, WP_Taxonomy $taxonomy ): void {
		$filter_name = $this->get_filter_name( $taxonomy );

		$current_term = (int) pods_v( $filter_name );

		if ( ! $current_term ) {
			return;
		}

		$tax_query = $query->get( 'tax_query', [] );

		$tax_query[] = [
			'taxonomy' => $taxonomy->name,
			'field'    => 'term_id',
			'terms'    => $current_term,
		];

		$query->set( 'tax_query', $tax_query );
	}

	/**
	 * Determine whether to use the taxonomy filter.
	 *
	 * @param Pod $pod The pod object.
	 *
	 * @return bool Whether to use the taxonomy filter.
	 */
	public function should_use_filter( Pod $pod ): bool {
		return (
			$pod->get_type() === 'taxonomy'
			&& 1 === (int) $pod->get_arg( 'show_admin_column_filter' )
		);
	}

	/**
	 * Get the filter name to use for the taxonomy.
	 *
	 * @param WP_Taxonomy $taxonomy The taxonomy object.
	 *
	 * @return string The filter name to use for the taxonomy.
	 */
	public function get_filter_name( WP_Taxonomy $taxonomy ): string {
		return 'pods_filter_' . $taxonomy->name;
	}

}
