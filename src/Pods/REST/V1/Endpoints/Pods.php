<?php

namespace Pods\REST\V1\Endpoints;

use Tribe__REST__Endpoints__CREATE_Endpoint_Interface as CREATE_Interface;
use Tribe__REST__Endpoints__READ_Endpoint_Interface as READ_Interface;
use Tribe__Documentation__Swagger__Provider_Interface as Swagger_Interface;
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
			'page'     => [
				'description'       => __( 'The page of results to return; defaults to 1', 'pods' ),
				'type'              => 'integer',
				'default'           => 1,
				'sanitize_callback' => 'absint',
				'minimum'           => 1,
			],
			'per_page' => [
				'description'       => __( 'How many tickets to return per results page; defaults to posts_per_page.', 'pods' ),
				'type'              => 'integer',
				'default'           => get_option( 'posts_per_page' ),
				'minimum'           => 1,
				'maximum'           => 100,
				'sanitize_callback' => 'absint',
			],
			'search'   => [
				'description'       => __( 'Limit results to tickets containing the specified string in the title or description.', 'pods' ),
				'type'              => 'string',
				'required'          => false,
				'validate_callback' => [ $this->validator, 'is_string' ],
			],
			'offset'   => [
				'description' => __( 'Offset the results by a specific number of items.', 'pods' ),
				'type'        => 'integer',
				'required'    => false,
				'min'         => 0,
			],
			'order'    => [
				'description' => __( 'Sort results in ASC or DESC order. Defaults to ASC.', 'pods' ),
				'type'        => 'string',
				'required'    => false,
				'enum'        => [
					'ASC',
					'DESC',
				],
			],
			'orderby'  => [
				'description' => __( 'Order the results by one of date, relevance, id, include, title, or slug; defaults to title.', 'pods' ),
				'type'        => 'string',
				'required'    => false,
				'enum'        => [
					'id',
					'include',
					'title',
					'slug',
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function get( WP_REST_Request $request ) {
		$data = [];

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
