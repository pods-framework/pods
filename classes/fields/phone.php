<?php
/**
 * @package Pods\Fields
 */
class PodsField_Phone extends PodsField {

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
    public static $type = 'phone';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Phone';

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
                'default' => '999-999-9999 x999',
                'type' => 'pick',
                'data' => array(
                    __( 'US', 'pods' ) => array(
                        '999-999-9999 x999' => '123-456-7890 x123',
                        '(999) 999-9999 x999' => '(123) 456-7890 x123',
                        '999.999.9999 x999' => '123.456.7890 x123'
                    ),
                    __( 'International', 'pods' ) => array(
                        'international' => __( 'Any (no validation available)', 'pods' )
                    )
                )
            ),
            self::$type . '_options' => array(
                'label' => __( 'Phone Options', 'pods' ),
                'group' => array(
                    self::$type . '_enable_phone_extension' => array(
                        'label' => __( 'Enable Phone Extension?', 'pods' ),
                        'default' => 1,
                        'type' => 'boolean'
                    )
                )
            ),
            self::$type . '_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 25,
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
        $length = (int) pods_var( self::$type . '_max_length', $options, 25, null, true );

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

        $field_type = 'phone';

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
                    $errors[] = sprintf( __( 'Invalid phone number provided for the field %s.', 'pods' ), $label );
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

        if ( 'international' == pods_var( self::$type . '_format', $options ) ) {
            // no validation/changes
        }
        else {
            // Clean input
            $number = preg_replace( '/([^0-9ext])/', '', $value );

            $number = str_replace(
                array( '-', '.', 'ext', 'x', 't', 'e', '(', ')' ),
                array( '', '', '|', '|', '', '', '', '', ),
                $number
            );

            // Get extension
            $extension = explode( '|', $number );
            if ( 1 < count( $extension ) ) {
                $number = preg_replace( '/([^0-9])/', '', $extension[ 0 ] );
                $extension = preg_replace( '/([^0-9])/', '', $extension[ 1 ] );
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
            if ( '(999) 999-9999 x999' == pods_var( self::$type . '_format', $options ) ) {
                if ( 2 == count( $numbers ) )
                    $value = implode( '-', $numbers );
                else
                    $value = '(' . $numbers[ 0 ] . ') ' . $numbers[ 1 ] . '-' . $numbers[ 2 ];
            }
            elseif ( '999.999.9999 x999' == pods_var( self::$type . '_format', $options ) )
                $value = implode( '.', $numbers );
            else //if ( '999-999-9999 x999' == pods_var( self::$type . '_format', $options ) )
                $value = implode( '-', $numbers );

            // Add extension
            if ( 1 == pods_var( self::$type . '_enable_phone_extension', $options ) && 0 < strlen( $extension ) )
                $value .= ' x' . $extension;
        }

        return $value;
    }
}
