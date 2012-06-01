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
                'data' => array(
                    'plain' => 'Plain Text',
                    'email' => 'E-mail Address (example@mail.com)',
                    'website' => 'Website (http://www.example.com/)',
                    'phone' => 'Phone Number'
                )
            ),
            'text_format_website' => array(
                'label' => 'Format',
                'depends-on' => array( 'text_format_type' => 'website' ),
                'default' => 'normal',
                'type' => 'pick',
                'data' => array(
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
                'depends-on' => array( 'text_format_type' => 'phone' ),
                'default' => '999-999-9999 x999',
                'type' => 'pick',
                'data' => array(
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
            'phone_options' => array(
                'label' => 'Phone Options',
                'depends-on' => array( 'text_format_type' => 'phone' ),
                'group' => array(
                    'text_enable_phone_extension' => array(
                        'label' => 'Enable Phone Extension?',
                        'default' => 1,
                        'type' => 'boolean'
                    )
                )
            ),
            'output_options' => array(
                'label' => 'Output Options',
                'depends-on' => array( 'text_format_type' => 'plain' ),
                'group' => array(
                    'text_allow_shortcode' => array(
                        'label' => 'Allow Shortcodes?',
                        'default' => 1,
                        'type' => 'boolean'
                    ),
                    'text_allow_html' => array(
                        'label' => 'Allow HTML?',
                        'default' => 1,
                        'type' => 'boolean'
                    )
                )
            ),
            'text_allowed_html_tags' => array(
                'label' => 'Allowed HTML Tags',
                'depends-on' => array( 'text_allow_html' => 1 ),
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
        $options = (array) $options;

        if ( 1 == $options[ 'text_allow_html' ] ) {
            if ( 0 < strlen( $options[ 'text_allowed_html_tags' ] ) )
                $value = strip_tags( $value, $options[ 'text_allowed_html_tags' ] );
        }
        else
            $value = strip_tags( $value );

        if ( 1 != $options[ 'text_allow_shortcode' ] )
            $value = strip_shortcodes( $value );
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

        $field_type = 'text';

        if ( 'email' == $options[ 'text_format_type' ] )
            $field_type = 'email';
        elseif ( 'website' == $options[ 'text_format_type' ] )
            $field_type = 'url';
        elseif ( 'phone' == $options[ 'text_format_type' ] )
            $field_type = 'phone';

        if ( !isset( $options[ 'regex_validation' ] ) || empty( $options[ 'regex_validation' ] ) )
            $options[ 'regex_validation' ] = $this->regex( $options );

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( $name, $value, self::$type, $options, $pod, $id ) );
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
    public function display ( &$value, $name, $options, $fields, $pod, $id ) {
        if ( 1 == $options[ 'text_allow_shortcode' ] )
            $value = do_shortcode( $value );
    }

    /**
     * Make regex from options
     *
     * @param array $options
     *
     * @since 2.0.0
     */
    public function regex ( $options ) {
        $regex = false;

        if ( 'email' == $options[ 'text_format_type' ] ) {
            $regex = '(?:[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"'
                . '(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])*")@'
                . '(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}'
                . '(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])';
        }
        elseif ( 'website' == $options[ 'text_format_type' ] && 0 < strlen( $options[ 'text_format_website' ] ) ) {
            $regex = false; // @todo build regex
        }
        elseif ( 'phone' == $options[ 'text_format_type' ] && 0 < strlen( $options[ 'text_format_phone' ] ) ) {
            $regex = false; // @todo build regex
        }

        return $regex;
    }
}