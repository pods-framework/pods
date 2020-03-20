<?php

namespace Pods\REST\V1\Endpoints;

use WP_REST_Request;

class Pod_Slug
	extends Pod {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public $route = '/pods/%1$s';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function READ_args() {
		return [
			'slug' => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The slug', 'pods' ),
				'required'    => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function get( WP_REST_Request $request ) {
		$slug = $request['slug'];

		return $this->get_pod_by_args( [
			'name' => $slug,
		], $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function EDIT_args() {
		return [
			'slug' => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The slug', 'pods' ),
				'required'    => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function update( WP_REST_Request $request ) {
		$slug = $request['slug'];

		return $this->get_pod_by_args( [
			'name' => $slug,
		], $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function DELETE_args() {
		return [
			'slug' => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The slug', 'pods' ),
				'required'    => true,
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function delete( WP_REST_Request $request ) {
		$slug = $request['slug'];

		$api = pods_api();

		$api->display_errors = 'wp_error';

		$deleted = $api->delete_pod( [
			'name' => $slug,
		] );

		if ( is_wp_error( $deleted ) ) {
			return $deleted;
		}

		if ( ! $deleted ) {
			// @todo Fix error messaging.
			return new WP_Error( 'not-deleted', 'pod not deleted' );
		}

		// Empty success.
		return [];
	}
}
