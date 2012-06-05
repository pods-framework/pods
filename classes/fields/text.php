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
                'label' => __( 'Format Type', 'pods' ),
                'default' => 'plain',
                'type' => 'pick',
                'data' => array(
                    'plain' => __( 'Plain Text', 'pods' ),
                    'email' => array(
                        'label' => __( 'E-mail Address (example@mail.com)', 'pods' ),
                        'regex' =>
                            '/(?:[a-z0-9!#$%&\'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+/=?^_`{|}~-]+)*|"'
                            . '(?:[\x01-\x08\x0b\x0c\x0e-\x1f\x21\x23-\x5b\x5d-\x7f]|\\[\x01-\x09\x0b'
                            . '\x0c\x0e-\x7f])*")@(?:(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9]'
                            . '(?:[a-z0-9-]*[a-z0-9])?|\[(?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.){3}'
                            . '(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?|[a-z0-9-]*[a-z0-9]:(?:[\x01-\x08'
                            . '\x0b\x0c\x0e-\x1f\x21-\x5a\x53-\x7f]|\\[\x01-\x09\x0b\x0c\x0e-\x7f])+)\])/g' // @todo test regex
                    ),
                    'website' => array(
                        'label' => __( 'Website (http://www.example.com/)', 'pods' )
                        //'regex' => false // @todo test regex
                    ),
                    'phone' => array(
                        'label' => __( 'Phone Number', 'pods' ),
                        'regex' => '/[0-9 \.\-\(\)ext]/g' // @todo test regex
                    )
                )
            ),
            'text_format_website' => array(
                'label' => __( 'Format', 'pods' ),
                'depends-on' => array( 'text_format_type' => 'website' ),
                'default' => 'normal',
                'type' => 'pick',
                'data' => array(
                    'normal' => __( 'http://example.com/', 'pods' ),
                    'no-www' => __( 'http://example.com/ (remove www)', 'pods' ),
                    'force-www' => __( 'http://www.example.com/ (force www if no sub-domain provided)', 'pods' ),
                    'no-http' => __( 'example.com', 'pods' ),
                    'no-http-no-www' => __( 'example.com (force removal of www)', 'pods' ),
                    'no-http-force-www' => __( 'www.example.com (force www if no sub-domain provided)', 'pods' )
                )
            ),
            'text_format_phone' => array(
                'label' => __( 'Format', 'pods' ),
                'depends-on' => array( 'text_format_type' => 'phone' ),
                'default' => '999-999-9999 x999',
                'type' => 'pick',
                'data' => array(
                    __( 'US', 'pods' ) => array(
                        '999-999-9999 x999' => __( '123-456-7890 x123', 'pods' ),
                        '(999) 999-9999 x999' => __( '(123) 456-7890 x123', 'pods' ),
                        '999.999.9999 x999' => __( '123.456.7890 x123', 'pods' )
                    ),
                    __( 'International', 'pods' ) => array(
                        'international' => __( 'Any (no validation available)', 'pods' )
                    )
                )
            ),
            'phone_options' => array(
                'label' => __( 'Phone Options', 'pods' ),
                'depends-on' => array( 'text_format_type' => 'phone' ),
                'group' => array(
                    'text_enable_phone_extension' => array(
                        'label' => __( 'Enable Phone Extension?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean'
                    )
                )
            ),
            'output_options' => array(
                'label' => __( 'Output Options', 'pods' ),
                'depends-on' => array( 'text_format_type' => 'plain' ),
                'group' => array(
                    'text_allow_shortcode' => array(
                        'label' => __( 'Allow Shortcodes?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean'
                    ),
                    'text_allow_html' => array(
                        'label' => __( 'Allow HTML?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean'
                    )
                )
            ),
            'text_allowed_html_tags' => array(
                'label' => __( 'Allowed HTML Tags', 'pods' ),
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
        if ( 1 == $options[ 'text_allow_shortcode' ] )
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
    public function input ( $name, $value = null, $options = null, &$pod = null, $id = null ) {
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
     * Validate a value before it's saved
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
    public function validate ( &$value, $name, $options ) {
        $errors = array();

        $check = $value; // $check will be passed by reference, but we want $value to stay the same
        $this->pre_save( $check, $name, $options );
        if ( 0 < strlen( $value ) && strlen( $check ) < 1 ) {
            if ( 1 == $options[ 'required' ] )
                $errors[] = __( 'This field is required.', 'pods' );
            else {
                // @todo Ask for a specific format in error message
                $errors[] = __( 'Invalid value provided for this field.', 'pods' );
            }
        }

        if ( empty( $errors ) )
            return true;
    }

    /**
     * Change the value or perform actions after validation but before saving to the DB
     *
     * @param string $value
     * @param string $name
     * @param array $options
     *
     * @since 2.0.0
     */
    public function pre_save ( &$value, $name, $options ) {
        $options = (array) $options;

        if ( 'plain' == $options[ 'text_format_type' ] ) {
            if ( 1 == $options[ 'text_allow_html' ] ) {
                if ( 0 < strlen( $options[ 'text_allowed_html_tags' ] ) )
                    $value = strip_tags( $value, $options[ 'text_allowed_html_tags' ] );
            }
            else
                $value = strip_tags( $value );

            if ( 1 != $options[ 'text_allow_shortcode' ] )
                $value = strip_shortcodes( $value );
        }
        elseif ( 'email' == $options[ 'text_format_type' ] ) {
            if ( !is_email( $value ) )
                $value = '';
        }
        elseif ( 'website' == $options[ 'text_format_type' ] && 0 < strlen( $options[ 'text_format_website' ] ) ) {
            if ( false === strpos( $value, '://' ) )
                $value = 'http://' . $value;
            $url = @parse_url( $value );
            if ( empty( $url ) || count( $url ) < 2 )
                $value = '';
            else {
                $defaults = array(
                    'scheme' => 'http',
                    'host' => '',
                    'path' => '/',
                    'query' => '',
                    'fragment' => ''
                );
                $url = array_merge( $defaults, $url );
                if ( 'normal' == $options[ 'text_format_website' ] ) {
                    $value = http_build_url( $url );
                }
                elseif ( 'no-www' == $options[ 'text_format_website' ] ) {
                    if ( 0 === strpos( $url[ 'host' ], 'www.' ) )
                        $url[ 'host' ] = substr( $url[ 'host' ], 4 );
                    $value = http_build_url( $url );
                }
                elseif ( 'force-www' == $options[ 'text_format_website' ] ) {
                    if ( false !== strpos( $url[ 'host' ], '.' ) && false === strpos( $url[ 'host' ], '.', 1 ) )
                        $url[ 'host' ] = 'www.' . $url[ 'host' ];
                    $value = http_build_url( $url );
                }
                elseif ( 'no-http' == $options[ 'text_format_website' ] ) {
                    $value = http_build_url( $url );
                    $value = str_replace( $url[ 'scheme' ] . '://', '', $value );
                }
                elseif ( 'no-http-no-www' == $options[ 'text_format_website' ] ) {
                    if ( 0 === strpos( $url[ 'host' ], 'www.' ) )
                        $url[ 'host' ] = substr( $url[ 'host' ], 4 );
                    $value = http_build_url( $url );
                    $value = str_replace( $url[ 'scheme' ] . '://', '', $value );
                }
                elseif ( 'no-http-force-www' == $options[ 'text_format_website' ] ) {
                    if ( false !== strpos( $url[ 'host' ], '.' ) && false === strpos( $url[ 'host' ], '.', 1 ) )
                        $url[ 'host' ] = 'www.' . $url[ 'host' ];
                    $value = http_build_url( $url );
                    $value = str_replace( $url[ 'scheme' ] . '://', '', $value );
                }
            }
        }
        elseif ( 'phone' == $options[ 'text_format_type' ] && 0 < strlen( $options[ 'text_format_phone' ] ) ) {
            if ( 'international' == $options[ 'text_format_phone' ] ) {
                // no validation/changes
            }
            else {
                // Clean input
                $number = preg_replace( '/([^0-9ext])/g', ' ', $value );
                $number = str_replace(
                    array( '-', '.', 'ext', 'x', 't', 'e', '(', ')' ),
                    array( '', '', '|', '|', '', '', '', '', ),
                    $number
                );

                // Get extension
                $extension = explode( '|', $number );
                if ( 1 < count( $extension ) ) {
                    $number = preg_replace( '/([^0-9])/g', ' ', $extension[ 0 ] );
                    $extension = preg_replace( '/([^0-9])/g', ' ', $extension[ 1 ] );
                }
                else
                    $extension = '';

                // Build number array
                $numbers = str_split( $number, 3 );
                if ( isset( $numbers[ 3 ] ) ) {
                    $numbers[ 2 ] .= $numbers[ 3 ];
                    $numbers = array( $numbers[ 0 ], $numbers[ 1 ], $numbers[ 2 ] );
                }
                elseif ( isset( $numbers[ 1 ] ) )
                    $numbers = array( $numbers[ 0 ], $numbers[ 1 ] );

                // Format number
                elseif ( '(999) 999-9999 x999' == $options[ 'text_format_phone' ] ) {
                    if ( 2 == count( $numbers ) )
                        $value = implode( '-', $numbers );
                    else
                        $value = '(' . $numbers[ 0 ] . ') ' . $numbers[ 1 ] . '-' . $numbers[ 2 ];
                }
                elseif ( '999.999.9999 x999' == $options[ 'text_format_phone' ] )
                    $value = implode( '.', $numbers );
                else //if ( '999-999-9999 x999' == $options[ 'text_format_phone' ] )
                    $value = implode( '-', $numbers );

                // Add extension
                if ( 1 == $options[ 'text_enable_phone_extension' ] && false !== $extension )
                    $value .= ' x' . $extension;
            }

        }
    }

    /**
     * Customize the Pods UI manage table column output
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
    public function ui ( &$value, $name, $options, $fields, &$pod, $id ) {
        if ( 'website' == $options[ 'text_format_type' ] && 0 < strlen( $options[ 'text_format_website' ] ) )
            $value = make_clickable( $value );
    }
}