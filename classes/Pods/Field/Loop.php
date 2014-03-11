<?php
/**
 * @package Pods\Fields
 */
class Pods_Field_Loop extends Pods_Field {

    /**
     * Field Type Group
     *
     * @var string
     * @since 2.0
     */
    public static $group = 'Relationships / Media';

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0
     */
    public static $type = 'loop';

    /**
     * Field Type Label
     *
     * @var string
     * @since 2.0
     */
    public static $label = 'Loop (Repeatable)';

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
     * {@inheritDocs}
     */
    public function schema( $options = null ) {
        $schema = 'LONGTEXT';

        return $schema;
    }

    /**
     * {@inheritDocs}
     */
    public function display( $value = null, $name = null, $options = null, $pod = null, $id = null ) {
        $fields = null;

        if ( is_object( $pod ) && isset( $pod->fields ) )
            $fields = $pod->fields;

        return pods_serial_comma( $value, array( 'field' => $name, 'fields' => $fields ) );
    }

    /**
     * {@inheritDocs}
     */
    public function input( $name, $value = null, $options = null, $pod = null, $id = null ) {
        $form_field_type = Pods_Form::$field_type;

        pods_view( PODS_DIR . 'ui/fields/loop.php', compact( array_keys( get_defined_vars() ) ) );
    }

    /**
     * {@inheritDocs}
     */
    public function ui( $id, $value, $name = null, $options = null, $fields = null, $pod = null ) {
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
    public function simple_value( $value, $options, $raw = false ) {

        return $value;

    }
}
