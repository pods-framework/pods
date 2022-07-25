<?php

namespace Pods\REST\V1\Endpoints;

use WP_REST_Request;

class Pod_Slug extends Pod {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public $route = '/pods/%1$s';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.11
	 */
	public $rest_route = '/pods/(?P<slug>[\w\_\-]+)';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.11
	 */
	public $rest_doc_route = '/pods/{slug}';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function READ_args() {
		return [
			'slug'           => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The Pod slug.', 'pods' ),
				'required'    => true,
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
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function get( WP_REST_Request $request ) {
		return $this->get_by_args( 'slug', 'name', $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function EDIT_args() {
		return [
			'slug'     => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The Pod slug.', 'pods' ),
				'required'    => true,
			],
			'new_name' => [
				'type'        => 'string',
				'description' => __( 'The new name of the Pod.', 'pods' ),
			],
			'label'    => [
				'type'        => 'string',
				'description' => __( 'The singular label of the Pod.', 'pods' ),
			],
			'args'     => [
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
		return $this->update_by_args( 'slug', 'name', $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function DELETE_args() {
		return [
			'slug'       => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The Pod slug.', 'pods' ),
				'required'    => true,
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
		return $this->delete_by_args( 'slug', 'name', $request );
	}
}
