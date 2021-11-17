<?php

namespace Pods\REST\V1\Endpoints;

use PodsForm;
use Tribe__Documentation__Swagger__Provider_Interface as Swagger_Interface;
use Tribe__REST__Endpoints__CREATE_Endpoint_Interface as CREATE_Interface;
use Tribe__REST__Endpoints__READ_Endpoint_Interface as READ_Interface;
use WP_REST_Request;

class Fields extends Base implements READ_Interface, CREATE_Interface, Swagger_Interface {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public $route = '/fields';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public $object = 'field';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
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
						'description' => __( 'Returns all the tickets matching the search criteria.', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type'       => 'object',
									'properties' => [
										'rest_url'    => [
											'type'        => 'string',
											'format'      => 'uri',
											'description' => __( 'This results page REST URL.', 'pods' ),
										],
										'total'       => [
											'type'        => 'integer',
											'description' => __( 'The total number of results across all pages.', 'pods' ),
										],
										'total_pages' => [
											'type'        => 'integer',
											'description' => __( 'The total number of result pages matching the search criteria.', 'pods' ),
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
						'description' => __( 'One or more of the specified query variables has a bad format.', 'pods' ),
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
	 * @since 2.8.0
	 */
	public function READ_args() {
		return [
			'return_type'    => [
				'description' => __( 'The type of data to return.', 'pods' ),
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
			'types'          => [
				'required'         => false,
				'description'      => __( 'A list of types to filter by.', 'pods' ),
				'swagger_type'     => 'array',
				'items'            => [
					'type' => 'string',
				],
				'collectionFormat' => 'csv',
			],
			'ids'            => [
				'required'         => false,
				'description'      => __( 'A list of IDs to filter by.', 'pods' ),
				'swagger_type'     => 'array',
				'items'            => [
					'type' => 'integer',
				],
				'collectionFormat' => 'csv',
			],
			'args'           => [
				'required'     => false,
				'description'  => __( 'A list of arguments to filter by.', 'pods' ),
				'swagger_type' => 'array',
			],
			'include_parent' => [
				'type'        => 'boolean',
				'description' => __( 'Whether to include the parent Pod details (default: off).', 'pods' ),
				'default'     => false,
				'cli_boolean' => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function get( WP_REST_Request $request ) {
		return $this->archive_by_args( $request );
	}

	/**
	 * Determine whether access to READ is available.
	 *
	 * @since 2.8.0
	 *
	 * @return bool Whether access to READ is available.
	 */
	public function can_read() {
		return pods_is_admin( 'pods' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function CREATE_args() {
		return [
			'pod_id' => [
				'type'              => 'string',
				'description'       => __( 'The Pod ID.', 'pods' ),
				'validate_callback' => [ $this->validator, 'is_pod_id' ],
			],
			'pod'    => [
				'type'              => 'string',
				'description'       => __( 'The Pod name.', 'pods' ),
				'validate_callback' => [ $this->validator, 'is_pod_slug' ],
			],
			'group_id' => [
				'type'              => 'string',
				'description'       => __( 'The Group ID.', 'pods' ),
				'validate_callback' => [ $this->validator, 'is_group_id' ],
			],
			'group'    => [
				'type'              => 'string',
				'description'       => __( 'The Group name.', 'pods' ),
				'validate_callback' => [ $this->validator, 'is_group_slug' ],
			],
			'name'   => [
				'type'        => 'string',
				'description' => __( 'The name of the Field.', 'pods' ),
			],
			'label'  => [
				'type'        => 'string',
				'description' => __( 'The singular label of the Field.', 'pods' ),
				'required'    => true,
			],
			'type'   => [
				'type'        => 'string',
				'description' => __( 'The type of the Field.', 'pods' ),
				'enum'        => PodsForm::field_types_list(),
				'required'    => true,
			],
			'args'   => [
				'required'     => false,
				'description'  => __( 'A list of additional options to save to the Field.', 'pods' ),
				'swagger_type' => 'array',
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function create( WP_REST_REQUEST $request, $return_id = false ) {
		return $this->create_by_args( $request, $return_id );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function can_create() {
		return pods_is_admin( 'pods' );
	}
}
