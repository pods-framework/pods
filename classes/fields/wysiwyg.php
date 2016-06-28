<?php
/**
 * @package Pods\Fields
 */
class PodsField_WYSIWYG extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0
     */
    public static $group = 'Paragraph';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'wysiwyg';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'WYSIWYG (Visual Editor)';

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
            self::$type . '_repeatable' => array(
                'label' => __( 'Repeatable Field', 'pods' ),
                'default' => 0,
                'type' => 'boolean',
                'help' => __( 'Making a field repeatable will add controls next to the field which allows users to Add/Remove/Reorder additional values. These values are saved in the database as an array, so searching and filtering by them may require further adjustments".', 'pods' ),
                'boolean_yes_label' => '',
                'dependency' => true,
                'developer_mode' => true
            ),
            self::$type . '_editor' => array(
                'label' => __( 'Editor', 'pods' ),
                'default' => 'tinymce',
                'type' => 'pick',
                'data' =>
                    apply_filters(
                        'pods_form_ui_field_wysiwyg_editors',
                        array(
                            'tinymce' => __( 'TinyMCE (WP Default)', 'pods' ),
                            'cleditor' => __( 'CLEditor', 'pods' )
                        )
                    ),
                'dependency' => true
            ),
            'editor_options' => array(
                'label' => __( 'Editor Options', 'pods' ),
                'depends-on' => array( self::$type . '_editor' => 'tinymce' ),
                'group' => array(
                    self::$type . '_media_buttons' => array(
                        'label' => __( 'Enable Media Buttons?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean'
                    )
                )
            ),
            'output_options' => array(
                'label' => __( 'Output Options', 'pods' ),
                'group' => array(
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
                            __( 'Changes double line-breaks in the text into HTML paragraphs', 'pods' ),
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
            ),
            self::$type . '_allowed_html_tags' => array(
                'label' => __( 'Allowed HTML Tags', 'pods' ),
                'default' => '',
                'type' => 'text',
				'help' => __( 'Format: strong em a ul ol li b i', 'pods' )
            ),/*
            self::$type . '_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 0,
                'type' => 'number',
                'help' => __( 'Set to -1 for no limit', 'pods' )
            ),
            self::$type . '_size' => array(
                'label' => __( 'Field Size', 'pods' ),
                'default' => 'medium',
                'type' => 'pick',
                'data' => array(
                    'small' => __( 'Small', 'pods' ),
                    'medium' => __( 'Medium', 'pods' ),
                    'large' => __( 'Large', 'pods' )
                )
            )*/
        );

		if ( function_exists( 'Markdown' ) ) {
			$options[ 'output_options' ][ 'group' ][ self::$type . '_allow_markdown' ] = array(
				'label' => __( 'Allow Markdown Syntax?', 'pods' ),
				'default' => 0,
				'type' => 'boolean'
			);
		}

        return $options;
    }

    /**
     * Define the current field's schema for DB table storage
     *
     * @param array $options
     *
     * @return string
     * @since 2.0
     */
    public function schema ( $options = null ) {
        $schema = 'LONGTEXT';

        return $schema;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     *
     * @since 2.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $value = $this->strip_html( $value, $options );

        if ( 1 == pods_var( self::$type . '_oembed', $options, 0 ) ) {
            $post_temp = false;

            // Workaround for WP_Embed since it needs a $post to work from
            if ( 'post_type' == pods_var( 'type', $pod ) && 0 < $id && ( !isset( $GLOBALS[ 'post' ] ) || empty( $GLOBALS[ 'post' ] ) ) ) {
                $post_temp = true;

                $GLOBALS[ 'post' ] = get_post( $id );
            }

            /**
             * @var $embed WP_Embed
             */
            $embed = $GLOBALS[ 'wp_embed' ];
            $value = $embed->run_shortcode( $value );
            $value = $embed->autoembed( $value );

            // Cleanup after ourselves
            if ( $post_temp )
                $GLOBALS[ 'post' ] = null;
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

		if ( function_exists( 'Markdown' ) && 1 == pods_v( self::$type . '_allow_markdown', $options ) ) {
			$value = Markdown( $value );
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
        $form_field_type = PodsForm::$field_type;

        if ( is_array( $value ) )
            $value = implode( "\n", $value );

        if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
            if ( pods_var( 'read_only', $options, false ) ) {
                $options[ 'readonly' ] = true;

                $field_type = 'textarea';
            }
            else
                return;
        }
        elseif ( !pods_has_permissions( $options ) && pods_var( 'read_only', $options, false ) ) {
            $options[ 'readonly' ] = true;

            $field_type = 'textarea';
        }
        elseif ( 'tinymce' == pods_var( self::$type . '_editor', $options ) )
            $field_type = 'tinymce';
        elseif ( 'cleditor' == pods_var( self::$type . '_editor', $options ) )
            $field_type = 'cleditor';
        else {
            // Support custom WYSIWYG integration
            do_action( 'pods_form_ui_field_wysiwyg_' . pods_var( self::$type . '_editor', $options ), $name, $value, $options, $pod, $id );
            do_action( 'pods_form_ui_field_wysiwyg', pods_var( self::$type . '_editor', $options ), $name, $value, $options, $pod, $id );

            return;
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @since 2.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $value = $this->strip_html( $value, $options );

		$length = (int) pods_var( self::$type . '_max_length', $options, 0 );

		if ( 0 < $length && $length < pods_mb_strlen( $value ) ) {
			$value = pods_mb_substr( $value, 0, $length );
		}

        return $value;
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

        $allowed_html_tags = '';

        if ( 0 < strlen( pods_var( self::$type . '_allowed_html_tags', $options ) ) ) {
			$allowed_tags = pods_var( self::$type . '_allowed_html_tags', $options );
			$allowed_tags = trim( str_replace( array( '<', '>', ',' ), ' ', $allowed_tags ) );
            $allowed_tags = explode( ' ', $allowed_tags );
			$allowed_tags = array_unique( array_filter( $allowed_tags ) );

			if ( !empty( $allowed_tags ) ) {
            	$allowed_html_tags = '<' . implode( '><', $allowed_tags ) . '>';
			}
        }

        if ( !empty( $allowed_html_tags ) )
            $value = strip_tags( $value, $allowed_html_tags );

        return $value;
    }
}
