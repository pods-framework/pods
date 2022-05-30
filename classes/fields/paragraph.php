<?php

/**
 * @package Pods\Fields
 */
class PodsField_Paragraph extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Paragraph';


	/**
	 * {@inheritdoc}
	 */
	public static $type = 'paragraph';


	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Plain Paragraph Text';


	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';


	/**
	 * {@inheritdoc}
	 */
	public function setup() {

		static::$group = __( 'Paragraph', 'pods' );
		static::$label = __( 'Plain Paragraph Text', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
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
					static::$type . '_allow_html'      => array(
						'label'      => __( 'Allow HTML', 'pods' ),
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
				'label'      => __( 'Allowed HTML Tags', 'pods' ),
				'depends-on' => array( static::$type . '_allow_html' => true ),
				'default'    => 'strong em a ul ol li b i',
				'type'       => 'text',
				'help'       => __( 'Format: strong em a ul ol li b i', 'pods' ),
			),
			static::$type . '_max_length'        => array(
				'label'   => __( 'Maximum Length', 'pods' ),
				'default' => -1,
				'type'    => 'number',
				'help'    => __( 'Set to -1 for no limit', 'pods' ),
			),
			static::$type . '_placeholder'       => array(
				'label'   => __( 'HTML Placeholder', 'pods' ),
				'default' => '',
				'type'    => 'text',
				'help'    => array(
					__( 'Placeholders can provide instructions or an example of the required data format for a field. Please note: It is not a replacement for labels or description text, and it is less accessible for people using screen readers.', 'pods' ),
					'https://www.w3.org/WAI/tutorials/forms/instructions/#placeholder-text',
				),
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

		$length = (int) pods_v( static::$type . '_max_length', $options, 0 );

		$schema = 'LONGTEXT';

		if ( 0 < $length ) {
			$schema = 'VARCHAR(' . $length . ')';
		}

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
			$embed = $GLOBALS['wp_embed'];
			$value = $embed->run_shortcode( $value );
			$value = $embed->autoembed( $value );
		}

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

		$value = $this->normalize_value_for_input( $value, $options, "\n" );

		if ( isset( $options['name'] ) && ! pods_permission( $options ) ) {
			if ( pods_v( 'read_only', $options, false ) ) {
				$options['readonly'] = true;
			} else {
				return;
			}
		} elseif ( ! pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) ) {
			$options['readonly'] = true;
		}

		if ( ! empty( $options['disable_dfv'] ) ) {
			return pods_view( PODS_DIR . 'ui/fields/textarea.php', compact( array_keys( get_defined_vars() ) ) );
		}

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

		return wp_trim_words( $value );
	}
}
