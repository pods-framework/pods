<?php

namespace Pods\REST\V1\Endpoints;

use PodsForm;
use Tribe__Documentation__Swagger__Provider_Interface as Swagger_Interface;
use Tribe__REST__Endpoints__CREATE_Endpoint_Interface as CREATE_Interface;
use Tribe__REST__Endpoints__READ_Endpoint_Interface as READ_Interface;
use WP_Error;
use WP_REST_Request;

class Pods extends Base implements READ_Interface, CREATE_Interface, Swagger_Interface {

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
	public $object = 'pod';

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
	 * @since 2.8
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
	 * @since 2.8
	 */
	public function get( WP_REST_Request $request ) {
		return $this->archive_by_args( $request );
	}

	/**
	 * Determine whether access to READ is available.
	 *
	 * @since 2.8
	 *
	 * @return bool Whether access to READ is available.
	 */
	public function can_read() {
		return pods_is_admin( 'pods' );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function CREATE_args() {
		return [
			'mode'          => [
				'type'        => 'string',
				'description' => __( 'The mode for creating the Pod.', 'pods' ),
				'default'     => 'create',
				'enum'        => [
					'create',
					'extend',
				],
			],
			'name'          => [
				'type'        => 'string',
				'description' => __( 'The name of the Pod.', 'pods' ),
			],
			'label'         => [
				'type'        => 'string',
				'description' => __( 'The singular label of the Pod.', 'pods' ),
			],
			'type'          => [
				'type'        => 'string',
				'description' => __( 'The type of the Pod.', 'pods' ),
				'enum'        => PodsForm::pod_types_list(),
				'required'    => true,
			],
			'storage'       => [
				'type'        => 'string',
				'description' => __( 'The storage used for the Pod.', 'pods' ),
				'default'     => 'meta',
				'enum'        => [
					'meta',
					'table',
					'none',
				],
			],
			'label_plural'  => [
				'type'        => 'string',
				'description' => __( 'The plural label of the Pod.', 'pods' ),
			],
			'label_title'   => [
				'type'        => 'string',
				'description' => __( 'The title label of the Pod.', 'pods' ),
			],
			'label_menu'    => [
				'type'        => 'string',
				'description' => __( 'The menu label of the Pod.', 'pods' ),
			],
			'menu_location' => [
				'type'        => 'string',
				'description' => __( 'The menu location of the Pod.', 'pods' ),
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function create( WP_REST_REQUEST $request, $return_id = false ) {
		$mode    = $request->get_param( 'mode' );
		$type    = $request->get_param( 'type' );
		$storage = $request->get_param( 'storage' );

		if ( 'extend' === $mode ) {
			$params = [
				'create_extend'   => 'extend',
				'extend_pod_type' => $type,
				'extend_storage'  => $storage,
			];

			$name = $request->get_param( 'name' );

			if ( 'post_type' === $params['extend_pod_type'] ) {
				$params['extend_post_type'] = $name;
			} elseif ( 'taxonomy' === $params['extend_pod_type'] ) {
				$params['extend_taxonomy'] = $name;
			} elseif ( 'table' === $params['extend_pod_type'] ) {
				$params['extend_table'] = $name;
			} elseif ( in_array( $params['extend_pod_type'], [ 'pod', 'settings' ], true ) ) {
				return new WP_Error( 'rest-object-extend-type-not-supported', __( 'Pod type not supported for extending.', 'pods' ) );
			}
		} else {
			$params = [
				'create_extend'         => 'create',
				'create_pod_type'       => $type,
				'create_storage'        => $storage,
				'create_label_singular' => $request->get_param( 'label' ),
				'create_label_plural'   => $request->get_param( 'label_plural' ),
				'create_label_title'    => $request->get_param( 'label_title' ),
				'create_label_menu'     => $request->get_param( 'label_menu' ),
				'create_menu_location'  => $request->get_param( 'menu_location' ),
			];

			$name = $request->get_param( 'name' );

			if ( 'settings' === $params['create_pod_type'] ) {
				$params['create_setting_name'] = $name;
				$params['create_storage']      = 'none';
			} else {
				$params['create_name'] = $name;
			}
		}

		$api = pods_api();

		$api->display_errors = 'wp_error';

		$id = $api->add_pod( $params );

		if ( is_wp_error( $id ) ) {
			return $id;
		}

		if ( empty( $id ) ) {
			return new WP_Error( 'rest-object-not-added', sprintf( __( '%s not added.', 'pods' ), ucwords( $this->object ) ) );
		}

		return $this->get_by_args( [
			'id' => $id,
		], 'id', $request, $return_id );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function can_create() {
		return pods_is_admin( 'pods' );
	}
}
