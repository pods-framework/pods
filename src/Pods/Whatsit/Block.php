<?php

namespace Pods\Whatsit;

use Exception;
use Pods\Whatsit;
use WP_Block;

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
			'keywords'         => wp_parse_list( $this->get_arg( 'keywords', 'pods' ) ),
			'supports'         => $this->get_arg( 'supports', [] ),
			'editor_script'    => $this->get_arg( 'editor_script', 'pods-blocks-api' ),
			'editor_style'     => $this->get_arg( 'editor_style' ),
			'script'           => $this->get_arg( 'script' ),
			'style'            => $this->get_arg( 'style' ),
			'enqueue_script'   => $this->get_arg( 'enqueue_script' ),
			'enqueue_style'    => $this->get_arg( 'enqueue_style' ),
			'enqueue_assets'   => $this->get_arg( 'enqueue_assets' ),
			'uses_context'     => $this->get_arg( 'usesContext', $this->get_arg( 'uses_context', [] ) ),
			'provides_context' => $this->get_arg( 'providesContext', $this->get_arg( 'provides_context', [] ) ),
			'fields'           => $this->get_block_fields(),
			'attributes'       => $this->get_arg( 'attributes', [] ),
			'transforms'       => $this->get_arg( 'transforms', [] ),
		];

		$default_supports = [
			'html'                     => false,
			// Extra block controls.
			'align'                    => true,
			'alignWide'                => true,
			'anchor'                   => false, // Not support for dynamic blocks yet as of WP 5.9
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
			$block_args['render_callback']        = [ $this, 'render' ];
			$block_args['render_custom_callback'] = $this->get_arg( 'render_callback' );
			$block_args['render_template_path']   = $this->get_arg( 'render_template', $this->get_arg( 'render_template_path' ) );
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
	 * @param array         $attributes The block instance argument values.
	 * @param string        $content    The block inner content.
	 * @param WP_Block|null $block_obj  The block object.
	 *
	 * @return  string   The HTML render for the block.
	 */
	public function render( $attributes, $content, $block_obj = null ) {
		$block_config            = $block_obj ? $block_obj->block_type : [];
		$block_name              = pods_v( 'name', $block_config, wp_generate_password( 12, false ), true );
		$enqueue_style           = pods_v( 'enqueue_style', $block_config );
		$enqueue_script          = pods_v( 'enqueue_script', $block_config );
		$enqueue_assets_callback = pods_v( 'enqueue_assets', $block_config );
		$template_path           = pods_v( 'render_template_path', $block_config );
		$render_callback         = pods_v( 'render_custom_callback', $block_config );

		$handle = 'block-' . pods_create_slug( $block_name );

		// Maybe enqueue the style.
		if ( $enqueue_style ) {
			wp_enqueue_style( $handle, $enqueue_style );
		}

		// Maybe enqueue the script.
		if ( $enqueue_script ) {
			wp_enqueue_script( $handle, $enqueue_script, [], false, true );
		}

		// Maybe run the enqueue assets callback.
		if ( $enqueue_assets_callback && is_callable( $enqueue_assets_callback ) ) {
			$enqueue_assets_callback( $block_config );
		}

		// Handle custom context overrides from editor.
		if (
			! empty( $_GET['podsContext'] )
			&& wp_is_json_request()
			&& did_action( 'rest_api_init' )
		) {
			$block_obj->context = array_merge( $block_obj->context, (array) $_GET['podsContext'] );
		}

		// Render block from callback.
		if ( $render_callback && is_callable( $render_callback ) ) {
			$return_exception = static function() {
				return 'exception';
			};

			add_filter( 'pods_error_mode', $return_exception, 50 );
			add_filter( 'pods_error_exception_fallback_enabled', '__return_false', 50 );

			try {
				$rendered_block = $render_callback( $attributes, $content, $block_obj );
			} catch ( Exception $exception ) {
				pods_debug_log( $exception );

				$rendered_block = '';

				if ( pods_is_debug_display() ) {
					$rendered_block = pods_message(
						sprintf(
							'<strong>%1$s:</strong> %2$s',
							esc_html__( 'Pods Block Error', 'pods' ),
							esc_html( $exception->getMessage() )
						),
						'error',
						true
					);

					$rendered_block .= '<pre style="overflow:scroll">' . esc_html( $exception->getTraceAsString() ) . '</pre>';
				} elseif (
					is_user_logged_in()
					&& (
						is_admin()
						|| (
							wp_is_json_request()
							&& did_action( 'rest_api_init' )
						)
					)
				) {
					$rendered_block = pods_message(
						sprintf(
							'<strong>%1$s:</strong> %2$s',
							esc_html__( 'Pods Block Error', 'pods' ),
						esc_html__( 'There was a problem displaying this content, enable WP_DEBUG in wp-config.php to show more details.', 'pods' )
						),
						'error',
						true
					);
				}
			}

			remove_filter( 'pods_error_mode', $return_exception, 50 );
			remove_filter( 'pods_error_exception_fallback_enabled', '__return_false', 50 );

			return $rendered_block;
		}

		// Render block from template.
		if ( $template_path ) {
			return $this->render_template( $template_path, $attributes, $content, $block_obj );
		}

		return '';
	}

	/**
	 * Render the template for the block.
	 *
	 * @since 2.8.0
	 *
	 * @param string        $template_path The block render template path.
	 * @param array         $attributes    The block instance argument values.
	 * @param string        $content       The block inner content.
	 * @param WP_Block|null $block_obj     The block object.
	 *
	 * @return  string   The HTML render for the block.
	 */
	public function render_template( $template_path, $attributes, $content, $block_obj = null ) {
		/**
		 * Allow filtering of the block render template path.
		 *
		 * @since 2.8.0
		 *
		 * @param string        $template_path The block render template path.
		 * @param array         $attributes    The block instance argument values.
		 * @param string        $content       The block inner content.
		 * @param WP_Block|null $block_obj     The block object.
		 */
		$template_path = apply_filters( 'pods_block_render_template_path', $template_path, $attributes, $content, $block_obj );

		if ( empty( $template_path ) ) {
			return '';
		}

		$render = pods_view( $template_path, compact( 'attributes', 'content', 'block_obj' ), false, 'cache', true );

		// Avoid regex issues with $ capture groups.
		$content = str_replace( '$', '\$', $content );

		// Replace the <InnerBlocks /> placeholder with the real deal.
		$render = preg_replace( '/<InnerBlocks([\S\s]*?)\/>/', $content, $render );

		/**
		 * Allow filtering of the block render HTML.
		 *
		 * @since 2.8.0
		 *
		 * @param string        $render        The HTML render for the block.
		 * @param string        $template_path The block render template path.
		 * @param array         $attributes    The block instance argument values.
		 * @param string        $content       The block inner content.
		 * @param WP_Block|null $block_obj     The block object.
		 */
		return apply_filters( 'pods_block_render_html', $render, $attributes, $content, $block_obj );
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

		return array_combine( $names, $objects );
	}
}
