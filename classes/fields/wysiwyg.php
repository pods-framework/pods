<?php
/**
 *
 */
class PodsField_WYSIWYG extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0.0
     */
    public static $group = 'Paragraph';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'wysiwyg';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'WYSIWYG (Visual Editor)';

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
            'wysiwyg_editor' => array(
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
                    )
            ),
            'output_options' => array(
                'label' => __( 'Output Options', 'pods' ),
                'group' => array(
                    'wysiwyg_allow_shortcode' => array(
                        'label' => __( 'Allow Shortcodes?', 'pods' ),
                        'default' => 0,
                        'type' => 'boolean',
                        'dependency' => true
                    )
                )
            ),
            'wysiwyg_allowed_html_tags' => array(
                'label' => __( 'Allowed HTML Tags', 'pods' ),
                'default' => '',
                'type' => 'text'
            ),
            'wysiwyg_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 0,
                'type' => 'number'
            ),
            'wysiwyg_size' => array(
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
        $value = $this->strip_html( $value, $options );

        if ( 1 == pods_var( 'wysiwyg_allow_shortcode', $options, 0 ) )
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

        if ( 'tinymce' == pods_var( 'wysiwyg_format_type', $options ) )
            $field_type = 'tinymce';
        elseif ( 'cleditor' == pods_var( 'wysiwyg_format_type', $options ) )
            $field_type = 'cleditor';
        else {
            // Support custom WYSIWYG integration
            do_action( 'pods_form_ui_field_wysiwyg_' . pods_var( 'wysiwyg_editor', $options ), $name, $value, $options, $pod, $id );
            do_action( 'pods_form_ui_field_wysiwyg', pods_var( 'wysiwyg_editor', $options ), $name, $value, $options, $pod, $id );

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
     * @since 2.0.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $value = $this->strip_html( $value, $options );

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
     * @since 2.0.0
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
        $options = (array) $options;

        $allowed_html_tags = '';

        if ( 0 < strlen( pods_var( 'wysiwyg_allowed_html_tags', $options ) ) ) {
            $allowed_html_tags = explode( ' ', trim( pods_var( 'wysiwyg_allowed_html_tags', $options ) ) );
            $allowed_html_tags = '<' . implode( '><', $allowed_html_tags ) . '>';
        }

        if ( !empty( $allowed_html_tags ) && '<>' != $allowed_html_tags )
            $value = strip_tags( $value, $allowed_html_tags );

        return $value;
    }
}
