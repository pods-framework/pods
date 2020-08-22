<?php

namespace Pods\Whatsit;

use Pods\Whatsit;
use Tribe__Utils__Array;

/**
 * Block class.
 *
 * @since 2.8
 */
class Block extends Pod {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'block';

	/**
	 * Get list of Block API arguments to use.
	 *
	 * @since 2.8
	 *
	 * @return array List of Block API arguments.
	 */
	public function get_block_args() {
		$namespace = $this->get_arg( 'namespace', 'pods' );
		$name      = $this->get_arg( 'slug', $this->get_arg( 'name' ) );
		$category  = $this->get_arg( 'category', 'layout' );

		// Blocks are only allowed A-Z0-9- characters, no underscores.
		$namespace = str_replace( '_', '-', sanitize_title_with_dashes( $namespace ) );
		$name      = str_replace( '_', '-', sanitize_title_with_dashes( $name ) );
		$category  = str_replace( '_', '-', sanitize_title_with_dashes( $category ) );

		$block_args = [
			'blockName'       => $namespace . '/' . $name,
			'blockGroupLabel' => $this->get_arg( 'group_label', __( 'Options', 'pods' ) ),
			'title'           => $this->get_arg( 'title', $this->get_arg( 'label' ) ),
			'description'     => $this->get_arg( 'description' ),
			'renderType'      => $this->get_arg( 'renderType', $this->get_arg( 'render_type', 'js' ) ),
			'category'        => $category,
			'icon'            => $this->get_arg( 'icon', 'align-right' ),
			'keywords'        => Tribe__Utils__Array::list_to_array( $this->get_arg( 'keywords', 'pods' ) ),
			'supports'        => $this->get_arg( 'supports', [
				'html' => false,
			] ),
			'editor_script'   => $this->get_arg( 'editor_script', 'pods-blocks-api' ),
			'fields'          => $this->get_block_fields(),
			'attributes'      => [
				'className' => [
					'type' => 'string',
				],
			],
		];

		// @todo Look into supporting example.
		// @todo Look into supporting variations.

		foreach ( $block_args['fields'] as $field ) {
			if ( ! isset( $field['attributeOptions'] ) ) {
				continue;
			}

			$block_args['attributes'][ $field['name'] ] = $field['attributeOptions'];
		}

		if ( 'js' === $block_args['renderType'] ) {
			$block_args['render_template'] = $this->get_arg( 'render_template', __( 'No block preview is available', 'pods' ) );
		} elseif ( 'php' === $block_args['renderType'] ) {
			$block_args['render_callback'] = $this->get_arg( 'render_callback' );
		}

		return $block_args;
	}

	/**
	 * Get list of Block API fields for the block.
	 *
	 * @since 2.8
	 *
	 * @return array List of Block API fields.
	 */
	public function get_block_fields() {
		/** @var Block_Field[] $fields */
		$fields = $this->get_fields();

		$fields = array_map( static function ( $field ) {
			return $field->get_block_args();
		}, $fields );

		// Ensure the response is an array with no empty values.
		$fields = array_values( array_filter( $fields ) );

		return $fields;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_args() {
		$args = Whatsit::get_args();

		// Pods generally have no parent, group, or order.
		unset( $args['parent'], $args['group'] );

		return $args;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_fields( array $args = [] ) {
		if ( [] === $this->_fields ) {
			return [];
		}

		$object_collection = Store::get_instance();

		$has_custom_args = ! empty( $args );

		if ( null === $this->_fields || $has_custom_args ) {
			$args = array_merge( [
				'object_type' => 'block-field',
			], $args );

			$objects = parent::get_fields( $args );

			if ( ! $has_custom_args ) {
				$this->_fields = wp_list_pluck( $objects, 'identifier' );
			}

			return $objects;
		}

		$objects = array_map( [ $object_collection, 'get_object' ], $this->_fields );
		$objects = array_filter( $objects );

		$names = wp_list_pluck( $objects, 'name' );

		$objects = array_combine( $names, $objects );

		return $objects;
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_object_fields() {
		return [];
	}

	/**
	 * {@inheritdoc}
	 */
	public function get_table_info() {
		return [];
	}
}
