<?php

namespace Pods\REST\V1\Endpoints;

use WP_REST_Request;

class Field_Slug extends Field {

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public $route = '/pods/%1$s/fields/%2$s';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.11
	 */
	public $rest_route = '/pods/(?P<pod>[\w\_\-]+)/fields/(?P<slug>[\w\_\-]+)';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.11
	 */
	public $rest_doc_route = '/pods/{pod}/fields/{slug}';

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8.0
	 */
	public function READ_args() {
		return [
			'pod'  => [
				'type'              => 'string',
				'in'                => 'path',
				'description'       => __( 'The Pod slug.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_pod_slug' ],
			],
			'slug' => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The Field slug.', 'pods' ),
				'required'    => true,
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
			'pod'          => [
				'type'              => 'string',
				'in'                => 'path',
				'description'       => __( 'The Pod slug.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_pod_slug' ],
			],
			'slug'         => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The Field slug.', 'pods' ),
				'required'    => true,
			],
			'new_name'     => [
				'type'        => 'string',
				'description' => __( 'The new name of the Field.', 'pods' ),
			],
			'new_group'    => [
				'type'        => 'string',
				'description' => __( 'The new Group to use for the Field.', 'pods' ),
			],
			'new_group_id' => [
				'type'        => 'string',
				'description' => __( 'The new Group ID to use for the Field.', 'pods' ),
			],
			'label'        => [
				'type'        => 'string',
				'description' => __( 'The singular label of the Field.', 'pods' ),
			],
			'type'         => [
				'type'        => 'string',
				'description' => __( 'The type of the Field.', 'pods' ),
			],
			'args'         => [
				'required'     => false,
				'description'  => __( 'A list of additional options to save to the Field.', 'pods' ),
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
			'pod'  => [
				'type'              => 'string',
				'in'                => 'path',
				'description'       => __( 'The Pod slug.', 'pods' ),
				'required'          => true,
				'validate_callback' => [ $this->validator, 'is_pod_slug' ],
			],
			'slug' => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The Field slug.', 'pods' ),
				'required'    => true,
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
