<?php

namespace Pods\REST\V1\Endpoints;

use Pods\REST\Interfaces\Endpoints\DELETE_Interface;
use Pods\REST\Interfaces\Endpoints\READ_Interface;
use Pods\REST\Interfaces\Endpoints\UPDATE_Interface;
use Pods\REST\Interfaces\Swagger\Provider_Interface;
use WP_REST_Request;

class Field extends Base implements READ_Interface, UPDATE_Interface, DELETE_Interface, Provider_Interface {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public $route = '/fields/%1$d';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.11
	 */
	public $rest_route = '/fields/(?P<id>\\d+)';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.11
	 */
	public $rest_doc_route = '/fields/{id}';

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
			'type'    => 'string',
		];

		$POST_defaults = [
			'in'      => 'body',
			'default' => '',
			'type'    => 'string',
		];

		return [
			'get' => [
				'summary'    => 'Retrieve a specific Field',
				'parameters' => $this->swaggerize_args( $this->READ_args(), $GET_defaults ),
				'responses'  => [
					'200' => [
						'description' => 'Returns the requested Field with its details',
						'content'     => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Field',
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
						'description' => 'Unauthorized access - user does not have permission to access this Field',
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'404' => [
						'description' => 'The requested Field was not found',
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
			'put' => [
				'summary'    => 'Update a specific Field',
				'parameters' => $this->swaggerize_args( $this->EDIT_args(), $POST_defaults ),
				'responses'  => [
					'200' => [
						'description' => 'Returns the updated Field',
						'content'     => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Field',
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
						'description' => 'Unauthorized access - user does not have permission to update this Field',
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'404' => [
						'description' => 'The Field to update was not found',
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
			'delete' => [
				'summary'    => 'Delete a specific Field',
				'parameters' => $this->swaggerize_args( $this->DELETE_args(), $POST_defaults ),
				'responses'  => [
					'200' => [
						'description' => 'Returns the deleted Field details',
						'content'     => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Field',
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
						'description' => 'Unauthorized access - user does not have permission to delete this Field',
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'404' => [
						'description' => 'The Field to delete was not found',
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
			'id' => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The Field ID.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_field_id' ],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function get( WP_REST_Request $request ) {
		return $this->get_by_args( 'id', 'id', $request );
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
	public function EDIT_args() {
		return [
			'id'    => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The Field ID.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_field_id' ],
			],
			'name'  => [
				'type'        => 'string',
				'description' => __( 'The new name of the Field.', 'pods' ),
			],
			'label' => [
				'type'        => 'string',
				'description' => __( 'The singular label of the Field.', 'pods' ),
			],
			'type'  => [
				'type'        => 'string',
				'description' => __( 'The type of the Field.', 'pods' ),
			],
			'args'  => [
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
	public function update( WP_REST_Request $request ) {
		return $this->update_by_args( 'id', 'id', $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function can_edit() {
		return pods_is_admin( 'pods' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function DELETE_args() {
		return [
			'id' => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The Field ID.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_field_id' ],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function delete( WP_REST_Request $request ) {
		return $this->delete_by_args( 'id', 'id', $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function can_delete() {
		return pods_is_admin( 'pods' );
	}
}
