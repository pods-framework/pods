<?php

namespace Pods\REST\V1\Endpoints;

use Pods\REST\Interfaces\Endpoints\DELETE_Interface;
use Pods\REST\Interfaces\Endpoints\READ_Interface;
use Pods\REST\Interfaces\Endpoints\UPDATE_Interface;
use Pods\REST\Interfaces\Swagger\Provider_Interface;
use WP_REST_Request;

class Pod extends Base implements READ_Interface, UPDATE_Interface, DELETE_Interface, Provider_Interface {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public $route = '/pods/%1$d';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.11
	 */
	public $rest_route = '/pods/(?P<id>\\d+)';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.11
	 */
	public $rest_doc_route = '/pods/{id}';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public $object = 'pod';

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

		// @todo Handle get/post/delete

		return [
			'get' => [
				'summary'    => '', // @todo Fill this out
				'parameters' => $this->swaggerize_args( $this->READ_args(), $GET_defaults ),
				'responses'  => [
					'200' => [
						'description' => '', // @todo Fill this out
						'content'     => [
							'application/json' => [
								'schema' => [
									'$ref' => '#/components/schemas/Pod',
								],
							],
						],
					],
					'400' => [
						'description' => '', // @todo Fill this out
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'401' => [
						'description' => '', // @todo Fill this out
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'404' => [
						'description' => '', // @todo Fill this out
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
			'id'             => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The Pod ID.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_pod_id' ],
			],
			'include_fields' => [
				'type'        => 'boolean',
				'description' => __( 'Whether to include fields (default: off).', 'pods' ),
				'default'     => false,
				'cli_boolean' => true,
			],
			'include_groups' => [
				'type'        => 'boolean',
				'description' => __( 'Whether to include groups (default: off).', 'pods' ),
				'default'     => false,
				'cli_boolean' => true,
			],
			'include_group_fields' => [
				'type'        => 'boolean',
				'description' => __( 'Whether to include group fields (default: off).', 'pods' ),
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
				'description'       => __( 'The Pod ID.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_pod_id' ],
			],
			'name'  => [
				'type'        => 'string',
				'description' => __( 'The new name of the Pod.', 'pods' ),
			],
			'label' => [
				'type'        => 'string',
				'description' => __( 'The singular label of the Pod.', 'pods' ),
			],
			'args'  => [
				'required'     => false,
				'description'  => __( 'A list of additional options to save to the Pod.', 'pods' ),
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
		if ( ! empty( $request['groups'] ) ) {
			$request->set_param( 'groups', null );
		}

		if ( ! empty( $request['fields'] ) ) {
			$request->set_param( 'fields', null );
		}

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
			'id'         => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The Pod ID.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_pod_id' ],
			],
			'delete_all' => [
				'type'        => 'boolean',
				'description' => __( 'Whether to delete all content for Pod (default: off).', 'pods' ),
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
