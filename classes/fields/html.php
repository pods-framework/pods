<?php
/**
 * @package Pods\Fields
 */
class PodsField_HTML extends PodsField {

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
    public static $type = 'html';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'HTML';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 2.0
     */
    public static $prepare = '%s';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0
     */
    public function __construct () {

    }

    /**
     * Add options and set defaults to
     *
     * @return array
     *
     * @since 2.0
     */
    public function options () {
        $options = array(
            'output_options' => array(
                'label' => __( 'Output Options', 'pods' ),
                'group' => array(
                    self::$type . '_allow_html' => array(
                        'label' => __( 'Allow HTML?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean',
                        'dependency' => true
                    ),
                    self::$type . '_oembed' => array(
                        'label' => __( 'Enable oEmbed?', 'pods' ),
                        'default' => 0,
                        'type' => 'boolean',
                        'help' => array(
                            __( 'Embed videos, images, tweets, and other content.', 'pods' ),
                            'http://codex.wordpress.org/Embeds'
                        )
                    ),
                    self::$type . '_wptexturize' => array(
                        'label' => __( 'Enable wptexturize?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean',
                        'help' => array(
                            __( 'Transforms less-beautfiul text characters into stylized equivalents.', 'pods' ),
                            'http://codex.wordpress.org/Function_Reference/wptexturize'
                        )
                    ),
                    self::$type . '_convert_chars' => array(
                        'label' => __( 'Enable convert_chars?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean',
                        'help' => array(
                            __( 'Converts text into valid XHTML and Unicode', 'pods' ),
                            'http://codex.wordpress.org/Function_Reference/convert_chars'
                        )
                    ),
                    self::$type . '_wpautop' => array(
                        'label' => __( 'Enable wpautop?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean',
                        'help' => array(
                            __( 'Changes double line-breaks in the text into HTML htmls', 'pods' ),
                            'http://codex.wordpress.org/Function_Reference/wpautop'
                        )
                    ),
                    self::$type . '_allow_shortcode' => array(
                        'label' => __( 'Allow Shortcodes?', 'pods' ),
                        'default' => 0,
                        'type' => 'boolean',
                        'dependency' => true,
                        'help' => array(
                            __( 'Embed [shortcodes] that help transform your static content into dynamic content.', 'pods' ),
                            'http://codex.wordpress.org/Shortcode_API'
                        )
                    )
                )
            )
        );

        return $options;
    }

    /**
     * Define the current field's schema for DB table storage
     *
     * @param array $options
     *
     * @return array
     * @since 2.0
     */
    public function schema ( $options = null ) {
        return false;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @return mixed|null|string
     * @since 2.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $value = $this->strip_html( $value, $options );

        if ( 1 == pods_var( self::$type . '_oembed', $options, 0 ) ) {
            $embed = $GLOBALS[ 'wp_embed' ];
            $value = $embed->run_shortcode( $value );
            $value = $embed->autoembed( $value );
        }

        if ( 1 == pods_var( self::$type . '_wptexturize', $options, 1 ) )
            $value = wptexturize( $value );

        if ( 1 == pods_var( self::$type . '_convert_chars', $options, 1 ) )
            $value = convert_chars( $value );

        if ( 1 == pods_var( self::$type . '_wpautop', $options, 1 ) )
            $value = wpautop( $value );

        if ( 1 == pods_var( self::$type . '_allow_shortcode', $options, 0 ) ) {
            if ( 1 == pods_var( self::$type . '_wpautop', $options, 1 ) )
                $value = shortcode_unautop( $value );

            $value = do_shortcode( $value );
        }

        return $value;
    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param mixed $value
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @since 2.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;

		echo $this->display( $value, $name, $options, $pod, $id );
    }

    /**
     * Customize the Pods UI manage table column output
     *
     * @param int $id
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     *
     * @return mixed|string
     * @since 2.0
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        $value = $this->strip_html( $value, $options );

        $value = wp_trim_words( $value );

        return $value;
    }

    /**
     * Strip HTML based on options
     *
     * @param string $value
     * @param array $options
     *
     * @return string
     */
    public function strip_html ( $value, $options = null ) {
        if ( is_array( $value ) )
            $value = @implode( ' ', $value );

        $value = trim( $value );

        if ( empty( $value ) )
            return $value;

        $options = (array) $options;

        if ( 1 != pods_var( self::$type . '_allow_html', $options ) ) {
            $value = strip_tags( $value );
		}

        return $value;
    }
}
