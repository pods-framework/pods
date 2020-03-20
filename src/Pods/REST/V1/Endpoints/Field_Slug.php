<?php

namespace Pods\REST\V1\Endpoints;

use WP_REST_Request;

class Field_Slug
	extends Field {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public $route = '/fields/%1$s';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function READ_args() {
		return [
			'slug' => [
				'type'        => 'string',
				'in'                => 'path',
				'description' => __( 'The slug', 'pods' ),
				'required'          => true,
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

		return $this->get_field_by_args( [
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
				'in'                => 'path',
				'description' => __( 'The slug', 'pods' ),
				'required'          => true,
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

		return $this->get_field_by_args( [
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
				'in'                => 'path',
				'description' => __( 'The slug', 'pods' ),
				'required'          => true,
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

		return $this->get_field_by_args( [
			'name' => $slug,
		], $request );
	}
}
