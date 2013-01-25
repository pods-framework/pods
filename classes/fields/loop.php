<?php
/**
 * @package Pods\Fields
 */
class PodsField_Loop extends PodsField {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0.0
     */
    public static $group = 'Relationships / Media';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    public static $type = 'loop';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0.0
     */
    public static $label = 'Loop (Repeatable)';

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
            'loop_limit' => array(
                'label' => __( 'Loop Limit', 'pods' ),
                'help' => __( 'help', 'pods' ),
                'default' => 0,
                'type' => 'number'
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
     * @since 2.0.0
     */
    public function schema ( $options = null ) {
        $schema = 'LONGTEXT';

        return $schema;
    }

    /**
     * Change the way the value of the field is displayed with Pods::get
     *
     * @param mixed $value
     * @param string $name
     * @param array $options
     * @param array $fields
     * @param array $pod
     * @param int $id
     *
     * @since 2.0.0
     */
    public function display ( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $fields = null;

        if ( is_object( $pod ) && isset( $pod->fields ) )
            $fields = $pod->fields;

        return pods_serial_comma( $value, $name, $fields );
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
        $form_field_type = PodsForm::$field_type;

        pods_view( PODS_DIR . 'ui/fields/loop.php', compact( array_keys( get_defined_vars() ) ) );
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
     * @since 2.0.0
     */
    public function ui ( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
        $value = $this->simple_value( $value, $options );

        return $this->display( $value, $name, $options, $pod, $id );
    }

    /**
     * Convert a simple value to the correct value
     *
     * @param mixed $value Value of the field
     * @param array $options Field options
     * @param boolean $raw Whether to return the raw list of keys (true) or convert to key=>value (false)
     */
    public function simple_value ( $value, $options, $raw = false ) {
        if ( isset( $options[ 'options' ] ) ) {
            $options = array_merge( $options[ 'options' ], $options );
            unset( $options[ 'options' ] );
        }

        return $value;
    }
}
