<?php
class PodsField_Paragraph extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    protected static $type = 'paragraph';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    protected static $label = 'Paragraph Text';

    /**
     * Do things like register/enqueue scripts and stylesheets
     *
     * @since 2.0.0
     */
    public function __construct () {

    }

    /**
     * Add options and set defaults to
     *
     * @param array $options
     *
     * @since 2.0.0
     */
    public function options () {
        $options = array(
            'paragraph_format_type' => array(
                'label' => __( 'Format Type', 'pods' ),
                'default' => 'textarea',
                'type' => 'pick',
                'data' => array(
                    'plain' => __( 'Plain Text Area', 'pods' ),
                    __( 'WYSIWYG', 'pods' ) =>
                        apply_filters( 'pods_form_ui_field_paragraph_wysiwyg_options',
                                       array(
                                           'tinymce' => __( 'TinyMCE (WP Default)', 'pods' ),
                                           'cleditor' => __( 'CLEditor', 'pods' )
                                       )
                        )
                )
            ),
            'output_options' => array(
                'label' => __( 'Output Options', 'pods' ),
                'depends-on' => array( 'paragraph_format_type' => 'plain' ),
                'group' => array(
                    'paragraph_allow_shortcode' => array(
                        'label' => __( 'Allow Shortcodes?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean'
                    ),
                    'paragraph_allow_html' => array(
                        'label' => __( 'Allow HTML?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean'
                    )
                )
            ),
            'paragraph_allowed_html_tags' => array(
                'label' => __( 'Allowed HTML Tags', 'pods' ),
                'depends-on' => array( 'paragraph_allow_html' => 1 ),
                'default' => 'strong em a ul ol li b i',
                'type' => 'text'
            ),
            'max_length' => 255,
            'size' => 'medium',
        );

        // Markdown integration
        if ( function_exists( 'Markdown' ) ) {
            $options[ 'output_options' ][ 'paragraph_allow_markdown' ] = array(
                'label' => __( 'Allow Markdown Syntax?', 'pods' ),
                'default' => 0,
                'type' => 'boolean'
            );
        }

        return $options;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function display ( &$value, $name, $options, $fields, &$pod, $id ) {
        // Markdown integration
        if ( function_exists( 'Markdown' ) && 1 == $options[ 'paragraph_allow_markdown' ] )
            $value = Markdown( $value );

        if ( 1 == $options[ 'paragraph_allow_shortcode' ] )
            $value = do_shortcode( $value );
    }

    /**
     * Customize output of the form field
     *
     * @param string $name
     * @param string $value
     * @param array $options
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;

        $field_type = 'textarea';

        if ( 'tinymce' == $options[ 'paragraph_format_type' ] )
            $field_type = 'tinymce';
        elseif ( 'cleditor' == $options[ 'paragraph_format_type' ] )
            $field_type = 'cleditor';

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( $name, $value, $options, $pod, $id ) );
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param string $value
     * @param string $name
     * @param array $options
     * @param array $data
     * @param object $api
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function pre_save ( &$value, $name, $options, $data, &$api, &$pod, $id = false ) {
        $options = (array) $options;

        if ( 1 == $options[ 'paragraph_allow_html' ] ) {
            if ( 0 < strlen( $options[ 'paragraph_allowed_html_tags' ] ) )
                $value = strip_tags( $value, $options[ 'paragraph_allowed_html_tags' ] );
        }
        else
            $value = strip_tags( $value );

        if ( 1 != $options[ 'paragraph_allow_shortcode' ] )
            $value = strip_shortcodes( $value );
    }
}