<?php

namespace Pods\Integrations\WPGraphQL\Connection_Resolver;

use Exception;
use GraphQL\Error\UserError;
use GraphQL\Type\Definition\ResolveInfo;
use Pods;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;

/**
 * GraphQL Connection resolver for Pods.
 *
 * @since 2.9.0
 */
class Pod extends AbstractConnectionResolver {

	/**
	 * The name of the pod the connection resolver is resolving for.
	 *
	 * @since 2.9.0
	 *
	 * @var string
	 */
	protected $pod_name;

	/**
	 * The Pods object or false if not valid.
	 *
	 * @since 2.9.0
	 *
	 * @var Pods|false
	 */
	protected $pods;

	/**
	 * Pod constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param mixed       $source   The source passed down from the resolve tree.
	 * @param array       $args     List of arguments input in the field as part of the GraphQL query.
	 * @param AppContext  $context  Object containing app context that gets passed down the resolve tree.
	 * @param ResolveInfo $info     Info about fields passed down the resolve tree.
	 * @param string      $pod_name The pod name to resolve for.
	 *
	 * @throws Exception
	 */
	public function __construct( $source, array $args, AppContext $context, ResolveInfo $info, $pod_name = '' ) {
		$this->pod_name = $pod_name;

		// The pod name is not set.
		if ( ! empty( $this->pod_name ) ) {
			$this->pod = pods_get_instance( $this->pod_name, null, true );
		}

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
		return 'pods_pod';
	}

	/**
	 * Converts the args that were provided to the connection into args that can be used with Pods::find().
	 *
	 * @since 2.9.0
	 *
	 * @return array The query arguments to use.
	 *
	 * @throws Exception
	 * @see     Pods::find
	 */
	public function get_query_args() {
		$query_args = [];

		$query_args['offset'] = $this->get_offset();

		// Determine whether we are pulling records by last first.
		$last = ! empty( $this->args['last'] ) ? (bool) $this->args['last'] : false;

		// @todo Eventually support this kind of functionality in Pods::find().
		// $query_args['offset_compare'] = $last ? '>' : '<';

		// Get the limit based on maximum provided in $max_query_amount.
		$query_args['limit'] = $this->get_query_amount();

		/**
		 * Take any of the input $args (under the "where" input) that were part of the GraphQL query and map and
		 * sanitize their GraphQL input to apply to the Pods::find().
		 */
		$input_fields = $this->sanitize_input_fields( $this->args );

		// Merge the default $query_args with the $args that were entered in the query.
		if ( ! empty( $input_fields ) ) {
			$query_args = array_merge( $query_args, $input_fields );
		}

		// Only query the IDs and let deferred resolution query the nodes.
		$query_args['select'] = $this->pod ? '`t`.`' . $this->pod->pod_data['field_id'] . '`' : '`t`.`id`';

		// If there's no orderby params in the inputArgs, set order based on the first/last argument.
		if ( empty( $query_args['orderby'] ) ) {
			$query_args['orderby'] = $query_args['select'] . ' ' . ( $last ? 'ASC' : 'DESC' );
		}

		return $query_args;
	}

	/**
	 * Get the Pods object with the data queried or false if not valid.
	 *
	 * @since 2.9.0
	 *
	 * @return Pods|bool The Pods object with the data queried or false if not valid.
	 */
	public function get_query() {
		// The pod is not set.
		if ( empty( $this->pod ) ) {
			return false;
		}

		return $this->pod->find( $this->query_args );
	}

	/**
	 * Returns an array of ids from the query being executed.
	 *
	 * @since 2.9.0
	 *
	 * @return array List of IDs from the query.
	 */
	public function get_ids() {
		$ids = [];

		if ( ! $this->query ) {
			return $ids;
		}

		while ( $this->query->fetch() ) {
			$id = $this->query->id();

			$ids[ $id ] = $id;
		}

		return $ids;
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
		 * @param array  $capabilities List of capabilities to require.
		 * @param array  $args         The input field arguments.
		 * @param string $pod_name     The pod name.
		 */
		$capabilities = (array) apply_filters( 'pods_wpgraphql_integration_connection_resolver_pod_input_fields_capabilities', [], $args, $this->pod_name );

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
		 * Allow filtering the query arguments mapped from the input fields which must be compatible with Pods::find().
		 *
		 * @since 2.9.0
		 *
		 * @param array       $query_args The query arguments to pass to Pods::find().
		 * @param array       $args       The input arguments provided which contain 'where', 'having', and others.
		 * @param mixed       $source     The source passed down from the resolve tree.
		 * @param array       $args       List of arguments input in the field as part of the GraphQL query.
		 * @param AppContext  $context    Object containing app context that gets passed down the resolve tree.
		 * @param ResolveInfo $info       Info about fields passed down the resolve tree.
		 * @param string      $pod_name   The pod name to resolve for.
		 */
		$query_args = (array) apply_filters( 'pods_wpgraphql_integration_connection_resolver_pod_input_fields_query_args', $query_args, $args, $this->source, $this->args, $this->context, $this->info, $this->pod_name );

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
		// The pod name is not set.
		if ( empty( $this->pod_name ) ) {
			return false;
		}

		// Set up a new Pods instance instead of reusing $this->pod to prevent conflicts.
		$pod = pods_get_instance( $this->pod_name, $offset, false );

		// The pod does not exist.
		if ( ! $pod->valid() ) {
			return false;
		}

		return $pod->exists();
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
		$disallowed_pattern = '/[^a-zA-Z0-9,\._\-\s]/';

		if ( in_array( $type, [ 'where', 'having' ], true ) ) {
			// Prepare these arguments in WP_Query meta_query style, do not allow SQL.
			$prepared_input_fields = $this->prepare_input_fields_for_meta_query( $input_fields, $disallowed_pattern );
		} elseif ( in_array( $type, [ 'orderby', 'groupby' ], true ) ) {
			// Prepare these arguments in basic SQL formatting, do not allow functions or special characters.
			$prepared_input_fields = $this->prepare_input_fields_for_simple_syntax( $input_fields, $disallowed_pattern );
		}

		return $prepared_input_fields;
	}

	/**
	 * Prepare a list of input fields for meta query.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $input_fields       The list of input fields to prepare.
	 * @param string $disallowed_pattern The disallowed pattern to prevent against.
	 *
	 * @return array The list of prepared input fields.
	 */
	private function prepare_input_fields_for_meta_query( array $input_fields, $disallowed_pattern = '' ) {
		$prepared_input_fields = [];

		foreach ( $input_fields as $key => $value ) {
			$is_array = is_array( $value );

			// Skip these input fields as they must be provided in some_field => value format.
			if ( ! $is_array && ! is_string( $key ) ) {
				continue;
			}

			// Check for meta_query formatting.
			if ( $is_array ) {
				// Check if we have a field provided, if so use that as $key.
				if ( isset( $value['field'] ) ) {
					$key = $value['field'];
				}

				// If we do not have a value provided, set up the meta query syntax to pass the whole array into value.
				if ( ! isset( $value['value'] ) ) {
					$value = [
						'value' => array_values( $value ),
					];
				}

				// Set the defaults of the meta query field.
				$field_query = [
					'field'    => $key,
					// Always enforce sanitizing.
					'sanitize' => true,
				];

				// Enforce the defaults.
				$value = array_merge( $value, $field_query );
			}

			// Do not use any input fields that provide disallowed characters.
			if ( $disallowed_pattern && false !== preg_match( $disallowed_pattern, $key ) ) {
				continue;
			}

			$prepared_input_fields[ $key ] = $value;
		}

		return $prepared_input_fields;
	}

	/**
	 * Prepare a list of input fields for simple syntax.
	 *
	 * @since 2.9.0
	 *
	 * @param array  $input_fields       The list of input fields to prepare.
	 * @param string $disallowed_pattern The disallowed pattern to prevent against.
	 *
	 * @return array The list of prepared input fields.
	 */
	private function prepare_input_fields_for_simple_syntax( array $input_fields, $disallowed_pattern = '' ) {
		$prepared_input_fields = [];

		foreach ( $input_fields as $input_field => $maybe_direction ) {
			// Detect if we are using some_field => ASC or not.
			if ( is_numeric( $input_field ) ) {
				$input_field = $maybe_direction;
			} else {
				$input_field .= ' ' . $maybe_direction;
			}

			// Do not use any input fields that provide disallowed characters.
			if ( $disallowed_pattern && false !== preg_match( $disallowed_pattern, $input_field ) ) {
				continue;
			}

			$prepared_input_fields[] = $input_field;
		}

		return $prepared_input_fields;
	}
}
