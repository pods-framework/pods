<?php
/**
 * @package Pods\Fields
 */
class PodsField_Website extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0
     */
    public static $group = 'Text';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'website';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Website';

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
     * @param array $options
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
            self::$type . '_format' => array(
                'label' => __( 'Format', 'pods' ),
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
            self::$type . '_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 255,
                'type' => 'number',
                'help' => __( 'Set to -1 for no limit', 'pods' )
            ),
            self::$type . '_html5' => array(
                'label' => __( 'Enable HTML5 Input Field?', 'pods' ),
                'default' => apply_filters( 'pods_form_ui_field_html5', 0, self::$type ),
                'type' => 'boolean'
            )/*,
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
        $length = (int) pods_var( self::$type . '_max_length', $options, 255 );

        $schema = 'VARCHAR(' . $length . ')';

        if ( 255 < $length || $length < 1 )
            $schema = 'LONGTEXT';

        return $schema;
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
            $value = implode( ' ', $value );

        $field_type = 'website';

        if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
            if ( pods_var( 'read_only', $options, false ) ) {
                $options[ 'readonly' ] = true;

                $field_type = 'text';
            }
            else
                return;
        }
        elseif ( !pods_has_permissions( $options ) && pods_var( 'read_only', $options, false ) ) {
            $options[ 'readonly' ] = true;

            $field_type = 'text';
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
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
     * @since 2.0
     */
    public function validate ( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        $errors = array();

        $label = strip_tags( pods_var_raw( 'label', $options, ucwords( str_replace( '_', ' ', $name ) ) ) );

        $check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

        if ( is_array( $check ) )
            $errors = $check;
        else {
            if ( 0 < strlen( $value ) && strlen( $check ) < 1 ) {
                if ( 1 == pods_var( 'required', $options ) )
                    $errors[] = sprintf( __( 'The %s field is required.', 'pods' ), $label );
                else
                    $errors[] = sprintf( __( 'Invalid website provided for the field %s.', 'pods' ), $label );
            }
        }

        if ( !empty( $errors ) )
            return $errors;

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
     * @since 2.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $options = (array) $options;

        if ( is_array( $value ) ) {
            if ( isset( $value[ 'scheme' ] ) )
                $value = $this->build_url( $value );
            else
                $value = implode( '', $value );
        }

        if ( false === strpos( $value, '://' ) && 0 !== strpos( $value, '//' ) )
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

            if ( 'normal' == pods_var( self::$type . '_format', $options ) )
                $value = $this->build_url( $url );
            elseif ( 'no-www' == pods_var( self::$type . '_format', $options ) ) {
                if ( 0 === strpos( $url[ 'host' ], 'www.' ) )
                    $url[ 'host' ] = substr( $url[ 'host' ], 4 );

                $value = $this->build_url( $url );
            }
            elseif ( 'force-www' == pods_var( self::$type . '_format', $options ) ) {
                if ( false !== strpos( $url[ 'host' ], '.' ) && false === strpos( $url[ 'host' ], '.', 1 ) )
                    $url[ 'host' ] = 'www.' . $url[ 'host' ];

                $value = $this->build_url( $url );
            }
            elseif ( 'no-http' == pods_var( self::$type . '_format', $options ) ) {
                $value = $this->build_url( $url );
                $value = str_replace( trim( $url[ 'scheme' ] . '://', ':' ), '', $value );

                if ( '/' == $url[ 'path' ] )
                    $value = trim( $value, '/' );
            }
            elseif ( 'no-http-no-www' == pods_var( self::$type . '_format', $options ) ) {
                if ( 0 === strpos( $url[ 'host' ], 'www.' ) )
                    $url[ 'host' ] = substr( $url[ 'host' ], 4 );

                $value = $this->build_url( $url );
                $value = str_replace( trim( $url[ 'scheme' ] . '://', ':' ), '', $value );

                if ( '/' == $url[ 'path' ] )
                    $value = trim( $value, '/' );
            }
            elseif ( 'no-http-force-www' == pods_var( self::$type . '_format', $options ) ) {
                if ( false !== strpos( $url[ 'host' ], '.' ) && false === strpos( $url[ 'host' ], '.', 1 ) )
                    $url[ 'host' ] = 'www.' . $url[ 'host' ];

                $value = $this->build_url( $url );
                $value = str_replace( trim( $url[ 'scheme' ] . '://', ':' ), '', $value );

                if ( '/' == $url[ 'path' ] )
                    $value = trim( $value, '/' );
            }
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
        if ( 'website' == pods_var( self::$type . '_format_type', $options ) && 0 < strlen( pods_var( self::$type . '_format', $options ) ) )
            $value = make_clickable( $value );

        return $value;
    }

    /**
     * Build an url
     *
     * @param array|string $url
     *
     * @return string
     */
    public function build_url ( $url ) {
        if ( function_exists( 'http_build_url' ) )
            return http_build_url( $url );

        $defaults = array(
            'scheme' => 'http',
            'host' => '',
            'path' => '/',
            'query' => '',
            'fragment' => ''
        );

        $url = array_merge( $defaults, (array) $url );

        $new_url = trim( $url[ 'scheme' ] . '://', ':' ) . $url[ 'host' ] . '/' . ltrim( $url[ 'path' ], '/' );

        if ( !empty( $url[ 'query' ] ) )
            $new_url .= '?' . ltrim( $url[ 'query' ], '?' );

        if ( !empty( $url[ 'fragment' ] ) )
            $new_url .= '#' . ltrim( $url[ 'fragment' ], '#' );

        return $new_url;
    }
}
