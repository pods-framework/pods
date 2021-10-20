<?php

/**
 * @package Pods\Fields
 */
class PodsField_WYSIWYG extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Paragraph';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'wysiwyg';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'WYSIWYG (Visual Editor)';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$group = __( 'Paragraph', 'pods' );
		static::$label = __( 'WYSIWYG (Visual Editor)', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			static::$type . '_repeatable'        => array(
				'label'             => __( 'Repeatable Field', 'pods' ),
				'default'           => 0,
				'type'              => 'boolean',
				'help'              => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
				'boolean_yes_label' => '',
				'dependency'        => true,
				'developer_mode'    => true,
			),
			static::$type . '_editor'            => array(
				'label'      => __( 'Editor', 'pods' ),
				'default'    => 'tinymce',
				'type'       => 'pick',
				'data'       => apply_filters(
					'pods_form_ui_field_wysiwyg_editors', array(
						'tinymce'  => __( 'TinyMCE (WP Default)', 'pods' ),
						'quill'    => __( 'Quill Editor', 'pods' ),
						'cleditor' => __( 'CLEditor (No longer available, now using Quill Editor)', 'pods' ),
					)
				),
				'pick_show_select_text' => 0,
				'dependency' => true,
			),
			'editor_options'                     => array(
				'label'         => __( 'Editor Options', 'pods' ),
				'type'          => 'boolean_group',
				'depends-on'    => array( static::$type . '_editor' => 'tinymce' ),
				'boolean_group' => array(
					static::$type . '_media_buttons' => array(
						'label'   => __( 'Enable Media Buttons', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
					),
					static::$type . '_tinymce' => array(
						'label'   => __( 'Enable TinyMCE (Visual)', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
					),
					static::$type . '_quicktags' => array(
						'label'   => __( 'Enable Quicktags (Text)', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
					),
				),
			),
			static::$type . '_editor_height'     => array(
				'label'           => __( 'Editor Height', 'pods' ),
				'help'            => __( 'Height in pixels', 'pods' ),
				'default'         => '',
				'type'            => 'number',
				'depends-on'      => array( static::$type . '_editor' => 'tinymce' ),
				'number_decimals' => 0,
			),
			'output_options'                     => array(
				'label' => __( 'Output Options', 'pods' ),
				'type'  => 'boolean_group',
				'boolean_group' => array(
					static::$type . '_trim'      => array(
						'label'      => __( 'Trim extra whitespace before/after contents', 'pods' ),
						'default'    => 1,
						'type'       => 'boolean',
						'dependency' => true,
					),
					static::$type . '_oembed'          => array(
						'label'   => __( 'Enable oEmbed', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
						'help'    => array(
							__( 'Embed videos, images, tweets, and other content.', 'pods' ),
							'https://wordpress.org/support/article/embeds/',
						),
					),
					static::$type . '_wptexturize'     => array(
						'label'   => __( 'Enable wptexturize', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => array(
							__( 'Transforms less-beautiful text characters into stylized equivalents.', 'pods' ),
							'https://developer.wordpress.org/reference/functions/wptexturize/',
						),
					),
					static::$type . '_convert_chars'   => array(
						'label'   => __( 'Enable convert_chars', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => array(
							__( 'Converts text into valid XHTML and Unicode', 'pods' ),
							'https://developer.wordpress.org/reference/functions/convert_chars/',
						),
					),
					static::$type . '_wpautop'         => array(
						'label'   => __( 'Enable wpautop', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => array(
							__( 'Changes double line-breaks in the text into HTML paragraphs', 'pods' ),
							'https://developer.wordpress.org/reference/functions/wpautop/',
						),
					),
					static::$type . '_allow_shortcode' => array(
						'label'      => __( 'Allow Shortcodes', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
						'help'       => array(
							__( 'Embed [shortcodes] that help transform your static content into dynamic content.', 'pods' ),
							'https://codex.wordpress.org/Shortcode_API',
						),
					),
				),
			),
			static::$type . '_allowed_html_tags' => array(
				'label'   => __( 'Allowed HTML Tags', 'pods' ),
				'default' => '',
				'type'    => 'text',
				'help'    => __( 'Format: strong em a ul ol li b i', 'pods' ),
			),
		);

		if ( function_exists( 'Markdown' ) ) {
			$options['output_options']['boolean_group'][ static::$type . '_allow_markdown' ] = array(
				'label'   => __( 'Allow Markdown Syntax', 'pods' ),
				'default' => 0,
				'type'    => 'boolean',
			);
		}

		return $options;
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {

		$schema = 'LONGTEXT';

		return $schema;
	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		if ( 1 === (int) pods_v( static::$type . '_oembed', $options, 0 ) ) {
			$post_temp = false;

			// Workaround for WP_Embed since it needs a $post to work from
			if ( 'post_type' === pods_v( 'type', $pod ) && 0 < $id && ( ! isset( $GLOBALS['post'] ) || empty( $GLOBALS['post'] ) ) ) {
				$post_temp = true;

				// @codingStandardsIgnoreLine
				$GLOBALS['post'] = get_post( $id );
			}

			/**
			 * @var $embed WP_Embed
			 */
			$embed = $GLOBALS['wp_embed'];
			$value = $embed->run_shortcode( $value );
			$value = $embed->autoembed( $value );

			// Cleanup after ourselves
			if ( $post_temp ) {
				// @codingStandardsIgnoreLine
				$GLOBALS['post'] = null;
			}
		}//end if

		if ( 1 === (int) pods_v( static::$type . '_wptexturize', $options, 1 ) ) {
			$value = wptexturize( $value );
		}

		if ( 1 === (int) pods_v( static::$type . '_convert_chars', $options, 1 ) ) {
			$value = convert_chars( $value );
		}

		if ( 1 === (int) pods_v( static::$type . '_wpautop', $options, 1 ) ) {
			$value = wpautop( $value );
		}

		if ( 1 === (int) pods_v( static::$type . '_allow_shortcode', $options, 0 ) ) {
			if ( 1 === (int) pods_v( static::$type . '_wpautop', $options, 1 ) ) {
				$value = shortcode_unautop( $value );
			}

			$value = do_shortcode( $value );
		}

		if ( function_exists( 'Markdown' ) && 1 === (int) pods_v( static::$type . '_allow_markdown', $options ) ) {
			$value = Markdown( $value );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$options         = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;
		$form_field_type = PodsForm::$field_type;

		if ( is_array( $value ) ) {
			$value = implode( "\n", $value );
		}

		if ( isset( $options['name'] ) && ! pods_permission( $options ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options['readonly'] = true;

				$field_type = 'textarea';
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options['readonly'] = true;

			$field_type = 'textarea';
		} elseif ( 'tinymce' === pods_v( static::$type . '_editor', $options ) ) {
			$field_type = 'tinymce';

			// Enforce booleans.
			$options[ static::$type . '_media_buttons' ] = filter_var( pods_v( static::$type . '_media_buttons', $options, true ), FILTER_VALIDATE_BOOLEAN );
			$options[ static::$type . '_tinymce' ] = filter_var( pods_v( static::$type . '_tinymce', $options, true ), FILTER_VALIDATE_BOOLEAN );
			$options[ static::$type . '_quicktags' ] = filter_var( pods_v( static::$type . '_quicktags', $options, true ), FILTER_VALIDATE_BOOLEAN );

			wp_tinymce_inline_scripts();
			wp_enqueue_editor();
		} elseif ( 'quill' === pods_v( static::$type . '_editor', $options ) ) {
			$field_type = 'quill';
		} elseif ( 'cleditor' === pods_v( static::$type . '_editor', $options ) ) {
			$field_type = 'quill';
		} else {
			// Support custom WYSIWYG integration
			$editor_type = pods_v( static::$type . '_editor', $options );
			do_action( "pods_form_ui_field_wysiwyg_{$editor_type}", $name, $value, $options, $pod, $id );
			do_action( 'pods_form_ui_field_wysiwyg', pods_v( static::$type . '_editor', $options ), $name, $value, $options, $pod, $id );

			return;
		}//end if

		if ( ! empty( $options['disable_dfv'] ) ) {
			return pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
		}

		wp_enqueue_script( 'pods-dfv' );

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function pre_save( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		$length = (int) pods_v( static::$type . '_max_length', $options, 0 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		$value = wp_trim_words( $value );

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function strip_html( $value, $options = null ) {

		$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

		// Allow HTML tags.
		$options[ static::$type . '_allow_html' ] = 1;

		return parent::strip_html( $value, $options );
	}
}
