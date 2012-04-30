<?php
class PodsField_Text extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    protected static $type = 'text';

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
            'text_format_type' => array(
                'default' => 'plain',
                'type' => 'pick',
                'values' => array(
                    'plain' => 'Plain Text',
                    'email' => 'E-mail Address (example@mail.com)',
                    'website' => 'Website (http://www.example.com/)',
                    'phone' => 'Phone Number'
                )
            ),

            'text_format_website' => array(
                'default' => 'normal',
                'type' => 'pick',
                'values' => array(
                    'normal' => 'http://example.com/',
                    'no-www' => 'http://example.com/ (remove www)',
                    'force-www' => 'http://www.example.com/ (force www if no sub-domain provided)',
                    'no-http' => 'example.com',
                    'no-http-no-www' => 'example.com (force removal of www)',
                    'no-http-force-www' => 'www.example.com (force www if no sub-domain provided)'
                )
            ),
            'text_format_phone' => array(
                'default' => '999-999-9999 x999',
                'type' => 'pick',
                'values' => array(
                    'US' => array(
                        '999-999-9999 x999' => '123-456-7890 x123',
                        '(999) 999-9999 x999' => '(123) 456-7890 x123',
                        '999.999.9999 x999' => '123.456.7890 x123'
                    ),
                    'International' => array(
                        '+9 999-999-9999 x999' => '+1 123-456-7890 x123',
                        '+9 (999) 999-9999 x999' => '+1 (123) 456-7890 x123',
                        '+9 999.999.9999 x999' => '+1 123.456.7890 x123'
                    )
                )
            ),
            'text_enable_phone_extension' => array(
                'default' => 1,
                'type' => 'boolean'
            ),
            'text_allow_html' => array(
                'default' => 1,
                'type' => 'boolean'
            ),
            'text_allowed_html_tags' => array(
                'default' => 'strong em a ul ol li b i',
                'type' => 'text'
            ),
            'max_length' => 255,
            'size' => 'medium',
        );
        return $options;
    }

    /**
     * Change the value before it's sent to be displayed or saved
     *
     * @param mixed $value
     * @param array $options
     *
     * @since 2.0.0
     */
    public function value ( &$value, $options ) {
        // Only allow 0 / 1
        $value = ( 1 == (int) $value ? 1 : 0 );
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
        $attributes = array();
        $attributes[ 'value' ] = 1;
        $attributes[ 'checked' ] = ( 1 == $value || true === $value ) ? 'CHECKED' : null;
        $attributes = self::merge_attributes( $attributes, $name, self::$type, $options );
        if ( isset( $options[ 'default' ] ) && strlen( $attributes[ 'value' ] ) < 1 )
            $attributes[ 'value' ] = $options[ 'default' ];
        $attributes[ 'value' ] = apply_filters( 'pods_form_ui_field_' . self::$type . '_value', $attributes[ 'value' ], $name, $attributes, $options );

        pods_view( PODS_DIR . 'ui/fields/checkbox.php', compact( $attributes, $name, $value, self::$type, $options, $pod, $id ) );
    }

    /**
     * Change the way the value of the field is displayed, optionally called with Pods::get
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
    public function display ( $value, $name, $options, $fields, $pod, $id ) {
        $yesno = array(
            1 => __( 'Yes', 'pods' ),
            0 => __( 'No', 'pods' )
        );

        // Handle options
        if ( isset( $field[ 'options' ][ 'boolean_yes_label' ] ) && 0 < strlen( $field[ 'options' ][ 'boolean_yes_label' ] ) )
            $yesno[ 1 ] = $field[ 'options' ][ 'boolean_yes_label' ];
        if ( isset( $field[ 'options' ][ 'boolean_no_label' ] ) && 0 < strlen( $field[ 'options' ][ 'boolean_no_label' ] ) )
            $yesno[ 0 ] = $field[ 'options' ][ 'boolean_no_label' ];

        // Deprecated handling for 1.x
        if ( parent::$deprecated ) {
            return $value;
        }

        return $yesno[ $value ];
    }
}