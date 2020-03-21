<?php

namespace Pods\REST\V1\Endpoints;

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
		$mode = $request->get_param( 'mode' );

		if ( 'create' === $mode ) {
			$params = [
				'create_extend'   => 'extend',
				'extend_pod_type' => $request->get_param( 'pod_type' ), //'post_type',
				'extend_table'    => $request->get_param( 'table' ), //'',
				'extend_storage'  => $request->get_param( 'storage' ), //'meta',
			];

			$name = $request->get_param( 'name' );

			if ( 'post_type' === $params['extend_pod_type'] ) {
				$params['extend_post_type'] = $name;
			} elseif ( 'taxonomy' === $params['extend_pod_type'] ) {
				$params['extend_taxonomy'] = $name;
			} else {
				// @todo Fix error messaging.
				return new WP_Error( 'no', 'invalid parameter value' );
			}
		} else {
			$params = [
				'create_extend'         => 'create',
				'create_pod_type'       => $request->get_param( 'type' ),
				'create_label_singular' => $request->get_param( 'label_singular' ),
				'create_label_plural'   => $request->get_param( 'label_plural' ),
				'create_storage'        => $request->get_param( 'storage' ),
				'create_label_title'    => $request->get_param( 'label_title' ),
				'create_label_menu'     => $request->get_param( 'label_menu' ),
				'create_menu_location'  => $request->get_param( 'menu_location' ),
			];

			$name = $request->get_param( 'name' );

			if ( 'settings' === $params['create_pod_type'] ) {
				$params['create_setting_name'] = $name;
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
			// @todo Fix error messaging.
			return new WP_Error( 'not-saved', 'pod not saved' );
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
