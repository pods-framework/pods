<?php
/**
 * @package Pods\Fields
 */
class PodsField_Color extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'color';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'Color Picker';

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
     * @return array
     * @since 2.0.0
     */
    public function options () {
        $options = array();

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
    public function schema ( $options = null ) {
        $schema = 'VARCHAR(7)';

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
     * @since 2.0.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
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
        global $wp_version;

        $options = (array) $options;

        if ( is_array( $value ) )
            $value = implode( ' ', $value );

        // Farbtastic for 3.4 and before
        if ( version_compare( $wp_version, '3.5-alpha', '<' ) )
            pods_view( PODS_DIR . 'ui/fields/farbtastic.php', compact( array_keys( get_defined_vars() ) ) );
        // WP Color Picker for 3.5+
        else
            pods_view( PODS_DIR . 'ui/fields/color.php', compact( array_keys( get_defined_vars() ) ) );
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
     * @param array $params
     *
     * @return array|bool
     * @since 2.0.0
     */
    public function validate ( &$value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        $errors = array();

        $check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

        if ( is_array( $check ) )
            $errors = $check;
        else {
            $color = str_replace( '#', '', $check );

            if ( 0 < strlen( $value ) && strlen( $check ) < 1 ) {
                if ( 1 == pods_var( 'required', $options ) )
                    $errors[] = __( 'This field is required.', 'pods' );
                else {
                    // @todo Ask for a specific format in error message
                    $errors[] = __( 'Invalid value provided for this field.', 'pods' );
                }
            }
            elseif ( 3 != strlen( $color ) && 6 != strlen( $color ) && 1 != empty( $color ) )
                $errors[] = __( 'Invalid Hex Color value provided for this field.', 'pods' );
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
     * @return mixed|string
     * @since 2.0.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $options = (array) $options;

        $value = str_replace( '#', '', $value );

        if ( 0 < strlen( $value ) )
            $value = '#' . $value;

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
     * @return mixed|string
     * @since 2.0.0
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        if ( !empty( $value ) )
            $value = $value . ' <span style="display:inline-block;width:25px;height:25px;border:1px solid #333;background-color:' . $value . '"></span>';

        return $value;
    }
}
