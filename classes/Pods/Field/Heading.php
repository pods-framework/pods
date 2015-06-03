<?php
/**
 * @package Pods
 * @category Field Types
 */
class Pods_Field_Heading extends Pods_Field {

	/**
	 * Field Type Group
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $group = 'Layout Blocks';

	/**
	 * Field Type Identifier
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $type = 'heading';

	/**
	 * Field Type Label
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $label = 'Custom Heading';

	/**
	 * Field Type Preparation
	 *
	 * @var string
	 * @since 2.0
	 */
	public static $prepare = '%s';

	/**
	 * {@inheritdoc}
	 */
	public function __construct() {

	}

	/**
	 * {@inheritdoc}
	 */
	public function options() {

		$options = array(
			'output_options'         => array(
				'label' => __( 'Output Options', 'pods' ),
				'group' => array(
					self::$type . '_wptexturize'     => array(
						'label'   => __( 'Enable wptexturize?', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => array(
							__( 'Transforms less beautiful text characters into stylized equivalents.', 'pods' ),
							'http://codex.wordpress.org/Function_Reference/wptexturize'
						)
					),
					self::$type . '_convert_chars'   => array(
						'label'   => __( 'Enable convert_chars?', 'pods' ),
						'default' => 1,
						'type'    => 'boolean',
						'help'    => array(
							__( 'Converts text into valid XHTML and Unicode', 'pods' ),
							'http://codex.wordpress.org/Function_Reference/convert_chars'
						)
					),
					self::$type . '_allow_shortcode' => array(
						'label'      => __( 'Allow Shortcodes?', 'pods' ),
						'default'    => 0,
						'type'       => 'boolean',
						'dependency' => true,
						'help'       => array(
							__( 'Embed [shortcodes] that help transform your static content into dynamic content.', 'pods' ),
							'http://codex.wordpress.org/Shortcode_API'
						)
					)
				)
			),
			self::$type . '_content' => array(
				'label'   => __( 'Custom Heading Content', 'pods' ),
				'default' => '',
				'type'    => 'text'
			)
		);

		return $options;

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
	public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {

		if ( strlen( trim( $value ) ) < 1 ) {
			$value = pods_v( self::$type . '_content', $options );
		}

		if ( strlen( trim( $value ) ) < 1 ) {
			$value = $options[ 'label' ];
		}

		if ( 1 == pods_v( self::$type . '_wptexturize', $options, 1 ) ) {
			$value = wptexturize( $value );
		}

		if ( 1 == pods_v( self::$type . '_convert_chars', $options, 1 ) ) {
			$value = convert_chars( $value );
		}

		if ( 1 == pods_v( self::$type . '_allow_shortcode', $options, 0 ) ) {
			if ( 1 == pods_v( self::$type . '_wpautop', $options, 1 ) ) {
				$value = shortcode_unautop( $value );
			}

			$value = do_shortcode( $value );
		}

		return $value;

	}

	/**
	 * {@inheritdoc}
	 */
	public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {

		$form_field_type = Pods_Form::$field_type;

		$field_type = 'heading';

		$value = $this->display( $value, $name, $options, $pod, $id );

		pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );

	}

	/**
	 * {@inheritdoc}
	 */
	public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

		$value = wp_trim_words( $value );

		return $value;

	}

}