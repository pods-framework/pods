<?php
/**
 * Utilities to manipulate file-system paths.
 *
 * @since   4.12.14
 *
 * @package Tribe\Utils
 */

namespace Tribe\Utils;

/**
 * Class Paths
 *
 * @since   4.12.14
 *
 * @package Tribe\Utils
 */
class Paths {

	/**
	 * Merge a set of paths into a single path.
	 *
	 * The function will take care of merging the paths intersecting their common fragments.
	 * E.g. `foo/bar/baz` and `bar/baz/test.php` will be merged int `foo/bar/baz/test.php`.
	 *
	 * @since 4.12.14
	 *
	 * @param string|array<string<array<string>> ...$paths A set of paths to merge, each one either a string or an array
	 *                                                     of path fragments.
	 *
	 * @return string The merged path, the path intersecting fragments removed.
	 */
	public static function merge( ...$paths ) {
		$merged_paths = '';

		if ( count( $paths ) > 2 ) {
			$slice = array_splice( $paths, 0, 1 );
			$paths = array_merge( $slice, [ static::merge( ...$paths ) ] );
		}

		$path_1      = isset( $paths[0] ) ? $paths[0] : '';
		$lead_slash  = is_string( $path_1 ) && $path_1 !== ltrim( $path_1, '\\/' ) ? DIRECTORY_SEPARATOR : '';
		$path_2      = isset( $paths[1] ) ? $paths[1] : '';
		$trail_slash = is_string( $path_2 ) && $path_2 !== rtrim( $path_2, '\\/' ) ? DIRECTORY_SEPARATOR : '';
		// Handle *nix spacing escape sequence (`\ `) correctly. The Windows one (`^ `) is already handled.
		$break_pattern          = '/[\\\\\\/](?!\\s)/';
		$drop_empty_strings     = static function ( $frag ) {
			return $frag !== '';
		};
		$path_1_frags           = is_array( $path_1 )
			? $path_1
			: array_filter( (array) preg_split( $break_pattern, $path_1 ), $drop_empty_strings );
		$path_2_frags           = is_array( $path_2 )
			? $path_2
			: array_filter( (array) preg_split( $break_pattern, $path_2 ), $drop_empty_strings );
		$non_consecutive_common = array_intersect( $path_1_frags, $path_2_frags );

		$trimmed_path_2 = trim(
			preg_replace(
				'#^' . preg_quote( implode( DIRECTORY_SEPARATOR, $non_consecutive_common ), '#' ) . '#', '',
				implode( DIRECTORY_SEPARATOR, $path_2_frags )
			),
			'\\/'
		);

		$merged_paths .= $lead_slash . implode( DIRECTORY_SEPARATOR, $path_1_frags );

		if ( $trimmed_path_2 ) {
			$merged_paths .= DIRECTORY_SEPARATOR . $trimmed_path_2 . $trail_slash;
		}

		return $merged_paths;
	}
}
