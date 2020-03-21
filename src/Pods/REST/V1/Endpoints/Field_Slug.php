<?php

namespace Pods\REST\V1\Endpoints;

use WP_REST_Request;

class Field_Slug extends Field {

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
		return $this->get_by_args( 'slug', 'name', $request );
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function EDIT_args() {
		return [
			'slug'     => [
				'type'        => 'string',
				'in'          => 'path',
				'description' => __( 'The slug', 'pods' ),
				'required'    => true,
			],
			'new_name' => [
				'type'        => 'string',
				'description' => __( 'The new name of the Field', 'pods' ),
			],
			'label'    => [
				'type'        => 'string',
				'description' => __( 'The singular label of the Field', 'pods' ),
			],
			'args'     => [
				'required'     => false,
				'description'  => __( 'A list of additional options to save to the Field.', 'pods' ),
				'swagger_type' => 'array',
			],
		];
	}

	/**
	 * {@inheritdoc}
	 *
	 * @since 2.8
	 */
	public function update( WP_REST_Request $request ) {
		return $this->update_by_args( 'slug', 'name', $request );
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
		return $this->delete_by_args( 'slug', 'name', $request );
	}
}
