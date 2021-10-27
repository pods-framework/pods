<?php

/**
 * @package Pods\Fields
 */
class PodsField_HTML extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Layout Elements';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'html';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'HTML Content';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {
		static::$group = __( 'Layout Elements', 'pods' );
		static::$label = __( 'HTML Content', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {
		return [
			static::$type . '_content' => [
				'label' => __( 'HTML Content', 'pods' ),
				'type'  => 'code',
			],
			static::$type . '_no_label' => [
				'label'   => __( 'Disable the form label', 'pods' ),
				'default' => 1,
				'type'    => 'boolean',
				'help'    => __( 'By disabling the form label, the HTML will show as full width without the label text. Only the HTML content will be displayed in the form.', 'pods' ),
			],
			'output_options'           => [
				'label' => __( 'Output Options', 'pods' ),
				'type'  => 'boolean_group',
				'boolean_group' => [
					static::$type . '_trim'      => array(
						'label'      => __( 'Trim extra whitespace before/after contents', 'pods' ),
						'default'    => 1,
						'type'       => 'boolean',
						'dependency' => true,
					),
					static::$type . '_oembed'          => [
						'label'   => __( 'Enable oEmbed', 'pods' ),
						'default' => 0,
						'type'    => 'boolean',
						'help'    => [
							__( 'Embed videos, images, tweets, and other content.', 'pods' ),
							'http://codex.wordpress.org/Embeds',
						],
					],
					static::$type . '_wptexturize'     => [
						'label'   => __( 'Enable wptexturize', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => [
							__( 'Transforms less-beautiful text characters into stylized equivalents.', 'pods' ),
							'http://codex.wordpress.org/Function_Reference/wptexturize',
						],
					],
					static::$type . '_convert_chars'   => [
						'label'   => __( 'Enable convert_chars', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => [
							__( 'Converts text into valid XHTML and Unicode', 'pods' ),
							'http://codex.wordpress.org/Function_Reference/convert_chars',
						],
					],
					static::$type . '_wpautop'         => [
						'label'   => __( 'Enable wpautop', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => [
							__( 'Changes double line-breaks in the text into HTML paragraphs.', 'pods' ),
							'http://codex.wordpress.org/Function_Reference/wpautop',
						],
					],
					static::$type . '_allow_shortcode' => [
						'label'      => __( 'Allow Shortcodes', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
						'help'       => [
							__( 'Embed [shortcodes] that help transform your static content into dynamic content.', 'pods' ),
							'http://codex.wordpress.org/Shortcode_API',
						],
					],
				],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function schema( $options = null ) {
		return false;
	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {
		$options = ( is_array( $options ) || is_object( $options ) ) ? $options : (array) $options;

		// Enforce boolean.
		$options[ static::$type . '_no_label' ] = filter_var( pods_v( static::$type . '_no_label', $options, false ), FILTER_VALIDATE_BOOLEAN );

		// @codingStandardsIgnoreLine
		echo $this->display( $value, $name, $options, $pod, $id );
	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		// Support passing html_content into the options for custom HTML option layouts.
		if ( in_array( $value, [ '', null ], true ) ) {
			$value = pods_v( static::$type . '_content', $options, '' );
		}

		if ( $options ) {
			$options[ static::$type . '_allow_html' ] = 1;
		}

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

		return $value;
	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
		if ( $options ) {
			$options[ static::$type . '_allow_html' ] = 1;
		}

		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		return wp_trim_words( $value );
	}
}
