<?php

namespace Pods\Whatsit;

use Pods\Whatsit;
use Tribe__Utils__Array;

/**
 * Block class.
 *
 * @since 2.8.0
 */
class Block extends Pod {

	/**
	 * {@inheritdoc}
	 */
	protected static $type = 'block';

	/**
	 * Get list of Block API arguments to use.
	 *
	 * @since 2.8.0
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
			'blockName'        => $namespace . '/' . $name,
			'blockGroupLabel'  => $this->get_arg( 'group_label', __( 'Options', 'pods' ) ),
			'title'            => $this->get_arg( 'title', $this->get_arg( 'label' ) ),
			'description'      => $this->get_arg( 'description' ),
			'renderType'       => $this->get_arg( 'renderType', $this->get_arg( 'render_type', 'js' ) ),
			'category'         => $category,
			'icon'             => $this->get_arg( 'icon', 'align-right' ),
			'keywords'         => Tribe__Utils__Array::list_to_array( $this->get_arg( 'keywords', 'pods' ) ),
			'supports'         => $this->get_arg( 'supports', [] ),
			'editor_script'    => $this->get_arg( 'editor_script', 'pods-blocks-api' ),
			'fields'           => $this->get_block_fields(),
			'attributes'       => $this->get_arg( 'attributes', [] ),
			'uses_context'     => $this->get_arg( 'usesContext', $this->get_arg( 'uses_context', [] ) ),
			'provides_context' => $this->get_arg( 'providesContext', $this->get_arg( 'provides_context', [] ) ),
		];

		$default_supports = [
			'html'                     => false,
			// Extra block controls.
			'align'                    => true,
			'alignWide'                => true,
			'anchor'                   => true,
			'customClassName'          => true,
			// Block functionality.
			'inserter'                 => true,
			'multiple'                 => true,
			'reusable'                 => true,
			// Experimental options.
			'__experimentalColor'      => true,
			'__experimentalFontSize'   => true,
			// Experimental options not yet confirmed working.
			'__experimentalPadding'    => true,
			'__experimentalLineHeight' => true,
			// Custom Pods functionality.
			'jsx'                      => false,
		];

		$block_args['supports'] = array_merge( $default_supports, $block_args['supports'] );

		// Custom supports handling for attributes.
		$custom_supports = [
			'className' => 'string',
			'align'     => 'string',
			'anchor'    => 'string',
		];

		// Experimental supports handling for attributes.
		$experimental_supports = [
			'__experimentalColor'    => [
				'textColor'       => 'string',
				'backgroundColor' => 'string',
			],
			'__experimentalFontSize' => [
				'fontSize' => 'string',
			],
			'__experimentalPadding'  => [
				'style' => 'string',
			],
		];

		foreach ( $custom_supports as $support => $attribute_type ) {
			if ( empty( $block_args['supports'][ $support ] ) ) {
				continue;
			}

			$block_args['attributes'][ $support ] = [
				'type' => $attribute_type,
			];
		}

		foreach ( $experimental_supports as $support => $support_attributes ) {
			if ( empty( $block_args['supports'][ $support ] ) ) {
				continue;
			}

			foreach ( $support_attributes as $attribute_key => $attribute_type ) {
				$block_args['attributes'][ $attribute_key ] = [
					'type' => $attribute_type,
				];
			}
		}

		// @todo Look into supporting example.
		// @todo Look into supporting variations.

		foreach ( $block_args['fields'] as $field ) {
			if ( ! isset( $field['attributeOptions'] ) ) {
				continue;
			}

			$block_args['attributes'][ $field['name'] ] = $field['attributeOptions'];
		}

		if ( 'js' === $block_args['renderType'] ) {
			$block_args['renderTemplate'] = $this->get_arg( 'render_template', $this->get_arg( 'renderTemplate', __( 'No block preview is available', 'pods' ) ) );
		} elseif ( 'php' === $block_args['renderType'] ) {
			$block_args['render_callback']      = $this->get_arg( 'render_callback', [ $this, 'render_template' ] );
			$block_args['render_template_path'] = $this->get_arg( 'render_template', $this->get_arg( 'render_template_path' ) );
		}

		$other_args = (array) $this->get_arg( 'raw_args', [] );

		if ( $other_args ) {
			$block_args = array_merge( $block_args, $other_args );
		}

		return $block_args;
	}

	/**
	 * Render the template for the block.
	 *
	 * @since 2.8.0
	 *
	 * @param array     $block     The block instance argument values.
	 * @param string    $content   The block inner content.
	 * @param \WP_Block $block_obj The block object.
	 *
	 * @return  string   The HTML render for the block.
	 */
	public function render_template( $block, $content, $block_obj ) {
		$render_template_path = $block_obj->block_type->render_template_path;

		/**
		 * Allow filtering of the block render template path.
		 *
		 * @since 2.8.0
		 *
		 * @param string    $render_template_path The block render template path.
		 * @param array     $block                The block instance argument values.
		 * @param string    $content              The block inner content.
		 * @param \WP_Block $block_obj            The block object.
		 */
		$render_template_path = apply_filters( 'pods_block_render_template_path', $render_template_path, $block, $content, $block_obj );

		if ( empty( $render_template_path ) ) {
			return '';
		}

		$render = pods_view( $render_template_path, compact( 'block', 'content', 'block_obj' ), false, 'cache', true );

		// Avoid regex issues with $ capture groups.
		$content = str_replace( '$', '\$', $content );

		// Replace the <InnerBlocks /> placeholder with the real deal.
		$render = preg_replace( '/<InnerBlocks([\S\s]*?)\/>/', $content, $render );

		/**
		 * Allow filtering of the block render HTML.
		 *
		 * @since 2.8.0
		 *
		 * @param string    $render    The HTML render for the block.
		 * @param array     $block     The block instance argument values.
		 * @param string    $content   The block inner content.
		 * @param \WP_Block $block_obj The block object.
		 */
		return apply_filters( 'pods_block_render_html', $render, $block, $content, $block_obj );
	}

	/**
	 * Get list of Block API fields for the block.
	 *
	 * @since 2.8.0
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
