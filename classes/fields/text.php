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
                'label' => 'Format Type',
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
                'label' => 'Format',
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
                'label' => 'Format',
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
                'label' => 'Enable Phone Extension?',
                'default' => 1,
                'type' => 'boolean'
            ),
            'text_allow_html' => array(
                'label' => 'Allow HTML?',
                'default' => 1,
                'type' => 'boolean'
            ),
            'text_allow_shortcode' => array(
                'label' => 'Allow Shortcodes?',
                'default' => 1,
                'type' => 'boolean'
            ),
            'text_allowed_html_tags' => array(
                'label' => 'Allowed HTML Tags',
                'default' => 'strong em a ul ol li b i',
                'type' => 'text'
            ),
            'max_length' => 255,
            'size' => 'medium',
        );
        return $options;
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
        $attributes[ 'type' ] = 'text';
        if ( is_array( $value ) )
            $value = current( $value );
        $attributes[ 'value' ] = $value;
        $attributes = self::merge_attributes( $attributes, $name, self::$type, $options );
        if ( isset( $options[ 'default' ] ) && strlen( $attributes[ 'value' ] ) < 1 )
            $attributes[ 'value' ] = $options[ 'default' ];
        $attributes[ 'value' ] = apply_filters( 'pods_form_ui_field_' . self::$type . '_value', $attributes[ 'value' ], $name, $attributes, $options );

        pods_view( PODS_DIR . 'ui/fields/text.php', compact( $attributes, $name, $value, self::$type, $options, $pod, $id ) );
    }
}