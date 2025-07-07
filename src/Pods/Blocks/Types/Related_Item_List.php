<?php

namespace Pods\Blocks\Types;

use WP_Block;

/**
 * Item List block functionality class.
 *
 * @since 3.2.7
 */
class Related_Item_List extends Base {

	/**
	 * Which is the name/slug of this block
	 *
	 * @since 3.2.7
	 *
	 * @return string
	 */
	public function slug() {
		return 'pods-block-related-list';
	}

	/**
	 * Get block configuration to register with Pods.
	 *
	 * @since 3.2.7
	 *
	 * @return array Block configuration.
	 */
	public function block() {
		return [
			'internal'        => true,
			'label'           => __( 'Pods Related Item List', 'pods' ),
			'description'     => __( 'List multiple related Pod items.', 'pods' ),
			'namespace'       => 'pods',
			'category'        => 'pods',
			'icon'            => 'pods',
			'renderType'      => 'php',
			'render_callback' => [ $this, 'safe_render' ],
			'keywords'        => [
				'pods',
				'related',
				'item',
				'list',
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
	 * @since 3.2.7
	 *
	 * @return array List of Field configurations.
	 */
	public function fields() {
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
		 * @since 3.2.7
		 *
		 * @param string $default_cache_mode Default cache mode.
		 */
		$default_cache_mode = apply_filters( 'pods_shortcode_default_cache_mode', 'none' );

		$fields = [
			'name' => [
				'name'        => 'name',
				'label'       => __( 'Pod Name', 'pods' ),
				'type'        => 'pick',
				'data'        => [ $this, 'callback_get_all_pods' ],
				'default'     => '',
				'description' => __( 'Choose the pod to reference, or reference the Pod in the current context of this block.', 'pods' ),
			],
			'access_rights_help' => [
				'name'    => 'access_rights_help',
				'label'   => __( 'Access Rights', 'pods' ),
				'type'    => 'html',
				'default' => '',
				'html_content' => sprintf(
					// translators: %s is the Read Documentation link.
					esc_html__( 'Read about how access rights control what can be displayed to other users: %s', 'pods' ),
					'<a href="https://docs.pods.io/displaying-pods/access-rights-in-pods/" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Documentation', 'pods' ) . '</a>'
				),
			],
			'slug' => [
				'name'        => 'slug',
				'label'       => __( 'Slug or ID', 'pods' ),
				'type'        => 'text',
				'description' => __( 'Defaults to using the current pod item.', 'pods' ),
			],
			'related_field' => [
				'name'        => 'related_field',
				'label'       => __( 'Related Field Name', 'pods' ),
				'type'        => 'text',
				'description' => __( 'This is the related field name you want to display.', 'pods' ),
			],
			'template' => [
				'name'        => 'template',
				'label'       => __( 'Template', 'pods' ),
				'type'        => 'pick',
				'data'        => [ $this, 'callback_get_all_pod_templates' ],
				'default'     => '',
				'description' => __( 'You can choose a previously saved Pods Template here. We recommend saving your Pods Templates with our Templates component so you can enjoy the full editing experience.', 'pods' ),
			],
			'template_custom' => [
				'name'        => 'template_custom',
				'label'       => __( 'Custom Template', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'You can specify a custom template to use, it accepts HTML and magic tags. Any content here will override whatever Template you may have chosen above.', 'pods' ),
			],
			'content_before' => [
				'name'        => 'content_before',
				'label'       => __( 'Content Before List', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'This content will appear before the list of templated items. A useful way to use this option is if you have a template that uses "li" HTML tags, you can use the "ul" HTML tag to start an unordered list. This will only be shown if items were found.', 'pods' ),
			],
			'content_after' => [
				'name'        => 'content_after',
				'label'       => __( 'Content After List', 'pods' ),
				'type'        => 'paragraph',
				'description' => __( 'This content will appear after the list of templated items. A useful way to use this option is if you have a template that uses "li" HTML tags, you can use the "/ul" HTML tag to end an unordered list. This will only be shown if items were found.', 'pods' ),
			],
			'not_found' => [
				'name'        => 'not_found',
				'label'       => __( 'Not Found Content', 'pods' ),
				'type'        => 'paragraph',
				'default'     => __( 'No content was found.', 'pods' ),
				'description' => __( 'If there are no items shown, this content will be shown in the block\'s place.', 'pods' ),
			],
			'limit' => [
				'name'        => 'limit',
				'label'       => __( 'Limit', 'pods' ),
				'type'        => 'number',
				'default'     => 15,
				'description' => __( 'Specify the number of items to show but keep in mind that the more items you show the longer it may take for the page to load. You should avoid using "-1" here unless you know what you\'re doing. If your pod has many items, it could stop the page from loading and cause errors. Default number of items to show is to show 15 items. See also: find()', 'pods' ),
			],
			'orderby' => [
				'name'        => 'orderby',
				'label'       => __( 'Order By', 'pods' ),
				'type'        => 'text',
				'description' => __( 'You can specify what field to order by here. That could be t.post_title ASC or you may want to use a custom field like my_field.meta_value ASC. The normal MySQL syntax works here, so you can sort ascending with ASC or descending with DESC. See also: find()', 'pods' ),
			],
			'where' => [
				'name'        => 'where',
				'label'       => __( 'Where', 'pods' ),
				'type'        => 'text',
				'description' => __( 'You can specify what field to restrict the item list by here. That could be t.post_title LIKE "%repairs%" or you may want to reference a custom field like  my_field.meta_value = "123". For a list of all things available for you to query, follow the find() Notation Options. See also: find()', 'pods' ),
			],
			'pagination' => [
				'name'        => 'pagination',
				'label'       => __( 'Enable Pagination', 'pods' ),
				'type'        => 'boolean',
				'description' => __( 'Whether to show pagination for the list of items. This will only show if there is more than one page of items found.', 'pods' ),
			],
			'pagination_location' => [
				'name'        => 'pagination_location',
				'label'       => __( 'Pagination Location', 'pods' ),
				'type'        => 'pick',
				'data'        => [
					'before' => __( 'Before list', 'pods' ),
					'after'  => __( 'After list', 'pods' ),
					'both'   => __( 'Before and After list', 'pods' ),
				],
				'default'     => 'after',
				'description' => __( 'The location to show the pagination.', 'pods' ),
			],
			'pagination_type' => [
				'name'        => 'pagination_type',
				'label'       => __( 'Pagination Type', 'pods' ),
				'type'        => 'pick',
				'data'        => [
					'advanced' => __( 'Basic links', 'pods' ),
					'simple'   => __( 'Previous and Next Links only', 'pods' ),
					'list'     => __( 'Use an unordered list with paginate_links() native functionality', 'pods' ),
					'paginate' => __( 'Use basic paginate_links() native functionality', 'pods' ),
				],
				'default'     => 'advanced',
				'description' => __( 'Choose which kind of pagination to display.', 'pods' ),
			],
			'filters_enable' => [
				'name'        => 'filters_enable',
				'label'       => __( 'Enable Filters', 'pods' ),
				'type'        => 'boolean',
				'description' => __( 'Whether to show filters for the list of items.', 'pods' ),
			],
			'filters' => [
				'name'        => 'filters',
				'label'       => __( 'Filter Fields', 'pods' ),
				'type'        => 'text',
				'description' => __( 'Comma-separated list of fields you want to allow filtering by. Default is to just show a text field to search with.', 'pods' ),
			],
			'filters_label' => [
				'name'        => 'filters_label',
				'label'       => __( 'Custom Filters Label', 'pods' ),
				'type'        => 'text',
				'description' => __( 'The label to show for the filters. Default is "Search".', 'pods' ),
			],
			'filters_location' => [
				'name'        => 'filters_location',
				'label'       => __( 'Filters Location', 'pods' ),
				'type'        => 'pick',
				'data'        => [
					'before' => __( 'Before list', 'pods' ),
					'after'  => __( 'After list', 'pods' ),
				],
				'default'     => 'before',
				'description' => __( 'The location to show the filters.', 'pods' ),
			],
			'cache_mode' => [
				'name'        => 'cache_mode',
				'label'       => __( 'Cache Mode', 'pods' ),
				'type'        => 'pick',
				'data'        => $cache_modes,
				'default'     => $default_cache_mode,
				'description' => __( 'The mode to cache the output with.', 'pods' ),
			],
			'expires' => [
				'name'        => 'expires',
				'label'       => __( 'Expires', 'pods' ),
				'type'        => 'number',
				'default'     => ( MINUTE_IN_SECONDS * 5 ),
				'description' => __( 'Set how long to cache the output for in seconds.', 'pods' ),
			],
		];

		if ( ! pods_can_use_dynamic_feature_sql_clauses( 'simple' ) ) {
			unset( $fields['orderby'] );
			unset( $fields['where'] );
		}

		return array_values( $fields );
	}

	/**
	 * Since we are dealing with a Dynamic type of Block we need a PHP method to render it.
	 *
	 * @since 3.2.7
	 *
	 * @param array         $attributes The block attributes.
	 * @param string        $content    The block default content.
	 * @param WP_Block|null $block      The block instance.
	 *
	 * @return string The block content to render.
	 */
	public function render( $attributes = [], $content = '', $block = null ) {
		// If the feature is disabled then return early.
		if ( ! pods_can_use_dynamic_feature( 'display' ) ) {
			return '';
		}

		$attributes = $this->attributes( $attributes );
		$attributes = array_map( 'pods_trim', $attributes );

		$attributes['source']  = __METHOD__;
		$attributes['context'] = 'related-item-list';

		if ( empty( $attributes['related_field'] ) ) {
			if ( $this->in_editor_mode( $attributes ) ) {
				return $this->render_placeholder(
					'<i class="pods-block-placeholder_error"></i>' . esc_html__( 'Pods Related Item List', 'pods' ),
					esc_html__( 'Please specify a "Related Field Name" under "More Settings" to configure this block.', 'pods' )
				);
			}

			return '';
		}

		if ( empty( $attributes['template'] ) && empty( $attributes['template_custom'] ) ) {
			if ( $this->in_editor_mode( $attributes ) ) {
				return $this->render_placeholder(
					'<i class="pods-block-placeholder_error"></i>' . esc_html__( 'Pods Related Item List', 'pods' ),
					esc_html__( 'Please specify a "Template" or "Custom Template" under "More Settings" to configure this block.', 'pods' )
				);
			}

			return '';
		}

		// Check whether we should preload the block.
		if ( $this->is_preloading_block() && ! $this->should_preload_block( $attributes, $block ) ) {
			return '';
		}

		// Use current if no pod name / slug provided.
		if ( empty( $attributes['name'] ) || empty( $attributes['slug'] ) ) {
			$attributes['use_current'] = true;
		} elseif ( ! isset( $attributes['use_current'] ) ) {
			$attributes['use_current'] = false;
		}

		$provided_post_id = $this->in_editor_mode( $attributes ) ? pods_v( 'post_id', 'get', 0, true ) : get_the_ID();
		$provided_post_id = absint( pods_v( '_post_id', $attributes, $provided_post_id, true ) );

		if ( $attributes['use_current'] && $block instanceof WP_Block && ! empty( $block->context['postType'] ) ) {
			// Detect post type / ID from context.
			$attributes['name'] = $block->context['postType'];

			if ( ! empty( $block->context['postId'] ) ) {
				$attributes['slug'] = $block->context['postId'];

				unset( $attributes['use_current'] );
			}
		} elseif (
			$attributes['use_current']
			&& 0 !== $provided_post_id
			&& $this->in_editor_mode( $attributes )
		) {
			$attributes['slug'] = $provided_post_id;

			if ( empty( $attributes['name'] ) ) {
				$attributes['name'] = get_post_type( $attributes['slug'] );
			}

			unset( $attributes['use_current'] );
		}

		if ( empty( $attributes['filters'] ) ) {
			$attributes['filters'] = false;
		}

		if ( ! empty( $attributes['not_found'] ) && $this->should_autop( $attributes['not_found'] ) ) {
			$attributes['not_found'] = wpautop( $attributes['not_found'], $attributes );
		}

		pods_set_render_is_in_block( true );

		$content = pods_shortcode( $attributes, $attributes['template_custom'] );

		pods_set_render_is_in_block( false );

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
