<?php

namespace Pods\REST\V1;

use Tribe__Documentation__Swagger__Builder_Interface as Swagger_Builder_Interface;
use Pods\REST\V1\Endpoints\Swagger_Documentation;
use Pods\REST\V1\Validator\Base;

/**
 * Class Service_Provider
 *
 * Add REST API endpoints and objects.
 *
 * @since 2.8
 */
class Service_Provider extends \tad_DI52_ServiceProvider {

	/**
	 * Binds and sets up implementations.
	 */
	public $namespace;

	/**
	 * Registers the classes and functionality needed fro REST API
	 *
	 * @since 2.8
	 */
	public function register() {
		tribe_singleton( 'pods.rest-v1.main', Main::class, [ 'hook' ] );
		tribe_singleton( 'pods.rest-v1.messages', Messages::class );
		tribe_singleton( 'pods.rest-v1.validator', Base::class );
		tribe_singleton( 'pods.rest-v1.repository', Post_Repository::class );
		tribe_singleton( 'pods.rest-v1.endpoints.documentation', Swagger_Documentation::class, [ 'hook' ] );

		tribe_singleton(
			'pods.rest-v1.endpoints.pods',
			new Tribe__Tickets__REST__V1__Endpoints__Pods(
				tribe( 'pods.rest-v1.messages' ),
				tribe( 'pods.rest-v1.repository' ),
				tribe( 'pods.rest-v1.validator' )
			)
		);
		tribe_singleton(
			'pods.rest-v1.endpoints.fields',
			new Tribe__Tickets__REST__V1__Endpoints__Fields(
				tribe( 'pods.rest-v1.messages' ),
				tribe( 'pods.rest-v1.repository' ),
				tribe( 'pods.rest-v1.validator' )
			)
		);
		tribe_singleton(
			'pods.rest-v1.endpoints.groups',
			new Tribe__Tickets__REST__V1__Endpoints__Groups(
				tribe( 'pods.rest-v1.messages' ),
				tribe( 'pods.rest-v1.repository' ),
				tribe( 'pods.rest-v1.validator' )
			)
		);

		$this->hooks();
	}

	/**
	 * Registers the REST API endpoints.
	 *
	 * @since 2.8
	 */
	public function register_endpoints() {
		/** @var Main $main */
		$main = tribe( 'pods.rest-v1.main' );

		$this->namespace = $main->get_events_route_namespace();

		$doc_endpoint = $this->register_documentation_endpoint();

		$this->register_endpoints();

		// @todo add the endpoints as documentation providers here
		$doc_endpoint->register_documentation_provider( '/doc', $doc_endpoint );
	}

	/**
	 * Builds, registers and returns the Swagger.io documentation provider endpoint.
	 *
	 * @since 2.8
	 *
	 * @return Swagger_Builder_Interface
	 */
	protected function register_documentation_endpoint() {
		/** @var Swagger_Builder_Interface $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.documentation' );

		register_rest_route( $this->namespace, '/doc', [
			'methods'  => WP_REST_Server::READABLE,
			'callback' => [ $endpoint, 'get' ],
		] );

		$endpoint->register_definition_provider( 'Attendee', new Tribe__Tickets__REST__V1__Documentation__Attendee_Definition_Provider() );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoints that will handle requests.
	 *
	 * @since 2.8
	 *
	 * @return Tribe__Tickets__REST__V1__Endpoints__Pods
	 */
	protected function register_endpoint_pods() {
		/** @var Tribe__Tickets__REST__V1__Endpoints__Pods $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.pods' );

		register_rest_route( $this->namespace, '/tickets/(?P<id>\\d+)', [
			'methods'  => WP_REST_Server::READABLE,
			'args'     => $endpoint->READ_args(),
			'callback' => [ $endpoint, 'get' ],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/pods/{id}', $endpoint );

		return $endpoint;
	}

	/**
	 * Registers the REST API endpoint that will handle ticket archive requests.
	 *
	 * @since 2.8
	 *
	 * @return Tribe__Tickets__REST__V1__Endpoints__Ticket_Archive
	 */
	protected function register_ticket_archive_endpoint() {
		/** @var Tribe__Tickets__REST__V1__Endpoints__Ticket_Archive $endpoint */
		$endpoint = tribe( 'pods.rest-v1.endpoints.tickets-archive' );

		register_rest_route( $this->namespace, '/tickets', [
			'methods'  => WP_REST_Server::READABLE,
			'args'     => $endpoint->READ_args(),
			'callback' => [ $endpoint, 'get' ],
		] );

		tribe( 'pods.rest-v1.endpoints.documentation' )->register_documentation_provider( '/tickets', $endpoint );

		return $endpoint;
	}

	/**
	 * Hooks all the methods and actions the class needs.
	 *
	 * @since 2.8
	 */
	protected function hooks() {
		add_action( 'rest_api_init', [ $this, 'register_endpoints' ] );
	}
}
