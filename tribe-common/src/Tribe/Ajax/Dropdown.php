<?php

/**
 * Handles common AJAX operations.
 *
 * @since  4.6
 */
class Tribe__Ajax__Dropdown {

	/**
	 * Hooks the AJAX for Select2 Dropdowns
	 *
	 * @since  4.6
	 *
	 * @return void
	 */
	public function hook() {
		add_action( 'wp_ajax_tribe_dropdown', [ $this, 'route' ] );
		add_action( 'wp_ajax_nopriv_tribe_dropdown', [ $this, 'route' ] );
	}

	/**
	 * Search for Terms using Select2
	 *
	 * @since  4.6
	 *
	 * @param string|array<string|mixed> $search Search string from Select2
	 * @param int                        $page   When we deal with pagination
	 * @param array<string|mixed>        $args   Which arguments we got from the Template
	 * @param string                     $source What source it is
	 *
	 * @return array<string|mixed>
	 */
	public function search_terms( $search, $page, $args, $source ) {
		$data = [];

		if ( empty( $args['taxonomy'] ) ) {
			$this->error( esc_attr__( 'Cannot look for Terms without a taxonomy', 'tribe-common' ) );
		}

		// We always want all the fields so we overwrite it
		$args['fields']     = isset( $args['fields'] ) ? $args['fields'] : 'all';
		$args['hide_empty'] = isset( $args['hide_empty'] ) ? $args['hide_empty'] : false;

		if ( ! empty( $search ) ) {
			if ( ! is_array( $search ) ) {
				// For older pieces that still use Select2 format.
				$args['search'] = $search;
			} else {
				// Newer SelectWoo uses a new search format.
				$args['search'] = $search['term'];
			}
		}

		// On versions older than 4.5 taxonomy goes as an Param
		if ( version_compare( $GLOBALS['wp_version'], '4.5', '<' ) ) {
			$terms = get_terms( $args['taxonomy'], $args );
		} else {
			$terms = get_terms( $args );
		}

		$results = [];

		// Respect the parent/child_of argument if set
		$parent = ! empty( $args['child_of'] ) ? (int) $args['child_of'] : 0;
		$parent = ! empty( $args['parent'] ) ? (int) $args['parent'] : $parent;

		if ( empty( $args['search'] ) ) {
			$this->sort_terms_hierarchically( $terms, $results, $parent );
			$results = $this->convert_children_to_array( $results );
		} else {
			foreach ( $terms as $term ) {
				// Prep for Select2
				$term->id          = $term->term_id;
				$term->text        = $term->name;
				$term->breadcrumbs = [];

				if ( 0 !== (int) $term->parent ) {
					$ancestors = get_ancestors( $term->id, $term->taxonomy );
					$ancestors = array_reverse( $ancestors );
					foreach ( $ancestors as $ancestor ) {
						$ancestor            = get_term( $ancestor );
						$term->breadcrumbs[] = $ancestor->name;
					}
				}

				$results[] = $term;
			}
		}

		$data['results']    = $results;
		$data['taxonomies'] = get_taxonomies();

		return $data;
	}

	/**
	 * Search for Posts using Select2
	 *
	 * @since  4.12.17
	 *
	 * @param string|array<string,mixed> $search   Search string from Select2.
	 * @param int                        $page     Page we want when we're dealing with pagination.
	 * @param array<string,mixed>        $args     Arguments to pass to the query.
	 * @param string|int                 $selected Selected item ID.
	 *
	 * @return array<string|mixed>        An Array of results.
	 */
	public function search_posts( $search, $page = 1, $args = [], $selected = null ) {
		if ( ! empty( $search ) ) {
			if ( is_array( $search ) ) {
				// Newer SelectWoo uses a new search format.
				$args['s'] = $search['term']; // post?
			} else {
				// For older pieces that still use Select2 format.
				$args['s'] = $search;
			}
		}

		$args['paged']                  = $page;
		$args['update_post_meta_cache'] = false;
		$args['update_post_term_cache'] = false;

		$results        = new WP_Query( $args );
		$has_pagination = $results->post_count < $results->found_posts;

		return $this->format_posts_for_dropdown( $results->posts, $selected, $has_pagination );
	}

	/**
	 * Formats a given array of posts to be displayed into the Dropdown.js module with SelectWoo.
	 *
	 * @since 4.12.17
	 *
	 * @param array<WP_Post>    $posts
	 * @param null|int $selected
	 * @param boolean  $pagination
	 *
	 * @return array
	 */
	public function format_posts_for_dropdown( array $posts, $selected = null, $pagination = false ) {
		$data = [
			'posts'      => [],
			'pagination' => $pagination,
		];

		// Skip when we don't have posts
		if ( empty( $posts ) ) {
			return $data;
		}

		foreach ( $posts as $post ) {
			if ( ! $post instanceof \WP_Post ) {
				$post = get_post( $post );
			}

			// Skip non WP Post Objects.
			if ( ! $post instanceof \WP_Post ) {
				continue;
			}

			// Prep for Select2.
			$data['posts'][] = [
				'id'       => $post->ID,
				'text'     => ! empty( $post->post_title_formatted ) ? $post->post_title_formatted : $post->post_title,
				'selected' => ! empty( $selected ) && (int) $post->ID === (int) $selected,
			];
		}

		return $data;
	}

	/**
	 * Sorts all the Terms for Select2 hierarchically.
	 *
	 * @since  4.6
	 *
	 * @param array<int|object>   &$terms  Array of Terms from `get_terms`.
	 * @param array<string|mixed> &$into   Variable where we will store the.
	 * @param integer              $parent Used for the recursion.
	 *
	 * @return array<string|mixed>
	 */
	public function sort_terms_hierarchically( &$terms, &$into, $parent = 0 ) {
		foreach ( $terms as $i => $term ) {
			if ( $term->parent === $parent ) {
				// Prep for Select2
				$term->id   = $term->term_id;
				$term->text = $term->name;

				$into[ $term->term_id ] = $term;
				unset( $terms[ $i ] );
			}
		}

		foreach ( $into as $term ) {
			$term->children = [];
			$this->sort_terms_hierarchically( $terms, $term->children, $term->term_id );
		}
	}

	/**
	 * Makes sure we have arrays for the JS data for Select2
	 *
	 * @since  4.6
	 *
	 * @param object|array<string|mixed> $results The Select2 results
	 *
	 * @return array<string|mixed>
	 */
	public function convert_children_to_array( $results ) {
		if ( isset( $results->children ) ) {
			$results->children = $this->convert_children_to_array( $results->children );
			if ( empty( $results->children ) ) {
				unset( $results->children );
			}
		} else {
			foreach ( $results as $key => $item ) {
				$item = $this->convert_children_to_array( $item );
			}
		}

		if ( empty( $results ) ) {
			return [];
		}

		return array_values( (array) $results );
	}

	/**
	 * Parses the Params coming from Select2 Search box
	 *
	 * @since  4.6
	 *
	 * @param array<string|mixed> $params Params to overwrite the defaults
	 *
	 * @return object
	 */
	public function parse_params( $params ) {
		$defaults = [
			'search' => null,
			'page'   => 0,
			'args'   => [],
			'source' => null,
		];

		$arguments = wp_parse_args( $params, $defaults );

		// Return Object just for the sake of making it simpler to read
		return (object) $arguments;
	}

	/**
	 * The default Method that will route all the AJAX calls from our Dropdown AJAX requests
	 * It is like a Catch All on `wp_ajax_tribe_dropdown` and `wp_ajax_nopriv_tribe_dropdown`
	 *
	 * @since  4.6
	 *
	 * @return void
	 */
	public function route() {
		// Push all POST params into a Default set of data
		$args = $this->parse_params( empty( $_POST ) ? [] : $_POST );

		if ( empty( $args->source ) ) {
			$this->error( esc_attr__( 'Missing data source for this dropdown', 'tribe-common' ) );
		}

		// Define a Filter to allow external calls to our Select2 Dropdowns.
		$filter = sanitize_key( 'tribe_dropdown_' . $args->source );
		if ( has_filter( $filter ) ) {
			$data = apply_filters( $filter, [], $args->search, $args->page, $args->args, $args->source );
		} else {
			$data = call_user_func_array( [ $this, $args->source ], (array) $args );
		}

		// If we've got a empty dataset we return an error.
		if ( empty( $data ) ) {
			$this->error( esc_attr__( 'Empty data set for this dropdown', 'tribe-common' ) );
		} else {
			$this->success( $data );
		}
	}

	/**
	 * Prints a success message and ensures that we don't hit bugs on Select2
	 *
	 * @since 4.6
	 *
	 * @param array $data
	 *
	 * @return void
	 */
	private function success( $data ) {
		// We need a Results item for Select2 Work
		if ( ! isset( $data['results'] ) ) {
			$data['results'] = [];
		}

		wp_send_json_success( $data );
	}

	/**
	 * Prints an error message and ensures that we don't hit bugs on Select2
	 *
	 * @since  4.6
	 *
	 * @param string $message
	 *
	 * @return void
	 */
	private function error( $message ) {
		$data = [
			'message' => $message,
			'results' => [],
		];

		wp_send_json_error( $data );
	}

	/**
	 * Avoid throwing fatals or notices on sources that are invalid
	 *
	 * @since  4.6
	 *
	 * @param string $name
	 * @param mixed  $arguments
	 *
	 * @return void
	 */
	public function __call( $name, $arguments ) {
		$message = __( 'The "%s" source is invalid and cannot be reached on "%s" instance.', 'tribe-common' );

		return $this->error( sprintf( $message, $name, __CLASS__ ) );
	}
}
