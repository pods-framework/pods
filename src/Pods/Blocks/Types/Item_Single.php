<?php

namespace Pods\Blocks\Types;

/**
 * Item_Single block functionality class.
 *
 * @since 2.8
 */
class Item_Single extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-single';
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
			'label'           => __( 'Pods Single Item', 'pods' ),
			'description'     => __( 'Display a single Pod item.', 'pods' ),
			'namespace'       => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'single',
				'item',
				'field',
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

		$all_templates = $api->load_templates( [ 'names' => true ] );
		$all_templates = array_merge( [
			'' => '- ' . __( 'Use Custom Template', 'pods' ) . ' -',
		], $all_templates );

		return [
			[
				'name'    => 'name',
				'label'   => __( 'Pod name', 'pods' ),
				'type'    => 'pick',
				'data'    => $all_pods,
				'default' => '',
			],
			[
				'name'        => 'slug',
				'label'       => __( 'Slug or ID (optional)', 'pods' ),
				'type'        => 'text',
				'description' => __( 'Defaults to using the current pod item.', 'pods' ),
			],
			[
				'name'    => 'template',
				'label'   => __( 'Template (optional)', 'pods' ),
				'type'    => 'pick',
				'data'    => $all_templates,
				'default' => '',
			],
			[
				'name'  => 'template_custom',
				'label' => __( 'Custom Template (optional)', 'pods' ),
				'type'  => 'paragraph',
			],
		];
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it
	 *
	 * @since TBD
	 *
	 * @param array $attributes
	 *
	 * @return string
	 */
	public function render( $attributes = [] ) {
		$attributes = $this->attributes( $attributes );
		$attributes = array_map( 'trim', $attributes );

		if (
			empty( $attributes['template'] )
			&& empty( $attributes['template_custom'] )
		) {
			if ( is_admin() || wp_is_json_request() ) {
				return __( 'No preview available, please fill in more Block details.', 'pods' );
			}

			return '';
		}

		if ( empty( $attributes['name'] ) || empty( $attributes['slug'] ) ) {
			$attributes['use_current'] = true;
		}

		if (
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

		return pods_shortcode( $attributes, $attributes['template_custom'] );
	}
}
