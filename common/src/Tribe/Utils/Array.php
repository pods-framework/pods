<?php

if ( ! class_exists( 'Tribe__Utils__Array' ) ) {
	/**
	 * Array utilities
	 */
	class Tribe__Utils__Array {

		/**
		 * Set key/value within an array, can set a key nested inside of a multidimensional array.
		 *
		 * Example: set( $a, [ 0, 1, 2 ], 'hi' ) sets $a[0][1][2] = 'hi' and returns $a.
		 *
		 * @param mixed        $array The array containing the key this sets.
		 * @param string|array $key To set a key nested multiple levels deep pass an array
		 *                             specifying each key in order as a value.
		 *                             Example: array( 'lvl1', 'lvl2', 'lvl3' );
		 * @param mixed        $value The value.
		 *
		 * @return array Full array with the key set to the specified value.
		 */
		public static function set( array $array, $key, $value ) {
			// Convert strings and such to array.
			$key = (array) $key;

			// Setup a pointer that we can point to the key specified.
			$key_pointer = &$array;

			// Iterate through every key, setting the pointer one level deeper each time.
			foreach ( $key as $i ) {

				// Ensure current array depth can have children set.
				if ( ! is_array( $key_pointer ) ) {
					// $key_pointer is set but is not an array. Converting it to an array
					// would likely lead to unexpected problems for whatever first set it.
					$error = sprintf(
						'Attempted to set $array[%1$s] but %2$s is already set and is not an array.',
						implode( $key, '][' ),
						$i
					);

					_doing_it_wrong( __FUNCTION__, esc_html( $error ), '4.3' );
					break;
				} elseif ( ! isset( $key_pointer[ $i ] ) ) {
					$key_pointer[ $i ] = [];
				}

				// Dive one level deeper into the nested array.
				$key_pointer = &$key_pointer[ $i ];
			}

			// Set the value for the specified key
			$key_pointer = $value;

			return $array;
		}

		/**
		 * Find a value inside of an array or object, including one nested a few levels deep.
		 *
		 * Example: get( $a, [ 0, 1, 2 ] ) returns the value of $a[0][1][2] or the default.
		 *
		 * @param array        $variable Array or object to search within.
		 * @param array|string $indexes Specify each nested index in order.
		 *                                Example: array( 'lvl1', 'lvl2' );
		 * @param mixed        $default Default value if the search finds nothing.
		 *
		 * @return mixed The value of the specified index or the default if not found.
		 */
		public static function get( $variable, $indexes, $default = null ) {
			if ( is_object( $variable ) ) {
				$variable = (array) $variable;
			}

			if ( ! is_array( $variable ) ) {
				return $default;
			}

			foreach ( (array) $indexes as $index ) {
				if ( ! is_array( $variable ) || ! isset( $variable[ $index ] ) ) {
					$variable = $default;
					break;
				}

				$variable = $variable[ $index ];
			}

			return $variable;
		}

		/**
		 * Find a value inside a list of array or objects, including one nested a few levels deep.
		 *
		 * @since 4.7.7
		 *
		 * Example: get( [$a, $b, $c], [ 0, 1, 2 ] ) returns the value of $a[0][1][2] found in $a, $b or $c
		 * or the default.
		 *
		 * @param array        $variables Array of arrays or objects to search within.
		 * @param array|string $indexes Specify each nested index in order.
		 *                                 Example: array( 'lvl1', 'lvl2' );
		 * @param mixed        $default Default value if the search finds nothing.
		 *
		 * @return mixed The value of the specified index or the default if not found.
		 */
		public static function get_in_any( array $variables, $indexes, $default = null ) {
			foreach ( $variables as $variable ) {
				$found = self::get( $variable, $indexes, '__not_found__' );
				if ( '__not_found__' !== $found ) {
					return $found;
				}
			}

			return $default;
		}

		/**
		 * Behaves exactly like the native strpos(), but accepts an array of needles.
		 *
		 * @param string       $haystack String to search in.
		 * @param array|string $needles Strings to search for.
		 * @param int          $offset Starting position of search.
		 *
		 * @return false|int Integer position of first needle occurrence.
		 * @see strpos()
		 *
		 */
		public static function strpos( $haystack, $needles, $offset = 0 ) {
			$needles = (array) $needles;

			foreach ( $needles as $i ) {
				$search = strpos( $haystack, $i, $offset );

				if ( false !== $search ) {
					return $search;
				}
			}

			return false;
		}

		/**
		 * Converts a list to an array filtering out empty string elements.
		 *
		 * @param mixed  $value A string representing a list of values separated by the specified separator
		 *                           or an array. If the list is a string (e.g. a CSV list) then it will urldecoded
		 *                           before processing.
		 * @param string $sep The char(s) separating the list elements; will be ignored if the list is an array.
		 *
		 * @return array An array of list elements.
		 */
		public static function list_to_array( $value, $sep = ',' ) {
			// since we might receive URL encoded strings for CSV lists let's URL decode them first
			$value = is_array( $value ) ? $value : urldecode( $value );

			$sep = is_string( $sep ) ? $sep : ',';

			if ( $value === null || $value === '' ) {
				return [];
			}

			if ( ! is_array( $value ) ) {
				$value = preg_split( '/\\s*' . preg_quote( $sep ) . '\\s*/', $value );
			}

			$filtered = [];
			foreach ( $value as $v ) {
				if ( '' === $v ) {
					continue;
				}
				$filtered[] = is_numeric( $v ) ? $v + 0 : $v;
			}

			return $filtered;
		}

		/**
		 * Returns a list separated by the specified separator.
		 *
		 * @since 4.6
		 *
		 * @param mixed  $list
		 * @param string $sep
		 *
		 * @return string The list separated by the specified separator or the original list if the list is empty.
		 */
		public static function to_list( $list, $sep = ',' ) {
			if ( empty( $list ) ) {
				return $list;
			}

			if ( is_array( $list ) ) {
				return implode( $sep, $list );
			}

			return $list;
		}

		/**
		 * Sanitize a multidimensional array.
		 *
		 * @since   4.7.18
		 *
		 * @param array $data The array to sanitize.
		 *
		 * @return array The sanitized array
		 *
		 * @link https://gist.github.com/esthezia/5804445
		 */
		public static function escape_multidimensional_array( $data = [] ) {

			if ( ! is_array( $data ) || ! count( $data ) ) {
				return [];
			}

			foreach ( $data as $key => $value ) {
				if ( ! is_array( $value ) && ! is_object( $value ) ) {
					$data[ $key ] = esc_attr( trim( $value ) );
				}
				if ( is_array( $value ) ) {
					$data[ $key ] = self::escape_multidimensional_array( $value );
				}
			}

			return $data;
		}

		/**
		 * Returns an array of values obtained by using the keys on the map; keys
		 * that do not have a match in map are discarded.
		 *
		 * To discriminate from not found results and legitimately `false`
		 * values from the map the `$found` parameter will be set by reference.
		 *
		 * @since 4.7.19
		 *
		 * @param string|array $keys One or more keys that should be used to get
		 *                                 the new values
		 * @param array        $map An associative array relating the keys to the new
		 *                                 values.
		 * @param bool         $found When using a single key this argument will be
		 *                                 set to indicate whether the mapping was successful
		 *                                 or not.
		 *
		 * @return array|mixed|false An array of mapped values, a single mapped value when passing
		 *                           one key only or `false` if one key was passed but the key could
		 *                           not be mapped.
		 */
		public static function map_or_discard( $keys, array $map, &$found = true ) {
			$hash   = md5( time() );
			$mapped = [];

			foreach ( (array) $keys as $key ) {
				$meta_key = Tribe__Utils__Array::get( $map, $key, $hash );
				if ( $hash === $meta_key ) {
					continue;
				}
				$mapped[] = $meta_key;
			}

			$found = (bool) count( $mapped );

			if ( is_array( $keys ) ) {
				return $mapped;
			}

			return $found ? $mapped[0] : false;
		}

		/**
		 * Duplicates any key prefixed with '_' creating an un-prefixed duplicate one.
		 *
		 * The un-prefixing and duplication is recursive.
		 *
		 * @since 4.9.5
		 *
		 * @param mixed $array The array whose keys should be duplicated.
		 * @param bool  $recursive Whether the un-prefixing and duplication should be
		 *                         recursive or shallow.
		 *
		 * @return array The array with the duplicate, unprefixed, keys or the
		 *               original input if not an array.
		 */
		public static function add_unprefixed_keys_to( $array, $recursive = false ) {
			if ( ! is_array( $array ) ) {
				return $array;
			}

			$unprefixed = [];
			foreach ( $array as $key => $value ) {
				if ( $recursive && is_array( $value ) ) {
					$value = self::add_unprefixed_keys_to( $value, true );
					// And also add it to the original array.
					$array[ $key ] = array_merge( $array[ $key ], $value );
				}

				if ( 0 !== strpos( $key, '_' ) ) {
					continue;
				}
				$unprefixed[ substr( $key, 1 ) ] = $value;
			}

			return array_merge( $array, $unprefixed );
		}

		/**
		 * Filters an associative array non-recursively, keeping only the values attached
		 * to keys starting with the specified prefix.
		 *
		 * @since 4.9.5
		 *
		 * @param array  $array The array to filter.
		 * @param string $prefix The prefix, or prefixes, of the keys to keep.
		 *
		 * @return array The filtered array.
		 */
		public static function filter_prefixed( array $array, $prefix ) {
			$prefixes = implode( '|', array_map( 'preg_quote', (array) $prefix ) );
			$pattern  = '/^(' . $prefixes . ')/';
			$filtered = [];
			foreach ( $array as $key => $value ) {
				if ( ! preg_match( $pattern, $key ) ) {
					continue;
				}
				$filtered[ $key ] = $value;
			}

			return $filtered;
		}

		/**
		 * Flattens an array transforming each value that is an array and only contains one
		 * element into that one element.
		 *
		 * Typical use case is to flatten arrays like those returned by `get_post_meta( $id )`.
		 * Empty arrays are replaced with an empty string.
		 *
		 * @since 4.9.5
		 *
		 * @param array $array The array to flatten.
		 *
		 * @return array The flattened array.
		 */
		public static function flatten( array $array ) {
			foreach ( $array as $key => &$value ) {
				if ( ! is_array( $value ) ) {
					continue;
				}

				$count = count( $value );

				switch ( $count ) {
					case 0:
						$value = '';
						break;
					case 1:
						$value = reset( $value );
						break;
					default:
						break;
				}
			}

			return $array;
		}

		/**
		 * Duplicates any key not prefixed with '_' creating a prefixed duplicate one.
		 *
		 * The prefixing and duplication is recursive.
		 *
		 * @since 4.9.5
		 *
		 * @param mixed $array The array whose keys should be duplicated.
		 * @param bool  $recursive Whether the prefixing and duplication should be
		 *                         recursive or shallow.
		 *
		 * @return array The array with the duplicate, prefixed, keys or the
		 *               original input if not an array.
		 */
		public static function add_prefixed_keys_to( $array, $recursive = false ) {
			if ( ! is_array( $array ) ) {
				return $array;
			}

			$prefixed = [];
			foreach ( $array as $key => $value ) {
				if ( $recursive && is_array( $value ) ) {
					$value = self::add_prefixed_keys_to( $value, true );
					// And also add it to the original array.
					$array[ $key ] = array_merge( $array[ $key ], $value );
				}

				if ( 0 === strpos( $key, '_' ) ) {
					continue;
				}

				$prefixed[ '_' . $key ] = $value;
			}

			return array_merge( $array, $prefixed );
		}

		/**
		 * Recursively key-sort an array.
		 *
		 * @since 4.9.5
		 *
		 * @param array $array The array to sort, modified by reference.
		 *
		 * @return bool The sorting result.
		 */
		public static function recursive_ksort( array &$array ) {
			foreach ( $array as &$value ) {
				if ( is_array( $value ) ) {
					static::recursive_ksort( $value );
				}
			}

			return ksort( $array );
		}

		/**
		 * Returns the value associated with the first index, among the indexes, that is set in the array..
		 *
		 * @since 4.9.11
		 *
		 * @param array $array The array to search.
		 * @param array $indexes The indexes to search; in order the function will look from the first to the last.
		 * @param null  $default The value that will be returned if the array does not have any of the indexes set.
		 *
		 * @return mixed|null The set value or the default value.
		 */
		public static function get_first_set( array $array, array $indexes, $default = null ) {
			foreach ( $indexes as $index ) {
				if ( ! isset( $array[ $index ] ) ) {
					continue;
				}

				return $array[ $index ];
			}

			return $default;
		}
	}
}
