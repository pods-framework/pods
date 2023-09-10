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
				'label'   => __( 'Heading HTML Tag', 'pods' ),
				'type'        => 'text',
				'default'     => '',
				'description' => __( 'Leave this empty to use the default heading tag for the form context the heading appears in.', 'pods' ),
				'help'        => __( 'This is the heading HTML tag to use for the heading text. Example "h2" will output your heading as <code>&lt;h2&gt;Heading Text&lt;/h2&gt;</code>', 'pods' ),
			],
			'output_options' => [
				'label' => __( 'Output Options', 'pods' ),
				'type'  => 'boolean_group',
				'boolean_group' => [
					static::$type . '_allow_html'      => [
						'label'      => __( 'Allow HTML', 'pods' ),
						'default'    => 1,
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

		// Format content.
		$options[ static::$type . '_content' ] = $this->display( $options[ static::$type . '_content' ], $name, $options, $pod, $id );

		if ( isset( $options['_field_object'] ) && $options['_field_object'] instanceof Field ) {
			$options['_field_object']->set_arg( static::$type . '_content', $options[ static::$type . '_content' ] );
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
		// Support passing html_content into the options for custom HTML option layouts.
		if ( empty( $value ) && ! empty( $options[ static::$type . '_content' ] ) ) {
			$value = $options[ static::$type . '_content' ];
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
}
