<?php
/**
 * @package Pods\Fields
 */
class Pods_Field_Password extends Pods_Field {

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
    public static $type = 'password';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Password';

    /**
     * Field Type Preparation
     *
     * @var string
     * @since 2.0
     */
    public static $prepare = '%s';

    /**
     * {@inheritDocs}
     */
    public function __construct() {

    }

    /**
     * {@inheritDocs}
     */
    public function options() {
        $options = array(
            self::$type . '_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 255,
                'type' => 'number',
                'help' => __( 'Set to -1 for no limit', 'pods' )
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
     * {@inheritDocs}
     */
    public function schema( $options = null ) {
        $length = (int) pods_v( self::$type . '_max_length', $options, 255 );

        $schema = 'VARCHAR(' . $length . ')';

        if ( 255 < $length || $length < 1 )
            $schema = 'LONGTEXT';

        return $schema;
    }

    /**
     * {@inheritDocs}
     */
    public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $form_field_type = Pods_Form::$field_type;

        if ( is_array( $value ) )
            $value = implode( ' ', $value );

        if ( isset( $options[ 'name' ] ) && false === Pods_Form::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
            if ( pods_v( 'read_only', $options, false ) )
                $options[ 'readonly' ] = true;
            else
                return;
        }
        elseif ( !pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) )
            $options[ 'readonly' ] = true;

        pods_view( PODS_DIR . 'ui/fields/password.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * {@inheritDocs}
     */
    public function validate( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        $errors = array();

        $check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

        if ( is_array( $check ) )
            $errors = $check;
        else {
            if ( 0 < strlen( $value ) && strlen( $check ) < 1 ) {
                if ( 1 == pods_v( 'required', $options ) )
                    $errors[] = __( 'This field is required.', 'pods' );
            }
        }

        if ( !empty( $errors ) )
            return $errors;

        return true;
    }
}
