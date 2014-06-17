<?php
/**
 * Handles boolean field type data and operations.
 *
 * @package Pods\Fields
 */
class PodsField_Boolean extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'boolean';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Yes / No';

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
     * @return array Array of available options
     *
     * @since 2.0
     */
    public function options () {
        $options = array(
            self::$type . '_format_type' => array(
                'label' => __( 'Input Type', 'pods' ),
                'default' => 'checkbox',
                'type' => 'pick',
                'data' => array(
                    'checkbox' => __( 'Checkbox', 'pods' ),
                    'radio' => __( 'Radio Buttons', 'pods' ),
                    'dropdown' => __( 'Drop Down', 'pods' )
                ),
                'dependency' => true
            ),
            self::$type . '_yes_label' => array(
                'label' => __( 'Yes Label', 'pods' ),
                'default' => __( 'Yes', 'pods' ),
                'type' => 'text'
            ),
            self::$type . '_no_label' => array(
                'label' => __( 'No Label', 'pods' ),
                'default' => __( 'No', 'pods' ),
                'type' => 'text'
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
     * @since 2.0
     */
    public function schema ( $options = null ) {
        $schema = 'BOOL DEFAULT 0';

        return $schema;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $pod
     * @param int $id
     *
     * @return mixed|null
     * @since 2.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $yesno = array(
            1 => pods_var_raw( self::$type . '_yes_label', $options ),
            0 => pods_var_raw( self::$type . '_no_label', $options )
        );

        // Deprecated handling for 1.x
        if ( !parent::$deprecated && isset( $yesno[ (int) $value ] ) )
            $value = $yesno[ (int) $value ];

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
     * @since 2.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;
        $form_field_type = PodsForm::$field_type;

        if ( is_array( $value ) )
            $value = !empty( $value );

        $field_type = 'checkbox';

        if ( 'radio' == pods_v( self::$type . '_format_type', $options ) )
            $field_type = 'radio';
        elseif ( 'dropdown' == pods_v( self::$type . '_format_type', $options ) )
            $field_type = 'select';

        if ( isset( $options[ 'name' ] ) && false === PodsForm::permission( self::$type, $options[ 'name' ], $options, null, $pod, $id ) ) {
            if ( pods_v( 'read_only', $options, false ) )
                $options[ 'readonly' ] = true;
            else
                return;
        }
        elseif ( !pods_has_permissions( $options ) && pods_v( 'read_only', $options, false ) )
            $options[ 'readonly' ] = true;

		if ( 1 === $value || '1' === $value || true === $value ) {
            $value = 1;
        }
        else {
            $value = 0;
        }

        pods_view( PODS_DIR . 'ui/fields/' . $field_type . '.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * Get the data from the field
     *
     * @param string $name The name of the field
     * @param string|array $value The value of the field
     * @param array $options
     * @param array $pod
     * @param int $id
     * @param boolean $in_form
     *
     * @return array Array of possible field data
     *
     * @since 2.0
     */
    public function data ( $name, $value = null, $options = null, $pod = null, $id = null, $in_form = true ) {
        if ( 'checkbox' != pods_v( self::$type . '_format_type', $options ) ) {
            $data = array(
                1 => pods_var_raw( self::$type . '_yes_label', $options ),
                0 => pods_var_raw( self::$type . '_no_label', $options )
            );
        }
        else {
            $data = array(
                1 => pods_var_raw( self::$type . '_yes_label', $options )
            );
        }

        return $data;
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
     * @return bool
     * @since 2.0
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
     * @param null $params
     *
     * @return bool
     * @since 2.0
     */
    public function validate ( $value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
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
     * @return int|mixed
     * @since 2.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        // Only allow 0 / 1
        if ( 'yes' === strtolower( $value ) || '1' === (string) $value )
            $value = 1;
        elseif ( 'no' === strtolower( $value ) || '0' === (string) $value )
            $value = 0;
        elseif ( strtolower( pods_var_raw( self::$type . '_yes_label', $options, __( 'Yes', 'pods' ), null, true ) ) === strtolower( $value ) )
            $value = 1;
        elseif ( strtolower( pods_var_raw( self::$type . '_no_label', $options, __( 'No', 'pods' ), null, true ) ) === strtolower( $value ) )
            $value = 0;
        else
            $value = ( 0 === (int) $value ? 0 : 1 );

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
        $yesno = array(
            1 => pods_var_raw( self::$type . '_yes_label', $options, __( 'Yes', 'pods' ), null, true ),
            0 => pods_var_raw( self::$type . '_no_label', $options, __( 'No', 'pods' ), null, true )
        );

        if ( isset( $yesno[ (int) $value ] ) )
            $value = strip_tags( $yesno[ (int) $value ], '<strong><a><em><span><img>' );

        return $value;
    }
}
