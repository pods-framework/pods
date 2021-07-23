<?php

namespace Pods\Blocks\Types;

use WP_Block;

/**
 * Field block functionality class.
 *
 * @since 2.8
 */
class Field extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-field';
	}

	/**
	 * Get block configuration to register with Pods.
	 *
	 * @since TBD
	 *
	 * @return array Block configuration.
	 */
	public function block() {
		return [
			'internal'        => true,
			'label'           => __( 'Pods Field Value', 'pods' ),
			'description'     => __( 'Display a single Pod item\'s field value.', 'pods' ),
			'namespace'       => 'pods',
			'category'        => 'pods',
			'icon'            => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'field',
				'value',
			],
			'uses_context'    => [
				'postType',
				'postId',
			],
		];
	}

	/**
	 * Get list of Field configurations to register with Pods for the block.
	 *
	 * @since TBD
	 *
	 * @return array List of Field configurations.
	 */
	public function fields() {
		$api = pods_api();

		$all_pods = $api->load_pods( [ 'names' => true ] );
		$all_pods = array_merge( [
			'' => '- ' . __( 'Use Current Pod', 'pods' ) . ' -',
		], $all_pods );

		return [
			[
				'name'  => 'name',
				'label' => __( 'Pod Name', 'pods' ),
				'type'  => 'pick',
				'data'  => $all_pods,
			],
			[
				'name'        => 'slug',
				'label'       => __( 'Slug or ID (optional)', 'pods' ),
				'type'        => 'text',
				'description' => __( 'Defaults to using the current pod item.', 'pods' ),
			],
			[
				'name'  => 'field',
				'label' => __( 'Field Name', 'pods' ),
				'type'  => 'text',
			],
		];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since TBD
	 *
	 * @param array         $attributes The block attributes.
	 * @param string        $content    The block default content.
	 * @param WP_Block|null $block      The block instance.
	 *
	 * @return string The block content to render.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		$attributes = $this->attributes( $attributes );
		$attributes = array_map( 'trim', $attributes );

		if ( empty( $attributes['field'] ) ) {
			if ( is_admin() || wp_is_json_request() ) {
				return $this->render_placeholder(
					'<i class="pods-block-placeholder_error"></i>' . esc_html__( 'Pods Field Value - Block Error', 'pods' ),
					esc_html__( 'This block is not configured properly, please specify a "Field Name" to use.', 'pods' )
				);
			}

			return '';
		}

		if ( empty( $attributes['name'] ) || empty( $attributes['slug'] ) ) {
			$attributes['use_current'] = true;
		}

		if ( $attributes['use_current'] && $block instanceof WP_Block && ! empty( $block->context['postType'] ) ) {
			$attributes['name'] = $block->context['postType'];

			if ( ! empty( $block->context['postId'] ) ) {
				$attributes['id'] = $block->context['postId'];

				unset( $attributes['use_current'] );
			}
		} elseif (
			! empty( $attributes['use_current'] )
			&& ! empty( $_GET['post_id'] )
			&& (
				is_admin()
				|| wp_is_json_request()
			)
		) {
			$attributes['slug'] = absint( $_GET['post_id'] );

			if ( empty( $attributes['name'] ) ) {
				$attributes['name'] = get_post_type( $attributes['slug'] );
			}

			unset( $attributes['use_current'] );
		}

		return pods_shortcode( $attributes );
	}
}
