<?php

if ( ! function_exists( 'tribe_array_merge_recursive' ) ) {
	/**
	 * Recursively merge two arrays preserving keys.
	 *
	 * @link http://php.net/manual/en/function.array-merge-recursive.php#92195
	 *
	 * @param array $array1
	 * @param array $array2
	 *
	 * @return array
	 */
	function tribe_array_merge_recursive( array &$array1, array &$array2 ) {
		$merged = $array1;

		foreach ( $array2 as $key => &$value ) {
			if ( is_array( $value ) && isset( $merged [ $key ] ) && is_array( $merged [ $key ] ) ) {
				$merged [ $key ] = tribe_array_merge_recursive( $merged [ $key ], $value );
			} else {
				$merged [ $key ] = $value;
			}
		}

		return $merged;
	}
}


if ( ! function_exists( 'tribe_register_plugin' ) ) {
	/**
	 * Checks if this plugin has permission to run, if not it notifies the admin
	 *
	 * @param string $file_path    Full file path to the base plugin file
	 * @param string $main_class   The Main/base class for this plugin
	 * @param string $version      The version
	 * @param array  $classes_req  Any Main class files/tribe plugins required for this to run
	 * @param array  $dependencies an array of dependencies to check
	 */
	function tribe_register_plugin( $file_path, $main_class, $version, $classes_req = [], $dependencies = [] ) {
		$tribe_dependency = tribe( Tribe__Dependency::class );
		$tribe_dependency->register_plugin( $file_path, $main_class, $version, $classes_req, $dependencies );
	}
}

if ( ! function_exists( 'tribe_check_plugin' ) ) {
	/**
	 * Checks if this plugin has permission to run, if not it notifies the admin
	 *
	 * @since 4.9
	 *
	 * @param string $main_class   The Main/base class for this plugin
	 *
	 * @return bool Indicates if plugin should continue initialization
	 */
	function tribe_check_plugin( $main_class ) {

		$tribe_dependency    = Tribe__Dependency::instance();
		return $tribe_dependency->check_plugin( $main_class );

	}
}

if ( ! function_exists( 'tribe_append_path' ) ) {
	/**
	 * Append a path fragment to a URL preserving query arguments
	 * and fragments.
	 *
	 * @param string $url A full URL in the `http://example.com/?query=var#frag` format.
	 * @param string $path The path to append to the existing, if any, one., e.g. `/some/path`
	 *
	 * @return mixed|string
	 *
	 * @since 4.3
	 */
	function tribe_append_path( $url, $path ) {
		$path = trim( $path, '/' );

		$query = @parse_url( $url, PHP_URL_QUERY );
		$frag  = @parse_url( $url, PHP_URL_FRAGMENT );

		if ( ! ( empty( $query ) && empty( $frag ) ) ) {
			$url   = str_replace( '?' . $query, '', $url );
			$url   = str_replace( '#' . $frag, '', $url );
			$query = $query ? '?' . $query : '';
			$frag  = $frag ? '#' . $frag : '';
		}

		$url = trailingslashit( esc_url_raw( trailingslashit( $url ) . $path ) );
		$url .= $query . $frag;

		return $url;
	}
}

if ( ! function_exists( 'tribe_exit' ) ) {
	/**
	 * Filterable `die` wrapper.
	 *
	 * @param string $status
	 *
	 * @return void|mixed Depending on the handler this function might return
	 *                    a value or `die` before anything is returned.
	 */
	function tribe_exit( $status = '' ) {
		$handler = 'die';

		/**
		 * Filters the callback to call in place of `die()`.
		 *
		 * @param callable $handler The `die` replacement callback.
		 * @param string   $status  The exit/die status.
		 */
		$handler = apply_filters( 'tribe_exit', $handler, $status );

		// Die and exit are language constructs that cannot be used as callbacks on all PHP runtimes
		if ( 'die' === $handler || 'exit' === $handler ) {
			exit ( $status );
		}

		return call_user_func( $handler, $status );
	}
}

if ( ! function_exists( 'tribe_get_request_var' ) ) {
	/**
	 * Tests to see if the requested variable is set either as a post field or as a URL
	 * param and returns the value if so.
	 *
	 * Post data takes priority over fields passed in the URL query. If the field is not
	 * set then $default (null unless a different value is specified) will be returned.
	 *
	 * The variable being tested for can be an array if you wish to find a nested value.
	 *
	 * @since 4.9.17 Included explicit check against $_REQUEST.
	 *
	 * @see   Tribe__Utils__Array::get()
	 *
	 * @param string|array $var
	 * @param mixed        $default
	 *
	 * @return mixed
	 */
	function tribe_get_request_var( $var, $default = null ) {
		$unsafe = Tribe__Utils__Array::get_in_any( [ $_GET, $_POST, $_REQUEST ], $var, $default );
		return tribe_sanitize_deep( $unsafe );
	}
}

if ( ! function_exists( 'tribe_get_global_query_object' ) ) {
	/**
	 * Grabs the $wp_query global in a safe way with some fallbacks that help prevent fatal errors
	 * on sites where themes or other plugins directly manipulate the $wp_query global.
	 *
	 * @since 4.7.8
	 *
	 * @return object The $wp_query, the $wp_the_query if $wp_query empty, null otherwise.
	 */
	function tribe_get_global_query_object() {
		global $wp_query;
		global $wp_the_query;

		if ( ! empty( $wp_query ) ) {
			return $wp_query;
		}

		if ( ! empty( $wp_the_query ) ) {
			return $wp_the_query;
		}

		return null;
	}
}

if ( ! function_exists( 'tribe_is_truthy' ) ) {
	/**
	 * Determines if the provided value should be regarded as 'true'.
	 *
	 * @param mixed $var
	 *
	 * @return bool
	 */
	function tribe_is_truthy( $var ) {
		if ( is_bool( $var ) ) {
			return $var;
		}

		/**
		 * Provides an opportunity to modify strings that will be
		 * deemed to evaluate to true.
		 *
		 * @param array $truthy_strings
		 */
		$truthy_strings = (array) apply_filters( 'tribe_is_truthy_strings', array(
			'1',
			'enable',
			'enabled',
			'on',
			'y',
			'yes',
			'true',
		) );
		// Makes sure we are dealing with lowercase for testing
		if ( is_string( $var ) ) {
			$var = strtolower( $var );
		}

		// If $var is a string, it is only true if it is contained in the above array
		if ( in_array( $var, $truthy_strings, true ) ) {
			return true;
		}

		// All other strings will be treated as false
		if ( is_string( $var ) ) {
			return false;
		}

		// For other types (ints, floats etc) cast to bool
		return (bool) $var;
	}
}

if ( ! function_exists( 'tribe_sort_by_priority' ) ) {
	/**
	 * Sorting function based on Priority
	 *
	 * @param object|array $a First Subject to compare
	 * @param object|array $b Second subject to compare
	 *
	 * @return int
	 * @since  4.7.20
	 *
	 */
	function tribe_sort_by_priority( $a, $b ) {
		if ( is_array( $a ) ) {
			$a_priority = $a['priority'];
		} else {
			$a_priority = $a->priority;
		}

		if ( is_array( $b ) ) {
			$b_priority = $b['priority'];
		} else {
			$b_priority = $b->priority;
		}

		return (int) $a_priority === (int) $b_priority ? 0 : (int) $a_priority > (int) $b_priority;
	}
}

if ( ! function_exists( 'tribe_normalize_terms_list' ) ) {
	/**
	 * Normalizes a list of terms to a list of fields.
	 *
	 * @param $terms A term or array of terms to normalize.
	 * @param string $taxonomy The terms taxonomy.
	 * @param string $field Teh fields the terms should be normalized to.
	 *
	 * @since 4.5
	 *
	 * @return array An array of the valid normalized terms.
	 */
	function tribe_normalize_terms_list( $terms, $taxonomy, $field = 'term_id' ) {
		if ( ! is_array( $terms ) ) {
			$terms = array( $terms );
		}

		$normalized = array();
		foreach ( $terms as $term ) {
			if ( is_object( $term ) && ! empty( $term->{$field} ) ) {
				$normalized[] = $term->{$field};
			} elseif ( is_numeric( $term ) ) {
				$term = get_term_by( 'id', $term, $taxonomy );
				if ( $term instanceof WP_Term ) {
					$normalized[] = $term->{$field};
				}
			} elseif ( is_string( $term ) ) {
				$term = get_term_by( 'slug', $term, $taxonomy );
				if ( $term instanceof WP_Term ) {
					$normalized[] = $term->{$field};
				}
			} elseif ( is_array( $term ) && ! empty( $term[ $field ] ) ) {
				$normalized[] = $term[ $field ];
			}
		}

		return $normalized;
	}

	if ( ! function_exists( 'tribe_upload_image' ) ) {
		/** * @param string|int $image The path to an image file, an image URL or an attachment post ID.
		 *
		 * @return int|bool The attachment post ID if the uploading and attachment is successful or the ID refers to an attachment;
		 *                  `false` otherwise.
		 *
		 * @see Tribe__Image__Uploader::upload_and_get_attachment_id()
		 */
		function tribe_upload_image( $image ) {
			$uploader = new Tribe__Image__Uploader( $image );

			return $uploader->upload_and_get_attachment_id();
		}
	}
}

if ( ! function_exists( 'tribe_is_error' ) ) {
	/**
	 * Check whether variable is a WordPress or Tribe Error.
	 *
	 * Returns true if $thing is an object of the Tribe_Error or WP_Error class.
	 *
	 * @since 4.5.3
	 *
	 * @param mixed $thing Any old variable will do.
	 *
	 * @return bool Indicates if $thing was an error.
	 */
	function tribe_is_error( $thing ) {
		return ( $thing instanceof Tribe__Error || is_wp_error( $thing ) );
	}
}

if ( ! function_exists( 'tribe_retrieve_object_by_hook' ) ) {
	/**
	 * Attempts to find and return an object of the specified type that is associated
	 * with a specific hook.
	 *
	 * This is useful when third party code registers callbacks that belong to anonymous
	 * objects and it isn't possible to obtain the reference any other way.
	 *
	 * @since 4.5.8
	 *
	 * @param string   $class_name
	 * @param string   $hook
	 * @param int      $priority
	 *
	 * @return object|false
	 */
	function tribe_retrieve_object_by_hook( $class_name, $hook, $priority ) {
		global $wp_filter;

		// No callbacks registered for this hook and priority?
		if (
			! isset( $wp_filter[ $hook ] )
			|| ! isset( $wp_filter[ $hook ][ $priority ] )
		) {
			return false;
		}

		// Otherwise iterate through the registered callbacks at the specified priority
		foreach ( $wp_filter[ $hook ]->callbacks[ $priority ] as $callback ) {
			// Skip if this callback isn't an object method
			if (
				! is_array( $callback['function'] )
				|| ! is_object( $callback['function'][0] )
			) {
				continue;
			}

			// If this isn't the callback we're looking for let's skip ahead
			if ( $class_name !== get_class( $callback['function'][0] ) ) {
				continue;
			}

			return $callback['function'][0];
		}

		return false;
	}
}

if ( ! function_exists( 'tribe_is_wpml_active' ) ) {
	/**
	 * A unified way of checking if WPML is activated.
	 *
	 * @since 4.6.2
	 *
	 * @return boolean
	 */
	function tribe_is_wpml_active() {
		return ( class_exists( 'SitePress' ) && defined( 'ICL_PLUGIN_PATH' ) );
	}
}

if ( ! function_exists( 'tribe_post_exists' ) ) {
	/**
	 * Checks if a post, optionally of a specific type, exists in the database.
	 *
	 * This is a low-level database check that will ignore caches and will
	 * check if there is an entry, in the posts table, for the post.
	 *
	 * @since 4.7.7
	 *
	 * @param string|int $post_id_or_name Either a post ID or a post name.
	 * @param null       $post_type       An optional post type, or a list of post types, the
	 *                                    post should have; a logic OR will be used.
	 *
	 * @return bool|int The matching post ID if found, `false` otherwise
	 */
	function tribe_post_exists( $post_id_or_name, $post_type = null ) {
		if ( $post_id_or_name instanceof WP_Post ) {
			$post_id_or_name = $post_id_or_name->ID;
		}

		global $wpdb;

		$query_template = "SELECT ID FROM {$wpdb->posts} WHERE %s";
		$query_vars     = array();
		$where          = '';

		if ( is_numeric( $post_id_or_name ) ) {
			$where        = 'ID = %d';
			$query_vars[] = $post_id_or_name;
		} elseif ( is_string( $post_id_or_name ) ) {
			$where        = 'post_name = %s';
			$query_vars[] = $post_id_or_name;
		}

		if (
			is_string( $post_type )
			|| (
				is_array( $post_type )
				&& count( $post_type ) === count( array_filter( $post_type, 'is_string' ) )
			)
		) {
			$post_types_where_template = ' AND post_type IN (%s)';
			$post_types                = (array) $post_type;

			$post_types_interval = $wpdb->prepare(
				implode(
					',',
					array_fill( 0, count( $post_types ), '%s' )
				),
				$post_types
			);

			$where .= sprintf( $post_types_where_template, $post_types_interval );
		}

		$prepared = $wpdb->prepare( sprintf( $query_template, $where ), $query_vars );
		$found    = $wpdb->get_var( $prepared );

		return ! empty( $found ) ? (int) $found : false;
	}
}

if ( ! function_exists( 'tribe_post_excerpt' ) ) {
	/**
	 * Wrapper function for `tribe_events_get_the_excerpt` to prevent access the function when is not present on the
	 * current site installation.
	 *
	 * @param $post
	 *
	 * @return null|string
	 */
	function tribe_post_excerpt( $post ) {
		if ( function_exists( 'tribe_events_get_the_excerpt' ) ) {
			return tribe_events_get_the_excerpt( $post );
		}

		if ( ! is_numeric( $post ) && ! $post instanceof WP_Post ) {
			$post = get_the_ID();
		}

		if ( is_numeric( $post ) ) {
			$post = WP_Post::get_instance( $post );
		}

		if ( ! $post instanceof WP_Post ) {
			return null;
		}

		$excerpt = has_excerpt( $post->ID )
			? $post->post_excerpt
			: wp_trim_words( $post->post_content );

		return wpautop( $excerpt );
	}
}

if ( ! function_exists( 'tribe_catch_and_throw' ) ) {
	/**
	 * A convenience function used to cast errors to exceptions.
	 *
	 * Use in `set_error_handler` calls:
	 *
	 *      try{
	 *          set_error_handler( 'tribe_catch_and_throw' );
	 *          // ...do something that could generate an error...
	 *          restore_error_handler();
	 *      } catch ( RuntimeException $e ) {
	 *          // Handle the exception.
	 *      }
	 *
	 * @since 4.9.5
	 *
	 * @throws RuntimeException The message will be the error message, the code will be the error code.
	 *
	 * @see   set_error_handler()
	 * @see   restore_error_handler()
	 */
	function tribe_catch_and_throw( $errno, $errstr ) {
		throw new RuntimeException( $errstr, $errno );
	}
}

if ( ! function_exists( 'tribe_is_regex' ) ) {

	/**
	 * Checks whether a candidate string is a valid regular expression or not.
	 *
	 * @since 4.9.5
	 *
	 * @param string $candidate The candidate string to check, it must include the
	 *                          regular expression opening and closing tags to validate.
	 *
	 * @return bool Whether a candidate string is a valid regular expression or not.
	 */
	function tribe_is_regex( $candidate ) {
		if ( ! is_string( $candidate ) ) {
			return false;
		}

		// We need to have the Try/Catch for Warnings too
		try {
			return ! ( @preg_match( $candidate, null ) === false );
		} catch ( Exception $e ) {
			return false;
		}
	}
}

if ( ! function_exists( 'tribe_unfenced_regex' ) ) {

	/**
	 * Removes fence characters and modifiers from a regular expression string.
	 *
	 * Use this to go from a PCRE-format regex (PHP) to one SQL can understand.
	 *
	 * @since 4.9.5
	 *
	 * @param string $regex The input regular expression string.
	 *
	 * @return string The un-fenced regular expression string.
	 */
	function tribe_unfenced_regex( $regex ) {
		if ( ! is_string( $regex ) ) {
			return $regex;
		}

		$str_fence   = $regex[0];
		// Let's pick a fence char the string itself is not using.
		$fence_char = '~' === $str_fence ? '#' : '~';
		$pattern = $fence_char
		           . preg_quote( $str_fence, $fence_char ) // the opening fence
		           . '(.*)' // keep anything after the opening fence, group 1
		           . preg_quote( $str_fence, $fence_char ) // the closing fence
		           . '.*' // any modifier after the closing fence
		           . $fence_char;

		return preg_replace( $pattern, '$1', $regex );
	}
}

/**
 * Create a function to mock the real function if the extension or Beta is not present.
 *
 *
 */
if ( ! function_exists( 'has_blocks' ) ) {
	/**
	 * Determine whether a post or content string has blocks.
	 *
	 * This test optimizes for performance rather than strict accuracy, detecting
	 * the pattern of a block but not validating its structure. For strict accuracy
	 * you should use the block parser on post content.
	 *
	 * @since 4.8
	 * @see https://github.com/WordPress/gutenberg/blob/73d9759116dde896931f4d152f186147a57889fe/lib/register.php#L313-L337s
	 *
	 * @param int|string|WP_Post|null $post Optional. Post content, post ID, or post object. Defaults to global $post.
	 * @return bool Whether the post has blocks.
	 */
	function has_blocks( $post = null ) {
		if ( ! is_string( $post ) ) {
			$wp_post = get_post( $post );
			if ( $wp_post instanceof WP_Post ) {
				$post = $wp_post->post_content;
			}
		}
		return false !== strpos( (string) $post, '<!-- wp:' );
	}
}

if ( ! function_exists( 'tribe_register_rest_route' ) ) {
	/**
	 * Wrapper function for `register_rest_route` to allow for filtering any Tribe REST API endpoint.
	 *
	 * @param string $namespace The first URL segment after core prefix. Should be unique to your package/plugin.
	 * @param string $route     The base URL for route you are adding.
	 * @param array  $args      Optional. Either an array of options for the endpoint, or an array of arrays for
	 *                          multiple methods. Default empty array.
	 * @param bool   $override  Optional. If the route already exists, should we override it? True overrides,
	 *                          false merges (with newer overriding if duplicate keys exist). Default false.
	 *
	 * @return bool True on success, false on error.
	 *
	 * @since 4.9.12
	 */
	function tribe_register_rest_route( $namespace, $route, $args = array(), $override = false ) {
		/**
		 * Allow plugins to customize REST API arguments and callbacks.
		 *
		 * @param array  $args      Either an array of options for the endpoint, or an array of arrays for
		 *                          multiple methods. Default empty array.
		 * @param string $namespace The first URL segment after core prefix. Should be unique to your package/plugin.
		 * @param string $route     The base URL for route you are adding.
		 * @param bool   $override  Optional. If the route already exists, should we override it? True overrides,
		 *                          false merges (with newer overriding if duplicate keys exist). Default false.
		 *
		 * @since 4.9.12
		 */
		$args = apply_filters( 'tribe_register_rest_route_args_' . $namespace . $route, $args, $namespace, $route, $override );

		/**
		 * Allow plugins to customize REST API arguments and callbacks.
		 *
		 * @param array  $args      Either an array of options for the endpoint, or an array of arrays for
		 *                          multiple methods. Default empty array.
		 * @param string $namespace The first URL segment after core prefix. Should be unique to your package/plugin.
		 * @param string $route     The base URL for route you are adding.
		 * @param bool   $override  Optional. If the route already exists, should we override it? True overrides,
		 *                          false merges (with newer overriding if duplicate keys exist). Default false.
		 *
		 * @since 4.9.12
		 */
		$args = apply_filters( 'tribe_register_rest_route_args', $args, $namespace, $route, $override );
		return register_rest_route( $namespace, $route, $args, $override );
	}
}

if ( ! function_exists( 'tribe_get_request_vars' ) ) {
	/**
	 * Returns the sanitized version of the `$_REQUEST` super-global array.
	 *
	 * Note: the return value is cached. It will be resolve the first time the function is called, per HTTP request,
	 * then the same return value will be returned. After the function has been called the first time, changes to the
	 * `$_REQUEST` super-global will NOT be reflected in the function return value.
	 * Call the function with `$refresh` set to `true` to refresh the function value.
	 *
	 * @since 4.9.18
	 *
	 * @param bool $refresh Whether to parse the `$_REQUEST` cache again and refresh the cache or not; defaults to
	 *                      `false`.
	 *
	 * @return array The sanitized version of the `$_REQUEST` super-global.
	 */
	function tribe_get_request_vars( $refresh = false ) {
		static $cache;

		if ( ! isset( $_REQUEST ) ) {
			return [];
		}

		if ( null !== $cache && ! $refresh ) {
			return $cache;
		}

		$cache = array_combine(
			array_keys( $_REQUEST ),
			array_map( static function ( $v )
			{
				return filter_var( $v, FILTER_SANITIZE_STRING );
			},
				$_REQUEST )
		);

		return $cache;
	}
}

if ( ! function_exists( 'tribe_sanitize_deep' ) ) {

	/**
	 * Sanitizes a value according to its type.
	 *
	 * The function will recursively sanitize array values.
	 *
	 * @since 4.9.20
	 *
	 * @param mixed $value The value, or values, to sanitize.
	 *
	 * @return mixed|null Either the sanitized version of the value, or `null` if the value is not a string, number or
	 *                    array.
	 */
	function tribe_sanitize_deep( &$value ) {
		if ( is_bool( $value ) ) {
			return $value;
		}
		if ( is_string( $value ) ) {
			$value = filter_var( $value, FILTER_SANITIZE_STRING );
			return $value;
		}
		if ( is_int( $value ) ) {
			$value = filter_var( $value, FILTER_VALIDATE_INT );
			return $value;
		}
		if ( is_float( $value ) ) {
			$value = filter_var( $value, FILTER_VALIDATE_FLOAT );
			return $value;
		}
		if ( is_array( $value ) ) {
			array_walk( $value, 'tribe_sanitize_deep' );
			return $value;
		}

		return null;
	}
}
