<?php

namespace Pods\REST\V1;

use Pods\REST\V1\Endpoints\Field;
use Pods\REST\V1\Endpoints\Field_Slug;
use Pods\REST\V1\Endpoints\Fields;
use Pods\REST\V1\Endpoints\Group;
use Pods\REST\V1\Endpoints\Group_Slug;
use Pods\REST\V1\Endpoints\Groups;
use Pods\REST\V1\Endpoints\Pod;
use Pods\REST\V1\Endpoints\Pod_Slug;
use Pods\REST\V1\Endpoints\Pods;
use Pods\REST\V1\Endpoints\Swagger_Documentation;
use Pods\REST\V1\Validator\Base as Base_Validator;
use Tribe__Documentation__Swagger__Builder_Interface as Swagger_Builder_Interface;
use WP_REST_Server;

/**
 * Class Service_Provider
 *
 * Add REST API endpoints and objects.
 *
 * @since 2.8.0
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public $namespace;

	/**
	 * Registers the classes and functionality needed for the REST API.
	 *
	 * @since 2.8.0
	 */
	public function register() {
		$this->container->singleton( 'pods.rest-v1.main', Main::class );
		$this->container->singleton( 'pods.rest-v1.messages', Messages::class );
		$this->container->singleton( 'pods.rest-v1.validator', Base_Validator::class );
		$this->container->singleton( 'pods.rest-v1.repository', Post_Repository::class );
		$this->container->singleton( 'pods.rest-v1.endpoints.documentation', Swagger_Documentation::class, [ 'hook' ] );

		$messages        = tribe( 'pods.rest-v1.messages' );
		$post_repository = tribe( 'pods.rest-v1.repository' );
		$validator       = tribe( 'pods.rest-v1.validator' );

		$this->container->singleton( 'pods.rest-v1.endpoints.pods', new Pods( $messages, $post_repository, $validator ) );
		$this->container->singleton( 'pods.rest-v1.endpoints.pod', new Pod( $messages, $post_repository, $validator ) );
		$this->container->singleton( 'pods.rest-v1.endpoints.pod-slug', new Pod_Slug( $messages, $post_repository, $validator ) );
		$this->container->singleton( 'pods.rest-v1.endpoints.fields', new Fields( $messages, $post_repository, $validator ) );
		$this->container->singleton( 'pods.rest-v1.endpoints.field', new Field( $messages, $post_repository, $validator ) );
		$this->container->singleton( 'pods.rest-v1.endpoints.field-slug', new Field_Slug( $messages, $post_repository, $validator ) );
		$this->container->singleton( 'pods.rest-v1.endpoints.groups', new Groups( $messages, $post_repository, $validator ) );
		$this->container->singleton( 'pods.rest-v1.endpoints.group', new Group( $messages, $post_repository, $validator ) );
		$this->container->singleton( 'pods.rest-v1.endpoints.group-slug', new Group_Slug( $messages, $post_repository, $validator ) );

		$this->hooks();
	}

	/**
	 * Registers the REST API endpoints.
	 *
	 * @since 2.8.0
	 */
	public function register_endpoints() {
		/** @var Main $main */
		$main = tribe( 'pods.rest-v1.main' );

		$this->namespace = $main->get_pods_route_namespace();

		$this->register_endpoint_documentation();

		$this->register_endpoint_pods();
		$this->register_endpoint_pod();
		$this->register_endpoint_pod_slug();
		$this->register_endpoint_fields();
		$this->register_endpoint_field();
		$this->register_endpoint_field_slug();
		$this->register_endpoint_groups();
		$this->register_endpoint_group();
		$this->register_endpoint_group_slug();
	}

	/**
	 * Builds, registers and returns the Swagger.io documentation provider endpoint.
	 *
	 * @since 2.8.0
	 *
	 * @return Swagger_Builder_Interface
	 */
	protected function register_endpoint_documentation() {
		/** @var Swagger_Builder_Interface $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.documentation' );

		register_rest_route( $this->namespace, '/doc', [
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => [ $endpoint, 'get' ],
			'permission_callback' => '__return_true',
		] );

		//$endpoint->register_definition_provider( 'Attendee', new Tribe__Tickets__REST__V1__Documentation__Attendee_Definition_Provider() );

		$endpoint->register_documentation_provider( '/doc', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle requests.
	 *
	 * @since 2.8.0
	 *
	 * @return Pods
	 */
	protected function register_endpoint_pods() {
		/** @var Pods $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.pods' );

		register_rest_route( $this->namespace, $endpoint->route, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => $endpoint->READ_args(),
				'callback'            => [ $endpoint, 'get' ],
				'permission_callback' => [ $endpoint, 'can_read' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'args'                => $endpoint->CREATE_args(),
				'callback'            => [ $endpoint, 'create' ],
				'permission_callback' => [ $endpoint, 'can_create' ],
			],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/pods', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle requests.
	 *
	 * @since 2.8.0
	 *
	 * @return Pod
	 */
	protected function register_endpoint_pod() {
		/** @var Pod $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.pod' );

		register_rest_route( $this->namespace, sprintf( str_replace( '$d', '$s', $endpoint->route ), '(?P<id>\\d+)' ), [
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => $endpoint->READ_args(),
				'callback'            => [ $endpoint, 'get' ],
				'permission_callback' => [ $endpoint, 'can_read' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'args'                => $endpoint->EDIT_args(),
				'callback'            => [ $endpoint, 'update' ],
				'permission_callback' => [ $endpoint, 'can_edit' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'args'                => $endpoint->DELETE_args(),
				'callback'            => [ $endpoint, 'delete' ],
				'permission_callback' => [ $endpoint, 'can_delete' ],
			],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/pods/{id}', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle requests.
	 *
	 * @since 2.8.0
	 *
	 * @return Pod_Slug
	 */
	protected function register_endpoint_pod_slug() {
		/** @var Pod_Slug $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.pod-slug' );

		register_rest_route( $this->namespace, sprintf( $endpoint->route, '(?P<slug>[\w\_\-]+)' ), [
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => $endpoint->READ_args(),
				'callback'            => [ $endpoint, 'get' ],
				'permission_callback' => [ $endpoint, 'can_read' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'args'                => $endpoint->EDIT_args(),
				'callback'            => [ $endpoint, 'update' ],
				'permission_callback' => [ $endpoint, 'can_edit' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'args'                => $endpoint->DELETE_args(),
				'callback'            => [ $endpoint, 'delete' ],
				'permission_callback' => [ $endpoint, 'can_delete' ],
			],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/pods/{slug}', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle requests.
	 *
	 * @since 2.8.0
	 *
	 * @return Fields
	 */
	protected function register_endpoint_fields() {
		/** @var Fields $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.fields' );

		register_rest_route( $this->namespace, $endpoint->route, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => $endpoint->READ_args(),
				'callback'            => [ $endpoint, 'get' ],
				'permission_callback' => [ $endpoint, 'can_read' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'args'                => $endpoint->CREATE_args(),
				'callback'            => [ $endpoint, 'create' ],
				'permission_callback' => [ $endpoint, 'can_create' ],
			],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/fields', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle requests.
	 *
	 * @since 2.8.0
	 *
	 * @return Field
	 */
	protected function register_endpoint_field() {
		/** @var Field $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.field' );

		register_rest_route( $this->namespace, sprintf( str_replace( '$d', '$s', $endpoint->route ), '(?P<id>\\d+)' ), [
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => $endpoint->READ_args(),
				'callback'            => [ $endpoint, 'get' ],
				'permission_callback' => [ $endpoint, 'can_read' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'args'                => $endpoint->EDIT_args(),
				'callback'            => [ $endpoint, 'update' ],
				'permission_callback' => [ $endpoint, 'can_edit' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'args'                => $endpoint->DELETE_args(),
				'callback'            => [ $endpoint, 'delete' ],
				'permission_callback' => [ $endpoint, 'can_delete' ],
			],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/fields/{id}', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle requests.
	 *
	 * @since 2.8.0
	 *
	 * @return Field_Slug
	 */
	protected function register_endpoint_field_slug() {
		/** @var Field_Slug $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.field-slug' );

		register_rest_route( $this->namespace, sprintf( $endpoint->route, '(?P<pod>[\w\_\-]+)', '(?P<slug>[\w\_\-]+)' ), [
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => $endpoint->READ_args(),
				'callback'            => [ $endpoint, 'get' ],
				'permission_callback' => [ $endpoint, 'can_read' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'args'                => $endpoint->EDIT_args(),
				'callback'            => [ $endpoint, 'update' ],
				'permission_callback' => [ $endpoint, 'can_edit' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'args'                => $endpoint->DELETE_args(),
				'callback'            => [ $endpoint, 'delete' ],
				'permission_callback' => [ $endpoint, 'can_delete' ],
			],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/fields/{slug}', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle requests.
	 *
	 * @since 2.8.0
	 *
	 * @return Groups
	 */
	protected function register_endpoint_groups() {
		/** @var Groups $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.groups' );

		register_rest_route( $this->namespace, $endpoint->route, [
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => $endpoint->READ_args(),
				'callback'            => [ $endpoint, 'get' ],
				'permission_callback' => [ $endpoint, 'can_read' ],
			],
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'args'                => $endpoint->CREATE_args(),
				'callback'            => [ $endpoint, 'create' ],
				'permission_callback' => [ $endpoint, 'can_create' ],
			],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/groups', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle requests.
	 *
	 * @since 2.8.0
	 *
	 * @return Group
	 */
	protected function register_endpoint_group() {
		/** @var Group $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.group' );

		register_rest_route( $this->namespace, sprintf( str_replace( '$d', '$s', $endpoint->route ), '(?P<id>\\d+)' ), [
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => $endpoint->READ_args(),
				'callback'            => [ $endpoint, 'get' ],
				'permission_callback' => [ $endpoint, 'can_read' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'args'                => $endpoint->EDIT_args(),
				'callback'            => [ $endpoint, 'update' ],
				'permission_callback' => [ $endpoint, 'can_edit' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'args'                => $endpoint->DELETE_args(),
				'callback'            => [ $endpoint, 'delete' ],
				'permission_callback' => [ $endpoint, 'can_delete' ],
			],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/groups/{id}', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle requests.
	 *
	 * @since 2.8.0
	 *
	 * @return Group_Slug
	 */
	protected function register_endpoint_group_slug() {
		/** @var Group_Slug $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.group-slug' );

		register_rest_route( $this->namespace, sprintf( $endpoint->route, '(?P<pod>[\w\_\-]+)', '(?P<slug>[\w\_\-]+)' ), [
			[
				'methods'             => WP_REST_Server::READABLE,
				'args'                => $endpoint->READ_args(),
				'callback'            => [ $endpoint, 'get' ],
				'permission_callback' => [ $endpoint, 'can_read' ],
			],
			[
				'methods'             => WP_REST_Server::EDITABLE,
				'args'                => $endpoint->EDIT_args(),
				'callback'            => [ $endpoint, 'update' ],
				'permission_callback' => [ $endpoint, 'can_edit' ],
			],
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'args'                => $endpoint->DELETE_args(),
				'callback'            => [ $endpoint, 'delete' ],
				'permission_callback' => [ $endpoint, 'can_delete' ],
			],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/groups/{slug}', $endpoint );

		return $endpoint;
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8.0
	 */
	protected function hooks() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}
}
