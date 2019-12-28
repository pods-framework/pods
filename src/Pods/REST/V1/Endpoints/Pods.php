<?php

namespace Pods\REST\V1\Endpoints;

use Tribe__Documentation__Swagger__Provider_Interface as Swagger_Interface;
use Tribe__REST__Endpoints__CREATE_Endpoint_Interface as CREATE_Interface;
use Tribe__REST__Endpoints__READ_Endpoint_Interface as READ_Interface;
use Tribe__Utils__Array;
use WP_REST_Request;

class Pods
	extends Base
	implements READ_Interface,
	CREATE_Interface,
	Swagger_Interface {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public $route = '/pods';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function get_documentation() {
		$GET_defaults = [
			'in'      => 'query',
			'default' => '',
		];

		// @todo Handle get/post

		return [
			'get' => [
				'parameters' => $this->swaggerize_args( $this->READ_args(), $GET_defaults ),
				'responses'  => [
					'200' => [
						'description' => __( 'Returns all the tickets matching the search criteria', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type'       => 'object',
									'properties' => [
										'rest_url'    => [
											'type'        => 'string',
											'format'      => 'uri',
											'description' => __( 'This results page REST URL', 'pods' ),
										],
										'total'       => [
											'type'        => 'integer',
											'description' => __( 'The total number of results across all pages', 'pods' ),
										],
										'total_pages' => [
											'type'        => 'integer',
											'description' => __( 'The total number of result pages matching the search criteria', 'pods' ),
										],
										'tickets'     => [
											'type'  => 'array',
											'items' => [ '$ref' => '#/components/schemas/Ticket' ],
										],
									],
								],
							],
						],
					],
					'400' => [
						'description' => __( 'One or more of the specified query variables has a bad format', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'404' => [
						'description' => __( 'The requested page was not found.', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function READ_args() {
		return [
			'return_type' => [
				'description' => __( 'The type of data to return for the Pods.', 'pods' ),
				'type'        => 'string',
				'default'     => 'full',
				'required'    => false,
				'enum'        => [
					'full',
					'names',
					'names_ids',
					'ids',
					'key_names',
					'count',
				],
			],
			'types'       => [
				'required'         => false,
				'description'      => __( 'A list of types to filter by.', 'pods' ),
				'swagger_type'     => 'array',
				'items'            => [
					'type' => 'string',
				],
				'collectionFormat' => 'csv',
			],
			'ids'         => [
				'required'         => false,
				'description'      => __( 'A list of IDs to filter by.', 'pods' ),
				'swagger_type'     => 'array',
				'items'            => [
					'type' => 'integer',
				],
				'collectionFormat' => 'csv',
			],
			'args'        => [
				'required'     => false,
				'description'  => __( 'A list of arguments to filter Pods by.', 'pods' ),
				'swagger_type' => 'array',
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function get( WP_REST_Request $request ) {
		$params = [
			'return_type' => $request['return_type'],
		];

		if ( ! empty( $request['types'] ) ) {
			$params['type'] = Tribe__Utils__Array::list_to_array( $request['types'] );
		}

		if ( ! empty( $request['ids'] ) ) {
			$params['id'] = Tribe__Utils__Array::list_to_array( $request['ids'] );
		}

		if ( ! empty( $request['args'] ) ) {
			$params['args'] = $request['args'];

			// Attempt to convert from JSON to array if needed.
			if ( is_string( $params['args'] ) ) {
				$json = @json_decode( $params['args'], true );

				if ( is_array( $json ) ) {
					$params['args'] = $json;
				}
			}
		}

		if ( ! empty( $request['return_type'] ) ) {
			$params['return_type'] = $request['return_type'];
		}

		$data = [
			'pods' => pods_api()->load_pods( $params ),
		];

		return $data;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function CREATE_args() {
		return [
			'provider' => [
				'type'              => 'string',
				'in'                => 'body',
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_string' ],
				'sanitize_callback' => 'sanitize_text_field',
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function create( WP_REST_REQUEST $request, $return_id = false ) {
		$data = [];

		return $data;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function can_create() {
		// @todo Check Pods permissions
		return true;
	}
}
