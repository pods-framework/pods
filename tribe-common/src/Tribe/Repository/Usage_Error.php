<?php

/**
 * Class Tribe__Repository__Usage_Error
 *
 * @since 4.7.19
 *
 * Thrown to indicate an error in the repository usage by a developer; this
 * is meant to be used to help developers to use the repository.
 */
class Tribe__Repository__Usage_Error extends Exception {

	/**
	 * Do not ally dynamic set of properties on the repository; protected
	 * properties are read-only.
	 *
	 * @since 4.7.19
	 *
	 * @param string                       $name   The name of the property the client code is trying to set.
	 * @param Tribe__Repository__Interface $object The instance of the repository.
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_properties_should_be_set_correctly( $name, $object ) {
		$class = get_class( $object );

		return new self( "Property {$name} should be set with a setter method, injected in the constructor and/or defined in an extending class." );
	}

	/**
	 * Clearly indicate that a filter is not defined on the repository in use.
	 *
	 * This is to allow for more clear comprehension of errors related to
	 * missing filters.
	 *
	 * @since 4.7.19
	 *
	 * @param string                       $key    The filter the client code is trying to use.
	 * @param Tribe__Repository__Interface $object The instance of the repository.
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_the_read_filter_is_not_defined( $key, $object ) {
		$class = get_class( $object );

		return new self( "The class {$class} does not define a {$key} read filter: either implement it or try to use the provided filters." );
	}

	/**
	 * Indicates that a property is not defined on the repository.
	 *
	 * @since 4.7.19
	 *
	 * @param string                       $name The name of the property the client code is trying to read.
	 * @param Tribe__Repository__Interface $object
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_property_is_not_defined( $name, $object ) {
		$class = get_class( $object );

		return new self( "The {$class} class does not define a {$name} property; add it by decorating or extending this class." );
	}

	/**
	 * Indicates that a field cannot be updated by the repository class.
	 *
	 * @since 4.7.19
	 *
	 * @param string                              $key
	 * @param Tribe__Repository__Update_Interface $object
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_this_field_cannot_be_updated( $key, $object ) {
		$class = get_class( $object );

		return new self( "The {$class} class does not allow updating the {$key} field; allow it by decorating or extending this class." );
	}

	/**
	 * "Sugar" method to correct a typo in a public method name.
	 * Indicates that the `set` method of the Update repository is being used incorrectly.
	 *
	 * @since 4.12.6
	 *
	 * @TODO: perhaps we should deprecate this at some point?
	 *
	 * @param Tribe__Repository__Update_Interface $object
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_udpate_key_should_be_a_string( $object ) {
		return self::because_update_key_should_be_a_string( $object );
	}

	/**
	 * Indicates that the `set` method of the Update repository is being used incorrectly.
	 *
	 * @since 4.7.19
	 *
	 * @param Tribe__Repository__Update_Interface $object
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_update_key_should_be_a_string( $object ) {
		$class = get_class( $object );

		return new self( 'The key used in the `set` method should be a string; if you want to set multiple fields at once use the `set_args` method.' );
	}

	/**
	 * Indicates that the client code is trying to use a single comparison operator with multiple values.
	 *
	 * @since 4.7.19
	 *
	 * @param string|array $key
	 * @param array  $value
	 * @param string $compare
	 * @param mixed  $object
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_single_value_comparisons_should_be_used_with_one_value( $key, array $value, $compare, $object ) {
		$class  = get_class( $object );
		$keys    = is_array( $key ) ? implode( ', ', $key ) : $key;
		$values = implode( ', ', $value );

		return new self( "You are trying to use a single SQL comparison operator ({$compare}) with multiple values; [ keys: {$keys}, values: {$values}]." );
	}

	/**
	 * Indicates that the client code is calling the query building method without
	 * providing all the arguments the comparison operator requires.
	 *
	 * @since 4.7.19
	 *
	 * @param string|array $key
	 * @param string       $compare
	 * @param mixed        $object
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_this_comparison_operator_requires_fields_and_values( $key, $compare, $object ) {
		$class = get_class( $object );
		$keys  = is_array( $key ) ? implode( ', ', $key ) : $key;

		return new self( "You are trying to use a SQL comparison operator ({$compare}) that requires fields and values [ keys: {$keys}]." );
	}

	/**
	 * Indicates that the client code is using an high-level filtering method while
	 * trying to build a WHERE OR clause.
	 *
	 * @param array array $method
	 * @param mixed $object
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_where_or_should_only_be_used_with_methods_that_add_where_clauses( array $method, $object ) {
		$class  = get_class( $object );
		$method = json_encode( $method );

		return new self( "You are trying to build a WHERE OR clause using a method ({$class}::{$method}) that does not call the Tribe__Repository__Query_Filters::where method directly; call `where_clause` directly or call methods that call it." );
	}

	/**
	 * Indicates that the client code is trying to use a wpdb::prepare format with
	 * a regular `meta_query`.
	 *
	 * @param string|array $key
	 * @param string       $type_or_format
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_the_type_is_a_wpdb_prepare_format( $key, $type_or_format ) {
		$keys  = is_array( $key ) ? implode( ', ', $key ) : $key;

		return new self( "You are trying to use a `wpdb::prepare` format ({$type_or_format}) with a regular meta_query [ keys: {$keys}]." );
	}

	/**
	 * Indicates that the client code is trying to use a wpdb::prepare format with
	 * a regular `meta_query`.
	 *
	 * @param string|array $key
	 * @param string       $type_or_format
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_the_format_is_not_a_wpdb_prepare_one( $key, $type_or_format ) {
		$keys = is_array( $key ) ? implode( ', ', $key ) : $key;

		return new self( "You are trying to use a format ({$type_or_format}) that is not a valid `wpdb::prepare` one with a query [ keys: {$keys}]." );
	}

	/**
	 * Indicates that the client code is trying to use a comparison operator not supported by a specific filter.
	 *
	 * @since 4.9.5
	 *
	 * @param string $operator The not supported comparison operator.
	 * @param string $filter   The filter in which the client code is trying to use the current operator.
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_this_comparison_operator_is_not_supported( $operator, $filter ) {
		return new self( "You are trying to use a comparison operator ({$operator}) that is not supported by this filter ({$filter})" );
	}

	/**
	 * Indicates that the client code is trying to use a comparison operator that requires a value of a specific type
	 * wrong.
	 *
	 * @since 4.9.5
	 *
	 * @param string $operator The operator the client code is using.
	 * @param string $filter   The filter the client code is using.
	 * @param string $type     The required value type for this operator and this filter.
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_this_comparison_operator_requires_an_value_of_type( $operator, $filter, $type ) {
		return new self( "You are trying to use a comparison operator ({$operator}) in the filter {$filter} that requires a value of type {$type}." );
	}

	/**
	 * Indicates that the client code is trying to use a comparison operator that is not valid..
	 *
	 * @since 4.9.6
	 *
	 * @param string $operator The not supported comparison operator.
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_this_comparison_operator_is_not_valid( $operator ) {
		return new self( "You are trying to use a comparison operator ({$operator}) that is not valid." );
	}

	/**
	 * Indicates that the client code is trying to use a relation that is not valid..
	 *
	 * @since 4.9.6
	 *
	 * @param string $relation The not supported relation.
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_this_relation_is_not_valid( $relation ) {
		return new self( "You are trying to use a relation ({$relation}) that is not valid." );
	}

	/**
	 * Indicates that the client code is trying to set a query on the repository after the query ran.
	 *
	 * @since 4.9.9
	 *
	 * @return Tribe__Repository__Usage_Error A ready to throw instance of the class.
	 */
	public static function because_query_cannot_be_set_after_it_ran() {
		return new self( "You are trying to set the repository query after it ran!" );
	}

	/**
	 * Indicates the client code is trying to call a filter without the correct number of req. parameters.
	 *
	 * @since 4.10.2
	 *
	 * @param string $filter        The called filter.
	 * @param array  $required_args The human-readable name of the required arguments.
	 *
	 * @return static A ready to throw instance of the class.
	 */
	public static function because_filter_requires_args( $filter, array $required_args ) {
		return new static(
			sprintf(
				'The "%s" filter requires %d arguments: %s',
				$filter,
				count( $required_args ),
				implode( ', ', $required_args )
			)
		);
	}

	/**
	 * Indicates the client code is trying to call a filter with an invalid parameter.
	 *
	 * @since 4.10.2
	 *
	 * @param string $filter   The called filter.
	 * @param string $arg_name The human-readable name of the parameter.
	 *
	 * @return static A ready to throw instance of the class.
	 */
	public static function because_filter_arg_is_not_valid( $filter, $arg_name ) {
		return new static(
			sprintf(
				'The "%s" filter "%s" argument is not valid.',
				$filter,
				$arg_name
			)
		);
	}
}
