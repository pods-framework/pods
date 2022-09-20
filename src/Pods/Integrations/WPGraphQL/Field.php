<?php

namespace Pods\Integrations\WPGraphQL;

use GraphQL\Type\Definition\ResolveInfo;
use Pods\Pod_Manager;
use Pods\Whatsit\Field as Pod_Field;
use Pods\Whatsit\Pod;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Loader\PostObjectLoader;
use WPGraphQL\Model\Comment;
use WPGraphQL\Model\Menu;
use WPGraphQL\Model\MenuItem;
use WPGraphQL\Model\Model;
use WPGraphQL\Model\Post;
use WPGraphQL\Model\Term;
use WPGraphQL\Model\User;

/**
 * Field interfacing functionality for GraphQL.
 *
 * @since 2.9.0
 */
class Field {

	/**
	 * The field data.
	 *
	 * @since 2.9.0
	 *
	 * @var Pod_Field
	 */
	private $field;

	/**
	 * The pod data.
	 *
	 * @since 2.9.0
	 *
	 * @var Pod
	 */
	private $pod;

	/**
	 * List of arguments.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	private $args;

	/**
	 * List of connections that need to be registered later.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	private static $connections = [];

	/**
	 * Field constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param Pod_Field $field The field data.
	 * @param Pod       $pod   The pod data.
	 * @param array     $args  List of arguments to setup.
	 */
	public function __construct( $field, $pod, $args ) {
		$this->field = $field;
		$this->pod   = $pod;
		$this->args  = $args;

		$required = [
			'type_name',
			'field_name',
			'graphql_type',
		];

		// If we are missing required arguments, don't register the field.
		foreach ( $required as $arg ) {
			if ( empty( $this->args[ $arg ] ) ) {
				return;
			}
		}

		// Register the field.
		$this->register_field();
	}

	/**
	 * Register the field.
	 *
	 * @since 2.9.0
	 */
	public function register_field() {
		$is_connection   = 'connection' === $this->args['graphql_format'] && ! empty( $this->args['related_type_name'] );
		$is_multi        = is_array( $this->args['graphql_type'] ) && isset( $this->args['graphql_type']['list_of'] );
		$connection_name = null;

		if ( $is_connection ) {
			$connection_name = $this->args['type_name'] . '_' . $this->args['field_name'] . '_connection';

			$this->args['graphql_type'] = $connection_name;

			if ( $is_multi ) {
				$this->args['graphql_type'] = [
					'list_of' => $this->args['graphql_type'],
				];
			}
		}

		$config = [
			'type'    => $this->args['graphql_type'],
			'args'    => [
				'format' => [
					'type'        => 'PostObjectFieldFormatEnum',
					'description' => __( 'Format of the field output', 'pods' ),
				],
			],
			'resolve' => [ $this, 'get_field_value' ],
		];

		// Remove the format arg if the type is not a string.
		if ( 'String' !== $this->args['graphql_type'] && ( ! $is_multi || 'String' !== $this->args['graphql_type']['list_of'] ) ) {
			unset( $config['args'] );
		}

		try {
			// Save the config for reference later.
			$this->args['config']   = $config;
			$this->args['is_multi'] = $is_multi;

			if ( ! $is_connection ) {
				register_graphql_field( $this->args['type_name'], $this->args['field_name'], $config );
			}

			// Maybe add a connection to set up later.
			if ( $is_connection ) {
				self::$connections[] = [
					'from'        => $this->args['type_name'],
					'from_field'  => $this->args['field_name'],
					'name'        => $connection_name,
					'to'          => $this->args['related_type_name'],
					'object_type' => $this->args['related_object_type'],
					'object_name' => $this->args['related_object_name'],
					'is_multi'    => $is_multi,
					'field'       => $this,
				];
			}
		} catch ( Exception $exception ) {
			// Connection was not registered.
			throw $exception;
		}
	}

	/**
	 * Get the ID from the model object.
	 *
	 * @since 2.9.0
	 *
	 * @param Model $source The model object.
	 *
	 * @return int|null The ID or null if not supported.
	 */
	public function get_id_from_model( $source ) {
		switch ( true ) {
			case $source instanceof Term:
				$id = $source->term_id;
				break;
			case $source instanceof Post:
				$id = $source->ID;
				break;
			case $source instanceof MenuItem:
				$id = $source->menuItemId;
				break;
			case $source instanceof Menu:
				$id = $source->menuId;
				break;
			case $source instanceof User:
				$id = $source->userId;
				break;
			case $source instanceof Comment:
				$id = $source->comment_ID;
				break;
			default:
				$id = null;
				break;
		}

		return $id;
	}

	/**
	 * Get the ID from the root object.
	 *
	 * @since 2.9.0
	 *
	 * @param Model       $source  The source passed down from the resolve tree.
	 * @param array       $args    List of arguments input in the field as part of the GraphQL query.
	 * @param AppContext  $context Object containing app context that gets passed down the resolve tree.
	 * @param ResolveInfo $info    Info about fields passed down the resolve tree.
	 *
	 * @return mixed The field value.
	 */
	public function get_field_value( $source, $args, $context, $info ) {
		$id = $this->get_id_from_model( $source );

		if ( null === $id ) {
			return '';
		}

		$pod = pods_container( Pod_Manager::class )->get_pod( $this->pod->get_name(), $id );

		$field_name = $this->field->get_name();

		$format = pods_v( 'format', $args, 'rendered', true );

		if ( in_array( $this->field['type'], [ 'pick', 'file' ], true ) ) {
			$return_type = $this->args[ $this->field['type'] . '_format' ];

			// Force different formats based on return type.
			if ( 'connection' === $return_type ) {
				$format = 'raw';
			} elseif ( 'id' === $return_type ) {
				$format = 'raw';
			} elseif ( 'title' === $return_type ) {
				$format = 'rendered';
			} elseif ( in_array( $return_type, [ 'view-url', 'asset-url' ], true ) ) {
				$format = 'raw';

				$field_name .= '.permalink';
			}
		}

		// Maybe return the raw field value.
		if (
			$this->args['is_multi']
			|| (
				'String' !== $this->args['graphql_type']
				&& (
					! $this->args['is_multi']
					|| 'String' !== $this->args['graphql_type']['list_of']
				)
			)
		) {
			$field_params = [
				'name'   => $field_name,
				'output' => 'ids',
			];

			// If this is a multi field and we need to get the rendered format, turn display on.
			if ( $this->args['is_multi'] && 'rendered' === $format ) {
				// Handle formatting.
				$field_params['display'] = true;

				// Format the values individually.
				$field_params['display_process_individually'] = true;
			}

			$value = $pod->field( $field_params );

			if ( ! $this->args['is_multi'] && is_array( $value ) ) {
				// Maybe force a string.
				$value = implode( ',', $value );
			} elseif ( $this->args['is_multi'] && ! is_array( $value ) ) {
				// Maybe force an array.
				if ( '' === $value || null === $value ) {
					$value = [];
				} else {
					$value = (array) $value;
				}
			}

			return $value;
		}

		// Check if we are need the raw format.
		if ( 'raw' === $format ) {
			$field_params = [
				'name'   => $field_name,
				'output' => 'ids',
			];

			// Return the field in the raw context.
			return $pod->field( $field_params );
		}

		// Return the field in the normal render context.
		return $pod->display( $field_name );
	}

	/**
	 * Register the connections.
	 *
	 * @since 2.9.0
	 */
	public static function register_connections() {
		if ( empty( self::$connections ) ) {
			return;
		}

		// Register the connections.
		foreach ( self::$connections as $connection ) {
			new Connection( $connection );
		}
	}

}
