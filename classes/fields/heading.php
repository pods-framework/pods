<?php

use Pods\Whatsit\Field;

/**
 * @package Pods\Fields
 */
class PodsField_Heading extends PodsField {

	/**
	 * {@inheritdoc}
	 */
	public static $group = 'Layout Elements';

	/**
	 * {@inheritdoc}
	 */
	public static $type = 'heading';

	/**
	 * {@inheritdoc}
	 */
	public static $label = 'Heading';

	/**
	 * {@inheritdoc}
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function setup() {
		static::$group = __( 'Layout Elements', 'pods' );
		static::$label = __( 'Heading', 'pods' );
	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {
		return [
			static::$type . '_tag' => [
				'label'       => __( 'Heading HTML Tag', 'pods' ),
				'type'        => 'pick',
				'data'        => [
					'h1'  => 'h1',
					'h2'  => 'h2',
					'h3'  => 'h3',
					'h4'  => 'h4',
					'h5'  => 'h5',
					'h6'  => 'h6',
					'p'   => 'p',
					'div' => 'div',
				],
				'default'     => 'h2',
				'description' => __( 'Leave this empty to use the default heading tag for the form context the heading appears in.', 'pods' ),
				'help'        => __( 'This is the heading HTML tag to use for the heading text. Example "h2" will output your heading as <code>&lt;h2&gt;Heading Text&lt;/h2&gt;</code>', 'pods' ),
			],
			'output_options'       => [
				'label'         => __( 'Output Options', 'pods' ),
				'type'          => 'boolean_group',
				'boolean_group' => [
					static::$type . '_allow_html'      => [
						'label'      => __( 'Allow HTML', 'pods' ),
						'default'    => 1,
						'type'       => 'boolean',
						'dependency' => true,
					],
					static::$type . '_sanitize_html'   => [
						'label'      => __( 'Sanitize HTML', 'pods' ),
						'default'    => 1,
						'help'       => __( 'This sanitizes things like script tags and other content not normally allowed in WordPress content. Disable this only if you trust users who will have access to enter content into this field.', 'pods' ),
						'type'       => 'boolean',
						'dependency' => true,
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

		$options[ static::$type . '_tag' ] = static::get_heading_tag( $options );

		// Format content.
		$options[ 'label' ] = $this->display( $options[ 'label' ], $name, $options, $pod, $id );

		if ( isset( $options['_field_object'] ) && $options['_field_object'] instanceof Field ) {
			$options['_field_object']->set_arg( 'label', $options[ 'label' ] );
		}

		$type = pods_v( 'type', $options, static::$type );

		$args = compact( array_keys( get_defined_vars() ) );
		$args = (object) $args;

		$this->render_input_script( $args );
	}

	/**
	 * {@inheritdoc}
	 */
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
		// Support passing label into the options for custom HTML option layouts.
		if ( empty( $value ) && ! empty( $options[ 'label' ] ) ) {
			$value = $options[ 'label' ];
		}

		$value = $this->strip_html( $value, $options );
		$value = $this->strip_shortcodes( $value, $options );
		$value = $this->trim_whitespace( $value, $options );

		if ( 1 === (int) pods_v( static::$type . '_wptexturize', $options, 1 ) ) {
			$value = wptexturize( $value );
		}

		if ( 1 === (int) pods_v( static::$type . '_allow_shortcode', $options, 0 ) ) {
			$value = do_shortcode( $value );
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

	/**
	 * Get the heading tag from the field options and ensure it's allowed.
	 *
	 * @since 3.2.7.1
	 *
	 * @param array|Field $options The field data.
	 * @param null|string $default The default heading tag to use.
	 *
	 * @return string The heading tag.
	 */
	public static function get_heading_tag( $options, ?string $default = null ): string {
		// Only allow specific HTML tags.
		$allowed_html_tags = [
			'h1' => 'h1',
			'h2' => 'h2',
			'h3' => 'h3',
			'h4' => 'h4',
			'h5' => 'h5',
			'h6' => 'h6',
			'p' => 'p',
			'div' => 'div',
		];

		$heading_tag = 'h2';

		if ( ! empty( $options[ static::$type . '_tag' ] ) && isset( $allowed_html_tags[ $options[ static::$type . '_tag' ] ] ) ) {
			$heading_tag = $options[ static::$type . '_tag' ];
		} elseif ( ! empty( $default ) && isset( $allowed_html_tags[ $default ] ) ) {
			$heading_tag = $default;
		}

		return $heading_tag;
	}
}
