<?php
class PodsField_Boolean extends PodsField {

    /**
     * Field Type Identifier
     *
     * @var string
     * @since 2.0.0
     */
    protected static $type = 'boolean';

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
            'boolean_format_type' => array(
                'default' => 'checkbox',
                'type' => 'pick',
                'values' => array(
                    'checkbox' => 'Checkbox',
                    'radio' => 'Radio Buttons',
                    'dropdown' => 'Drop Down'
                )
            ),
            'boolean_yes_label' => array(
                'default' => 'Yes',
                'type' => 'text'
            ),
            'boolean_no_label' => array(
                'default' => 'No',
                'type' => 'text'
            )
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
        // Only allow 0 / 1
        $value = ( 1 == (int) $value ? 1 : 0 );
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
        $attributes = array();
        $attributes[ 'type' ] = 'checkbox';
        $attributes[ 'value' ] = 1;
        $attributes[ 'checked' ] = ( 1 == $value || true === $value ) ? 'CHECKED' : null;
        $attributes = self::merge_attributes( $attributes, $name, self::$type, $options );
        if ( isset( $options[ 'default' ] ) && strlen( $attributes[ 'value' ] ) < 1 )
            $attributes[ 'value' ] = $options[ 'default' ];
        $attributes[ 'value' ] = apply_filters( 'pods_form_ui_field_' . self::$type . '_value', $attributes[ 'value' ], $name, $attributes, $options );

        if ( $options[''] )
        pods_view( PODS_DIR . 'ui/fields/checkbox.php', compact( $attributes, $name, $value, self::$type, $options, $pod, $id ) );
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
    public function display ( $value, $name, $options, $fields, $pod, $id ) {
        $yesno = array(
            1 => __( 'Yes', 'pods' ),
            0 => __( 'No', 'pods' )
        );

        // Handle options
        if ( isset( $field[ 'options' ][ 'boolean_yes_label' ] ) && 0 < strlen( $field[ 'options' ][ 'boolean_yes_label' ] ) )
            $yesno[ 1 ] = $field[ 'options' ][ 'boolean_yes_label' ];
        if ( isset( $field[ 'options' ][ 'boolean_no_label' ] ) && 0 < strlen( $field[ 'options' ][ 'boolean_no_label' ] ) )
            $yesno[ 0 ] = $field[ 'options' ][ 'boolean_no_label' ];

        // Deprecated handling for 1.x
        if ( parent::$deprecated ) {
            return $value;
        }

        return $yesno[ $value ];
    }
}