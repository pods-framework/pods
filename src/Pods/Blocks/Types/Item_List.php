<?php

namespace Pods\Blocks\Types;

/**
 * Item_List block functionality class.
 *
 * @since 2.8
 */
class Item_List extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since TBD
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-list';
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
			'label'           => __( 'Pods Item List', 'pods' ),
			'description'     => __( 'List multiple Pod items.', 'pods' ),
			'namespace'       => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'render' ],
			'keywords'        => [
				'pods',
				'item',
				'list',
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

		$cache_modes = [
			[
				'label' => 'Disable Caching',
				'value' => 'none',
			],
			[
				'label' => 'Object Cache',
				'value' => 'cache',
			],
			[
				'label' => 'Transient',
				'value' => 'transient',
			],
			[
				'label' => 'Site Transient',
				'value' => 'site-transient',
			],
		];

		/**
		 * Allow filtering of the default cache mode used for the Pods shortcode.
		 *
		 * @since TBD
		 *
		 * @param string $default_cache_mode Default cache mode.
		 */
		$default_cache_mode = apply_filters( 'pods_shortcode_default_cache_mode', 'none' );

		return [
			[
				'name'    => 'name',
				'label'   => __( 'Pod name', 'pods' ),
				'type'    => 'pick',
				'data'    => $all_pods,
				'default' => '',
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
			[
				'name'        => 'content_before',
				'label'       => __( 'Content Before List (optional)', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'This only shows if the list is not empty.', 'pods' ),
			],
			[
				'name'        => 'content_after',
				'label'       => __( 'Content After List (optional)', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'This only shows if the list is not empty.', 'pods' ),
			],
			[
				'name'        => 'not_found',
				'label'       => __( 'Not Found Content (optional)', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'This only shows if the list is empty.', 'pods' ),
			],
			[
				'name'    => 'limit',
				'label'   => __( 'Limit', 'pods' ),
				'type'    => 'number',
				'default' => 15,
			],
			[
				'name'  => 'orderby',
				'label' => __( 'Order By (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'where',
				'label' => __( 'Where (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'pagination',
				'label' => __( 'Enable Pagination (optional)', 'pods' ),
				'type'  => 'boolean',
			],
			[
				'name'  => 'pagination_location',
				'label' => __( 'Pagination Location (optional)', 'pods' ),
				'type'  => 'pick',
				'data'  => [
					'before' => __( 'Before list', 'pods' ),
					'after'  => __( 'After list', 'pods' ),
					'both'   => __( 'Before and After list', 'pods' ),
				],
				'default' => 'after',
			],
			[
				'name'        => 'filters',
				'label'       => __( 'Filters (optional)', 'pods' ),
				'type'        => 'text',
				'description' => __( 'Comma-separated list of fields you want to allow filtering by.', 'pods' ),
			],
			[
				'name'  => 'filters_label',
				'label' => __( 'Custom Filters Label (optional)', 'pods' ),
				'type'  => 'text',
			],
			[
				'name'  => 'filters_location',
				'label' => __( 'Filters Location (optional)', 'pods' ),
				'type'  => 'pick',
				'data'  => [
					'before' => __( 'Before list', 'pods' ),
					'after'  => __( 'After list', 'pods' ),
				],
				'default' => 'before',
			],
			[
				'name'    => 'expires',
				'label'   => __( 'Expires (optional)', 'pods' ),
				'type'    => 'number',
				'default' => ( MINUTE_IN_SECONDS * 5 ),
			],
			[
				'name'    => 'cache_mode',
				'label'   => __( 'Cache Mode (optional)', 'pods' ),
				'type'    => 'pick',
				'data'    => $cache_modes,
				'default' => $default_cache_mode,
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

		if ( empty( $attributes['template'] ) && empty( $attributes['template_custom'] ) ) {
			if ( is_admin() || wp_is_json_request() ) {
				return __( 'No preview available, please fill in more Block details.', 'pods' );
			}

			return '';
		}

		if ( empty( $attributes['name'] ) ) {
			if (
				! empty( $_GET['post_id'] )
				&& (
					is_admin()
					|| wp_is_json_request()
				)
			) {
				$post_id = absint( $_GET['post_id'] );

				$attributes['name'] = get_post_type( $post_id );
			} else {
				$attributes['name'] = get_post_type();
			}
		}

		if ( empty( $attributes['filters'] ) ) {
			$attributes['filters'] = false;
		}

		$content = pods_shortcode( $attributes, $attributes['template_custom'] );

		if ( '' !== $content ) {
			if ( ! empty( $attributes['content_before'] ) ) {
				$content = $attributes['content_before'] . $content;
			}

			if ( ! empty( $attributes['content_after'] ) ) {
				$content .= $attributes['content_after'];
			}
		}

		return $content;
	}
}
