<?php

namespace Pods\REST\V1\Endpoints;

use Pods\REST\Interfaces\Endpoints\CREATE_Interface;
use Pods\REST\Interfaces\Endpoints\READ_Interface;
use Pods\REST\Interfaces\Swagger\Provider_Interface;
use WP_REST_Request;

class Groups extends Base implements READ_Interface, CREATE_Interface, Provider_Interface {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public $route = '/groups';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public $object = 'group';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function get_documentation() {
		$GET_defaults = [
			'in'      => 'query',
			'default' => '',
			'type'    => 'string',
		];

		$POST_defaults = [
			'in'      => 'body',
			'default' => '',
			'type'    => 'string',
		];

		return [
			'get' => [
				'summary'    => 'Retrieve a collection of Groups',
				'parameters' => $this->swaggerize_args( $this->READ_args(), $GET_defaults ),
				'responses'  => [
					'200' => [
						'description' => 'Returns a collection of Groups matching the request',
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
										'groups'     => [
											'type'  => 'array',
											'items' => [ '$ref' => '#/components/schemas/Group' ],
										],
									],
								],
							],
						],
					],
					'400' => [
						'description' => 'The request was invalid or cannot be otherwise served',
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'401' => [
						'description' => 'Unauthorized access - user does not have permission to access Groups',
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
			'post' => [
				'summary'    => 'Create a new Group',
				'parameters' => $this->swaggerize_args( $this->CREATE_args(), $POST_defaults ),
				'responses'  => [
					'201' => [
						'description' => 'Returns the newly created Group',
						'content'     => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Group',
								],
							],
						],
					],
					'400' => [
						'description' => 'The request was invalid or cannot be otherwise served',
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'401' => [
						'description' => 'Unauthorized access - user does not have permission to create Groups',
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'403' => [
						'description' => 'Forbidden - creation of this Group is not allowed',
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
			'return_type' => [
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
				'description'  => __( 'A list of arguments to filter by.', 'pods' ),
				'swagger_type' => 'array',
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
			'name'   => [
				'type'        => 'string',
				'description' => __( 'The name of the Group.', 'pods' ),
			],
			'label'  => [
				'type'        => 'string',
				'description' => __( 'The singular label of the Group.', 'pods' ),
				'required'    => true,
			],
			'args'   => [
				'required'     => false,
				'description'  => __( 'A list of additional options to save to the Group.', 'pods' ),
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
