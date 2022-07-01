<?php

namespace Pods\Integrations\WPGraphQL\Connection_Resolver;

use Exception;
use GraphQL\Type\Definition\ResolveInfo;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;

/**
 * GraphQL Connection resolver for Custom Simple Relationships.
 *
 * @since 2.9.0
 */
class Custom_Simple extends AbstractConnectionResolver {

	/**
	 * The custom data provided from the relationship field.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	protected $custom_data;

	/**
	 * Pod constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param mixed       $source      The source passed down from the resolve tree.
	 * @param array       $args        List of arguments input in the field as part of the GraphQL query.
	 * @param AppContext  $context     Object containing app context that gets passed down the resolve tree.
	 * @param ResolveInfo $info        Info about fields passed down the resolve tree.
	 * @param array       $custom_data The custom data provided from the relationship field.
	 *
	 * @throws Exception
	 */
	public function __construct( $source, array $args, AppContext $context, ResolveInfo $info, array $custom_data ) {
		$this->custom_data = $custom_data;

		/**
		 * Call the parent construct to setup class data
		 */
		parent::__construct( $source, $args, $context, $info );
	}

	/**
	 * Determines whether the query should execute at all. It's possible that in some
	 * situations we may want to prevent the underlying query from executing at all.
	 *
	 * In those cases, this would be set to false.
	 *
	 * @since 2.9.0
	 *
	 * @return bool Whether the query should execute.
	 */
	public function should_execute() {
		return true;
	}

	/**
	 * Provide the loader name.
	 *
	 * @since 2.9.0
	 *
	 * @return string The loader name.
	 */
	public function get_loader_name() {
		return 'pods_custom_simple';
	}

	/**
	 * Converts the args that were provided to the connection into args that can be used with the data.
	 *
	 * @since 2.9.0
	 *
	 * @return array The query arguments to use.
	 *
	 * @throws Exception
	 */
	public function get_query_args() {
		$query_args = [];

		// Handle preparing the values to filter by.
		if ( ! empty( $this->args['values'] ) ) {
			$values = $this->prepare_input_fields( (array) $this->args['values'] );

			if ( $values ) {
				$query_args['values'] = $values;
			}
		}

		// Handle preparing the labels to filter by.
		if ( ! empty( $this->args['labels'] ) ) {
			$labels = $this->prepare_input_fields( (array) $this->args['labels'] );

			if ( $labels ) {
				$query_args['labels'] = $labels;
			}
		}

		return $query_args;
	}

	/**
	 * Get the list of custom data filtered as requested.
	 *
	 * @since 2.9.0
	 *
	 * @return array List of custom data.
	 */
	public function get_query() {
		// The data is not set.
		if ( empty( $this->custom_data ) ) {
			return [];
		}

		$matching_data = $this->custom_data;

		// Remove data items from matches if they aren't in the provided values to filter by.
		if ( isset( $this->query_args['values'] ) ) {
			foreach ( $matching_data as $value => $label ) {
				if ( in_array( (string) $value, $this->query_args['values'], true ) ) {
					continue;
				}

				unset( $matching_data[ $value ] );
			}
		}

		// Remove data items from matches if they aren't in the provided labels to filter by.
		if ( isset( $this->query_args['labels'] ) ) {
			foreach ( $matching_data as $value => $label ) {
				if ( in_array( (string) $label, $this->query_args['labels'], true ) ) {
					continue;
				}

				unset( $matching_data[ $value ] );
			}
		}

		return $matching_data;
	}

	/**
	 * Returns an array of ids from the query being executed.
	 *
	 * @since 2.9.0
	 *
	 * @return array List of IDs from the query.
	 */
	public function get_ids() {
		if ( ! $this->query ) {
			return [];
		}

		// Get the IDs from the list of keys.
		return array_keys( $this->query );
	}

	/**
	 * Determine whether or not the the offset is valid, i.e the user corresponding to the offset
	 * exists. Offset is equivalent to user_id. So this function is equivalent to checking if the
	 * user with the given ID exists.
	 *
	 * @since 2.9.0
	 *
	 * @param int $offset The ID of the node used as the offset in the cursor.
	 *
	 * @return bool Whether the offset is valid.
	 */
	public function is_valid_offset( $offset ) {
		// The data is not set.
		if ( empty( $this->custom_data ) ) {
			return false;
		}

		return isset( $this->custom_data[ $offset ] );
	}

	/**
	 * Prepare a list of input fields.
	 *
	 * @since 2.9.0
	 *
	 * @param array $input_fields The list of input fields to prepare.
	 *
	 * @return array The list of prepared input fields.
	 */
	private function prepare_input_fields( array $input_fields ) {
		$prepared_input_fields = [];

		// Set the disallowed pattern to prevent against.
		$disallowed_pattern = '/[^a-zA-Z0-9_\-]/';

		foreach ( $input_fields as $input_field ) {
			// If the input field is already an array, make it a string and sanitize it.
			if ( is_array( $input_field ) ) {
				$input_field = implode( ',', $input_field );
			}

			$input_field = (string) $input_field;

			// Do not use any input fields that provide disallowed characters.
			if ( false !== preg_match( $disallowed_pattern, $input_field ) ) {
				continue;
			}

			$prepared_input_fields = explode( ',', $input_field );
		}

		$prepared_input_fields = array_merge( ...$prepared_input_fields );
		$prepared_input_fields = array_values( array_unique( array_filter( $prepared_input_fields ) ) );

		return $prepared_input_fields;
	}
}
