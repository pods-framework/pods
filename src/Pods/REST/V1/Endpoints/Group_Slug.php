<?php

namespace Pods\REST\V1\Endpoints;

use WP_REST_Request;

class Group_Slug
	extends Group {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function READ_args() {
		return [
			'slug'           => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The slug', 'pods' ),
				'required'    => true,
			],
			'include_fields' => [
				'type'        => 'integer',
				'description' => __( 'Whether to include fields (default: off)', 'pods' ),
				'default'     => 0,
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

		return $this->get_by_args( [
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

		return $this->get_by_args( [
			'name' => $slug,
		], $request );
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

		return $this->get_by_args( [
			'name' => $slug,
		], $request );
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
