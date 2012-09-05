<?php
/**
 *
 */
class PodsField_Paragraph extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'paragraph';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'Paragraph Text';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 2.0.0
     */
    public static $prepare = '%s';

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
                'default' => 'plain',
                'type' => 'pick',
                'data' => array(
                    'plain' => __( 'Plain Text Area', 'pods' ),
                    __( 'WYSIWYG', 'pods' ) =>
                        apply_filters(
                            'pods_form_ui_field_paragraph_wysiwyg_options',
                            array(
                                'tinymce' => __( 'TinyMCE (WP Default)', 'pods' ),
                                'cleditor' => __( 'CLEditor', 'pods' )
                            )
                        )
                ),
                'dependency' => true
            ),
            'output_options' => array(
                'label' => __( 'Output Options', 'pods' ),
                'depends-on' => array( 'paragraph_format_type' => 'plain' ),
                'group' => array(
                    'paragraph_allow_shortcode' => array(
                        'label' => __( 'Allow Shortcodes?', 'pods' ),
                        'default' => 0,
                        'type' => 'boolean',
                        'dependency' => true
                    ),
                    'paragraph_allow_html' => array(
                        'label' => __( 'Allow HTML?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean',
                        'dependency' => true
                    )
                )
            ),
            'paragraph_allowed_html_tags' => array(
                'label' => __( 'Allowed HTML Tags', 'pods' ),
                'depends-on' => array( 'paragraph_allow_html' => true ),
                'default' => 'strong em a ul ol li b i',
                'type' => 'text'
            ),
            'paragraph_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 0,
                'type' => 'number'
            ),
            'paragraph_size' => array(
                'label' => __( 'Field Size', 'pods' ),
                'default' => 'medium',
                'type' => 'pick',
                'data' => array(
                    'small' => __( 'Small', 'pods' ),
                    'medium' => __( 'Medium', 'pods' ),
                    'large' => __( 'Large', 'pods' )
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
     * @since 2.0.0
     */
    public function schema ( $options ) {
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
     * @since 2.0.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        if ( 1 == pods_var( 'paragraph_allow_shortcode', $options ) )
            $value = do_shortcode( $value );

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
     * @since 2.0.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;

        if ( is_array( $value ) )
            $value = implode( "\n", $value );

        if ( 'plain' == pods_var( 'paragraph_format_type', $options ) )
            $field_type = 'textarea';
        elseif ( 'tinymce' == pods_var( 'paragraph_format_type', $options ) )
            $field_type = 'tinymce';
        elseif ( 'cleditor' == pods_var( 'paragraph_format_type', $options ) )
            $field_type = 'cleditor';
        else {
            // Support custom WYSIWYG integration
            do_action( 'pods_form_ui_field_paragraph_wysiwyg_' . pods_var( 'paragraph_format_type', $options ), $name, $value, $options, $pod, $id );
            do_action( 'pods_form_ui_field_paragraph_wysiwyg', pods_var( 'paragraph_format_type', $options ), $name, $value, $options, $pod, $id );
            return;
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Build regex necessary for JS validation
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param string $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function regex ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        return false;
    }

    /**
     * Validate a value before it's saved
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function validate ( &$value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        return true;
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
     * @since 2.0.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $options = (array) $options;

        if ( 1 == pods_var( 'paragraph_allow_html', $options ) ) {
            if ( 0 < strlen( pods_var( 'paragraph_allowed_html_tags', $options ) ) )
                $value = strip_tags( $value, pods_var( 'paragraph_allowed_html_tags', $options ) );
        }
        else
            $value = strip_tags( $value );

        if ( 1 != pods_var( 'paragraph_allow_shortcode', $options ) )
            $value = strip_shortcodes( $value );

        return $value;
    }

    /**
     * Perform actions after saving to the DB
     *
     * @param mixed $value
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param object $params
     *
     * @since 2.0.0
     */
    public function post_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {

    }

    /**
     * Perform actions before deleting from the DB
     *
     * @param string $name
     * @param string $pod
     * @param int $id
     * @param object $api
     *
     * @since 2.0.0
     */
    public function pre_delete ( $id = null, $name = null, $options = null, $pod = null ) {

    }

    /**
     * Perform actions after deleting from the DB
     *
     * @param int $id
     * @param string $name
     * @param array $options
     * @param array $pod
     *
     * @since 2.0.0
     */
    public function post_delete ( $id = null, $name = null, $options = null, $pod = null ) {

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
     * @since 2.0.0
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {

    }
}