<?php

namespace Pods\Integrations\WPGraphQL\Connection_Resolver;

use Exception;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use PodsAPI;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;

/**
 * GraphQL Connection resolver for Pod Types.
 *
 * @since 2.9.0
 */
class Pod_Type extends AbstractConnectionResolver {

	/**
	 * The PodsAPI object.
	 *
	 * @since 2.9.0
	 *
	 * @var PodsAPI
	 */
	protected $pods_api;

	/**
	 * Pod constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param mixed       $source  The source passed down from the resolve tree.
	 * @param array       $args    List of arguments input in the field as part of the GraphQL query.
	 * @param AppContext  $context Object containing app context that gets passed down the resolve tree.
	 * @param ResolveInfo $info    Info about fields passed down the resolve tree.
	 *
	 * @throws Exception
	 */
	public function __construct( $source, array $args, AppContext $context, ResolveInfo $info ) {
		$this->pods_api = pods_api();

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
		return 'pods_pod_type';
	}

	/**
	 * Converts the args that were provided to the connection into args that can be used with PodsAPI::load_pods().
	 *
	 * @since 2.9.0
	 *
	 * @return array The query arguments to use.
	 *
	 * @throws Exception
	 * @see     PodsAPI::load_pods
	 */
	public function get_query_args() {
		$query_args = [
			'fields' => false,
		];

		// @todo Eventually support this kind of functionality in PodsAPI::load_pods().
		// $query_args['offset'] = $this->get_offset();

		// Determine whether we are pulling records by last first.
		$last = ! empty( $this->args['last'] ) ? (bool) $this->args['last'] : false;

		// @todo Eventually support this kind of functionality in PodsAPI::load_pods().
		// $query_args['offset_compare'] = $last ? '>' : '<';

		// Get the limit based on maximum provided in $max_query_amount.
		$query_args['limit'] = $this->get_query_amount();

		/**
		 * Take any of the input $args (under the "where" input) that were part of the GraphQL query and map and
		 * sanitize their GraphQL input to apply to the PodsAPI::load_pods().
		 */
		$input_fields = $this->sanitize_input_fields( $this->args );

		// Merge the default $query_args with the $args that were entered in the query.
		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		return $query_args;
	}

	/**
	 * Get the list of Pods filtered by the provided query arguments.
	 *
	 * @since 2.9.0
	 *
	 * @return array List of Pods filtered by the provided query arguments.
	 */
	public function get_query() {
		// The API is not set.
		if ( empty( $this->pods_api ) ) {
			return false;
		}

		try {
			return $this->pods_api->load_pods( $this->query_args );
		} catch ( Exception $exception ) {
			// Something went wrong.
			pods_debug_log( $exception );

			return false;
		}
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
	 * This sets up the "allowed" args, and translates the GraphQL-friendly keys to Pods-friendly keys.
	 *
	 * @since 2.9.0
	 *
	 * @param array $args The query args.
	 *
	 * @return array The properly prepared input field arguments.
	 */
	protected function sanitize_input_fields( array $args ) {
		/**
		 * Allow filtering which capabilities are required for specific input fields.
		 *
		 * @since 2.9.0
		 *
		 * @param array $capabilities List of capabilities to require.
		 * @param array $args         The input field arguments.
		 */
		$capabilities = (array) apply_filters( 'pods_wpgraphql_integration_connection_resolver_pod_type_input_fields_capabilities', [], $args );

		$has_access = empty( $capabilities );

		foreach ( $capabilities as $capability ) {
			if ( current_user_can( $capability ) ) {
				$has_access = true;

				break;
			}
		}

		// Maybe throw an error if necessary.
		if ( ! $has_access ) {
			throw new UserError( __( 'Sorry, you are not allowed to use this combination of filters.', 'pods' ) );
		}

		$supported_query_args = [
			'where',
			'having',
			'orderby',
			'groupby',
		];

		$query_args = [];

		// Set up supported query arguments.
		foreach ( $supported_query_args as $arg_type ) {
			if ( empty( $args[ $arg_type ] ) ) {
				continue;
			}

			$query_args[] = $this->prepare_input_fields_by_type( $arg_type, (array) $args[ $arg_type ] );
		}

		$query_args = array_merge( ...$query_args );

		/**
		 * Allow filtering the query arguments mapped from the input fields which must be compatible with PodsAPI::load_pods().
		 *
		 * @since 2.9.0
		 *
		 * @param array       $query_args The query arguments to pass to PodsAPI::load_pods().
		 * @param array       $args       The input arguments provided which contain 'where', 'having', and others.
		 * @param mixed       $source     The source passed down from the resolve tree.
		 * @param array       $args       List of arguments input in the field as part of the GraphQL query.
		 * @param AppContext  $context    Object containing app context that gets passed down the resolve tree.
		 * @param ResolveInfo $info       Info about fields passed down the resolve tree.
		 */
		$query_args = (array) apply_filters( 'pods_wpgraphql_integration_connection_resolver_pod_input_fields_query_args', $query_args, $args, $this->source, $this->args, $this->context, $this->info );

		return $query_args;
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
		// The API is not set.
		if ( empty( $this->pods_api ) ) {
			return false;
		}

		try {
			$pod = $this->pods_api->load_pod( $offset );
		} catch ( Exception $exception ) {
			// Something went wrong.
			pods_debug_log( $exception );

			return false;
		}

		return ! empty( $pod );
	}

	/**
	 * Prepare a list of input fields by type.
	 *
	 * @since 2.9.0
	 *
	 * @param string $type         The input type: 'where', 'having', 'orderby', and 'groupby'.
	 * @param array  $input_fields The list of input fields to prepare.
	 *
	 * @return array The list of prepared input fields.
	 */
	private function prepare_input_fields_by_type( $type, array $input_fields ) {
		// @todo Add something into Pods core that does this automatically for strict handling.
		$prepared_input_fields = [];

		// Set the disallowed pattern to prevent against.
		$disallowed_pattern = '/[^a-zA-Z0-9_\-]/';

		if ( in_array( $type, [ 'type', 'ids', 'object' ], true ) ) {
			foreach ( $input_fields as $input_field ) {
				// If the input field is already an array, make it a string and sanitize it.
				if ( is_array( $input_field ) ) {
					$input_field = implode( ',', $input_field );
				}

				// Do not use any input fields that provide disallowed characters.
				if ( false !== preg_match( $disallowed_pattern, $input_field ) ) {
					continue;
				}

				$prepared_input_fields = explode( ',', $input_field );
			}
		}

		$prepared_input_fields = array_merge( ...$prepared_input_fields );
		$prepared_input_fields = array_values( array_unique( array_filter( $prepared_input_fields ) ) );

		return $prepared_input_fields;
	}
}
