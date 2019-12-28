<?php

namespace Pods\REST\V1\Endpoints;

use Tribe__REST__Endpoints__DELETE_Endpoint_Interface as DELETE_Interface;
use Tribe__REST__Endpoints__READ_Endpoint_Interface as READ_Interface;
use Tribe__REST__Endpoints__UPDATE_Endpoint_Interface as UPDATE_Interface;
use Tribe__Documentation__Swagger__Provider_Interface as Swagger_Interface;
use WP_REST_Request;

class Pod
	extends Base
	implements READ_Interface,
	UPDATE_Interface,
	DELETE_Interface,
	Swagger_Interface {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function get_documentation() {
		$GET_defaults = [
			'in'      => 'query',
			'default' => '',
			'type'    => 'string',
		];

		// @todo Handle get/post/delete

		return [
			'get' => [
				'summary'    => __( 'Returns a single ticket data', 'pods' ),
				'parameters' => $this->swaggerize_args( $this->READ_args(), $GET_defaults ),
				'responses'  => [
					'200' => [
						'description' => __( 'Returns the data of the ticket with the specified post ID', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Ticket',
								],
							],
						],
					],
					'400' => [
						'description' => __( 'The ticket post ID is invalid.', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'401' => [
						'description' => __( 'The ticket with the specified ID is not accessible.', 'pods' ),
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'404' => [
						'description' => __( 'A ticket with the specified ID does not exist.', 'pods' ),
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
			'id' => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The ID', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function get( WP_REST_Request $request ) {
		$id = $request['id'];

		$data = [];

		return $data;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function EDIT_args() {
		return [
			'id' => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The ID', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function update( WP_REST_Request $request ) {
		$id = $request['id'];

		$data = [];

		return $data;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function can_edit() {
		// @todo Check Pods permissions
		return true;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function DELETE_args() {
		return [
			'id' => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The ID', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_positive_int' ],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function delete( WP_REST_Request $request ) {
		$id = $request['id'];

		$data = [];

		return $data;
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function can_delete() {
		// @todo Check Pods permissions
		return true;
	}
}
