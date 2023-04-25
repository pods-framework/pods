<?php

namespace Pods\Integrations\WPGraphQL;

use Exception;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use Pods\Integrations\WPGraphQL\Connection_Resolver\Custom_Simple;
use Pods\Integrations\WPGraphQL\Connection_Resolver\Pod;
use Pods\Integrations\WPGraphQL\Connection_Resolver\Pod_Type;
use WPGraphQL\AppContext;
use WPGraphQL\Data\Connection\AbstractConnectionResolver;
use WPGraphQL\Data\Connection\CommentConnectionResolver;
use WPGraphQL\Data\Connection\ContentTypeConnectionResolver;
use WPGraphQL\Data\Connection\MenuConnectionResolver;
use WPGraphQL\Data\Connection\MenuItemConnectionResolver;
use WPGraphQL\Data\Connection\PluginConnectionResolver;
use WPGraphQL\Data\Connection\PostObjectConnectionResolver;
use WPGraphQL\Data\Connection\TaxonomyConnectionResolver;
use WPGraphQL\Data\Connection\TermObjectConnectionResolver;
use WPGraphQL\Data\Connection\ThemeConnectionResolver;
use WPGraphQL\Data\Connection\UserConnectionResolver;
use WPGraphQL\Data\Connection\UserRoleConnectionResolver;

/**
 * Connection interfacing functionality for GraphQL.
 *
 * @since 2.9.0
 */
class Connection {

	/**
	 * List of arguments.
	 *
	 * @since 2.9.0
	 *
	 * @var array
	 */
	private $args;

	/**
	 * @since 2.9.0
	 *
	 * @param The WPGraphQL field object to reference.
	 *
	 * @var Field
	 */
	private $field;

	/**
	 * Connection constructor.
	 *
	 * @since 2.9.0
	 *
	 * @param array $args List of arguments to setup.
	 */
	public function __construct( $args ) {
		$this->field = $args['field'];

		unset( $args['field'] );

		$this->args = $args;

		$required = [
			'from',
			'to',
			'from_field',
			'name',
			'object_type',
		];

		// If we are missing required arguments, don't register the connection.
		foreach ( $required as $arg ) {
			if ( empty( $this->args[ $arg ] ) ) {
				return;
			}
		}

		// Register the connection.
		$this->register_connection();
	}

	/**
	 * Register the connection.
	 *
	 * @since 2.9.0
	 */
	public function register_connection() {
		$config = [
			'fromType'           => $this->args['from'],
			'toType'             => $this->args['to'],
			'fromFieldName'      => $this->args['from_field'],
			'connectionTypeName' => $this->args['name'],
			'resolve'            => [ $this, 'get_connection_from_resolver' ],
		];

		if ( empty( $this->args['is_multi'] ) ) {
			$config['oneToOne'] = true;
		}

		try {
			register_graphql_connection( $config );
		} catch ( Exception $exception ) {
			// Connection was not registered.
			pods_debug_log( $exception );

			return;
		}
	}

	/**
	 * Get the connection from the resolver.
	 *
	 * @param mixed       $source  The source passed down from the resolve tree.
	 * @param array       $args    List of arguments input in the field as part of the GraphQL query.
	 * @param AppContext  $context Object containing app context that gets passed down the resolve tree.
	 * @param ResolveInfo $info    Info about fields passed down the resolve tree.
	 *
	 * @return array|Deferred|mixed|null
	 * @throws Exception
	 */
	public function get_connection_from_resolver( $source, $args, $context, $info ) {
		$resolver = $this->get_connection_resolver( $source, $args, $context, $info );

		if ( ! $resolver ) {
			return null;
		}

		if ( ! $this->args['is_multi'] ) {
			$resolver->one_to_one();
		}

		return $resolver->get_connection();
	}

	/**
	 * Get the connection resolver.
	 *
	 * @since 2.9.0
	 *
	 * @param mixed       $source  The source passed down from the resolve tree.
	 * @param array       $args    List of arguments input in the field as part of the GraphQL query.
	 * @param AppContext  $context Object containing app context that gets passed down the resolve tree.
	 * @param ResolveInfo $info    Info about fields passed down the resolve tree.
	 *
	 * @return AbstractConnectionResolver|null The resolver object or null if not supported.
	 *
	 * @throws Exception
	 */
	public function get_connection_resolver( $source, $args, $context, $info ) {
		$object_type = $this->args['object_type'];
		$object_name = pods_v( 'object_name', $this->args, 'any' );

		$field_value = (array) $this->field->get_field_value( $source, $args, $context, $info );

		if ( empty( $field_value ) ) {
			$field_value = [
				0,
			];
		}

		switch ( $object_type ) {
			case 'post_type':
				$connection = new PostObjectConnectionResolver( $source, $args, $context, $info, $object_name );
				$connection->set_query_arg( 'post__in', $field_value );

				return $connection;
			case 'post_type_object':
				$connection = new ContentTypeConnectionResolver( $source, $args, $context, $info );
				$connection->set_query_arg( 'contentTypeNames', $field_value );

				return $connection;
			case 'taxonomy':
				$connection = new TermObjectConnectionResolver( $source, $args, $context, $info, $object_name );
				$connection->set_query_arg( 'include', $field_value );

				return $connection;
			case 'taxonomy_object':
				$connection = new TaxonomyConnectionResolver( $source, $args, $context, $info );
				$connection->set_query_arg( 'in', $field_value );

				return $connection;
			case 'user':
				$connection = new UserConnectionResolver( $source, $args, $context, $info );
				$connection->set_query_arg( 'include', $field_value );

				return $connection;
			case 'user_role':
				$connection = new UserRoleConnectionResolver( $source, $args, $context, $info );
				$connection->set_query_arg( 'slugIn', $field_value );

				return $connection;
			case 'attachment':
			case 'media':
				$connection = new PostObjectConnectionResolver( $source, $args, $context, $info, 'attachment' );
				$connection->set_query_arg( 'post__in', $field_value );

				return $connection;
			case 'comment':
				$connection = new CommentConnectionResolver( $source, $args, $context, $info );
				$connection->set_query_arg( 'comment__in', $field_value );

				return $connection;
			case 'menu':
				$connection = new MenuConnectionResolver( $source, $args, $context, $info );
				$connection->set_query_arg( 'include', $field_value );

				return $connection;
			case 'menu_item':
				$connection = new MenuItemConnectionResolver( $source, $args, $context, $info );
				$connection->set_query_arg( 'post__in', $field_value );

				return $connection;
			case 'plugin':
				$connection = new PluginConnectionResolver( $source, $args, $context, $info );

				// @todo Plugin connection does not support filtering, we probably need to add that.
				$connection->set_query_arg( 'include', $field_value );

				return $connection;
			case 'theme':
				$connection = new ThemeConnectionResolver( $source, $args, $context, $info );

				// @todo Theme connection does not support filtering, we probably need to add that.
				$connection->set_query_arg( 'include', $field_value );

				return $connection;
			case 'pod':
				$connection = new Pod( $source, $args, $context, $info, $object_name );

				$where = [
					[
						'field'   => 'id',
						'value'   => $field_value,
						'compare' => 'IN',
					],
				];

				$connection->set_query_arg( 'where', $where );

				return $connection;
			case 'pod_type':
				$connection = new Pod_Type( $source, $args, $context, $info );

				$connection->set_query_arg( 'id', $field_value );

				return $connection;
			case 'custom-simple':
				$simple_data = pods_v( 'data', pods_v( 'field', $this->args, [] ), [] );

				$connection = new Custom_Simple( $source, $args, $context, $info, $simple_data );

				$connection->set_query_arg( 'values', $field_value );

				return $connection;
		}

		return null;
	}

}
