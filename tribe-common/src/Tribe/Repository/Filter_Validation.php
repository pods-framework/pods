<?php
/**
 * Provides methods to validate repository filters.
 *
 * @since   4.10.2
 *
 * @package Tribe\Repository
 */

namespace Tribe\Repository;

use Tribe__Repository__Usage_Error as Usage_Error;
use Tribe__Utils__Array as Arr;

trait Filter_Validation {
	/**
	 * Checks the passed arguments to make sure they are the correct number and nature.
	 *
	 * This method requires the class using it to define a `static::$filter_args_map` property in the shape:
	 * ```
	 *  [
	 *      <filter> => [ <arg_name> => <arg_validation_callback> ]
	 *  ]
	 * ```
	 *
	 * @since 4.10.2
	 *
	 * @param       string $filter The name of the filter currently validating.
	 * @param array        $call_args The current filter call args, usually `func_get_args()`.
	 *
	 * @throws Usage_Error If there is a definition for the filter and the argument count or nature is not correct.
	 */
	protected function ensure_args_for_filter( $filter, array $call_args ) {
		$map = isset( static::$filter_args_map ) ? static::$filter_args_map : false;

		if ( empty( $map ) ) {
			return;
		}

		$required_args = Arr::get( $filter, $map, false );

		if ( false === $required_args ) {
			return;
		}

		if ( count( $required_args ) !== count( $call_args ) ) {
			throw Usage_Error::because_filter_requires_args( $filter, array_keys( $required_args ) );
		}

		$iterator = new \MultipleIterator();
		$iterator->attachIterator( new \ArrayIterator( array_keys( $required_args ) ) );
		$iterator->attachIterator( new \ArrayIterator( array_values( $required_args ) ) );
		$iterator->attachIterator( new \ArrayIterator( $call_args ) );

		foreach ( $required_args as list( $arg_name, $validator, $input ) ) {
			if ( empty( $validator( $input ) ) ) {
				throw Usage_Error::because_filter_arg_is_not_valid( $filter, $arg_name );
			}
		}
	}
}
