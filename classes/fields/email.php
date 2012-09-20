<?php
/**
 * @package Pods\Fields
 */
class PodsField_Email extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0.0
     */
    public static $group = 'Text';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'email';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'E-mail';

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
     *
     * @since 2.0.0
     */
    public function options () {
        $options = array(
            'email_max_length' => array(
                'label' => __( 'Maximum Length', 'pods' ),
                'default' => 255,
                'type' => 'number'
            ),
            'email_html5' => array(
                'label' => __( 'Enable HTML5 Input Field?', 'pods' ),
                'default' => apply_filters( 'pods_form_ui_field_html5', 0, self::$type ),
                'type' => 'boolean'
            )/*,
            'email_size' => array(
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
     * @since 2.0.0
     */
    public function schema ( $options = null ) {
        $length = (int) pods_var( 'email_max_length', $options, 255, null, true );

        if ( $length < 1 )
            $length = 255;

        $schema = 'VARCHAR(' . $length . ')';

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
     * @since 2.0.0
     */
    public function input ( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $options = (array) $options;

        if ( is_array( $value ) )
            $value = implode( ' ', $value );

        pods_view( PODS_DIR . 'ui/fields/email.php', compact( array_keys( get_defined_vars() ) ) );
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
     * @since 2.0.0
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
     * @return array|bool
     * @since 2.0.0
     */
    public function validate ( &$value, $name = null, $options = null, $fields = null, $pod = null, $id = null, $params = null ) {
        $errors = array();

        $check = $this->pre_save( $value, $id, $name, $options, $fields, $pod, $params );

        if ( is_array( $check ) )
            $errors = $check;
        else {
            if ( 0 < strlen( $value ) && strlen( $check ) < 1 ) {
                if ( 1 == pods_var( 'required', $options ) )
                    $errors[] = __( 'This field is required.', 'pods' );
                else
                    $errors[] = __( 'Invalid e-mail provided.', 'pods' );
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
     * @return mixed|string
     * @since 2.0.0
     */
    public function pre_save ( $value, $id = null, $name = null, $options = null, $fields = null, $pod = null, $params = null ) {
        $options = (array) $options;

        if ( !is_email( $value ) )
            $value = '';

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
        return '<a href="mailto:' . esc_attr( $value ) . '">' . $value . '</a>';
    }
}
