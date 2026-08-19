<?php

namespace Pods\REST\V1\Endpoints;

use Pods\REST\Interfaces\Endpoints\CREATE_Interface;
use Pods\REST\Interfaces\Swagger\Provider_Interface;
use Pods\REST\V1\Endpoints\Base;
use WP_REST_Request;

class Group_Duplicate extends Base implements CREATE_Interface, Provider_Interface {

	/**
	 * {@inheritdoc}
	 *
	 * @since TBD
	 */
	public $route = '/groups/%1$d/duplicate';

	/**
	 * {@inheritdoc}
	 *
	 * @since TBD
	 */
	public $rest_route = '/groups/(?P<id>\\d+)/duplicate';

	/**
	 * {@inheritdoc}
	 *
	 * @since TBD
	 */
	public $rest_doc_route = '/groups/{id}/duplicate';

	/**
	 * {@inheritdoc}
	 *
	 * @since TBD
	 */
	public $object = 'group';

	/**
	 * {@inheritdoc}
	 *
	 * @since TBD
	 */
	public function get_documentation() {
		$POST_defaults = [
			'in'      => 'body',
			'default' => '',
			'type'    => 'string',
		];

		return [
			'post'   => [
				'summary'    => 'Duplicate a specific Group',
				'parameters' => $this->swaggerize_args( $this->CREATE_args(), $POST_defaults ),
				'responses'  => [
					'201' => [
						'description' => 'Returns the newly duplicated Group',
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
						'description' => 'Unauthorized access - user does not have permission to duplicate this Group',
						'content'     => [
							'application/json' => [
								'schema' => [
									'type' => 'object',
								],
							],
						],
					],
					'404' => [
						'description' => 'The Group to duplicate was not found',
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
	 * @since TBD
	 */
	public function CREATE_args() {
		return [
			'id'               => [
				'type'              => 'integer',
				'in'                => 'path',
				'description'       => __( 'The Group ID to duplicate.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_group_id' ],
			],
			'new_name'         => [
				'type'        => 'string',
				'description' => __( 'The name of the new Group.', 'pods' ),
				'required'    => false,
			],
			'duplicate_fields' => [
				'type'        => 'boolean',
				'description' => __( 'Whether to duplicate the fields in the Group (default: on).', 'pods' ),
				'default'     => true,
				'cli_boolean' => true,
			],
			'include_fields' => [
				'type'        => 'boolean',
				'description' => __( 'Whether to return the fields in the Group (default: on).', 'pods' ),
				'default'     => true,
				'cli_boolean' => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since TBD
	 */
	public function create( WP_REST_Request $request, $return_id = false ) {
		$id               = $request->get_param( 'id' );
		$new_name         = $request->get_param( 'new_name' );
		$duplicate_fields = $request->get_param( 'duplicate_fields' );

		$api                 = pods_api();
		$api->display_errors = 'wp_error';

		// Get the original group to duplicate
		$group = $api->load_group( [ 'id' => $id ] );

		if ( empty( $group ) ) {
			return new \WP_Error( 'rest-group-not-found', __( 'Group not found', 'pods' ) );
		}

		// Prepare the parameters for the new group
		$params = [
			'pod'              => $group['pod'],
			'pod_id'           => $group['pod_id'],
			'name'             => $group['name'],
			'id'               => $group['id'],
			'new_name'         => $new_name,
			'duplicate_fields' => $duplicate_fields,
		];

		// Duplicate the group.
		$new_id = $api->duplicate_group( array_filter( $params ) );

		if ( empty( $new_id ) ) {
			return new \WP_Error( 'rest-group-not-duplicated', __( 'Group could not be duplicated', 'pods' ) );
		}

		return $this->get_by_args( [
			'id' => $new_id,
		], 'id', $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since TBD
	 */
	public function can_create() {
		return pods_is_admin( 'pods' );
	}
}
